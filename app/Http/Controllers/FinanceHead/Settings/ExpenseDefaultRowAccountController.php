<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ExpenseSubsection;
use App\Models\ExpenseSubsectionDefaultRow;
use Illuminate\Http\Request;

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
            'note' => ['nullable', 'string', 'max:1000'],
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
            'note' => $data['note'] ?? null,
            'sort_order' => $data['sort_order'],
            'default_values' => [],
            'is_active' => true,
        ]);

        return back()->with('success', 'Default row added.');
    }

    public function update(Request $request, ExpenseSubsectionDefaultRow $expenseSubsectionDefaultRow)
    {
        $data = $request->validate([
            'chart_of_account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
        ]);

        $account = ! empty($data['chart_of_account_id'])
            ? ChartOfAccount::findOrFail($data['chart_of_account_id'])
            : null;

        $expenseSubsectionDefaultRow->update([
            'chart_of_account_id' => $account?->id,
            'reference' => $account?->account_code,
        ]);

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

        return back()->with('success', 'Default row account link saved.');
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
