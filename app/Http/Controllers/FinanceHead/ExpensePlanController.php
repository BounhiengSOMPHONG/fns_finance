<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCatalogItem;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpensePlanController extends Controller
{
    public function index()
    {
        return redirect()->route('head_of_finance.manage-plan.index');
    }

    public function create()
    {
        return view('dashboards.finance_head.expense.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100|unique:planning_years,year',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $sourceYear = PlanningYear::where('year', '<', $data['year'])
            ->orderByDesc('year')
            ->first();

        $planningYear = DB::transaction(function () use ($data, $sourceYear) {
            $planningYear = PlanningYear::create([
                'year' => $data['year'],
                'name' => $data['name'] ?: 'Planning ' . $data['year'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            if ($sourceYear) {
                $this->copyYearStructure($sourceYear, $planningYear);
            }

            return $planningYear;
        });

        return redirect()->route('head_of_finance.expense.manage', $planningYear)
            ->with('success', 'ສ້າງແຜນລາຍຈ່າຍສຳເລັດ');
    }

    public function manage(PlanningYear $expensePlan)
    {
        $planningYear = $expensePlan;

        abort_if(
            $planningYear->canBeEdited() === false,
            403,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );

        $sections = ExpenseSection::with([
            'subsections.defaultPattern',
            'subsections.children.defaultPattern',
        ])
            ->where('planning_year_id', $planningYear->id)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $patterns = ExpensePattern::where('is_active', true)
            ->orderBy('id')
            ->get();

        $fieldSettings = collect();

        $rules = collect();

        $this->ensureCatalogExpenseRows($planningYear, $sections, $patterns);
        $this->syncRowsWithPatternDefaults($planningYear, $patterns);

        $expenseRows = ExpensePlan::with(['section', 'subsection', 'pattern', 'chartOfAccount'])
            ->where('planning_year_id', $planningYear->id)
            ->orderBy('section_id')
            ->orderBy('subsection_id')
            ->orderBy('id')
            ->get();

        $academicIncomeTotal = (float) $planningYear->academicIncomePlans()
            ->with('items:id,plan_id,total_income')
            ->get()
            ->sum(fn ($plan) => $plan->items->sum('total_income'));

        $expenseTotal = (float) $expenseRows->sum(fn (ExpensePlan $row) => $row->yearlyTotal());
        $budgetSummary = [
            'income_total' => $academicIncomeTotal,
            'expense_total' => $expenseTotal,
            'remaining_total' => $academicIncomeTotal - $expenseTotal,
        ];

        $chartAccounts = ChartOfAccount::whereDoesntHave('children')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        $subsectionCodes = $sections
            ->flatMap(fn ($section) => $section->subsections->pluck('code'))
            ->unique()
            ->values();
        $defaultRows = ExpenseCatalogItem::with(['chartOfAccount', 'pattern', 'subsection'])
            ->whereHas('subsection', fn ($query) => $query->whereIn('code', $subsectionCodes))
            ->where('is_active', true)
            ->orderBy('subsection_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (ExpenseCatalogItem $item) => $item->subsection?->code)
            ->map(fn ($rows) => $rows->map(function ($row) use ($patterns) {
                $pattern = $row->pattern ?: $patterns->firstWhere('id', $row->pattern_id ?: $row->subsection?->default_pattern_id);
                $values = $pattern
                    ? $this->catalogDefaultValues($row, $pattern)
                    : $this->cleanExpenseValues($row->default_values ?? []);

                return [
                    'item_name' => $row->item_name,
                    'reference' => $row->chartOfAccount?->account_code,
                    'values' => $values,
                ];
            })->values());

        return view('dashboards.finance_head.expense.manage', [
            'planningYear' => $planningYear,
            'sections' => $sections,
            'patterns' => $patterns,
            'fieldSettings' => $fieldSettings,
            'rules' => $rules,
            'expenseRows' => $expenseRows,
            'chartAccounts' => $chartAccounts,
            'defaultRows' => $defaultRows,
            'budgetSummary' => $budgetSummary,
        ]);
    }

    private function ensureCatalogExpenseRows(PlanningYear $planningYear, $sections, $patterns): void
    {
        $subsections = $sections->flatMap(fn ($section) => $section->subsections);
        $catalogItemsBySubsection = ExpenseCatalogItem::with(['chartOfAccount', 'pattern'])
            ->whereIn('subsection_id', $subsections->pluck('id')->filter()->values())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('subsection_id');

        if ($catalogItemsBySubsection->isEmpty()) {
            return;
        }

        $patternsById = $patterns->keyBy('id');

        DB::transaction(function () use ($planningYear, $subsections, $catalogItemsBySubsection, $patternsById): void {
            $existingKeys = ExpensePlan::where('planning_year_id', $planningYear->id)
                ->whereIn('subsection_id', $subsections->pluck('id')->filter()->values())
                ->get(['subsection_id', 'item_name', 'plan_detail'])
                ->mapWithKeys(fn (ExpensePlan $row) => [
                    $row->subsection_id . '|' . trim((string) ($row->item_name ?: $row->plan_detail)) => true,
                ]);

            $planRows = [];
            $userId = Auth::id();
            $now = now();

            foreach ($subsections as $subsection) {
                $catalogItems = $catalogItemsBySubsection->get($subsection->id);
                if (!$catalogItems) {
                    continue;
                }

                foreach ($catalogItems as $catalogItem) {
                    $pattern = $catalogItem->pattern ?: $patternsById->get($catalogItem->pattern_id ?: $subsection->default_pattern_id);
                    if (!$pattern) {
                        continue;
                    }

                    $itemName = trim((string) $catalogItem->item_name);
                    $rowKey = $subsection->id . '|' . $itemName;

                    if ($existingKeys->has($rowKey)) {
                        continue;
                    }

                    $values = $this->catalogDefaultValues($catalogItem, $pattern);
                    $values['yearly_total'] = $pattern->calculateTotal($values);

                    $planRows[] = [
                        'planning_year_id' => $planningYear->id,
                        'section_id' => $subsection->section_id,
                        'subsection_id' => $subsection->id,
                        'catalog_item_id' => $catalogItem->id,
                        'chart_of_account_id' => $catalogItem->chart_of_account_id,
                        'pattern_id' => $pattern->id,
                        'version' => (string) $planningYear->year,
                        'plan_type' => $pattern->key,
                        'item_name' => $catalogItem->item_name,
                        'plan_detail' => $catalogItem->item_name,
                        'detail' => null,
                        'calculation_values' => json_encode($values, JSON_UNESCAPED_UNICODE),
                        'pattern_snapshot' => json_encode($pattern->snapshot(), JSON_UNESCAPED_UNICODE),
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $existingKeys->put($rowKey, true);
                }
            }

            if ($planRows === []) {
                return;
            }

            foreach (array_chunk($planRows, 200) as $chunk) {
                ExpensePlan::insert($chunk);
            }
        });
    }

    private function catalogDefaultValues(ExpenseCatalogItem $catalogItem, ExpensePattern $pattern): array
    {
        $values = $this->cleanExpenseValues($catalogItem->default_values ?? []);

        return array_merge($values, $pattern->defaultInputValues());
    }

    private function cleanExpenseValues(array $values): array
    {
        unset($values['item_name'], $values['reference'], $values['note']);

        return $values;
    }

    private function syncRowsWithPatternDefaults(PlanningYear $planningYear, Collection $patterns): void
    {
        $patternsById = $patterns->keyBy('id');

        ExpensePlan::with('pattern')
            ->where('planning_year_id', $planningYear->id)
            ->orderBy('id')
            ->chunkById(200, function (Collection $rows) use ($patternsById): void {
                foreach ($rows as $row) {
                    $pattern = $row->pattern ?: $patternsById->get($row->pattern_id);
                    if (! $pattern) {
                        continue;
                    }

                    $values = $row->calculation_values ?? [];
                    $changed = false;

                    foreach ($pattern->defaultInputValues() as $key => $defaultValue) {
                        if (! array_key_exists($key, $values) || $values[$key] === null || $values[$key] === '') {
                            $values[$key] = $defaultValue;
                            $changed = true;
                            continue;
                        }

                        if ($this->shouldReplaceLegacyDefaultValue($key, $values[$key], $defaultValue, $values)) {
                            $values[$key] = $defaultValue;
                            $changed = true;
                        }
                    }

                    if (! $changed) {
                        continue;
                    }

                    $values['yearly_total'] = $pattern->calculateTotal($values);

                    $row->update([
                        'calculation_values' => $values,
                        'pattern_snapshot' => $pattern->snapshot(),
                    ]);
                }
            });
    }

    private function shouldReplaceLegacyDefaultValue(string $key, mixed $currentValue, mixed $defaultValue, array $values): bool
    {
        if ($key !== 'month_count') {
            return false;
        }

        if ((float) $currentValue !== 1.0 || (float) $defaultValue === 1.0) {
            return false;
        }

        return (float) ($values['amount_per_month'] ?? 0) === 0.0
            && (float) ($values['yearly_total'] ?? 0) === 0.0;
    }

    public function destroy(PlanningYear $expensePlan)
    {
        abort_if(
            $expensePlan->canBeEdited() === false,
            403,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );

        $expensePlan->delete();

        return redirect()->route('head_of_finance.manage-plan.index')
            ->with('success', 'ລຶບແຜນລາຍຈ່າຍສຳເລັດ');
    }

    public function approve(PlanningYear $expensePlan)
    {
        abort_if(
            $expensePlan->canBeEdited() === false,
            403,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );

        $expensePlan->update(['is_active' => true]);

        return back()->with('success', 'ຕັ້ງແຜນນີ້ເປັນແຜນທີ່ໃຊ້ງານແລ້ວ');
    }

    public function updateSectionSummarySettings(Request $request, PlanningYear $expensePlan, ExpenseSection $expenseSection)
    {
        abort_if(
            $expensePlan->canBeEdited() === false,
            423,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );

        abort_unless((int) $expenseSection->planning_year_id === (int) $expensePlan->id, 404);

        $data = $request->validate([
            'summary_period_count' => 'required|numeric|min:1|max:999',
        ]);

        $expenseSection->update([
            'summary_period_count' => $data['summary_period_count'],
        ]);

        return response()->json([
            'success' => true,
            'section' => [
                'id' => $expenseSection->id,
                'summary_period_count' => $expenseSection->summary_period_count,
            ],
        ]);
    }

    public function updateSubsectionSummarySettings(Request $request, PlanningYear $expensePlan, ExpenseSubsection $expenseSubsection)
    {
        abort_if(
            $expensePlan->canBeEdited() === false,
            423,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );

        abort_unless(
            (int) $expenseSubsection->section?->planning_year_id === (int) $expensePlan->id,
            404
        );

        $data = $request->validate([
            'summary_period_count' => 'required|numeric|min:1|max:999',
        ]);

        $expenseSubsection->update([
            'summary_period_count' => $data['summary_period_count'],
        ]);

        return response()->json([
            'success' => true,
            'subsection' => [
                'id' => $expenseSubsection->id,
                'summary_period_count' => $expenseSubsection->summary_period_count,
            ],
        ]);
    }

    private function copyYearStructure(PlanningYear $sourceYear, PlanningYear $targetYear): void
    {
        $subsectionIdMap = [];

        $sourceSections = ExpenseSection::with('subsections.catalogItems')
            ->where('planning_year_id', $sourceYear->id)
            ->orderBy('display_order')
            ->get();

        foreach ($sourceSections as $sourceSection) {
            $section = ExpenseSection::create([
                'planning_year_id' => $targetYear->id,
                'code' => $sourceSection->code,
                'name' => $sourceSection->name,
                'description' => $sourceSection->description,
                'display_order' => $sourceSection->display_order,
                'summary_period_count' => $sourceSection->summary_period_count ?? 12,
                'is_active' => $sourceSection->is_active,
            ]);

            foreach ($sourceSection->subsections->sortBy('display_order') as $sourceSubsection) {
                $subsection = ExpenseSubsection::create([
                    'section_id' => $section->id,
                    'parent_id' => null,
                    'code' => $sourceSubsection->code,
                    'name' => $sourceSubsection->name,
                    'description' => $sourceSubsection->description,
                    'default_pattern_id' => $sourceSubsection->default_pattern_id,
                    'summary_period_count' => $sourceSubsection->summary_period_count ?? 12,
                    'display_order' => $sourceSubsection->display_order,
                    'is_active' => $sourceSubsection->is_active,
                ]);

                $subsectionIdMap[$sourceSubsection->id] = $subsection->id;
            }
        }

        foreach ($sourceSections as $sourceSection) {
            foreach ($sourceSection->subsections as $sourceSubsection) {
                if ($sourceSubsection->parent_id && isset($subsectionIdMap[$sourceSubsection->id], $subsectionIdMap[$sourceSubsection->parent_id])) {
                    ExpenseSubsection::whereKey($subsectionIdMap[$sourceSubsection->id])
                        ->update(['parent_id' => $subsectionIdMap[$sourceSubsection->parent_id]]);
                }
            }
        }

        foreach ($sourceSections as $sourceSection) {
            foreach ($sourceSection->subsections as $sourceSubsection) {
                $targetSubsectionId = $subsectionIdMap[$sourceSubsection->id] ?? null;
                if (! $targetSubsectionId) {
                    continue;
                }

                foreach ($sourceSubsection->catalogItems as $catalogItem) {
                    ExpenseCatalogItem::create([
                        'subsection_id' => $targetSubsectionId,
                        'item_name' => $catalogItem->item_name,
                        'chart_of_account_id' => $catalogItem->chart_of_account_id,
                        'pattern_id' => $catalogItem->pattern_id,
                        'default_values' => $catalogItem->default_values,
                        'sort_order' => $catalogItem->sort_order,
                        'is_active' => $catalogItem->is_active,
                    ]);
                }
            }
        }

    }
}
