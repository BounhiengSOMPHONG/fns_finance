<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomePlan;
use App\Models\ExpenseCalculationRule;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\ExpenseSubsectionFieldSetting;
use App\Models\PlanningYear;
use App\Models\PlanningYearFieldSetting;
use App\Models\SalaryPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagePlanController extends Controller
{
    public function index()
    {
        $plans = PlanningYear::with(['academicIncomePlans', 'salaryPlans'])
            ->withCount('expensePlans')
            ->orderByDesc('year')
            ->paginate(12);

        return view('dashboards.finance_head.manage-plan.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100', 'unique:planning_years,year'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $planningYear = DB::transaction(function () use ($data) {
            $sourceYear = PlanningYear::where('year', '<', $data['year'])
                ->whereHas('sections')
                ->orderByDesc('year')
                ->first();

            $planningYear = PlanningYear::create([
                'year' => (int) $data['year'],
                'name' => $data['name'] ?: 'Planning ' . $data['year'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            if ($sourceYear) {
                $this->copyExpenseStructure($sourceYear, $planningYear);
            }

            $this->ensureCompanionPlans($planningYear);

            return $planningYear;
        });

        return redirect()
            ->route('head_of_finance.manage-plan.index')
            ->with('success', 'ສ້າງແຜນລວມປະຈຳປີ ' . $planningYear->year . ' ສຳເລັດ');
    }

    public function sync(PlanningYear $planningYear)
    {
        DB::transaction(function () use ($planningYear): void {
            $this->ensureCompanionPlans($planningYear);
            $this->ensureExpenseStructure($planningYear);
        });

        return back()->with('success', 'ກວດແລະສ້າງແຜນທີ່ຂາດສຳເລັດ');
    }

    private function ensureCompanionPlans(PlanningYear $planningYear): void
    {
        AcademicIncomePlan::firstOrCreate(
            ['fiscal_year' => $planningYear->year],
            [
                'planning_year_id' => $planningYear->id,
                'notes' => null,
                'created_by' => Auth::id(),
            ]
        )->update(['planning_year_id' => $planningYear->id]);

        SalaryPlan::firstOrCreate(
            [
                'fiscal_year' => $planningYear->year,
                'month' => 1,
            ],
            [
                'planning_year_id' => $planningYear->id,
                'notes' => null,
                'created_by' => Auth::id(),
            ]
        )->update(['planning_year_id' => $planningYear->id]);
    }

    private function ensureExpenseStructure(PlanningYear $planningYear): void
    {
        if (ExpenseSection::where('planning_year_id', $planningYear->id)->exists()) {
            return;
        }

        $sourceYear = PlanningYear::where('year', '<', $planningYear->year)
            ->whereHas('sections')
            ->orderByDesc('year')
            ->first();

        if ($sourceYear) {
            $this->copyExpenseStructure($sourceYear, $planningYear);
        }
    }

    private function copyExpenseStructure(PlanningYear $sourceYear, PlanningYear $targetYear): void
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
                'summary_period_count' => $sourceSection->summary_period_count ?? 12,
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
            ->each(function (PlanningYearFieldSetting $setting) use ($targetYear): void {
                PlanningYearFieldSetting::updateOrCreate([
                    'planning_year_id' => $targetYear->id,
                    'pattern_field_id' => $setting->pattern_field_id,
                ], [
                    'label' => $setting->label,
                    'display_order' => $setting->display_order,
                    'is_required' => $setting->is_required,
                    'is_active' => $setting->is_active,
                    'default_value' => $setting->default_value,
                ]);
            });

        ExpenseSubsectionFieldSetting::whereIn('subsection_id', array_keys($subsectionIdMap))
            ->get()
            ->each(function (ExpenseSubsectionFieldSetting $setting) use ($subsectionIdMap): void {
                ExpenseSubsectionFieldSetting::updateOrCreate([
                    'subsection_id' => $subsectionIdMap[$setting->subsection_id],
                    'pattern_field_id' => $setting->pattern_field_id,
                ], [
                    'label' => $setting->label,
                    'display_order' => $setting->display_order,
                    'is_required' => $setting->is_required,
                    'is_active' => $setting->is_active,
                    'default_value' => $setting->default_value,
                ]);
            });

        ExpenseCalculationRule::where('planning_year_id', $sourceYear->id)
            ->get()
            ->each(function (ExpenseCalculationRule $rule) use ($targetYear, $sectionIdMap, $subsectionIdMap): void {
                ExpenseCalculationRule::firstOrCreate([
                    'planning_year_id' => $targetYear->id,
                    'pattern_id' => $rule->pattern_id,
                    'section_id' => $rule->section_id ? ($sectionIdMap[$rule->section_id] ?? null) : null,
                    'subsection_id' => $rule->subsection_id ? ($subsectionIdMap[$rule->subsection_id] ?? null) : null,
                    'target_field_key' => $rule->target_field_key,
                ], [
                    'formula' => $rule->formula,
                    'is_active' => $rule->is_active,
                ]);
            });
    }
}
