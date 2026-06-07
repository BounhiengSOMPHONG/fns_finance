<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseStructureController extends Controller
{
    public function index(Request $request)
    {
        $years = PlanningYear::orderByDesc('year')->get();
        $planningYear = $request->filled('planning_year_id')
            ? $years->firstWhere('id', (int) $request->integer('planning_year_id'))
            : $years->first();

        $sections = collect();
        if ($planningYear) {
            $sections = ExpenseSection::with(['subsections.defaultPattern', 'subsections.children'])
                ->where('planning_year_id', $planningYear->id)
                ->orderBy('display_order')
                ->get();
        }

        $patterns = ExpensePattern::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('dashboards.finance_head.settings.expense-structure.index', [
            'years' => $years,
            'planningYear' => $planningYear,
            'sections' => $sections,
            'patterns' => $patterns,
        ]);
    }

    public function storeSection(Request $request)
    {
        $data = $request->validate([
            'planning_year_id' => ['required', 'exists:planning_years,id'],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('expense_sections', 'code')->where('planning_year_id', $request->integer('planning_year_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'display_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ExpenseSection::create([
            'planning_year_id' => $data['planning_year_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'display_order' => $data['display_order'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Expense section added.');
    }

    public function updateSection(Request $request, ExpenseSection $expenseSection)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('expense_sections', 'code')
                    ->where('planning_year_id', $expenseSection->planning_year_id)
                    ->ignore($expenseSection->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'display_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $expenseSection->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'display_order' => $data['display_order'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Expense section updated.');
    }

    public function destroySection(ExpenseSection $expenseSection)
    {
        if ($expenseSection->subsections()->exists() || ExpensePlan::where('section_id', $expenseSection->id)->exists()) {
            return back()->with('error', 'Cannot delete this section because it has subsections or plan rows.');
        }

        $expenseSection->delete();

        return back()->with('success', 'Expense section deleted.');
    }

    public function storeSubsection(Request $request, ExpenseSection $expenseSection)
    {
        $data = $request->validate([
            'parent_id' => [
                'nullable',
                Rule::exists('expense_subsections', 'id')->where('section_id', $expenseSection->id),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('expense_subsections', 'code')->where('section_id', $expenseSection->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'default_pattern_id' => ['nullable', 'exists:expense_patterns,id'],
            'display_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ExpenseSubsection::create([
            'section_id' => $expenseSection->id,
            'parent_id' => $data['parent_id'] ?? null,
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'default_pattern_id' => $data['default_pattern_id'] ?? null,
            'display_order' => $data['display_order'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Expense subsection added.');
    }

    public function updateSubsection(Request $request, ExpenseSubsection $expenseSubsection)
    {
        $data = $request->validate([
            'parent_id' => [
                'nullable',
                Rule::exists('expense_subsections', 'id')->where('section_id', $expenseSubsection->section_id),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('expense_subsections', 'code')
                    ->where('section_id', $expenseSubsection->section_id)
                    ->ignore($expenseSubsection->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'default_pattern_id' => ['nullable', 'exists:expense_patterns,id'],
            'display_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $parentId = (int) ($data['parent_id'] ?? 0) ?: null;
        if ($parentId === $expenseSubsection->id) {
            return back()->withErrors(['parent_id' => 'A subsection cannot be its own parent.']);
        }

        $expenseSubsection->update([
            'parent_id' => $parentId,
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'default_pattern_id' => $data['default_pattern_id'] ?? null,
            'display_order' => $data['display_order'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Expense subsection updated.');
    }

    public function destroySubsection(ExpenseSubsection $expenseSubsection)
    {
        if ($expenseSubsection->children()->exists() || ExpensePlan::where('subsection_id', $expenseSubsection->id)->exists()) {
            return back()->with('error', 'Cannot delete this subsection because it has child subsections or plan rows.');
        }

        $expenseSubsection->delete();

        return back()->with('success', 'Expense subsection deleted.');
    }
}
