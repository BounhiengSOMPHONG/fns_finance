<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpensePattern;
use Illuminate\Http\Request;

class ExpensePatternController extends Controller
{
    public function index()
    {
        return redirect()
            ->route('head_of_finance.settings.expense-setup.index')
            ->with('info', 'Expense formulas are locked to the 5 system defaults.');
    }

    public function store(Request $request)
    {
        abort(403, 'Expense formulas are locked to the 5 system defaults.');
    }

    public function update(Request $request, ExpensePattern $expensePattern)
    {
        abort(403, 'Expense formulas are locked to the 5 system defaults.');
    }

    public function storeField(Request $request, ExpensePattern $expensePattern)
    {
        abort(403, 'Expense formulas are locked to the 5 system defaults.');
    }

    public function updateField(Request $request, ExpensePattern $expensePattern, string $fieldKey)
    {
        abort(403, 'Expense formulas are locked to the 5 system defaults.');
    }

    public function destroyField(ExpensePattern $expensePattern, string $fieldKey)
    {
        abort(403, 'Expense formulas are locked to the 5 system defaults.');
    }
}
