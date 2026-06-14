<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\ExpenseSubsectionDefaultRow;
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
        $defaultRowsByCode = collect();
        if ($planningYear) {
            if (! ExpenseSection::where('planning_year_id', $planningYear->id)->exists()) {
                $this->buildStructureFromDefaultRows($planningYear);
            }

            $sections = ExpenseSection::with(['subsections.defaultPattern', 'subsections.children'])
                ->where('planning_year_id', $planningYear->id)
                ->orderBy('display_order')
                ->get();

            $subsectionCodes = $sections
                ->flatMap(fn (ExpenseSection $section) => $section->subsections->pluck('code'))
                ->filter()
                ->unique()
                ->values();

            if ($subsectionCodes->isNotEmpty()) {
                $defaultRowsByCode = ExpenseSubsectionDefaultRow::with('chartOfAccount.parent')
                    ->whereIn('subsection_code', $subsectionCodes)
                    ->orderBy('subsection_code')
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy('subsection_code');
            }
        }

        $patterns = ExpensePattern::where('is_active', true)
            ->orderBy('id')
            ->get();

        $accountOptions = ChartOfAccount::with('parent')
            ->whereDoesntHave('children')
            ->orderBy('account_code')
            ->get()
            ->map(fn (ChartOfAccount $account) => [
                'id' => $account->id,
                'code' => $account->account_code,
                'name' => $account->account_name,
                'label' => $this->accountLabel($account),
            ]);

        return view('dashboards.finance_head.settings.expense-structure.index', [
            'years' => $years,
            'planningYear' => $planningYear,
            'sections' => $sections,
            'patterns' => $patterns,
            'defaultRowsByCode' => $defaultRowsByCode,
            'accountOptions' => $accountOptions,
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

    private function buildStructureFromDefaultRows(PlanningYear $planningYear): void
    {
        $codes = ExpenseSubsectionDefaultRow::query()
            ->select('subsection_code')
            ->distinct()
            ->orderBy('subsection_code')
            ->pluck('subsection_code')
            ->filter()
            ->values();

        if ($codes->isEmpty()) {
            return;
        }

        $defaultPatternId = ExpensePattern::where('is_active', true)->orderBy('id')->value('id');
        $sectionsByCode = [];

        $sectionCodes = $codes
            ->map(fn (string $code) => implode('.', array_slice(explode('.', $code), 0, 2)))
            ->unique()
            ->values();

        foreach ($sectionCodes as $index => $sectionCode) {
            $sectionsByCode[$sectionCode] = ExpenseSection::create([
                'planning_year_id' => $planningYear->id,
                'code' => $sectionCode,
                'name' => 'ກຸ່ມລາຍຈ່າຍ ' . $sectionCode,
                'description' => null,
                'display_order' => $index + 1,
                'summary_period_count' => 12,
                'is_active' => true,
            ]);
        }

        $subsectionCodes = collect();
        foreach ($codes as $code) {
            $parts = explode('.', $code);
            for ($length = 3; $length <= count($parts); $length++) {
                $subsectionCodes->push(implode('.', array_slice($parts, 0, $length)));
            }
        }

        $subsectionsByCode = [];
        foreach ($subsectionCodes->unique()->sort()->values() as $index => $code) {
            $sectionCode = implode('.', array_slice(explode('.', $code), 0, 2));
            if (! isset($sectionsByCode[$sectionCode])) {
                continue;
            }

            $subsectionsByCode[$code] = ExpenseSubsection::create([
                'section_id' => $sectionsByCode[$sectionCode]->id,
                'parent_id' => null,
                'code' => $code,
                'name' => 'ລາຍການ ' . $code,
                'description' => null,
                'default_pattern_id' => $defaultPatternId,
                'summary_period_count' => 12,
                'display_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        foreach ($subsectionsByCode as $code => $subsection) {
            $parts = explode('.', $code);
            if (count($parts) <= 3) {
                continue;
            }

            $parentCode = implode('.', array_slice($parts, 0, -1));
            if (isset($subsectionsByCode[$parentCode])) {
                $subsection->update(['parent_id' => $subsectionsByCode[$parentCode]->id]);
            }
        }
    }
}
