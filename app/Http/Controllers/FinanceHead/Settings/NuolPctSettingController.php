<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\NuolPctSetting;
use Illuminate\Http\Request;

class NuolPctSettingController extends Controller
{
    public function index()
    {
        // Merged into the course-credits settings page.
        return redirect()->route('head_of_finance.settings.course-credits.index');
    }

    public function update(Request $request, NuolPctSetting $nuolPct)
    {
        $validated = $request->validate([
            'level'      => 'required|in:bachelor,master,phd',
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
            ->route('head_of_finance.settings.course-credits.index')
            ->with('success', 'ອັບເດດອັດຕາ ມຊ ສຳເລັດ');
    }
}
