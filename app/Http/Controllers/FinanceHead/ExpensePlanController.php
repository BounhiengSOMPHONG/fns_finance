<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCalculationRule;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\ExpenseSubsectionDefaultRow;
use App\Models\PlanningYear;
use App\Models\PlanningYearFieldSetting;
use App\Support\ExpenseStructureNames;
use Illuminate\Http\Request;
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

        $sections = ExpenseSection::with([
            'subsections.defaultPattern',
            'subsections.children.defaultPattern',
        ])
            ->where('planning_year_id', $planningYear->id)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $patterns = ExpensePattern::with('fields')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $fieldSettings = PlanningYearFieldSetting::where('planning_year_id', $planningYear->id)
            ->get()
            ->keyBy('pattern_field_id');

        $rules = ExpenseCalculationRule::where('planning_year_id', $planningYear->id)
            ->where('is_active', true)
            ->get();

        $this->ensureDefaultExpenseRows($planningYear, $sections, $patterns, $rules);

        $expenseRows = ExpensePlan::with(['values', 'section', 'subsection', 'pattern'])
            ->where('planning_year_id', $planningYear->id)
            ->orderBy('section_id')
            ->orderBy('subsection_id')
            ->orderBy('id')
            ->get();

        $chartAccounts = ChartOfAccount::whereDoesntHave('children')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        $subsectionCodes = $sections
            ->flatMap(fn ($section) => $section->subsections->pluck('code'))
            ->unique()
            ->values();
        $defaultRows = ExpenseSubsectionDefaultRow::with('chartOfAccount')
            ->whereIn('subsection_code', $subsectionCodes)
            ->where('is_active', true)
            ->orderBy('subsection_code')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('subsection_code')
            ->map(fn ($rows) => $rows->map(function ($row) {
                $values = $row->default_values ?? [];
                unset($values['note'], $values['reference']);

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
        ]);
    }

    private function ensureDefaultExpenseRows(PlanningYear $planningYear, $sections, $patterns, $rules): void
    {
        $subsections = $sections->flatMap(fn ($section) => $section->subsections);
        $subsectionCodes = $subsections->pluck('code')->unique()->values();
        $defaultsByCode = ExpenseSubsectionDefaultRow::with('chartOfAccount')
            ->whereIn('subsection_code', $subsectionCodes)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('subsection_code');

        if ($defaultsByCode->isEmpty()) {
            return;
        }

        $patternsById = $patterns->keyBy('id');

        DB::transaction(function () use ($planningYear, $subsections, $defaultsByCode, $patternsById, $rules): void {
            $existingKeys = ExpensePlan::where('planning_year_id', $planningYear->id)
                ->whereIn('subsection_id', $subsections->pluck('id')->filter()->values())
                ->get(['subsection_id', 'plan_detail'])
                ->mapWithKeys(fn (ExpensePlan $row) => [
                    $row->subsection_id . '|' . trim((string) $row->plan_detail) => true,
                ]);

            $planRows = [];
            $valueRowsByKey = [];
            $userId = Auth::id();
            $now = now();

            foreach ($subsections as $subsection) {
                $defaultRows = $defaultsByCode->get($subsection->code);
                if (!$defaultRows || !$subsection->default_pattern_id) {
                    continue;
                }

                $pattern = $patternsById->get($subsection->default_pattern_id);
                if (!$pattern) {
                    continue;
                }

                foreach ($defaultRows as $defaultRow) {
                    $itemName = trim((string) $defaultRow->item_name);
                    $rowKey = $subsection->id . '|' . $itemName;

                    if ($existingKeys->has($rowKey)) {
                        continue;
                    }

                    $defaultValues = $defaultRow->default_values ?? [];
                    unset($defaultValues['note'], $defaultValues['reference']);

                    $values = array_merge($defaultValues, [
                        'item_name' => $defaultRow->item_name,
                        'reference' => $defaultRow->chartOfAccount?->account_code,
                    ]);

                    $rule = $rules
                        ->where('pattern_id', $pattern->id)
                        ->filter(fn ($rule) => $rule->section_id === null || (int) $rule->section_id === (int) $subsection->section_id)
                        ->filter(fn ($rule) => $rule->subsection_id === null || (int) $rule->subsection_id === (int) $subsection->id)
                        ->sortBy(fn ($rule) => ($rule->subsection_id === null ? 1 : 0) + ($rule->section_id === null ? 1 : 0))
                        ->first();

                    if ($rule) {
                        $values[$rule->target_field_key] = $this->calculateFormula($rule->formula, $values);
                    }

                    $planRows[] = [
                        'planning_year_id' => $planningYear->id,
                        'section_id' => $subsection->section_id,
                        'subsection_id' => $subsection->id,
                        'pattern_id' => $pattern->id,
                        'version' => (string) $planningYear->year,
                        'plan_type' => $pattern->key,
                        'plan_detail' => $defaultRow->item_name,
                        'detail' => null,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $fieldValues = [];
                    foreach ($pattern->fields as $field) {
                        if (array_key_exists($field->field_key, $values)) {
                            $fieldValues[] = $this->makeExpensePlanValuePayload(
                                $field->field_key,
                                $field->data_type,
                                $values[$field->field_key],
                                $now
                            );
                        }
                    }

                    $valueRowsByKey[$rowKey] = $fieldValues;
                    $existingKeys->put($rowKey, true);
                }
            }

            if ($planRows === []) {
                return;
            }

            foreach (array_chunk($planRows, 200) as $chunk) {
                ExpensePlan::insert($chunk);
            }

            $insertedPlans = ExpensePlan::where('planning_year_id', $planningYear->id)
                ->whereIn('subsection_id', collect($planRows)->pluck('subsection_id')->unique()->values())
                ->whereIn('plan_detail', collect($planRows)->pluck('plan_detail')->unique()->values())
                ->get(['id', 'subsection_id', 'plan_detail']);

            $valueRows = [];
            foreach ($insertedPlans as $expensePlan) {
                $rowKey = $expensePlan->subsection_id . '|' . trim((string) $expensePlan->plan_detail);
                foreach ($valueRowsByKey[$rowKey] ?? [] as $payload) {
                    $payload['expense_plan_id'] = $expensePlan->id;
                    $valueRows[] = $payload;
                }
            }

            foreach (array_chunk($valueRows, 500) as $chunk) {
                DB::table('expense_plan_values')->insert($chunk);
            }
        });
    }

    private function calculateFormula(string $formula, array $values): float
    {
        $sum = 0.0;
        foreach (explode('+', $formula) as $addend) {
            $product = 1.0;
            foreach (explode('*', $addend) as $token) {
                $key = trim($token);
                if ($key === '') {
                    continue;
                }

                $product *= is_numeric($key) ? (float) $key : (float) ($values[$key] ?? 0);
            }
            $sum += $product;
        }

        return $sum;
    }

    private function makeExpensePlanValuePayload(string $fieldKey, string $dataType, mixed $value, $now): array
    {
        $payload = [
            'expense_plan_id' => null,
            'field_key' => $fieldKey,
            'value_text' => null,
            'value_number' => null,
            'value_date' => null,
            'value_boolean' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($dataType === 'number') {
            $payload['value_number'] = is_numeric($value) ? $value : 0;
        } elseif ($dataType === 'date') {
            $payload['value_date'] = $value ?: null;
        } elseif ($dataType === 'boolean') {
            $payload['value_boolean'] = (bool) $value;
        } else {
            $payload['value_text'] = $value;
        }

        return $payload;
    }

    public function destroy(PlanningYear $expensePlan)
    {
        $expensePlan->delete();

        return redirect()->route('head_of_finance.manage-plan.index')
            ->with('success', 'ລຶບແຜນລາຍຈ່າຍສຳເລັດ');
    }

    public function approve(PlanningYear $expensePlan)
    {
        $expensePlan->update(['is_active' => true]);

        return back()->with('success', 'ຕັ້ງແຜນນີ້ເປັນແຜນທີ່ໃຊ້ງານແລ້ວ');
    }

    public function updateSectionSummarySettings(Request $request, PlanningYear $expensePlan, ExpenseSection $expenseSection)
    {
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
        $sectionIdMap = [];
        $subsectionIdMap = [];

        $sourceSections = ExpenseSection::with('subsections')
            ->where('planning_year_id', $sourceYear->id)
            ->orderBy('display_order')
            ->get();

        foreach ($sourceSections as $sourceSection) {
            $section = ExpenseSection::create([
                'planning_year_id' => $targetYear->id,
                'code' => $sourceSection->code,
                'name' => ExpenseStructureNames::nameFor($sourceSection->code) ?? $sourceSection->name,
                'description' => $sourceSection->description,
                'display_order' => $sourceSection->display_order,
                'summary_period_count' => $sourceSection->summary_period_count ?? 12,
                'is_active' => $sourceSection->is_active,
            ]);

            $sectionIdMap[$sourceSection->id] = $section->id;

            foreach ($sourceSection->subsections->sortBy('display_order') as $sourceSubsection) {
                $subsection = ExpenseSubsection::create([
                    'section_id' => $section->id,
                    'parent_id' => null,
                    'code' => $sourceSubsection->code,
                    'name' => ExpenseStructureNames::nameFor($sourceSubsection->code) ?? $sourceSubsection->name,
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

        PlanningYearFieldSetting::where('planning_year_id', $sourceYear->id)
            ->get()
            ->each(function (PlanningYearFieldSetting $setting) use ($targetYear) {
                PlanningYearFieldSetting::create([
                    'planning_year_id' => $targetYear->id,
                    'pattern_field_id' => $setting->pattern_field_id,
                    'label' => $setting->label,
                    'display_order' => $setting->display_order,
                    'is_required' => $setting->is_required,
                    'is_active' => $setting->is_active,
                    'default_value' => $setting->default_value,
                ]);
            });

        ExpenseCalculationRule::where('planning_year_id', $sourceYear->id)
            ->get()
            ->each(function (ExpenseCalculationRule $rule) use ($targetYear, $sectionIdMap, $subsectionIdMap) {
                ExpenseCalculationRule::create([
                    'planning_year_id' => $targetYear->id,
                    'pattern_id' => $rule->pattern_id,
                    'section_id' => $rule->section_id ? ($sectionIdMap[$rule->section_id] ?? null) : null,
                    'subsection_id' => $rule->subsection_id ? ($subsectionIdMap[$rule->subsection_id] ?? null) : null,
                    'target_field_key' => $rule->target_field_key,
                    'formula' => $rule->formula,
                    'is_active' => $rule->is_active,
                ]);
            });
    }
}
