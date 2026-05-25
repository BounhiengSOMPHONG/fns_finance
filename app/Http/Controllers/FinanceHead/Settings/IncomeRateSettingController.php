<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\IncomeRateSetting;
use Illuminate\Http\Request;

class IncomeRateSettingController extends Controller
{
    public function index()
    {
        $rates = IncomeRateSetting::allKeyed();

        return view('dashboards.finance_head.settings.income-rates.index', compact('rates'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'item3_rate' => 'required|numeric|min:0',
            'item4_rate' => 'required|numeric|min:0',
            'item5_rate' => 'required|numeric|min:0',
            'item6_rate' => 'required|numeric|min:0',
        ]);

        foreach (['item3', 'item4', 'item5', 'item6'] as $key) {
            IncomeRateSetting::where('key', $key . '_rate')->update([
                'rate' => $validated[$key . '_rate'],
            ]);
        }

        return redirect()
            ->route('head_of_finance.settings.income-rates.index')
            ->with('success', 'ອັບເດດອັດຕາສຳເລັດ');
    }
}
