<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomePlan;
use App\Models\AcademicIncomeItem;
use App\Models\CourseCreditSplitSetting;
use App\Models\CreditUnitPriceSetting;
use App\Models\DegreeProgram;
use App\Models\NuolPctSetting;
use App\Models\RegistrationFeeSetting;
use App\Models\IncomeRateSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicIncomeAssessmentController extends Controller
{
    public function evaluate(AcademicIncomePlan $academicIncome)
    {
        $this->ensurePlanCanBeEdited($academicIncome);

        $programs11 = DegreeProgram::includedInPlanning()
            ->with('latestCourseCredit')
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->where('level', 'bachelor')->where('study_year', '>=', 2))
                ->orWhereIn('level', ['master', 'phd'])
            )
            ->planningOrder()
            ->get();

        $programs13_bach = DegreeProgram::includedInPlanning()
            ->with('latestCourseCredit')
            ->where('level', 'bachelor')
            ->where(fn($q) => $q->where('study_year', 1)->orWhereNull('study_year'))
            ->planningOrder()
            ->get();

        $programs13_master = DegreeProgram::includedInPlanning()
            ->with('latestCourseCredit')
            ->whereIn('level', ['master', 'phd'])
            ->planningOrder()
            ->get();

        $creditPrices = $this->creditPricesFor();

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();

        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();

        $existingItems = $academicIncome->items->keyBy(fn($item) => $item->section_code . '_' . $item->degree_program_id);

        $incomeRates = $this->incomeRatesFor();

        return view('dashboards.finance_head.academic-income.evaluate', compact(
            'academicIncome', 'programs11', 'programs13_bach', 'programs13_master',
            'creditPrices', 'feeYear2_4', 'feeYear1', 'existingItems',
            'incomeRates'
        ));
    }

    public function saveEvaluate(Request $request, AcademicIncomePlan $academicIncome)
    {
        $this->ensurePlanCanBeEdited($academicIncome);

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

        // Income rates (items 3–6) are edited inline on this entry page; persist
        // any submitted values so the section 2.1–2.4 calculations below use them.
        foreach (['item3', 'item4', 'item5', 'item6'] as $rateKey) {
            if ($request->filled($rateKey . '_rate')) {
                $this->updateIncomeRate($rateKey . '_rate', (float) $request->input($rateKey . '_rate'));
            }
        }

        $nuolSettings = $this->nuolSettingsFor();
        $nuolBachelor = (float) ($nuolSettings->get('bachelor')?->percentage ?? 0.17);
        $nuolMaster   = (float) ($nuolSettings->get('master')?->percentage ?? 0.10);
        $nuolPhd      = (float) ($nuolSettings->get('phd')?->percentage ?? 0.10);
        $nuolByLevel  = ['bachelor' => $nuolBachelor, 'master' => $nuolMaster, 'phd' => $nuolPhd];

        $programs11 = DegreeProgram::includedInPlanning()
            ->with('latestCourseCredit')
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->where('level', 'bachelor')->where('study_year', '>=', 2))
                ->orWhereIn('level', ['master', 'phd'])
            )->get()->keyBy('id');

        $programs13_bach = DegreeProgram::includedInPlanning()
            ->with('latestCourseCredit')
            ->where('level', 'bachelor')
            ->where(fn($q) => $q->where('study_year', 1)->orWhereNull('study_year'))
            ->get()->keyBy('id');

        $programs13_master = DegreeProgram::includedInPlanning()
            ->with('latestCourseCredit')
            ->whereIn('level', ['master', 'phd'])
            ->get()->keyBy('id');

        $creditPrices = $this->creditPricesFor();

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();

        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();

        // Section 1.1 — bachelor yr2-4 + master/phd year 2+ split.
        $inputs11 = $request->input('s11', []);
        foreach ($programs11 as $program) {
            $nuol       = $nuolByLevel[$program->level] ?? $nuolBachelor;
            $count      = (int) ($inputs11[$program->id] ?? 0);
            $creditUnit = $this->courseCreditUnitFor($program, false);
            $price      = $creditPrices[$program->level]?->credit_unit_price ?? 0;
            $total      = $count * $creditUnit * $price * (1 - $nuol);

            $this->saveIncomeItem(
                ['plan_id' => $academicIncome->id, 'section_code' => '1.1', 'degree_program_id' => $program->id],
                [
                    'student_count'              => $count,
                    'snap_credit_unit_price'      => $price,
                    'snap_course_credit_unit'     => $creditUnit,
                    'snap_registration_fee_rate'  => null,
                    'snap_nuol_pct'               => $nuol,
                    'credit_unit_price_setting_id' => $creditPrices->get($program->level)?->id,
                    'income_rate_setting_id'       => null,
                    'registration_fee_setting_id'  => null,
                    'nuol_pct_setting_id'          => $nuolSettings->get($program->level)?->id,
                    'total_income'               => $total,
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
                    'snap_credit_unit_price'      => $price,
                    'snap_course_credit_unit'     => $creditUnit,
                    'snap_registration_fee_rate'  => null,
                    'snap_nuol_pct'               => $nuolBachelor,
                    'credit_unit_price_setting_id' => $creditPrices->get('bachelor')?->id,
                    'income_rate_setting_id'       => null,
                    'registration_fee_setting_id'  => null,
                    'nuol_pct_setting_id'          => $nuolSettings->get('bachelor')?->id,
                    'total_income'               => $total,
                ]
            );
        }

        // Section 1.3 master/phd — year 1 split of total program credits.
        $inputs13m = $request->input('s13m', []);
        foreach ($programs13_master as $program) {
            $nuol       = $nuolByLevel[$program->level] ?? $nuolMaster;
            $count      = (int) ($inputs13m[$program->id] ?? 0);
            $creditUnit = $this->courseCreditUnitFor($program, true);
            $price      = $creditPrices[$program->level]?->credit_unit_price ?? 0;
            $total      = $count * $creditUnit * $price * (1 - $nuol);

            $this->saveIncomeItem(
                ['plan_id' => $academicIncome->id, 'section_code' => '1.3', 'degree_program_id' => $program->id],
                [
                    'student_count'              => $count,
                    'snap_credit_unit_price'      => $price,
                    'snap_course_credit_unit'     => $creditUnit,
                    'snap_registration_fee_rate'  => null,
                    'snap_nuol_pct'               => $nuol,
                    'credit_unit_price_setting_id' => $creditPrices->get($program->level)?->id,
                    'income_rate_setting_id'       => null,
                    'registration_fee_setting_id'  => null,
                    'nuol_pct_setting_id'          => $nuolSettings->get($program->level)?->id,
                    'total_income'               => $total,
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
                'snap_credit_unit_price'      => null,
                'snap_course_credit_unit'     => null,
                'snap_registration_fee_rate'  => $feeRate2_4,
                'snap_nuol_pct'               => $weightedNuol24,
                'credit_unit_price_setting_id' => null,
                'income_rate_setting_id'       => null,
                'registration_fee_setting_id'  => $feeYear2_4?->id,
                'nuol_pct_setting_id'          => null,
                'total_income'               => $total12,
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
                'snap_credit_unit_price'      => null,
                'snap_course_credit_unit'     => null,
                'snap_registration_fee_rate'  => $feeRate1,
                'snap_nuol_pct'               => $weightedNuol1,
                'credit_unit_price_setting_id' => null,
                'income_rate_setting_id'       => null,
                'registration_fee_setting_id'  => $feeYear1?->id,
                'nuol_pct_setting_id'          => null,
                'total_income'               => $total14,
            ]
        );

        // Sections 2.1–2.4 — income rate based items
        $incomeRates = $this->incomeRatesFor();

        // 2.1 — count × item3_rate
        $rate21  = (float) ($incomeRates->get('item3_rate')?->rate ?? 0);
        $count21 = (int) $request->students_2_1;
        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.1', 'degree_program_id' => null],
            [
                'student_count'              => $count21,
                'snap_credit_unit_price'      => $rate21,
                'snap_course_credit_unit'     => null,
                'snap_registration_fee_rate'  => null,
                'snap_nuol_pct'               => 0,
                'credit_unit_price_setting_id' => null,
                'income_rate_setting_id'       => $incomeRates->get('item3_rate')?->id,
                'registration_fee_setting_id'  => null,
                'nuol_pct_setting_id'          => null,
                'total_income'               => $count21 * $rate21,
            ]
        );

        // 2.2 — count(1.2+1.4) × item4_rate
        $rate22  = (float) ($incomeRates->get('item4_rate')?->rate ?? 0);
        $count22 = (int) $request->students_2_2;
        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.2', 'degree_program_id' => null],
            [
                'student_count'              => $count22,
                'snap_credit_unit_price'      => null,
                'snap_course_credit_unit'     => null,
                'snap_registration_fee_rate'  => $rate22,
                'snap_nuol_pct'               => 0,
                'credit_unit_price_setting_id' => null,
                'income_rate_setting_id'       => $incomeRates->get('item4_rate')?->id,
                'registration_fee_setting_id'  => null,
                'nuol_pct_setting_id'          => null,
                'total_income'               => $count22 * $rate22,
            ]
        );

        // 2.3 — count(1.2+1.4) × item5_rate
        $rate23  = (float) ($incomeRates->get('item5_rate')?->rate ?? 0);
        $count23 = (int) $request->students_2_3;
        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.3', 'degree_program_id' => null],
            [
                'student_count'              => $count23,
                'snap_credit_unit_price'      => null,
                'snap_course_credit_unit'     => null,
                'snap_registration_fee_rate'  => $rate23,
                'snap_nuol_pct'               => 0,
                'credit_unit_price_setting_id' => null,
                'income_rate_setting_id'       => $incomeRates->get('item5_rate')?->id,
                'registration_fee_setting_id'  => null,
                'nuol_pct_setting_id'          => null,
                'total_income'               => $count23 * $rate23,
            ]
        );

        // 2.4 — count × item6_rate
        $rate24  = (float) ($incomeRates->get('item6_rate')?->rate ?? 0);
        $count24 = (int) $request->students_2_4;
        $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.4', 'degree_program_id' => null],
            [
                'student_count'              => $count24,
                'snap_credit_unit_price'      => $rate24,
                'snap_course_credit_unit'     => null,
                'snap_registration_fee_rate'  => null,
                'snap_nuol_pct'               => 0,
                'credit_unit_price_setting_id' => null,
                'income_rate_setting_id'       => $incomeRates->get('item6_rate')?->id,
                'registration_fee_setting_id'  => null,
                'nuol_pct_setting_id'          => null,
                'total_income'               => $count24 * $rate24,
            ]
        );

        return back()->with('success', 'ບັນທຶກປະເມີນລາຍຮັບສຳເລັດ');
    }

    public function saveField(Request $request, AcademicIncomePlan $academicIncome): JsonResponse
    {
        $this->ensurePlanCanBeEdited($academicIncome, true);

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
            $this->updateIncomeRate($data['rate_key'], (float) $data['rate']);

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
        $program = DegreeProgram::includedInPlanning()
            ->with('latestCourseCredit')
            ->findOrFail($programId);

        $creditPrices = $this->creditPricesFor();
        $nuolSettings = $this->nuolSettingsFor();
        $nuolByLevel = [
            'bachelor' => (float) ($nuolSettings->get('bachelor')?->percentage ?? 0.17),
            'master' => (float) ($nuolSettings->get('master')?->percentage ?? 0.10),
            'phd' => (float) ($nuolSettings->get('phd')?->percentage ?? 0.10),
        ];

        $section = $inputPrefix === 's11' ? '1.1' : '1.3';
        $nuol = $nuolByLevel[$program->level] ?? $nuolByLevel['bachelor'];
        $creditUnit = $this->courseCreditUnitFor($program, $inputPrefix === 's13m');
        $price = $creditPrices[$program->level]?->credit_unit_price ?? 0;
        $total = $count * $creditUnit * $price * (1 - $nuol);

        return $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => $section, 'degree_program_id' => $program->id],
            [
                'student_count' => $count,
                'snap_credit_unit_price' => $price,
                'snap_course_credit_unit' => $creditUnit,
                'snap_registration_fee_rate' => null,
                'snap_nuol_pct' => $nuol,
                'credit_unit_price_setting_id' => $creditPrices->get($program->level)?->id,
                'income_rate_setting_id' => null,
                'registration_fee_setting_id' => null,
                'nuol_pct_setting_id' => $nuolSettings->get($program->level)?->id,
                'total_income' => $total,
            ]
        );
    }

    private function ensurePlanCanBeEdited(AcademicIncomePlan $academicIncome, bool $json = false): void
    {
        if ($academicIncome->planningYear?->canBeEdited() !== false) {
            return;
        }

        abort($json ? 423 : 403, 'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້');
    }

    private function persistFlatItem(AcademicIncomePlan $academicIncome, string $itemName, ?int $count = null): ?AcademicIncomeItem
    {
        $existing = fn (string $section): int => (int) ($academicIncome->items()
            ->where('section_code', $section)
            ->whereNull('degree_program_id')
            ->value('student_count') ?? 0);

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();
        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();
        $incomeRates = $this->incomeRatesFor();

        return match ($itemName) {
            'students_1_2' => $this->updateFlatItem($academicIncome, '1.2', $count ?? $existing('1.2'), null, $feeYear2_4?->total_rate ?? 0, $this->weightedNuol($feeYear2_4), [
                'registration_fee_setting_id' => $feeYear2_4?->id,
            ]),
            'students_1_4' => $this->updateFlatItem($academicIncome, '1.4', $count ?? $existing('1.4'), null, $feeYear1?->total_rate ?? 0, $this->weightedNuol($feeYear1), [
                'registration_fee_setting_id' => $feeYear1?->id,
            ]),
            'students_2_1' => $this->updateFlatItem($academicIncome, '2.1', $count ?? $existing('2.1'), (float) ($incomeRates->get('item3_rate')?->rate ?? 0), null, 0, [
                'income_rate_setting_id' => $incomeRates->get('item3_rate')?->id,
            ]),
            'students_2_2' => $this->updateFlatItem($academicIncome, '2.2', $count ?? $existing('2.2'), null, (float) ($incomeRates->get('item4_rate')?->rate ?? 0), 0, [
                'income_rate_setting_id' => $incomeRates->get('item4_rate')?->id,
            ]),
            'students_2_3' => $this->updateFlatItem($academicIncome, '2.3', $count ?? $existing('2.3'), null, (float) ($incomeRates->get('item5_rate')?->rate ?? 0), 0, [
                'income_rate_setting_id' => $incomeRates->get('item5_rate')?->id,
            ]),
            'students_2_4' => $this->updateFlatItem($academicIncome, '2.4', $count ?? $existing('2.4'), (float) ($incomeRates->get('item6_rate')?->rate ?? 0), null, 0, [
                'income_rate_setting_id' => $incomeRates->get('item6_rate')?->id,
            ]),
        };
    }

    private function updateFlatItem(AcademicIncomePlan $academicIncome, string $section, int $count, ?float $creditPrice, ?float $registrationRate, float $nuol, array $settingReferences = []): ?AcademicIncomeItem
    {
        $baseRate = $registrationRate ?? $creditPrice ?? 0;
        $total = $count * $baseRate * (1 - $nuol);

        return $this->saveIncomeItem(
            ['plan_id' => $academicIncome->id, 'section_code' => $section, 'degree_program_id' => null],
            array_merge([
                'student_count' => $count,
                'snap_credit_unit_price' => $creditPrice,
                'snap_course_credit_unit' => null,
                'snap_registration_fee_rate' => $registrationRate,
                'snap_nuol_pct' => $nuol,
                'total_income' => $total,
                'credit_unit_price_setting_id' => null,
                'income_rate_setting_id' => null,
                'registration_fee_setting_id' => null,
                'nuol_pct_setting_id' => null,
            ], $settingReferences)
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

    private function creditPricesFor(): \Illuminate\Support\Collection
    {
        return CreditUnitPriceSetting::orderByDesc('start_year')->get()->groupBy('level')->map(fn ($items) => $items->first());
    }

    private function incomeRatesFor(): \Illuminate\Support\Collection
    {
        return IncomeRateSetting::all()->keyBy('key');
    }

    private function nuolSettingsFor(): \Illuminate\Support\Collection
    {
        return NuolPctSetting::orderByDesc('start_year')->get()->groupBy('level')->map(fn ($items) => $items->first());
    }

    private function courseCreditUnitFor(DegreeProgram $program, bool $year1): float
    {
        $total = (float) ($program->latestCourseCredit?->course_credit_unit ?? 0);

        if (! in_array($program->level, ['master', 'phd'], true)) {
            return $total;
        }

        $percentage = $year1
            ? CourseCreditSplitSetting::year1For($program->level)
            : CourseCreditSplitSetting::year2For($program->level);

        return round($total * $percentage, 2);
    }

    private function updateIncomeRate(string $key, float $rate): void
    {
        IncomeRateSetting::where('key', $key)->update(['rate' => $rate]);
    }
}
