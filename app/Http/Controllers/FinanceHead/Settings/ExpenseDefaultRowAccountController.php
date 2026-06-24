<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCatalogItem;
use App\Models\ExpensePlan;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseDefaultRowAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $planningYear = PlanningYear::where('is_active', true)
            ->orderByDesc('year')
            ->first()
            ?? PlanningYear::orderByDesc('year')->first();

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
            'pattern_id' => ['nullable', 'integer', 'exists:expense_patterns,id'],
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

        ExpenseCatalogItem::create([
            'subsection_id' => $subsection->id,
            'item_name' => $data['item_name'],
            'chart_of_account_id' => $account?->id,
            'pattern_id' => $data['pattern_id'] ?? $subsection->default_pattern_id,
            'sort_order' => $data['sort_order'],
            'default_values' => [],
            'is_active' => true,
        ]);

        return back()->with('success', 'Catalog item added.');
    }

    public function update(Request $request, ExpenseCatalogItem $expenseCatalogItem)
    {
        $data = $request->validate([
            'item_name' => ['sometimes', 'required', 'string', 'max:255'],
            'chart_of_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'pattern_id' => ['nullable', 'integer', 'exists:expense_patterns,id'],
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
        $plans = ExpensePlan::where('catalog_item_id', $catalogItem->id)
            ->orWhere(function ($query) use ($catalogItem, $oldItemName): void {
                $query->where('subsection_id', $catalogItem->subsection_id)
                    ->where(function ($nested) use ($oldItemName): void {
                        $nested->where('item_name', $oldItemName)
                            ->orWhere('plan_detail', $oldItemName);
                    });
            })
            ->get();

        foreach ($plans as $plan) {
            $plan->update([
                'catalog_item_id' => $catalogItem->id,
                'chart_of_account_id' => $catalogItem->chart_of_account_id,
                'pattern_id' => $catalogItem->pattern_id ?: $plan->pattern_id,
                'item_name' => $catalogItem->item_name,
                'plan_detail' => $catalogItem->item_name,
            ]);
        }
    }

    private function deletePlanRowsForCatalogItem(ExpenseCatalogItem $catalogItem): void
    {
        $plans = ExpensePlan::where('catalog_item_id', $catalogItem->id)
            ->orWhere(function ($query) use ($catalogItem): void {
                $query->where('subsection_id', $catalogItem->subsection_id)
                    ->where(function ($nested) use ($catalogItem): void {
                        $nested->where('item_name', $catalogItem->item_name)
                            ->orWhere('plan_detail', $catalogItem->item_name);
                    });
            })
            ->get();

        ExpensePlan::whereIn('id', $plans->pluck('id'))->delete();
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

        return $account->account_code . ' - ' . implode(' / ', $parts);
    }
}
