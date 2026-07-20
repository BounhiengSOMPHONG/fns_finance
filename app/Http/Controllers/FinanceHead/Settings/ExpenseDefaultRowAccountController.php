<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCatalogItem;
use App\Models\ExpensePattern;
use App\Models\ExpensePlanRow;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseDefaultRowAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $years = PlanningYear::orderByDesc('year')->get();
        $planningYear = $request->filled('planning_year_id')
            ? $years->firstWhere('id', (int) $request->integer('planning_year_id'))
            : $years->firstWhere('is_active', true);
        $planningYear ??= $years->first();

        $rows = ExpenseCatalogItem::with(['chartOfAccount.parent', 'subsection.section.planningYear'])
            ->when($planningYear, function ($builder) use ($planningYear): void {
                $builder->whereHas('subsection.section', fn ($sectionQuery) => $sectionQuery
                    ->where('planning_year_id', $planningYear->id));
            })
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($nested) use ($query) {
                    $nested->where('item_name', 'like', "%{$query}%")
                        ->orWhereHas('subsection', fn ($subsectionQuery) => $subsectionQuery->where('code', 'like', "%{$query}%"))
                        ->orWhereHas('chartOfAccount', function ($accountQuery) use ($query): void {
                            $accountQuery->where('account_code', 'like', "%{$query}%")
                                ->orWhere('account_name', 'like', "%{$query}%");
                        });
                });
            })
            ->join('expense_subsections', 'expense_subsections.id', '=', 'expense_catalog_items.subsection_id')
            ->select('expense_catalog_items.*')
            ->orderBy('expense_subsections.code')
            ->orderBy('sort_order')
            ->get();

        $subsectionLabels = ExpenseSubsection::with('section.planningYear')
            ->when($planningYear, fn ($builder) => $builder
                ->whereHas('section', fn ($sectionQuery) => $sectionQuery
                    ->where('planning_year_id', $planningYear->id)))
            ->get()
            ->sortByDesc(fn (ExpenseSubsection $subsection) => $subsection->section?->planningYear?->year ?? 0)
            ->unique('code')
            ->keyBy('code');

        $accounts = ChartOfAccount::with('parent')
            ->expenseSelectable()
            ->whereDoesntHave('children')
            ->orderBy('account_code')
            ->get();

        $accountOptions = $accounts->map(fn (ChartOfAccount $account) => [
            'id' => $account->id,
            'code' => $account->account_code,
            'name' => $account->account_name,
            'label' => $this->accountLabel($account),
        ]);

        return view('dashboards.finance_head.settings.expense-default-rows.index', [
            'rows' => $rows,
            'query' => $query,
            'years' => $years,
            'planningYear' => $planningYear,
            'subsectionLabels' => $subsectionLabels,
            'accountOptions' => $accountOptions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subsection_code' => ['required', 'string', 'max:30'],
            'subsection_id' => ['nullable', 'integer', 'exists:expense_subsections,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'chart_of_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'pattern_id' => [
                'nullable',
                'integer',
                Rule::exists('expense_patterns', 'id')->whereIn('key', ExpensePattern::SYSTEM_DEFAULT_KEYS),
            ],
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $subsection = ! empty($data['subsection_id'])
            ? ExpenseSubsection::findOrFail($data['subsection_id'])
            : ExpenseSubsection::with('section.planningYear')
                ->where('code', $data['subsection_code'])
                ->get()
                ->sortByDesc(fn (ExpenseSubsection $subsection) => $subsection->section?->planningYear?->year ?? 0)
                ->first();

        if (! $subsection) {
            return back()->withErrors(['subsection_code' => 'Subsection not found.']);
        }

        $account = ! empty($data['chart_of_account_id'])
            ? ChartOfAccount::findOrFail($data['chart_of_account_id'])
            : null;

        $patternId = ExpensePattern::systemDefaultIdOrFallback($data['pattern_id'] ?? null, $subsection->default_pattern_id);

        ExpenseCatalogItem::create([
            'subsection_id' => $subsection->id,
            'item_name' => $data['item_name'],
            'chart_of_account_id' => $account?->id,
            'pattern_id' => $patternId,
            'sort_order' => $data['sort_order'],
            'default_values' => [],
            'is_active' => true,
        ]);

        return redirect()
            ->route('head_of_finance.settings.expense-structure.index', [
                'planning_year_id' => $subsection->section?->planning_year_id,
                'active_section' => $subsection->section_id,
                'active_default' => $subsection->id,
            ])
            ->with('success', 'Catalog item added.');
    }

    public function update(Request $request, ExpenseCatalogItem $expenseCatalogItem)
    {
        $data = $request->validate([
            'item_name' => ['sometimes', 'required', 'string', 'max:255'],
            'chart_of_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'pattern_id' => [
                'nullable',
                'integer',
                Rule::exists('expense_patterns', 'id')->whereIn('key', ExpensePattern::SYSTEM_DEFAULT_KEYS),
            ],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:1', 'max:999'],
        ]);

        $account = ! empty($data['chart_of_account_id'])
            ? ChartOfAccount::findOrFail($data['chart_of_account_id'])
            : null;

        $payload = [
            'chart_of_account_id' => $account?->id,
        ];

        foreach (['item_name', 'pattern_id', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        $oldItemName = $expenseCatalogItem->item_name;

        DB::transaction(function () use ($expenseCatalogItem, $payload, $oldItemName): void {
            $expenseCatalogItem->update($payload);
            $expenseCatalogItem->load(['chartOfAccount', 'subsection']);
            $this->syncPlanRowsFromCatalogItem($expenseCatalogItem, $oldItemName);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'row' => [
                    'id' => $expenseCatalogItem->id,
                    'chart_of_account_id' => $expenseCatalogItem->chart_of_account_id,
                    'reference' => $account?->account_code,
                    'account_label' => $account ? $this->accountLabel($account) : null,
                ],
            ]);
        }

        return back()->with('success', 'Catalog item saved.');
    }

    public function destroy(ExpenseCatalogItem $expenseCatalogItem)
    {
        DB::transaction(function () use ($expenseCatalogItem): void {
            $this->deletePlanRowsForCatalogItem($expenseCatalogItem);
            $expenseCatalogItem->delete();
        });

        return back()->with('success', 'Catalog item deleted.');
    }

    private function syncPlanRowsFromCatalogItem(ExpenseCatalogItem $catalogItem, string $oldItemName): void
    {
        $pattern = $catalogItem->pattern_id
            ? ExpensePattern::systemDefaults()->find($catalogItem->pattern_id)
            : null;

        $plans = ExpensePlanRow::where('catalog_item_id', $catalogItem->id)
            ->orWhere(function ($query) use ($catalogItem, $oldItemName): void {
                $query->where('subsection_id', $catalogItem->subsection_id)
                    ->where(function ($nested) use ($oldItemName): void {
                        $nested->where('item_name', $oldItemName)
                            ->orWhere('plan_detail', $oldItemName);
                    });
            })
            ->get();

        foreach ($plans as $plan) {
            $payload = [
                'catalog_item_id' => $catalogItem->id,
                'chart_of_account_id' => $catalogItem->chart_of_account_id,
                'pattern_id' => $pattern?->id ?: $plan->pattern_id,
                'item_name' => $catalogItem->item_name,
                'plan_detail' => $catalogItem->item_name,
            ];

            if ($pattern) {
                $values = $this->calculationValuesForPattern($pattern, $plan->calculation_values ?? []);
                $payload['pattern_id'] = $pattern->id;
                $payload['plan_type'] = $pattern->key;
                $payload['calculation_values'] = $values;
                $payload['pattern_snapshot'] = $pattern->snapshot();
            }

            $plan->update($payload);
        }
    }

    private function calculationValuesForPattern(ExpensePattern $pattern, array $currentValues): array
    {
        $values = array_merge($pattern->defaultInputValues(), $currentValues);
        $values['yearly_total'] = $pattern->calculateTotal($values);

        return $values;
    }

    private function deletePlanRowsForCatalogItem(ExpenseCatalogItem $catalogItem): void
    {
        $plans = ExpensePlanRow::where('catalog_item_id', $catalogItem->id)
            ->orWhere(function ($query) use ($catalogItem): void {
                $query->where('subsection_id', $catalogItem->subsection_id)
                    ->where(function ($nested) use ($catalogItem): void {
                        $nested->where('item_name', $catalogItem->item_name)
                            ->orWhere('plan_detail', $catalogItem->item_name);
                    });
            })
            ->get();

        ExpensePlanRow::whereIn('id', $plans->pluck('id'))->delete();
    }

    private function accountLabel(ChartOfAccount $account): string
    {
        $parts = [];
        $node = $account;
        $guard = 0;

        while ($node && $guard++ < 10) {
            array_unshift($parts, $node->account_name);
            $node = $node->parent;
        }

        return $account->account_code.' - '.implode(' / ', $parts);
    }
}
