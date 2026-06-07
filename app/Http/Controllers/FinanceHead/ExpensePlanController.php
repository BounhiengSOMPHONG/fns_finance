<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCalculationRule;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use App\Models\PlanningYearFieldSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpensePlanController extends Controller
{
    public function index()
    {
        $plans = PlanningYear::orderByDesc('year')->paginate(15);

        return view('dashboards.finance_head.expense.index', compact('plans'));
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

        $expenseRows = ExpensePlan::with(['values', 'section', 'subsection', 'pattern'])
            ->where('planning_year_id', $planningYear->id)
            ->orderBy('section_id')
            ->orderBy('subsection_id')
            ->orderBy('id')
            ->get();

        $chartAccounts = ChartOfAccount::whereDoesntHave('children')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        return view('dashboards.finance_head.expense.manage', [
            'planningYear' => $planningYear,
            'sections' => $sections,
            'patterns' => $patterns,
            'fieldSettings' => $fieldSettings,
            'rules' => $rules,
            'expenseRows' => $expenseRows,
            'chartAccounts' => $chartAccounts,
        ]);
    }

    public function destroy(PlanningYear $expensePlan)
    {
        $expensePlan->delete();

        return redirect()->route('head_of_finance.expense.index')
            ->with('success', 'ລຶບແຜນລາຍຈ່າຍສຳເລັດ');
    }

    public function approve(PlanningYear $expensePlan)
    {
        $expensePlan->update(['is_active' => true]);

        return back()->with('success', 'ຕັ້ງແຜນນີ້ເປັນແຜນທີ່ໃຊ້ງານແລ້ວ');
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
                'name' => $sourceSection->name,
                'description' => $sourceSection->description,
                'display_order' => $sourceSection->display_order,
                'is_active' => $sourceSection->is_active,
            ]);

            $sectionIdMap[$sourceSection->id] = $section->id;

            foreach ($sourceSection->subsections->sortBy('display_order') as $sourceSubsection) {
                $subsection = ExpenseSubsection::create([
                    'section_id' => $section->id,
                    'parent_id' => null,
                    'code' => $sourceSubsection->code,
                    'name' => $sourceSubsection->name,
                    'description' => $sourceSubsection->description,
                    'default_pattern_id' => $sourceSubsection->default_pattern_id,
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
