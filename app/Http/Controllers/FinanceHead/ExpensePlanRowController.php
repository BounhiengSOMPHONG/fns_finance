<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCalculationRule;
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
        $section = ExpenseSection::findOrFail($data['section_id']);
        $subsection = $data['subsection_id'] ? ExpenseSubsection::findOrFail($data['subsection_id']) : null;
        $pattern = ExpensePattern::with('fields')->findOrFail($data['pattern_id']);

        $values = $data['values'];
        $rule = ExpenseCalculationRule::where('planning_year_id', $planningYear->id)
            ->where('pattern_id', $pattern->id)
            ->where(function ($query) use ($section) {
                $query->whereNull('section_id')->orWhere('section_id', $section->id);
            })
            ->where(function ($query) use ($subsection) {
                $query->whereNull('subsection_id');
                if ($subsection) {
                    $query->orWhere('subsection_id', $subsection->id);
                }
            })
            ->where('is_active', true)
            ->orderByRaw('subsection_id IS NULL')
            ->orderByRaw('section_id IS NULL')
            ->first();

        if ($rule) {
            $values[$rule->target_field_key] = $this->calculateFormula($rule->formula, $values);
        }

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

        $data = $request->validate([
            'plan_detail' => 'required|string|max:255',
            'detail' => 'nullable|string|max:1000',
            'values' => 'required|array',
        ]);

        $rule = ExpenseCalculationRule::where('planning_year_id', $expensePlan->planning_year_id)
            ->where('pattern_id', $expensePlan->pattern_id)
            ->where(function ($query) use ($expensePlan) {
                $query->whereNull('section_id')->orWhere('section_id', $expensePlan->section_id);
            })
            ->where(function ($query) use ($expensePlan) {
                $query->whereNull('subsection_id');
                if ($expensePlan->subsection_id) {
                    $query->orWhere('subsection_id', $expensePlan->subsection_id);
                }
            })
            ->where('is_active', true)
            ->orderByRaw('subsection_id IS NULL')
            ->orderByRaw('section_id IS NULL')
            ->first();

        $values = $this->preserveLockedRowValues($expensePlan, $data['values']);
        if ($rule) {
            $values[$rule->target_field_key] = $this->calculateFormula($rule->formula, $values);
        }

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
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Expense rows can be added or removed from expense structure only.',
            ], 403);
        }

        return back()->with('error', 'ຕ້ອງເພີ່ມ ຫຼື ລຶບລາຍການທີ່ໜ້າ Expense structure ເທົ່ານັ້ນ');
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
