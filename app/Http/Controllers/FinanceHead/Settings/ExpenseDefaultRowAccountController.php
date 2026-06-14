<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpensePlan;
use App\Models\ExpensePlanValue;
use App\Models\ExpenseSubsection;
use App\Models\ExpenseSubsectionDefaultRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseDefaultRowAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        $rows = ExpenseSubsectionDefaultRow::with('chartOfAccount.parent')
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($nested) use ($query) {
                    $nested->where('item_name', 'like', "%{$query}%")
                        ->orWhere('subsection_code', 'like', "%{$query}%")
                        ->orWhere('reference', 'like', "%{$query}%");
                });
            })
            ->orderBy('subsection_code')
            ->orderBy('sort_order')
            ->get();

        $subsectionLabels = ExpenseSubsection::with('section.planningYear')
            ->get()
            ->sortByDesc(fn (ExpenseSubsection $subsection) => $subsection->section?->planningYear?->year ?? 0)
            ->unique('code')
            ->keyBy('code');

        $accounts = ChartOfAccount::with('parent')
            ->whereDoesntHave('children')
            ->orderBy('account_code')
            ->get();

        $accountOptions = $accounts->map(fn (ChartOfAccount $account) => [
            'id' => $account->id,
            'code' => $account->account_code,
            'name' => $account->account_name,
            'label' => $this->accountLabel($account),
        ]);

        return view('dashboards.finance_head.settings.expense-default-rows.index', [
            'rows' => $rows,
            'query' => $query,
            'subsectionLabels' => $subsectionLabels,
            'accountOptions' => $accountOptions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subsection_code' => ['required', 'string', 'max:30'],
            'item_name' => ['required', 'string', 'max:255'],
            'chart_of_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $account = ! empty($data['chart_of_account_id'])
            ? ChartOfAccount::findOrFail($data['chart_of_account_id'])
            : null;

        ExpenseSubsectionDefaultRow::create([
            'subsection_code' => $data['subsection_code'],
            'item_name' => $data['item_name'],
            'reference' => $account?->account_code,
            'chart_of_account_id' => $account?->id,
            'note' => null,
            'sort_order' => $data['sort_order'],
            'default_values' => [],
            'is_active' => true,
        ]);

        return back()->with('success', 'Default row added.');
    }

    public function update(Request $request, ExpenseSubsectionDefaultRow $expenseSubsectionDefaultRow)
    {
        $data = $request->validate([
            'item_name' => ['sometimes', 'required', 'string', 'max:255'],
            'chart_of_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:1', 'max:999'],
        ]);

        $account = ! empty($data['chart_of_account_id'])
            ? ChartOfAccount::findOrFail($data['chart_of_account_id'])
            : null;

        $payload = [
            'chart_of_account_id' => $account?->id,
            'reference' => $account?->account_code,
        ];

        foreach (['item_name', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        $oldItemName = $expenseSubsectionDefaultRow->item_name;

        DB::transaction(function () use ($expenseSubsectionDefaultRow, $payload, $oldItemName): void {
            $expenseSubsectionDefaultRow->update($payload);
            $this->syncPlanRowsFromDefaultRow($expenseSubsectionDefaultRow, $oldItemName);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'row' => [
                    'id' => $expenseSubsectionDefaultRow->id,
                    'chart_of_account_id' => $expenseSubsectionDefaultRow->chart_of_account_id,
                    'reference' => $expenseSubsectionDefaultRow->reference,
                    'account_label' => $account ? $this->accountLabel($account) : null,
                ],
            ]);
        }

        return back()->with('success', 'Default row saved.');
    }

    public function destroy(ExpenseSubsectionDefaultRow $expenseSubsectionDefaultRow)
    {
        DB::transaction(function () use ($expenseSubsectionDefaultRow): void {
            $this->deletePlanRowsForDefaultRow($expenseSubsectionDefaultRow);
            $expenseSubsectionDefaultRow->delete();
        });

        return back()->with('success', 'Default row deleted.');
    }

    private function syncPlanRowsFromDefaultRow(ExpenseSubsectionDefaultRow $defaultRow, string $oldItemName): void
    {
        $subsectionIds = ExpenseSubsection::where('code', $defaultRow->subsection_code)->pluck('id');

        if ($subsectionIds->isEmpty()) {
            return;
        }

        $plans = ExpensePlan::whereIn('subsection_id', $subsectionIds)
            ->where('plan_detail', $oldItemName)
            ->get();

        foreach ($plans as $plan) {
            $plan->update([
                'plan_detail' => $defaultRow->item_name,
            ]);

            $this->setPlanTextValue($plan, 'item_name', $defaultRow->item_name);
            $this->setPlanTextValue($plan, 'reference', $defaultRow->reference);
        }
    }

    private function deletePlanRowsForDefaultRow(ExpenseSubsectionDefaultRow $defaultRow): void
    {
        $subsectionIds = ExpenseSubsection::where('code', $defaultRow->subsection_code)->pluck('id');

        if ($subsectionIds->isEmpty()) {
            return;
        }

        $plans = ExpensePlan::whereIn('subsection_id', $subsectionIds)
            ->where('plan_detail', $defaultRow->item_name)
            ->get();

        ExpensePlanValue::whereIn('expense_plan_id', $plans->pluck('id'))->delete();
        ExpensePlan::whereIn('id', $plans->pluck('id'))->delete();
    }

    private function setPlanTextValue(ExpensePlan $plan, string $fieldKey, ?string $value): void
    {
        ExpensePlanValue::updateOrCreate([
            'expense_plan_id' => $plan->id,
            'field_key' => $fieldKey,
        ], [
            'value_text' => $value,
            'value_number' => null,
            'value_date' => null,
            'value_boolean' => null,
        ]);
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
}
