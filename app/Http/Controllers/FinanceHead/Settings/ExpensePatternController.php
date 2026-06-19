<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpensePattern;
use App\Models\ExpensePatternField;
use App\Models\ExpensePlan;
use App\Models\ExpensePlanValue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpensePatternController extends Controller
{
    public function index()
    {
        $patterns = ExpensePattern::with([
            'fields',
            'leafDefaultSubsections.section',
        ])
            ->withCount('leafDefaultSubsections')
            ->orderBy('id')
            ->get();

        return view('dashboards.finance_head.settings.expense-patterns.index', compact('patterns'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:expense_patterns,key'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ExpensePattern::create([
            'key' => $data['key'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Expense pattern created.');
    }

    public function update(Request $request, ExpensePattern $expensePattern)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $expensePattern->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Expense pattern updated.');
    }

    public function storeField(Request $request, ExpensePattern $expensePattern)
    {
        $data = $request->validate([
            'field_key' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('expense_pattern_fields', 'field_key')->where('pattern_id', $expensePattern->id),
            ],
            'default_label' => ['required', 'string', 'max:255'],
            'data_type' => ['required', Rule::in(['text', 'number', 'date', 'boolean'])],
            'display_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_required' => ['nullable', 'boolean'],
            'is_calculated' => ['nullable', 'boolean'],
            'default_value' => ['nullable', 'string', 'max:255'],
        ]);

        $expensePattern->fields()->create([
            'field_key' => $data['field_key'],
            'default_label' => $data['default_label'],
            'data_type' => $data['data_type'],
            'display_order' => $data['display_order'],
            'is_required' => $request->boolean('is_required'),
            'is_calculated' => $request->boolean('is_calculated'),
            'default_value' => $data['default_value'] ?? null,
        ]);

        return back()->with('success', 'Pattern field added.');
    }

    public function updateField(Request $request, ExpensePatternField $expensePatternField)
    {
        $data = $request->validate([
            'default_label' => ['required', 'string', 'max:255'],
            'data_type' => ['required', Rule::in(['text', 'number', 'date', 'boolean'])],
            'display_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_required' => ['nullable', 'boolean'],
            'is_calculated' => ['nullable', 'boolean'],
            'default_value' => ['nullable', 'string', 'max:255'],
        ]);

        $expensePatternField->update([
            'default_label' => $data['default_label'],
            'data_type' => $data['data_type'],
            'display_order' => $data['display_order'],
            'is_required' => $request->boolean('is_required'),
            'is_calculated' => $request->boolean('is_calculated'),
            'default_value' => $data['default_value'] ?? null,
        ]);

        return back()->with('success', 'Pattern field updated.');
    }

    public function destroyField(ExpensePatternField $expensePatternField)
    {
        $planIds = ExpensePlan::where('pattern_id', $expensePatternField->pattern_id)->pluck('id');
        $hasPlanValues = $planIds->isNotEmpty() && ExpensePlanValue::whereIn('expense_plan_id', $planIds)
            ->where('field_key', $expensePatternField->field_key)
            ->exists();

        if ($hasPlanValues) {
            return back()->with('error', 'Cannot delete this field because it is already used by plan data.');
        }

        $expensePatternField->delete();

        return back()->with('success', 'Pattern field deleted.');
    }
}
