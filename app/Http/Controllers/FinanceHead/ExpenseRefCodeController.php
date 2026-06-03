<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRefCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseRefCodeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'parent' => 'nullable|string|max:30',
            'code'   => 'required_without:parent|nullable|string|max:30',
            'label'  => 'nullable|string|max:255',
        ]);

        // A ລາຍການຫຼັກ (level 2) is added under a parent ໝວດຫຼັກ — its code is
        // auto-built as "<parent>.<next>" so it can never be orphaned. A ໝວດຫຼັກ
        // (level 1) is added with its code typed directly.
        if (! empty($data['parent'])) {
            $parent = $data['parent'];
            $next = ExpenseRefCode::where('code', 'like', $parent . '.%')
                ->pluck('code')
                ->map(fn ($c) => (int) Str::afterLast($c, '.'))
                ->max() ?? 0;
            $code = $parent . '.' . ($next + 1);
        } else {
            $code = $data['code'];
        }

        if (ExpenseRefCode::where('code', $code)->exists()) {
            return back()->with('error', "ລະຫັດ {$code} ມີຢູ່ແລ້ວ");
        }

        ExpenseRefCode::create([
            'code'       => $code,
            'label'      => $data['label'] ?? null,
            'sort_order' => (ExpenseRefCode::max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('success', "ເພີ່ມ {$code} ສຳເລັດ");
    }

    public function update(Request $request, ExpenseRefCode $expenseRefCode)
    {
        $data = $request->validate([
            'code'  => 'required|string|max:30|unique:expense_ref_codes,code,' . $expenseRefCode->id,
            'label' => 'nullable|string|max:255',
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
