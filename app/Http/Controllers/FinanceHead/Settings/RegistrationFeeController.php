<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\RegistrationFeeSetting;
use App\Models\RegistrationFeeItem;
use Illuminate\Http\Request;

class RegistrationFeeController extends Controller
{
    public function index()
    {
        $settings = RegistrationFeeSetting::with('items')->orderByDesc('start_year')->orderBy('section_type')->get();

        return view('dashboards.finance_head.settings.registration-fee.index', compact('settings'));
    }

    public function edit(RegistrationFeeSetting $registrationFee)
    {
        $registrationFee->load('items');

        return view('dashboards.finance_head.settings.registration-fee.edit', compact('registrationFee'));
    }

    public function update(Request $request, RegistrationFeeSetting $registrationFee)
    {
        $validated = $request->validate([
            'section_type'       => 'required|in:year2_4,year1',
            'gov_doc_id'         => 'nullable|string|max:255',
            'start_year'         => 'required|integer|min:2000|max:2100',
            'items'              => 'required|array|min:1',
            'items.*.name'       => 'required|string|max:255',
            'items.*.amount'     => 'required|numeric|min:0',
            'items.*.nuol_pct'   => 'required|numeric|min:0|max:100',
        ]);

        $registrationFee->update([
            'section_type' => $validated['section_type'],
            'gov_doc_id'   => $validated['gov_doc_id'],
            'start_year'   => $validated['start_year'],
        ]);

        $registrationFee->items()->delete();

        foreach ($validated['items'] as $index => $item) {
            RegistrationFeeItem::create([
                'fee_setting_id' => $registrationFee->id,
                'sort_order'     => $index,
                'name'           => $item['name'],
                'amount'         => $item['amount'],
                'nuol_pct'       => $item['nuol_pct'] / 100,
            ]);
        }

        return redirect()
            ->route('head_of_finance.settings.registration-fee.index')
            ->with('success', 'ອັບເດດຄ່າລົງທະບຽນສຳເລັດ');
    }
}
