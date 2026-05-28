<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRefCode;
use Illuminate\Http\Request;

class ExpenseRefCodeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'         => 'required|string|max:30|unique:expense_ref_codes,code',
            'label'        => 'nullable|string|max:255',
            'account_code' => 'nullable|string|max:30',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        ExpenseRefCode::create($data);

        return back()->with('success', 'ເພີ່ມລະຫັດອ້າງອີງສຳເລັດ');
    }

    public function update(Request $request, ExpenseRefCode $expenseRefCode)
    {
        $data = $request->validate([
            'code'         => 'required|string|max:30|unique:expense_ref_codes,code,' . $expenseRefCode->id,
            'label'        => 'nullable|string|max:255',
            'account_code' => 'nullable|string|max:30',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        $expenseRefCode->update($data);

        return back()->with('success', 'ອັບເດດລະຫັດອ້າງອີງສຳເລັດ');
    }

    public function destroy(ExpenseRefCode $expenseRefCode)
    {
        $expenseRefCode->delete();

        return back()->with('success', 'ລຶບລະຫັດອ້າງອີງສຳເລັດ');
    }
}
