<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\NuolPctSetting;
use Illuminate\Http\Request;

class NuolPctSettingController extends Controller
{
    public function index()
    {
        $settings = NuolPctSetting::orderByDesc('start_year')->orderBy('level')->get();

        return view('dashboards.finance_head.settings.nuol-pct.index', compact('settings'));
    }

    public function create()
    {
        return view('dashboards.finance_head.settings.nuol-pct.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'level'      => 'required|in:bachelor,master_phd',
            'percentage' => 'required|numeric|min:0|max:100',
            'gov_doc_id' => 'nullable|string|max:255',
            'start_year' => 'required|integer|min:2000|max:2100',
        ]);

        NuolPctSetting::create([
            'level'      => $validated['level'],
            'percentage' => $validated['percentage'] / 100,
            'gov_doc_id' => $validated['gov_doc_id'],
            'start_year' => $validated['start_year'],
        ]);

        return redirect()
            ->route('head_of_finance.settings.nuol-pct.index')
            ->with('success', 'ບັນທຶກອັດຕາ ມຊ ສຳເລັດ');
    }

    public function edit(NuolPctSetting $nuolPct)
    {
        return view('dashboards.finance_head.settings.nuol-pct.edit', compact('nuolPct'));
    }

    public function update(Request $request, NuolPctSetting $nuolPct)
    {
        $validated = $request->validate([
            'level'      => 'required|in:bachelor,master_phd',
            'percentage' => 'required|numeric|min:0|max:100',
            'gov_doc_id' => 'nullable|string|max:255',
            'start_year' => 'required|integer|min:2000|max:2100',
        ]);

        $nuolPct->update([
            'level'      => $validated['level'],
            'percentage' => $validated['percentage'] / 100,
            'gov_doc_id' => $validated['gov_doc_id'],
            'start_year' => $validated['start_year'],
        ]);

        return redirect()
            ->route('head_of_finance.settings.nuol-pct.index')
            ->with('success', 'ອັບເດດອັດຕາ ມຊ ສຳເລັດ');
    }

    public function destroy(NuolPctSetting $nuolPct)
    {
        $nuolPct->delete();

        return redirect()
            ->route('head_of_finance.settings.nuol-pct.index')
            ->with('success', 'ລຶບອັດຕາ ມຊ ສຳເລັດ');
    }
}
