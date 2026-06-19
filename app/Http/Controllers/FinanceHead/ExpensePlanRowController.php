<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpensePlanValue;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpensePlanRowController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'planning_year_id' => 'required|exists:planning_years,id',
            'section_id' => 'required|exists:expense_sections,id',
            'subsection_id' => 'nullable|exists:expense_subsections,id',
            'pattern_id' => 'required|exists:expense_patterns,id',
            'plan_detail' => 'required|string|max:255',
            'detail' => 'nullable|string|max:1000',
            'values' => 'required|array',
        ]);

        $planningYear = PlanningYear::findOrFail($data['planning_year_id']);
        $this->ensurePlanCanBeEdited($planningYear);

        $section = ExpenseSection::findOrFail($data['section_id']);
        $subsection = $data['subsection_id'] ? ExpenseSubsection::findOrFail($data['subsection_id']) : null;
        $pattern = ExpensePattern::with('fields')->findOrFail($data['pattern_id']);

        $values = $data['values'];
        $values['yearly_total'] = $this->calculatePatternTotal($pattern->key, $values);

        $expensePlan = DB::transaction(function () use ($data, $planningYear, $section, $subsection, $pattern, $values) {
            $expensePlan = ExpensePlan::create([
                'planning_year_id' => $planningYear->id,
                'section_id' => $section->id,
                'subsection_id' => $subsection?->id,
                'pattern_id' => $pattern->id,
                'version' => (string) $planningYear->year,
                'plan_type' => $pattern->key,
                'plan_detail' => $data['plan_detail'],
                'detail' => $data['detail'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ($pattern->fields as $field) {
                if (!array_key_exists($field->field_key, $values)) {
                    continue;
                }

                $this->storeValue($expensePlan, $field->field_key, $field->data_type, $values[$field->field_key]);
            }

            return $expensePlan->load(['values', 'section', 'subsection', 'pattern']);
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
        $expensePlan = ExpensePlan::with(['pattern.fields', 'values'])->findOrFail($expensePlanRow);
        $this->ensurePlanCanBeEdited($expensePlan->planningYear);

        $data = $request->validate([
            'plan_detail' => 'required|string|max:255',
            'detail' => 'nullable|string|max:1000',
            'values' => 'required|array',
        ]);

        $values = $this->preserveLockedRowValues($expensePlan, $data['values']);
        $values['yearly_total'] = $this->calculatePatternTotal($expensePlan->pattern?->key, $values);

        DB::transaction(function () use ($expensePlan, $data, $values) {
            $expensePlan->update([
                'plan_detail' => $expensePlan->plan_detail,
                'detail' => $data['detail'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $expensePlan->values()->delete();
            foreach ($expensePlan->pattern->fields as $field) {
                if (array_key_exists($field->field_key, $values)) {
                    $this->storeValue($expensePlan, $field->field_key, $field->data_type, $values[$field->field_key]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'entry' => $this->serializePlan($expensePlan->fresh(['values', 'section', 'subsection', 'pattern'])),
        ]);
    }

    public function destroy(Request $request, int $expensePlanRow)
    {
        $expensePlan = ExpensePlan::find($expensePlanRow);
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

    private function preserveLockedRowValues(ExpensePlan $expensePlan, array $values): array
    {
        $currentValues = $expensePlan->values->mapWithKeys(function (ExpensePlanValue $value) {
            return [$value->field_key => $value->value_number ?? $value->value_text ?? $value->value_date ?? $value->value_boolean];
        });

        foreach (['item_name', 'reference'] as $fieldKey) {
            if ($currentValues->has($fieldKey)) {
                $values[$fieldKey] = $currentValues->get($fieldKey);
            }
        }

        return $values;
    }

    private function storeValue(ExpensePlan $expensePlan, string $fieldKey, string $dataType, mixed $value): void
    {
        $payload = [
            'expense_plan_id' => $expensePlan->id,
            'field_key' => $fieldKey,
            'value_text' => null,
            'value_number' => null,
            'value_date' => null,
            'value_boolean' => null,
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

        ExpensePlanValue::create($payload);
    }

    private function calculatePatternTotal(?string $patternKey, array $values): float
    {
        $number = fn (string $key): float => (float) ($values[$key] ?? 0);

        return match ($patternKey) {
            'monthly' => $number('amount_per_month') * $number('month_count'),
            'unit_quantity' => $number('unit_price') * $number('quantity'),
            'unit_quantity_frequency' => $number('unit_price') * $number('quantity') * $number('times_per_year'),
            'frequency_based' => $number('unit_price') * $number('quantity') * $number('frequency_count'),
            'event_based' => $number('unit_price') * $number('event_count') * $number('people_count'),
            default => (float) ($values['yearly_total'] ?? 0),
        };
    }

    private function serializePlan(ExpensePlan $expensePlan): array
    {
        return [
            'id' => $expensePlan->id,
            'section_id' => $expensePlan->section_id,
            'subsection_id' => $expensePlan->subsection_id,
            'pattern_id' => $expensePlan->pattern_id,
            'pattern_key' => $expensePlan->pattern?->key,
            'plan_detail' => $expensePlan->plan_detail,
            'detail' => $expensePlan->detail,
            'total' => $expensePlan->yearlyTotal(),
            'values' => $expensePlan->values->mapWithKeys(function (ExpensePlanValue $value) {
                return [$value->field_key => $value->value_number ?? $value->value_text ?? $value->value_date ?? $value->value_boolean];
            }),
        ];
    }
}
