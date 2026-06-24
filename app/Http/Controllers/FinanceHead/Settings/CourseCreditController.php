<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\CourseCreditSetting;
use App\Models\CourseCreditSplitSetting;
use App\Models\CreditUnitPriceSetting;
use App\Models\DegreeProgram;
use App\Models\NuolPctSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $displayCourseCredits = $this->displayCourseCredits($courseCredits);

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
        $displayPrograms = $this->displayPrograms($programs);

        $creditPrices = CreditUnitPriceSetting::orderByDesc('start_year')
            ->get()
            ->groupBy('level')
            ->map(fn($i) => (float) $i->first()->credit_unit_price);

        return view('dashboards.finance_head.settings.course-credits.index', compact('courseCredits', 'displayCourseCredits', 'prices', 'nuolPcts', 'creditSplits', 'programs', 'displayPrograms', 'creditPrices'));
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
            'degree_program_id'  => 'required_without:course_credit_units|exists:degree_programs,id',
            'course_credit_unit' => 'required_without:course_credit_units|numeric|min:1|max:999',
            'course_credit_units' => 'nullable|array',
            'course_credit_units.*' => 'nullable|numeric|min:1|max:999',
            'gov_doc_id'         => 'nullable|string|max:255',
            'start_year'         => 'required|integer|min:2000|max:2100',
        ]);

        if (! empty($validated['course_credit_units'])) {
            DB::transaction(function () use ($validated): void {
                foreach ($validated['course_credit_units'] as $programId => $unit) {
                    if ($unit === null || $unit === '') {
                        continue;
                    }

                    CourseCreditSetting::updateOrCreate(
                        [
                            'degree_program_id' => (int) $programId,
                            'start_year' => $validated['start_year'],
                        ],
                        [
                            'course_credit_unit' => $unit,
                            'gov_doc_id' => $validated['gov_doc_id'] ?? null,
                        ]
                    );
                }
            });
        } else {
            CourseCreditSetting::create([
                'degree_program_id' => $validated['degree_program_id'],
                'course_credit_unit' => $validated['course_credit_unit'],
                'gov_doc_id' => $validated['gov_doc_id'] ?? null,
                'start_year' => $validated['start_year'],
            ]);
        }

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
            'degree_program_id'  => 'required_without:setting_units|exists:degree_programs,id',
            'course_credit_unit' => 'required_without:setting_units|numeric|min:1|max:999',
            'setting_units' => 'nullable|array',
            'setting_units.*' => 'nullable|numeric|min:1|max:999',
            'gov_doc_id'         => 'nullable|string|max:255',
            'start_year'         => 'required|integer|min:2000|max:2100',
        ]);

        if (! empty($validated['setting_units'])) {
            DB::transaction(function () use ($validated): void {
                CourseCreditSetting::whereIn('id', array_keys($validated['setting_units']))
                    ->get()
                    ->each(function (CourseCreditSetting $setting) use ($validated): void {
                        $unit = $validated['setting_units'][$setting->id] ?? null;
                        if ($unit === null || $unit === '') {
                            return;
                        }

                        $setting->update([
                            'course_credit_unit' => $unit,
                            'gov_doc_id' => $validated['gov_doc_id'] ?? null,
                            'start_year' => $validated['start_year'],
                        ]);
                    });
            });
        } else {
            $courseCredit->update([
                'degree_program_id' => $validated['degree_program_id'],
                'course_credit_unit' => $validated['course_credit_unit'],
                'gov_doc_id' => $validated['gov_doc_id'] ?? null,
                'start_year' => $validated['start_year'],
            ]);
        }

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

    private function displayCourseCredits($courseCredits)
    {
        return $courseCredits
            ->groupBy(fn (CourseCreditSetting $setting): string => $this->displayGroupKey($setting))
            ->map(function ($group) {
                $setting = clone $group->sortBy(fn (CourseCreditSetting $item): int => (int) ($item->degreeProgram?->study_year ?? 0))->first();
                $setting->display_rows = $group
                    ->sortBy(fn (CourseCreditSetting $item): int => (int) ($item->degreeProgram?->study_year ?? 0))
                    ->values();
                $setting->display_years = $setting->display_rows
                    ->map(fn (CourseCreditSetting $item): array => [
                        'id' => $item->id,
                        'program_id' => $item->degree_program_id,
                        'year' => $item->degreeProgram?->study_year,
                        'unit' => (float) $item->course_credit_unit,
                        'start_year' => $item->start_year,
                        'gov_doc_id' => $item->gov_doc_id,
                    ])
                    ->values();
                $setting->display_codes = $setting->display_rows
                    ->pluck('degreeProgram.code')
                    ->filter()
                    ->implode(' ');

                return $setting;
            })
            ->values();
    }

    private function displayGroupKey(CourseCreditSetting $setting): string
    {
        $program = $setting->degreeProgram;
        if ($program?->level === 'bachelor') {
            return implode('|', [$program->level, $program->name]);
        }

        return implode('|', [$program?->level ?? 'unknown', $program?->id ?? $setting->id]);
    }

    private function displayPrograms($programs)
    {
        return $programs
            ->groupBy(fn (DegreeProgram $program): string => $program->level === 'bachelor'
                ? implode('|', [$program->level, $program->name])
                : implode('|', [$program->level, $program->id]))
            ->map(function ($group) {
                $program = clone $group->sortBy(fn (DegreeProgram $item): int => (int) ($item->study_year ?? 0))->first();
                $program->display_rows = $group
                    ->sortBy(fn (DegreeProgram $item): int => (int) ($item->study_year ?? 0))
                    ->values();
                $program->display_program_ids = $program->display_rows->pluck('id')->implode(',');
                $program->display_years = $program->display_rows
                    ->map(fn (DegreeProgram $item): array => [
                        'program_id' => $item->id,
                        'year' => $item->study_year,
                    ])
                    ->values();

                return $program;
            })
            ->values();
    }
}
