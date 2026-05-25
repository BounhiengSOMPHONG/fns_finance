<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\CreditUnitPriceSetting;
use Illuminate\Http\Request;

class CreditUnitPriceController extends Controller
{
    public function index()
    {
        // Merged into the course-credits settings page.
        return redirect()->route('head_of_finance.settings.course-credits.index');
    }

    public function update(Request $request, CreditUnitPriceSetting $creditUnitPrice)
    {
        $validated = $request->validate([
            'level'             => 'required|in:bachelor,master,phd',
            'credit_unit_price' => 'required|numeric|min:0',
            'gov_doc_id'        => 'nullable|string|max:255',
            'start_year'        => 'required|integer|min:2000|max:2100',
        ]);

        $creditUnitPrice->update($validated);

        return redirect()
            ->route('head_of_finance.settings.course-credits.index')
            ->with('success', 'ອັບເດດລາຄາຄ່າໜ່ວຍກິດສຳເລັດ');
    }
}
