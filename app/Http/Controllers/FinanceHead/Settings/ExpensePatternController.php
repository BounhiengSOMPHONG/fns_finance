<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpensePatternController extends Controller
{
    public function index()
    {
        $patterns = ExpensePattern::with('leafDefaultSubsections.section')
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
            'fields_schema' => [],
            'formula_schema' => ['operation' => 'multiply', 'fields' => []],
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
            ],
            'default_label' => ['required', 'string', 'max:255'],
            'data_type' => ['required', Rule::in(['text', 'number', 'date', 'boolean'])],
            'display_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_required' => ['nullable', 'boolean'],
            'is_calculated' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'include_in_formula' => ['nullable', 'boolean'],
            'default_value' => ['nullable', 'string', 'max:255'],
        ]);

        $fields = collect($expensePattern->fields_schema ?? []);
        if ($fields->contains(fn (array $field): bool => ($field['field_key'] ?? null) === $data['field_key'])) {
            return back()->withErrors(['field_key' => 'This field key already exists in the pattern.']);
        }

        $fields->push([
            'field_key' => $data['field_key'],
            'default_label' => $data['default_label'],
            'data_type' => $data['data_type'],
            'display_order' => $data['display_order'],
            'is_required' => $request->boolean('is_required'),
            'is_calculated' => $request->boolean('is_calculated'),
            'is_active' => $request->boolean('is_active', true),
            'default_value' => $data['default_value'] ?? null,
        ]);

        $expensePattern->fields_schema = $fields
            ->sortBy(fn (array $field): int => (int) ($field['display_order'] ?? 0))
            ->values()
            ->all();
        $expensePattern->formula_schema = $this->formulaSchemaAfterToggle(
            $expensePattern,
            $data['field_key'],
            $request->boolean('include_in_formula')
        );
        $expensePattern->save();

        return back()->with('success', 'Pattern field added.');
    }

    public function updateField(Request $request, ExpensePattern $expensePattern, string $fieldKey)
    {
        $data = $request->validate([
            'default_label' => ['required', 'string', 'max:255'],
            'data_type' => ['required', Rule::in(['text', 'number', 'date', 'boolean'])],
            'display_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_required' => ['nullable', 'boolean'],
            'is_calculated' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'include_in_formula' => ['nullable', 'boolean'],
            'default_value' => ['nullable', 'string', 'max:255'],
        ]);

        $found = false;
        $fields = collect($expensePattern->fields_schema ?? [])
            ->map(function (array $field) use ($fieldKey, $data, $request, &$found): array {
                if (($field['field_key'] ?? null) !== $fieldKey) {
                    return $field;
                }

                $found = true;

                return array_merge($field, [
                    'default_label' => $data['default_label'],
                    'data_type' => $data['data_type'],
                    'display_order' => $data['display_order'],
                    'is_required' => $request->boolean('is_required'),
                    'is_calculated' => $request->boolean('is_calculated'),
                    'is_active' => $request->boolean('is_active'),
                    'default_value' => $data['default_value'] ?? null,
                ]);
            });

        abort_if(! $found, 404);

        $expensePattern->fields_schema = $fields
            ->sortBy(fn (array $field): int => (int) ($field['display_order'] ?? 0))
            ->values()
            ->all();
        $expensePattern->formula_schema = $this->formulaSchemaAfterToggle(
            $expensePattern,
            $fieldKey,
            $request->boolean('include_in_formula')
        );
        $expensePattern->save();

        return back()->with('success', 'Pattern field updated.');
    }

    public function destroyField(ExpensePattern $expensePattern, string $fieldKey)
    {
        $hasPlanValues = ExpensePlan::where('pattern_id', $expensePattern->id)
            ->where(function ($query) use ($fieldKey): void {
                $query->whereNotNull("calculation_values->{$fieldKey}")
                    ->orWhereNotNull('pattern_snapshot');
            })
            ->exists();

        if ($hasPlanValues) {
            return back()->with('error', 'Cannot delete this field because it is already used by plan data.');
        }

        $expensePattern->fields_schema = collect($expensePattern->fields_schema ?? [])
            ->reject(fn (array $field): bool => ($field['field_key'] ?? null) === $fieldKey)
            ->values()
            ->all();
        $formula = $expensePattern->formula_schema ?? ['operation' => 'multiply', 'fields' => []];
        $formula['fields'] = collect($formula['fields'] ?? [])
            ->reject(fn (string $key): bool => $key === $fieldKey)
            ->values()
            ->all();
        $expensePattern->formula_schema = $formula;
        $expensePattern->save();

        return back()->with('success', 'Pattern field deleted.');
    }

    private function formulaSchemaAfterToggle(ExpensePattern $expensePattern, string $fieldKey, bool $include): array
    {
        $formula = $expensePattern->formula_schema ?? ['operation' => 'multiply', 'fields' => []];
        $fields = collect($formula['fields'] ?? [])
            ->filter(fn ($key): bool => is_string($key) && $key !== '' && $key !== $fieldKey);

        if ($include) {
            $fields->push($fieldKey);
        }

        return [
            'operation' => 'multiply',
            'fields' => $fields->values()->all(),
        ];
    }
}
