<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ExpensePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensePlanController extends Controller
{
    public function index()
    {
        $plans = ExpensePlan::with(['creator', 'allCategories.items'])
            ->orderByDesc('fiscal_year')
            ->paginate(15);

        return view('dashboards.finance_head.expense.index', compact('plans'));
    }

    public function create()
    {
        return view('dashboards.finance_head.expense.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100|unique:expense_plans,fiscal_year',
            'notes'       => 'nullable|string|max:500',
        ]);

        $plan = ExpensePlan::create([
            'fiscal_year' => $data['fiscal_year'],
            'notes'       => $data['notes'] ?? null,
            'status'      => 'DRAFT',
            'created_by'  => Auth::id(),
        ]);

        return redirect()->route('head_of_finance.expense.manage', $plan)
            ->with('success', 'ສ້າງແຜນງົບປະມານສຳເລັດ');
    }

    public function show(ExpensePlan $expensePlan)
    {
        $expensePlan->load([
            'topCategories.children.children.items.chartOfAccount',
            'topCategories.children.items.chartOfAccount',
            'topCategories.items.chartOfAccount',
            'creator',
        ]);

        return view('dashboards.finance_head.expense.show', compact('expensePlan'));
    }

    public function manage(ExpensePlan $expensePlan)
    {
        $expensePlan->load([
            'topCategories.children.children.items.chartOfAccount',
            'topCategories.children.items.chartOfAccount',
            'topCategories.items.chartOfAccount',
        ]);

        $chartOfAccounts = \App\Models\ChartOfAccount::orderBy('account_code')->get();

        return view('dashboards.finance_head.expense.manage', compact('expensePlan', 'chartOfAccounts'));
    }

    public function destroy(ExpensePlan $expensePlan)
    {
        if ($expensePlan->isApproved()) {
            return back()->with('error', 'ບໍ່ສາມາດລຶບແຜນທີ່ອະນຸມັດແລ້ວ');
        }

        $expensePlan->delete();

        return redirect()->route('head_of_finance.expense.index')
            ->with('success', 'ລຶບແຜນງົບປະມານສຳເລັດ');
    }

    public function approve(ExpensePlan $expensePlan)
    {
        $expensePlan->update(['status' => 'APPROVED']);

        return back()->with('success', 'ອະນຸມັດແຜນງົບປະມານສຳເລັດ');
    }
}
