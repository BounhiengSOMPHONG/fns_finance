<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\CourseCreditSetting;
use App\Models\CourseCreditSplitSetting;
use App\Models\CreditUnitPriceSetting;
use App\Models\DegreeProgram;
use App\Models\NuolPctSetting;
use Illuminate\Http\Request;

class CourseCreditController extends Controller
{
    public function index()
    {
        // Merged settings page: credit-unit prices (per level) + course credits (per program).
        $levelRank = ['bachelor' => 0, 'master' => 1, 'phd' => 2];

        $courseCredits = CourseCreditSetting::with('degreeProgram')->get()
            ->sortBy(fn($s) => sprintf(
                '%d-%02d-%s',
                $levelRank[$s->degreeProgram?->level] ?? 9,
                $s->degreeProgram?->study_year ?? 99,
                $s->degreeProgram?->name
            ))
            ->values();

        // latest credit-unit price per level, keyed by level
        $prices = CreditUnitPriceSetting::orderByDesc('start_year')
            ->get()->groupBy('level')->map->first();

        // latest NUOL % per level, keyed by level
        $nuolPcts = NuolPctSetting::orderByDesc('start_year')
            ->get()->groupBy('level')->map->first();

        $creditSplits = CourseCreditSplitSetting::orderByDesc('start_year')
            ->get()->groupBy('level')->map->first();

        $programs = DegreeProgram::where('is_active', true)
            ->orderBy('level')
            ->orderByRaw('study_year IS NULL')
            ->orderBy('study_year')
            ->orderBy('name')
            ->get();

        $creditPrices = CreditUnitPriceSetting::orderByDesc('start_year')
            ->get()
            ->groupBy('level')
            ->map(fn($i) => (float) $i->first()->credit_unit_price);

        return view('dashboards.finance_head.settings.course-credits.index', compact('courseCredits', 'prices', 'nuolPcts', 'creditSplits', 'programs', 'creditPrices'));
    }

    public function create()
    {
        $programs = DegreeProgram::where('is_active', true)->orderBy('level')->orderByRaw('study_year IS NULL')->orderBy('study_year')->orderBy('name')->get();
        $creditPrices = CreditUnitPriceSetting::orderByDesc('start_year')
            ->get()->groupBy('level')->map(fn($i) => (float) $i->first()->credit_unit_price);

        return view('dashboards.finance_head.settings.course-credits.create', compact('programs', 'creditPrices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'degree_program_id'  => 'required|exists:degree_programs,id',
            'course_credit_unit' => 'required|numeric|min:1|max:999',
            'gov_doc_id'         => 'nullable|string|max:255',
            'start_year'         => 'required|integer|min:2000|max:2100',
        ]);

        CourseCreditSetting::create($validated);

        return redirect()
            ->route('head_of_finance.settings.course-credits.index')
            ->with('success', 'ສ້າງໜ່ວຍກິດຕາມຫຼັກສູດສຳເລັດ');
    }

    public function edit(CourseCreditSetting $courseCredit)
    {
        $programs = DegreeProgram::orderBy('level')->orderByRaw('study_year IS NULL')->orderBy('study_year')->orderBy('name')->get();
        $creditPrices = CreditUnitPriceSetting::orderByDesc('start_year')
            ->get()->groupBy('level')->map(fn($i) => (float) $i->first()->credit_unit_price);

        return view('dashboards.finance_head.settings.course-credits.edit', compact('courseCredit', 'programs', 'creditPrices'));
    }

    public function update(Request $request, CourseCreditSetting $courseCredit)
    {
        $validated = $request->validate([
            'degree_program_id'  => 'required|exists:degree_programs,id',
            'course_credit_unit' => 'required|numeric|min:1|max:999',
            'gov_doc_id'         => 'nullable|string|max:255',
            'start_year'         => 'required|integer|min:2000|max:2100',
        ]);

        $courseCredit->update($validated);

        return redirect()
            ->route('head_of_finance.settings.course-credits.index')
            ->with('success', 'ອັບເດດໜ່ວຍກິດຕາມຫຼັກສູດສຳເລັດ');
    }

    public function destroy(CourseCreditSetting $courseCredit)
    {
        $courseCredit->delete();

        return redirect()
            ->route('head_of_finance.settings.course-credits.index')
            ->with('success', 'ລຶບໜ່ວຍກິດຕາມຫຼັກສູດສຳເລັດ');
    }

    public function updateSplit(Request $request, string $level)
    {
        abort_unless(in_array($level, ['master', 'phd'], true), 404);

        $validated = $request->validate([
            'year1_percentage' => 'required|numeric|min:0|max:100',
            'year2_percentage' => 'required|numeric|min:0|max:100',
            'gov_doc_id'       => 'nullable|string|max:255',
            'start_year'       => 'required|integer|min:2000|max:2100',
        ]);

        if (round((float) $validated['year1_percentage'] + (float) $validated['year2_percentage'], 2) !== 100.0) {
            return back()->withErrors(['year1_percentage' => 'ສັດສ່ວນປີ 1 ແລະ ປີ 2+ ຕ້ອງລວມເປັນ 100%'])->withInput();
        }

        CourseCreditSplitSetting::updateOrCreate(
            ['level' => $level, 'start_year' => $validated['start_year']],
            [
                'year1_percentage' => $validated['year1_percentage'] / 100,
                'year2_percentage' => $validated['year2_percentage'] / 100,
                'gov_doc_id' => $validated['gov_doc_id'] ?? null,
            ]
        );

        return redirect()
            ->route('head_of_finance.settings.course-credits.index')
            ->with('success', 'ບັນທຶກສັດສ່ວນໜ່ວຍກິດສຳເລັດ');
    }

    public function resetSplitDefaults()
    {
        foreach (['master', 'phd'] as $level) {
            CourseCreditSplitSetting::updateOrCreate(
                ['level' => $level, 'start_year' => (int) date('Y')],
                [
                    'year1_percentage' => 0.60,
                    'year2_percentage' => 0.40,
                    'gov_doc_id' => null,
                ]
            );
        }

        return redirect()
            ->route('head_of_finance.settings.course-credits.index')
            ->with('success', 'ຕັ້ງຄ່າ ປ.ໂທ/ປ.ເອກ ເປັນ 60/40 ສຳເລັດ');
    }
}
