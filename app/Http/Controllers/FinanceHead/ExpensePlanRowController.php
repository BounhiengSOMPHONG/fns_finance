<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCatalogItem;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpensePlanRow;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpensePlanRowController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'planning_year_id' => 'required|exists:planning_years,id',
            'section_id' => 'required|exists:expense_sections,id',
            'subsection_id' => 'nullable|exists:expense_subsections,id',
            'catalog_item_id' => 'nullable|exists:expense_catalog_items,id',
            'pattern_id' => [
                'required',
                Rule::exists('expense_patterns', 'id')->whereIn('key', ExpensePattern::SYSTEM_DEFAULT_KEYS),
            ],
            'plan_detail' => 'required|string|max:255',
            'detail' => 'nullable|string|max:1000',
            'values' => 'required|array',
        ]);

        $planningYear = PlanningYear::findOrFail($data['planning_year_id']);
        $this->ensurePlanCanBeEdited($planningYear);

        $section = ExpenseSection::findOrFail($data['section_id']);
        $subsection = $data['subsection_id'] ? ExpenseSubsection::findOrFail($data['subsection_id']) : null;
        $catalogItem = isset($data['catalog_item_id']) ? ExpenseCatalogItem::with(['chartOfAccount', 'pattern'])->find($data['catalog_item_id']) : null;
        $pattern = $catalogItem?->pattern ?: ExpensePattern::findOrFail($data['pattern_id']);
        abort_if(! in_array($pattern->key, ExpensePattern::SYSTEM_DEFAULT_KEYS, true), 422);

        $values = $data['values'];
        $calculationValues = $this->calculationValues($pattern, $values);

        $expensePlan = DB::transaction(function () use ($data, $planningYear, $section, $subsection, $catalogItem, $pattern, $calculationValues) {
            $plan = $this->ensureExpensePlan($planningYear);
            $expensePlan = ExpensePlanRow::create([
                'expense_plan_id' => $plan->id,
                'planning_year_id' => $planningYear->id,
                'section_id' => $section->id,
                'subsection_id' => $subsection?->id,
                'catalog_item_id' => $catalogItem?->id,
                'chart_of_account_id' => $catalogItem?->chart_of_account_id,
                'pattern_id' => $pattern->id,
                'version' => (string) $planningYear->year,
                'plan_type' => $pattern->key,
                'item_name' => $catalogItem?->item_name ?: $data['plan_detail'],
                'plan_detail' => $catalogItem?->item_name ?: $data['plan_detail'],
                'detail' => $data['detail'] ?? null,
                'calculation_values' => $calculationValues,
                'pattern_snapshot' => $pattern->snapshot(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            return $expensePlan->load(['section', 'subsection', 'pattern', 'chartOfAccount']);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'entry' => $this->serializePlan($expensePlan),
            ]);
        }

        return redirect()->route('head_of_finance.expense.manage', $planningYear)
            ->with('success', 'ເພີ່ມລາຍການສຳເລັດ');
    }

    public function update(Request $request, int $expensePlanRow)
    {
        $expensePlan = ExpensePlanRow::with(['pattern', 'chartOfAccount'])->findOrFail($expensePlanRow);
        $this->ensurePlanCanBeEdited($expensePlan->planningYear);

        $data = $request->validate([
            'plan_detail' => 'required|string|max:255',
            'detail' => 'nullable|string|max:1000',
            'values' => 'required|array',
        ]);

        $pattern = $expensePlan->pattern ?? new ExpensePattern;
        $values = $data['values'];
        $calculationValues = $this->calculationValues($pattern, $values, $expensePlan->pattern_snapshot);

        DB::transaction(function () use ($expensePlan, $data, $calculationValues) {
            $payload = [
                'detail' => $data['detail'] ?? null,
                'calculation_values' => $calculationValues,
                'updated_by' => Auth::id(),
            ];

            if (! $expensePlan->catalog_item_id) {
                $payload['item_name'] = $data['plan_detail'];
                $payload['plan_detail'] = $data['plan_detail'];
            }

            $expensePlan->update($payload);
        });

        return response()->json([
            'success' => true,
            'entry' => $this->serializePlan($expensePlan->fresh(['section', 'subsection', 'pattern', 'chartOfAccount'])),
        ]);
    }

    public function destroy(Request $request, int $expensePlanRow)
    {
        $expensePlan = ExpensePlanRow::find($expensePlanRow);
        if ($expensePlan) {
            $this->ensurePlanCanBeEdited($expensePlan->planningYear);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Expense rows can be added or removed from expense structure only.',
            ], 403);
        }

        return back()->with('error', 'ຕ້ອງເພີ່ມ ຫຼື ລຶບລາຍການທີ່ໜ້າ Expense structure ເທົ່ານັ້ນ');
    }

    private function ensurePlanCanBeEdited(?PlanningYear $planningYear): void
    {
        abort_if(
            $planningYear?->canBeEdited() === false,
            423,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );
    }

    private function calculationValues(ExpensePattern $pattern, array $values, ?array $snapshot = null): array
    {
        $inputDefaults = $pattern->defaultInputValues($snapshot);
        $enteredValues = collect($values)
            ->reject(fn ($value, string $key): bool => in_array($key, ['item_name', 'reference', 'note'], true))
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->all();

        $calculationValues = array_merge($inputDefaults, $enteredValues);
        $calculationValues['yearly_total'] = $pattern->calculateTotal($calculationValues, $snapshot);

        return $calculationValues;
    }

    private function ensureExpensePlan(PlanningYear $planningYear): ExpensePlan
    {
        return ExpensePlan::firstOrCreate(
            ['planning_year_id' => $planningYear->id],
            [
                'fiscal_year' => $planningYear->year,
                'notes' => null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );
    }

    private function serializePlan(ExpensePlanRow $expensePlan): array
    {
        return [
            'id' => $expensePlan->id,
            'section_id' => $expensePlan->section_id,
            'subsection_id' => $expensePlan->subsection_id,
            'pattern_id' => $expensePlan->pattern_id,
            'pattern_key' => $expensePlan->pattern?->key,
            'plan_detail' => $expensePlan->item_name ?: $expensePlan->plan_detail,
            'detail' => $expensePlan->detail,
            'total' => $expensePlan->yearlyTotal(),
            'values' => array_merge($expensePlan->calculation_values ?? [], [
                'item_name' => $expensePlan->item_name ?: $expensePlan->plan_detail,
                'reference' => $expensePlan->chartOfAccount?->account_code,
                'note' => $expensePlan->detail,
            ]),
        ];
    }
}
