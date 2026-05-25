<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\CreditUnitPriceSetting;
use Illuminate\Http\Request;

class CreditUnitPriceController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditUnitPriceSetting::query();

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $settings = $query->orderByDesc('start_year')->orderBy('level')->paginate(15)->withQueryString();

        return view('dashboards.finance_head.settings.credit-unit-price.index', compact('settings'));
    }

    public function create()
    {
        return view('dashboards.finance_head.settings.credit-unit-price.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'level'             => 'required|in:bachelor,master,phd',
            'credit_unit_price' => 'required|numeric|min:0',
            'gov_doc_id'        => 'nullable|string|max:255',
            'start_year'        => 'required|integer|min:2000|max:2100',
        ]);

        CreditUnitPriceSetting::create($validated);

        return redirect()
            ->route('head_of_finance.settings.credit-unit-price.index')
            ->with('success', 'ສ້າງລາຄາຄ່າໜ່ວຍກິດສຳເລັດ');
    }

    public function edit(CreditUnitPriceSetting $creditUnitPrice)
    {
        return view('dashboards.finance_head.settings.credit-unit-price.edit', compact('creditUnitPrice'));
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
            ->route('head_of_finance.settings.credit-unit-price.index')
            ->with('success', 'ອັບເດດລາຄາຄ່າໜ່ວຍກິດສຳເລັດ');
    }

    public function destroy(CreditUnitPriceSetting $creditUnitPrice)
    {
        $creditUnitPrice->delete();

        return redirect()
            ->route('head_of_finance.settings.credit-unit-price.index')
            ->with('success', 'ລຶບລາຄາຄ່າໜ່ວຍກິດສຳເລັດ');
    }
}
