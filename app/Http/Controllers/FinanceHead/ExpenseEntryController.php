<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ExpenseEntry;
use App\Models\ExpensePlan;
use Illuminate\Http\Request;

class ExpenseEntryController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request, true);

        $plan = ExpensePlan::findOrFail($data['plan_id']);
        if ($plan->isApproved()) {
            return $this->approvedBlocked($request);
        }

        $entry = ExpenseEntry::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'entry' => $entry->fresh()]);
        }

        return redirect()->route('head_of_finance.expense.manage', $plan)
            ->with('success', 'ເພີ່ມລາຍການສຳເລັດ');
    }

    public function update(Request $request, ExpenseEntry $expenseEntry)
    {
        if ($expenseEntry->plan->isApproved()) {
            return $this->approvedBlocked($request);
        }

        $expenseEntry->update($this->validated($request, false));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'entry' => $expenseEntry->fresh()]);
        }

        return back()->with('success', 'ອັບເດດລາຍການສຳເລັດ');
    }

    public function destroy(Request $request, ExpenseEntry $expenseEntry)
    {
        if ($expenseEntry->plan->isApproved()) {
            return $this->approvedBlocked($request);
        }

        $plan = $expenseEntry->plan;
        $expenseEntry->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('head_of_finance.expense.manage', $plan)
            ->with('success', 'ລຶບລາຍການສຳເລັດ');
    }

    private function validated(Request $request, bool $isStore): array
    {
        $rules = [
            'ref_code'            => 'nullable|string|max:30',
            'chart_of_account_id' => 'nullable|exists:chart_of_accounts,id',
            'main_cat_code'       => 'nullable|string|max:30',
            'main_cat'            => 'nullable|string|max:255',
            'main_item_code'      => 'nullable|string|max:30',
            'main_item'           => 'nullable|string|max:255',
            'sub_item'            => 'required|string|max:255',
            'rate1'               => 'nullable|numeric|min:0',
            'rate2'               => 'nullable|numeric|min:0',
            'qty'                 => 'nullable|numeric|min:0',
            'period'              => 'nullable|numeric|min:0',
            'frequency'           => 'nullable|numeric|min:0',
            'add_on'              => 'nullable|numeric|min:0',
            'note'                => 'nullable|string|max:500',
            'sort_order'          => 'nullable|integer|min:0',
        ];
        if ($isStore) {
            $rules['plan_id'] = 'required|exists:expense_plans,id';
        }

        $data = $request->validate($rules);

        foreach (['rate1', 'rate2', 'add_on'] as $f) {
            $data[$f] = $data[$f] ?? 0;
        }
        foreach (['qty', 'period', 'frequency'] as $f) {
            $data[$f] = $data[$f] ?? 1;
        }

        return $data;
    }

    private function approvedBlocked(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'ບໍ່ສາມາດແກ້ໄຂແຜນທີ່ອະນຸມັດແລ້ວ'], 403);
        }

        return back()->with('error', 'ບໍ່ສາມາດແກ້ໄຂແຜນທີ່ອະນຸມັດແລ້ວ');
    }
}
