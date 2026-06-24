<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCatalogItem;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use App\Support\ExpenseAccountLinkCatalog;
use App\Support\ExpenseStructureNames;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseStructureController extends Controller
{
    public function overview()
    {
        $years = PlanningYear::with(['sections.subsections.catalogItems'])
            ->orderByDesc('year')
            ->get();

        $yearSummaries = $years->map(function (PlanningYear $year): array {
            $sections = $year->sections;
            $subsections = $sections->flatMap(fn (ExpenseSection $section) => $section->subsections);
            $items = $subsections->flatMap(fn (ExpenseSubsection $subsection) => $subsection->catalogItems);
            $linkedItems = $items->whereNotNull('chart_of_account_id')->count();

            return [
                'year' => $year,
                'sections_count' => $sections->count(),
                'subsections_count' => $subsections->count(),
                'items_count' => $items->count(),
                'linked_items_count' => $linkedItems,
                'unlinked_items_count' => max($items->count() - $linkedItems, 0),
            ];
        });

        $catalogYear = $years->firstWhere('is_active', true) ?? $years->first();
        $catalogItemsQuery = ExpenseCatalogItem::query()
            ->when($catalogYear, fn ($query) => $query
                ->whereHas('subsection.section', fn ($sectionQuery) => $sectionQuery
                    ->where('planning_year_id', $catalogYear->id)));

        $catalogItemsCount = (clone $catalogItemsQuery)->count();
        $linkedCatalogItemsCount = (clone $catalogItemsQuery)->whereNotNull('chart_of_account_id')->count();
        $patterns = ExpensePattern::orderBy('id')->get();

        return view('dashboards.finance_head.settings.expense-setup.index', [
            'yearSummaries' => $yearSummaries,
            'catalogItemsCount' => $catalogItemsCount,
            'linkedCatalogItemsCount' => $linkedCatalogItemsCount,
            'unlinkedCatalogItemsCount' => max($catalogItemsCount - $linkedCatalogItemsCount, 0),
            'activePatternsCount' => $patterns->where('is_active', true)->count(),
            'patternsCount' => $patterns->count(),
            'patternFieldsCount' => $patterns->sum(fn (ExpensePattern $pattern): int => $pattern->fields->count()),
        ]);
    }

    public function index(Request $request, ExpenseAccountLinkCatalog $accountLinkCatalog)
    {
        $years = PlanningYear::orderByDesc('year')->get();
        $planningYear = $request->filled('planning_year_id')
            ? $years->firstWhere('id', (int) $request->integer('planning_year_id'))
            : $years->first();

        $sections = collect();
        $defaultRowsByCode = collect();
        $accountsByCode = ChartOfAccount::with('parent')
            ->orderBy('account_code')
            ->get()
            ->keyBy('account_code');

        if ($planningYear) {
            if (! ExpenseSection::where('planning_year_id', $planningYear->id)->exists()) {
                $detachedSections = $this->latestDetachedExpenseSections();
                if ($detachedSections->isNotEmpty()) {
                    $this->copyStructureFromSections($detachedSections, $planningYear);
                    $this->deleteDetachedExpenseStructure();
                } else {
                    $this->buildStructureFromDefaultRows($planningYear);
                }
            }

            $sections = ExpenseSection::with(['subsections.defaultPattern', 'subsections.children'])
                ->where('planning_year_id', $planningYear->id)
                ->orderBy('display_order')
                ->get();

            $subsectionIds = $sections
                ->flatMap(fn (ExpenseSection $section) => $section->subsections->pluck('id'))
                ->filter()
                ->unique()
                ->values();

            if ($subsectionIds->isNotEmpty()) {
                $defaultRows = ExpenseCatalogItem::with(['chartOfAccount.parent', 'subsection'])
                    ->whereIn('expense_catalog_items.subsection_id', $subsectionIds)
                    ->join('expense_subsections', 'expense_subsections.id', '=', 'expense_catalog_items.subsection_id')
                    ->select('expense_catalog_items.*')
                    ->orderBy('expense_subsections.code')
                    ->orderBy('sort_order')
                    ->get();

                $defaultRowsByCode = $accountLinkCatalog
                    ->decorateRows($defaultRows, $accountsByCode)
                    ->groupBy('subsection_code');
            }
        }

        $accountWarnings = $defaultRowsByCode
            ->flatten(1)
            ->filter(fn (ExpenseCatalogItem $row): bool => $row->chart_of_account_id === null)
            ->values();

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
            'accountWarnings' => $accountWarnings,
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
        DB::transaction(function () use ($expenseSection): void {
            $subsectionIds = ExpenseSubsection::where('section_id', $expenseSection->id)->pluck('id');

            ExpensePlan::where('section_id', $expenseSection->id)->delete();

            if ($subsectionIds->isNotEmpty()) {
                ExpenseCatalogItem::whereIn('subsection_id', $subsectionIds)->delete();
                ExpenseSubsection::whereIn('id', $subsectionIds)->delete();
            }

            $expenseSection->delete();
        });

        return back()->with('success', 'Expense section and related rows deleted.');
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

        $oldPatternId = $expenseSubsection->default_pattern_id;
        $newPatternId = $data['default_pattern_id'] ?? null;

        $expenseSubsection->update([
            'parent_id' => $parentId,
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'default_pattern_id' => $newPatternId,
            'display_order' => $data['display_order'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if ((int) $oldPatternId !== (int) $newPatternId) {
            $this->syncSubsectionDefaultPattern($expenseSubsection, $oldPatternId, $newPatternId);
        }

        return back()->with('success', 'Expense subsection updated.');
    }

    public function destroySubsection(ExpenseSubsection $expenseSubsection)
    {
        DB::transaction(function () use ($expenseSubsection): void {
            $subsectionIds = collect([$expenseSubsection->id])
                ->merge($this->descendantSubsectionIds($expenseSubsection))
                ->unique()
                ->values();

            ExpensePlan::whereIn('subsection_id', $subsectionIds)->delete();
            ExpenseCatalogItem::whereIn('subsection_id', $subsectionIds)->delete();
            ExpenseSubsection::whereIn('id', $subsectionIds)->delete();
        });

        return back()->with('success', 'Expense subsection and related rows deleted.');
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

    private function descendantSubsectionIds(ExpenseSubsection $subsection): Collection
    {
        $children = ExpenseSubsection::where('parent_id', $subsection->id)->get(['id']);

        return $children->pluck('id')
            ->merge($children->flatMap(fn (ExpenseSubsection $child): Collection => $this->descendantSubsectionIds($child)));
    }

    private function syncSubsectionDefaultPattern(ExpenseSubsection $subsection, ?int $oldPatternId, ?int $newPatternId): void
    {
        if (! $newPatternId) {
            return;
        }

        $pattern = ExpensePattern::find($newPatternId);
        if (! $pattern) {
            return;
        }

        $catalogItems = ExpenseCatalogItem::where('subsection_id', $subsection->id)
            ->where(function ($query) use ($oldPatternId): void {
                $query->whereNull('pattern_id');
                if ($oldPatternId) {
                    $query->orWhere('pattern_id', $oldPatternId);
                }
            })
            ->get();

        if ($catalogItems->isNotEmpty()) {
            ExpenseCatalogItem::whereIn('id', $catalogItems->pluck('id'))->update([
                'pattern_id' => $pattern->id,
            ]);
        }

        $plans = ExpensePlan::where('subsection_id', $subsection->id)
            ->where(function ($query) use ($catalogItems, $oldPatternId): void {
                if ($catalogItems->isNotEmpty()) {
                    $query->whereIn('catalog_item_id', $catalogItems->pluck('id'));
                } else {
                    $query->whereRaw('1 = 0');
                }

                $query->orWhereNull('pattern_id');
                if ($oldPatternId) {
                    $query->orWhere('pattern_id', $oldPatternId);
                }
            })
            ->get();

        foreach ($plans as $plan) {
            $values = $this->calculationValuesForPattern($pattern, $plan->calculation_values ?? []);
            $plan->update([
                'pattern_id' => $pattern->id,
                'plan_type' => $pattern->key,
                'calculation_values' => $values,
                'pattern_snapshot' => $pattern->snapshot(),
            ]);
        }
    }

    private function calculationValuesForPattern(ExpensePattern $pattern, array $currentValues): array
    {
        $values = array_merge($pattern->defaultInputValues(), $currentValues);
        $values['yearly_total'] = $pattern->calculateTotal($values);

        return $values;
    }

    private function buildStructureFromDefaultRows(PlanningYear $planningYear): void
    {
        $codes = ExpenseCatalogItem::query()
            ->join('expense_subsections', 'expense_subsections.id', '=', 'expense_catalog_items.subsection_id')
            ->select('expense_subsections.code')
            ->distinct()
            ->orderBy('expense_subsections.code')
            ->pluck('expense_subsections.code')
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
                'name' => ExpenseStructureNames::fallbackSectionName($sectionCode),
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
        foreach ($subsectionCodes->unique()->sortBy(fn (string $code) => ExpenseStructureNames::codeSortKey($code))->values() as $index => $code) {
            $sectionCode = implode('.', array_slice(explode('.', $code), 0, 2));
            if (! isset($sectionsByCode[$sectionCode])) {
                continue;
            }

            $subsectionsByCode[$code] = ExpenseSubsection::create([
                'section_id' => $sectionsByCode[$sectionCode]->id,
                'parent_id' => null,
                'code' => $code,
                'name' => ExpenseStructureNames::fallbackSubsectionName($code),
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

        $sourceCatalogItems = ExpenseCatalogItem::with('subsection')
            ->whereHas('subsection', fn ($query) => $query->whereIn('code', array_keys($subsectionsByCode)))
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (ExpenseCatalogItem $item): ?string => $item->subsection?->code)
            ->map(fn ($items) => $items
                ->unique(fn (ExpenseCatalogItem $item): string => $item->sort_order.'|'.$item->item_name)
                ->values());

        foreach ($subsectionsByCode as $code => $subsection) {
            foreach ($sourceCatalogItems->get($code, collect()) as $catalogItem) {
                ExpenseCatalogItem::create([
                    'subsection_id' => $subsection->id,
                    'item_name' => $catalogItem->item_name,
                    'chart_of_account_id' => $catalogItem->chart_of_account_id,
                    'pattern_id' => $catalogItem->pattern_id ?: $subsection->default_pattern_id,
                    'default_values' => $catalogItem->default_values ?? [],
                    'sort_order' => $catalogItem->sort_order,
                    'is_active' => $catalogItem->is_active,
                ]);
            }
        }
    }

    private function latestDetachedExpenseSections(): Collection
    {
        return ExpenseSection::with('subsections.catalogItems')
            ->whereNull('planning_year_id')
            ->orderBy('code')
            ->orderByDesc('id')
            ->get()
            ->unique('code')
            ->sortBy('display_order')
            ->values();
    }

    private function copyStructureFromSections(Collection $sourceSections, PlanningYear $targetYear): void
    {
        $subsectionIdMap = [];

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

                foreach ($sourceSubsection->catalogItems->sortBy('sort_order') as $catalogItem) {
                    ExpenseCatalogItem::create([
                        'subsection_id' => $subsection->id,
                        'item_name' => $catalogItem->item_name,
                        'chart_of_account_id' => $catalogItem->chart_of_account_id,
                        'pattern_id' => $catalogItem->pattern_id,
                        'default_values' => $catalogItem->default_values ?? [],
                        'sort_order' => $catalogItem->sort_order,
                        'is_active' => $catalogItem->is_active,
                    ]);
                }
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
    }

    private function deleteDetachedExpenseStructure(): void
    {
        $sectionIds = ExpenseSection::whereNull('planning_year_id')->pluck('id');
        if ($sectionIds->isEmpty()) {
            return;
        }

        $subsectionIds = ExpenseSubsection::whereIn('section_id', $sectionIds)->pluck('id');
        if ($subsectionIds->isNotEmpty()) {
            ExpenseCatalogItem::whereIn('subsection_id', $subsectionIds)->delete();
            ExpenseSubsection::whereIn('id', $subsectionIds)->delete();
        }

        ExpenseSection::whereIn('id', $sectionIds)->delete();
    }
}
