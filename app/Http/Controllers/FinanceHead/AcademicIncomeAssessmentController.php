<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomePlan;
use App\Models\AcademicIncomeItem;
use App\Models\AcademicIncomeSettingSet;
use App\Models\CreditUnitPriceSetting;
use App\Models\DegreeProgram;
use App\Models\NuolPctSetting;
use App\Models\RegistrationFeeSetting;
use App\Models\IncomeRateSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class AcademicIncomeAssessmentController extends Controller
{
    public function evaluate(AcademicIncomePlan $academicIncome)
    {
        $settingSet = $this->settingSetFor($academicIncome);

        $programs11 = DegreeProgram::where('is_active', true)
            ->with('latestCourseCredit')
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->where('level', 'bachelor')->where('study_year', '>=', 2))
                ->orWhereIn('level', ['master', 'phd'])
            )
            ->orderBy('level')->orderByRaw('study_year IS NULL')->orderBy('study_year')->orderBy('name')
            ->get();

        $programs13_bach = DegreeProgram::where('is_active', true)
            ->with('latestCourseCredit')
            ->where('level', 'bachelor')
            ->where(fn($q) => $q->where('study_year', 1)->orWhereNull('study_year'))
            ->orderBy('name')
            ->get();

        $programs13_master = DegreeProgram::where('is_active', true)
            ->with('latestCourseCredit')
            ->whereIn('level', ['master', 'phd'])
            ->orderBy('level')->orderBy('name')
            ->get();

        $creditPrices = $this->creditPricesFor($settingSet);

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();

        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();

        $existingItems = $academicIncome->items->keyBy(fn($item) => $item->section_code . '_' . $item->degree_program_id);

        $incomeRates = $this->incomeRatesFor($settingSet);

        return view('dashboards.finance_head.academic-income.evaluate', compact(
            'academicIncome', 'programs11', 'programs13_bach', 'programs13_master',
            'creditPrices', 'feeYear2_4', 'feeYear1', 'existingItems',
            'incomeRates'
        ));
    }

    public function saveEvaluate(Request $request, AcademicIncomePlan $academicIncome)
    {
        $request->validate([
            's11'          => 'nullable|array',
            's11.*'        => 'nullable|integer|min:0',
            's13'          => 'nullable|array',
            's13.*'        => 'nullable|integer|min:0',
            's13m'         => 'nullable|array',
            's13m.*'       => 'nullable|integer|min:0',
            'students_1_2' => 'required|integer|min:0',
            'students_1_4' => 'required|integer|min:0',
            'students_2_1' => 'required|integer|min:0',
            'students_2_2' => 'required|integer|min:0',
            'students_2_3' => 'required|integer|min:0',
            'students_2_4' => 'required|integer|min:0',
            'item3_rate'   => 'nullable|numeric|min:0',
            'item4_rate'   => 'nullable|numeric|min:0',
            'item5_rate'   => 'nullable|numeric|min:0',
            'item6_rate'   => 'nullable|numeric|min:0',
        ]);

        $settingSet = $this->settingSetFor($academicIncome);

        // Income rates (items 3–6) are edited inline on this entry page; persist
        // any submitted values so the section 2.1–2.4 calculations below use them.
        foreach (['item3', 'item4', 'item5', 'item6'] as $rateKey) {
            if ($request->filled($rateKey . '_rate')) {
                $this->updateIncomeRate($rateKey . '_rate', (float) $request->input($rateKey . '_rate'), $settingSet);
            }
        }

        $nuolBachelor = $this->nuolFor('bachelor', $settingSet, 0.17);
        $nuolMaster   = $this->nuolFor('master', $settingSet, 0.10);
        $nuolPhd      = $this->nuolFor('phd', $settingSet, 0.10);
        $nuolByLevel  = ['bachelor' => $nuolBachelor, 'master' => $nuolMaster, 'phd' => $nuolPhd];

        $programs11 = DegreeProgram::where('is_active', true)
            ->with('latestCourseCredit')
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->where('level', 'bachelor')->where('study_year', '>=', 2))
                ->orWhereIn('level', ['master', 'phd'])
            )->get()->keyBy('id');

        $programs13_bach = DegreeProgram::where('is_active', true)
            ->with('latestCourseCredit')
            ->where('level', 'bachelor')
            ->where(fn($q) => $q->where('study_year', 1)->orWhereNull('study_year'))
            ->get()->keyBy('id');

        $programs13_master = DegreeProgram::where('is_active', true)
            ->with('latestCourseCredit')
            ->whereIn('level', ['master', 'phd'])
            ->get()->keyBy('id');

        $creditPrices = $this->creditPricesFor($settingSet);

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();

        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();

        // Section 1.1 — bachelor yr2-4 (60/40) + master/phd (40/60)
        $inputs11 = $request->input('s11', []);
        foreach ($programs11 as $program) {
            $nuol       = $nuolByLevel[$program->level] ?? $nuolBachelor;
            $count      = (int) ($inputs11[$program->id] ?? 0);
            $creditUnit = $program->latestCourseCredit?->course_credit_unit ?? 0;
            $price      = $creditPrices[$program->level]?->credit_unit_price ?? 0;
            $total      = $count * $creditUnit * $price * (1 - $nuol);

            // Bachelor: 60% first / 40% teaching. Master/PhD: 40% first / 60% teaching.
            $teachingPct = $program->level === 'bachelor' ? 0.40 : 0.60;

            $this->saveIncomeItem(
                ['plan_id' => $academicIncome->id, 'section_code' => '1.1', 'degree_program_id' => $program->id],
                [
                    'student_count'              => $count,
                    'setting_set_id'              => $settingSet?->id,
                    'total_income'               => $total,
                    'first_payment_amount'       => round($total * (1 - $teachingPct), 2),
                    'second_payment_amount'      => round($total * $teachingPct, 2),
                ]
            );
        }

        // Section 1.3 bachelor — same formula as 1.1
        $inputs13 = $request->input('s13', []);
        foreach ($programs13_bach as $program) {
            $count      = (int) ($inputs13[$program->id] ?? 0);
            $creditUnit = $program->latestCourseCredit?->course_credit_unit ?? 0;
            $price      = $creditPrices['bachelor']?->credit_unit_price ?? 0;
            $total      = $count * $creditUnit * $price * (1 - $nuolBachelor);

            $this->saveIncomeItem(
                ['plan_id' => $academicIncome->id, 'section_code' => '1.3', 'degree_program_id' => $program->id],
                [
                    'student_count'              => $count,
                    'setting_set_id'              => $settingSet?->id,
                    'total_income'               => $total,
                    'first_payment_amount'       => round($total * 0.60, 2),
                    'second_payment_amount'      => round($total * 0.40, 2),
                ]
            );
        }

        // Section 1.3 master/phd — year 1 has 60% of total program credits (vs 40% in year 2+).
        // Use year1_credit_unit × price/unit so pricing stays consistent with section 1.1.
        $inputs13m = $request->input('s13m', []);
        foreach ($programs13_master as $program) {
            $nuol       = $nuolByLevel[$program->level] ?? $nuolMaster;
            $count      = (int) ($inputs13m[$program->id] ?? 0);
            $creditUnit = $program->latestCourseCredit?->year1_credit_unit ?? 0;
            $price      = $creditPrices[$program->level]?->credit_unit_price ?? 0;
            $total      = $count * $creditUnit * $price * (1 - $nuol);

            $this->saveIncomeItem(
                ['plan_id' => $academicIncome->id, 'section_code' => '1.3', 'degree_program_id' => $program->id],
                [
                    'student_count'              => $count,
                    'setting_set_id'              => $settingSet?->id,
                    'total_income'               => $total,
                    'first_payment_amount'       => round($total * 0.40, 2),
                    'second_payment_amount'      => round($total * 0.60, 2),
                ]
            );
        }

        // Section 1.2 — Year 2-4 registration fee (per-item weighted NUOL)
        $feeRate2_4 = $feeYear2_4 ? $feeYear2_4->total_rate : 0;
        $feeItems24 = $feeYear2_4 ? $feeYear2_4->items : collect();
        $weightedNuol24 = $feeRate2_4 > 0
            ? $feeItems24->sum(fn($i) => $i->amount * $i->nuol_pct) / $feeRate2_4
            : 0;
        $count12 = (int) $request->students_1_2;
        $total12 = $count12 * $feeRate2_4 * (1 - $weightedNuol24);

        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '1.2', 'degree_program_id' => null],
            [
                'student_count'              => $count12,
                'setting_set_id'              => $settingSet?->id,
                'total_income'               => $total12,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // Section 1.4 — Year 1 registration fee (per-item weighted NUOL)
        $feeRate1 = $feeYear1 ? $feeYear1->total_rate : 0;
        $feeItems1 = $feeYear1 ? $feeYear1->items : collect();
        $weightedNuol1 = $feeRate1 > 0
            ? $feeItems1->sum(fn($i) => $i->amount * $i->nuol_pct) / $feeRate1
            : 0;
        $count14 = (int) $request->students_1_4;
        $total14 = $count14 * $feeRate1 * (1 - $weightedNuol1);

        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '1.4', 'degree_program_id' => null],
            [
                'student_count'              => $count14,
                'setting_set_id'              => $settingSet?->id,
                'total_income'               => $total14,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // Sections 2.1–2.4 — income rate based items
        $incomeRates = $this->incomeRatesFor($settingSet);

        // 2.1 — count × item3_rate
        $rate21  = (float) ($incomeRates->get('item3_rate')?->rate ?? 0);
        $count21 = (int) $request->students_2_1;
        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.1', 'degree_program_id' => null],
            [
                'student_count'              => $count21,
                'setting_set_id'              => $settingSet?->id,
                'total_income'               => $count21 * $rate21,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // 2.2 — count(1.2+1.4) × item4_rate
        $rate22  = (float) ($incomeRates->get('item4_rate')?->rate ?? 0);
        $count22 = (int) $request->students_2_2;
        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.2', 'degree_program_id' => null],
            [
                'student_count'              => $count22,
                'setting_set_id'              => $settingSet?->id,
                'total_income'               => $count22 * $rate22,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // 2.3 — count(1.2+1.4) × item5_rate
        $rate23  = (float) ($incomeRates->get('item5_rate')?->rate ?? 0);
        $count23 = (int) $request->students_2_3;
        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.3', 'degree_program_id' => null],
            [
                'student_count'              => $count23,
                'setting_set_id'              => $settingSet?->id,
                'total_income'               => $count23 * $rate23,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // 2.4 — count × item6_rate
        $rate24  = (float) ($incomeRates->get('item6_rate')?->rate ?? 0);
        $count24 = (int) $request->students_2_4;
        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.4', 'degree_program_id' => null],
            [
                'student_count'              => $count24,
                'setting_set_id'              => $settingSet?->id,
                'total_income'               => $count24 * $rate24,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        return back()->with('success', 'ບັນທຶກປະເມີນລາຍຮັບສຳເລັດ');
    }

    public function saveField(Request $request, AcademicIncomePlan $academicIncome): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:count,rate',
            'student_count' => 'required_if:type,count|integer|min:0',
            'input_prefix' => 'nullable|in:s11,s13,s13m',
            'program_id' => [
                Rule::requiredIf(fn () => $request->input('type') === 'count' && filled($request->input('input_prefix'))),
                'nullable',
                'integer',
                'exists:degree_programs,id',
            ],
            'item_name' => [
                Rule::requiredIf(fn () => $request->input('type') === 'count' && blank($request->input('input_prefix'))),
                'nullable',
                'in:students_1_2,students_1_4,students_2_1,students_2_2,students_2_3,students_2_4',
            ],
            'rate_key' => 'required_if:type,rate|nullable|in:item3_rate,item4_rate,item5_rate,item6_rate',
            'rate' => 'required_if:type,rate|nullable|numeric|min:0',
        ]);

        if ($data['type'] === 'rate') {
            $settingSet = $this->settingSetFor($academicIncome);
            $this->updateIncomeRate($data['rate_key'], (float) $data['rate'], $settingSet);

            $itemName = match ($data['rate_key']) {
                'item3_rate' => 'students_2_1',
                'item4_rate' => 'students_2_2',
                'item5_rate' => 'students_2_3',
                'item6_rate' => 'students_2_4',
            };
            $item = $this->persistFlatItem($academicIncome, $itemName);

            return response()->json([
                'success' => true,
                'deleted' => $item === null,
                'item' => $this->serializeItem($item),
            ]);
        }

        $item = filled($data['input_prefix'] ?? null)
            ? $this->persistProgramItem(
                $academicIncome,
                $data['input_prefix'],
                (int) $data['program_id'],
                (int) $data['student_count']
            )
            : $this->persistFlatItem(
                $academicIncome,
                $data['item_name'],
                (int) $data['student_count']
            );

        return response()->json([
            'success' => true,
            'deleted' => $item === null,
            'item' => $this->serializeItem($item),
        ]);
    }

    private function persistProgramItem(AcademicIncomePlan $academicIncome, string $inputPrefix, int $programId, int $count): ?AcademicIncomeItem
    {
        $settingSet = $this->settingSetFor($academicIncome);
        $program = DegreeProgram::where('is_active', true)
            ->with('latestCourseCredit')
            ->findOrFail($programId);

        $creditPrices = $this->creditPricesFor($settingSet);
        $nuolByLevel = [
            'bachelor' => $this->nuolFor('bachelor', $settingSet, 0.17),
            'master' => $this->nuolFor('master', $settingSet, 0.10),
            'phd' => $this->nuolFor('phd', $settingSet, 0.10),
        ];

        $section = $inputPrefix === 's11' ? '1.1' : '1.3';
        $nuol = $nuolByLevel[$program->level] ?? $nuolByLevel['bachelor'];
        $creditUnit = $inputPrefix === 's13m'
            ? ($program->latestCourseCredit?->year1_credit_unit ?? 0)
            : ($program->latestCourseCredit?->course_credit_unit ?? 0);
        $price = $creditPrices[$program->level]?->credit_unit_price ?? 0;
        $total = $count * $creditUnit * $price * (1 - $nuol);

        $teachingPct = match ($inputPrefix) {
            's11' => $program->level === 'bachelor' ? 0.40 : 0.60,
            's13m' => 0.60,
            default => 0.40,
        };

        return $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => $section, 'degree_program_id' => $program->id],
            [
                'student_count' => $count,
                'setting_set_id' => $settingSet?->id,
                'total_income' => $total,
                'first_payment_amount' => round($total * (1 - $teachingPct), 2),
                'second_payment_amount' => round($total * $teachingPct, 2),
            ]
        );
    }

    private function persistFlatItem(AcademicIncomePlan $academicIncome, string $itemName, ?int $count = null): ?AcademicIncomeItem
    {
        $settingSet = $this->settingSetFor($academicIncome);
        $existing = fn (string $section): int => (int) ($academicIncome->items()
            ->where('section_code', $section)
            ->whereNull('degree_program_id')
            ->value('student_count') ?? 0);

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();
        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();
        $incomeRates = $this->incomeRatesFor($settingSet);

        return match ($itemName) {
            'students_1_2' => $this->updateFlatItem($academicIncome, '1.2', $count ?? $existing('1.2'), null, $feeYear2_4?->total_rate ?? 0, $this->weightedNuol($feeYear2_4), $settingSet),
            'students_1_4' => $this->updateFlatItem($academicIncome, '1.4', $count ?? $existing('1.4'), null, $feeYear1?->total_rate ?? 0, $this->weightedNuol($feeYear1), $settingSet),
            'students_2_1' => $this->updateFlatItem($academicIncome, '2.1', $count ?? $existing('2.1'), (float) ($incomeRates->get('item3_rate')?->rate ?? 0), null, 0, $settingSet),
            'students_2_2' => $this->updateFlatItem($academicIncome, '2.2', $count ?? $existing('2.2'), null, (float) ($incomeRates->get('item4_rate')?->rate ?? 0), 0, $settingSet),
            'students_2_3' => $this->updateFlatItem($academicIncome, '2.3', $count ?? $existing('2.3'), null, (float) ($incomeRates->get('item5_rate')?->rate ?? 0), 0, $settingSet),
            'students_2_4' => $this->updateFlatItem($academicIncome, '2.4', $count ?? $existing('2.4'), (float) ($incomeRates->get('item6_rate')?->rate ?? 0), null, 0, $settingSet),
        };
    }

    private function updateFlatItem(AcademicIncomePlan $academicIncome, string $section, int $count, ?float $creditPrice, ?float $registrationRate, float $nuol, ?AcademicIncomeSettingSet $settingSet): ?AcademicIncomeItem
    {
        $baseRate = $registrationRate ?? $creditPrice ?? 0;
        $total = $count * $baseRate * (1 - $nuol);

        return $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => $section, 'degree_program_id' => null],
            [
                'student_count' => $count,
                'setting_set_id' => $settingSet?->id,
                'total_income' => $total,
                'first_payment_amount' => 0,
                'second_payment_amount' => 0,
            ]
        );
    }

    private function weightedNuol(?RegistrationFeeSetting $setting): float
    {
        if (! $setting || $setting->total_rate <= 0) {
            return 0;
        }

        return (float) ($setting->items->sum(fn($item) => $item->amount * $item->nuol_pct) / $setting->total_rate);
    }

    private function saveIncomeItem(array $attributes, array $values): ?AcademicIncomeItem
    {
        if ((int) ($values['student_count'] ?? 0) <= 0) {
            AcademicIncomeItem::query()
                ->where($attributes)
                ->delete();

            return null;
        }

        return AcademicIncomeItem::updateOrCreate($attributes, $values);
    }

    private function serializeItem(?AcademicIncomeItem $item): ?array
    {
        if (! $item) {
            return null;
        }

        return [
            'id' => $item->id,
            'section_code' => $item->section_code,
            'degree_program_id' => $item->degree_program_id,
            'student_count' => $item->student_count,
            'total_income' => (float) $item->total_income,
        ];
    }

    private function settingSetFor(AcademicIncomePlan $academicIncome): ?AcademicIncomeSettingSet
    {
        return AcademicIncomeSettingSet::latestForFiscalYear((int) $academicIncome->fiscal_year);
    }

    private function creditPricesFor(?AcademicIncomeSettingSet $settingSet): \Illuminate\Support\Collection
    {
        $query = CreditUnitPriceSetting::query();
        if ($settingSet && Schema::hasColumn('credit_unit_price_settings', 'setting_set_id')) {
            $scoped = CreditUnitPriceSetting::where('setting_set_id', $settingSet->id);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        return $query->orderByDesc('start_year')->get()->groupBy('level')->map(fn ($items) => $items->first());
    }

    private function incomeRatesFor(?AcademicIncomeSettingSet $settingSet): \Illuminate\Support\Collection
    {
        $query = IncomeRateSetting::query();
        if ($settingSet && Schema::hasColumn('income_rate_settings', 'setting_set_id')) {
            $scoped = IncomeRateSetting::where('setting_set_id', $settingSet->id);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        return $query->get()->keyBy('key');
    }

    private function nuolFor(string $level, ?AcademicIncomeSettingSet $settingSet, float $default): float
    {
        $query = NuolPctSetting::where('level', $level);
        if ($settingSet && Schema::hasColumn('nuol_pct_settings', 'setting_set_id')) {
            $scoped = NuolPctSetting::where('setting_set_id', $settingSet->id)->where('level', $level);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        return (float) ($query->orderByDesc('start_year')->first()?->percentage ?? $default);
    }

    private function updateIncomeRate(string $key, float $rate, ?AcademicIncomeSettingSet $settingSet): void
    {
        $query = IncomeRateSetting::where('key', $key);
        if ($settingSet && Schema::hasColumn('income_rate_settings', 'setting_set_id')) {
            $scoped = IncomeRateSetting::where('setting_set_id', $settingSet->id)->where('key', $key);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        $query->update(['rate' => $rate]);
    }
}
