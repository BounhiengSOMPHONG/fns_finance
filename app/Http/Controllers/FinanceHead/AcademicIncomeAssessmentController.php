<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomePlan;
use App\Models\AcademicIncomeItem;
use App\Models\CreditUnitPriceSetting;
use App\Models\DegreeProgram;
use App\Models\NuolPctSetting;
use App\Models\RegistrationFeeSetting;
use App\Models\IncomeRateSetting;
use Illuminate\Http\Request;

class AcademicIncomeAssessmentController extends Controller
{
    public function evaluate(AcademicIncomePlan $academicIncome)
    {
        if ($academicIncome->isApproved()) {
            return redirect()
                ->route('head_of_finance.academic-income.show', $academicIncome)
                ->with('error', 'ແຜນທີ່ອະນຸມັດແລ້ວບໍ່ສາມາດແກ້ໄຂໄດ້');
        }

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

        $creditPrices = CreditUnitPriceSetting::orderByDesc('start_year')
            ->get()->groupBy('level')->map(fn($i) => $i->first());

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();

        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();

        $existingItems = $academicIncome->items->keyBy(fn($item) => $item->section_code . '_' . $item->degree_program_id);

        $nuolBachelor  = NuolPctSetting::latestFor('bachelor');
        $nuolMasterPhd = NuolPctSetting::latestFor('master_phd');
        $incomeRates   = IncomeRateSetting::allKeyed();

        return view('dashboards.finance_head.academic-income.evaluate', compact(
            'academicIncome', 'programs11', 'programs13_bach', 'programs13_master',
            'creditPrices', 'feeYear2_4', 'feeYear1', 'existingItems',
            'nuolBachelor', 'nuolMasterPhd', 'incomeRates'
        ));
    }

    public function saveEvaluate(Request $request, AcademicIncomePlan $academicIncome)
    {
        if ($academicIncome->isApproved()) {
            return back()->with('error', 'ແຜນທີ່ອະນຸມັດແລ້ວບໍ່ສາມາດແກ້ໄຂໄດ້');
        }

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
        ]);

        $nuolBachelor  = NuolPctSetting::latestFor('bachelor')?->percentage ?? 0.17;
        $nuolMasterPhd = NuolPctSetting::latestFor('master_phd')?->percentage ?? 0.10;

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

        $creditPrices = CreditUnitPriceSetting::orderByDesc('start_year')
            ->get()->groupBy('level')->map(fn($i) => $i->first());

        $feeYear2_4 = RegistrationFeeSetting::where('section_type', 'year2_4')
            ->with('items')->orderByDesc('start_year')->first();

        $feeYear1 = RegistrationFeeSetting::where('section_type', 'year1')
            ->with('items')->orderByDesc('start_year')->first();

        // Section 1.1 — bachelor yr2-4 + master/phd yr2+; rate = credit_unit × price/unit
        $inputs11 = $request->input('s11', []);
        foreach ($programs11 as $program) {
            $nuol       = $program->level === 'bachelor' ? $nuolBachelor : $nuolMasterPhd;
            $count      = (int) ($inputs11[$program->id] ?? 0);
            $creditUnit = $program->latestCourseCredit?->course_credit_unit ?? 0;
            $price      = $creditPrices[$program->level]?->credit_unit_price ?? 0;
            $total      = $count * $creditUnit * $price * (1 - $nuol);

            AcademicIncomeItem::updateOrCreate(
                ['plan_id' => $academicIncome->id, 'section_code' => '1.1', 'degree_program_id' => $program->id],
                [
                    'student_count'              => $count,
                    'snap_credit_unit_price'     => $price,
                    'snap_course_credit_unit'    => $creditUnit,
                    'snap_registration_fee_rate' => null,
                    'snap_nuol_pct'              => $nuol,
                    'total_income'               => $total,
                    'first_payment_amount'       => round($total * 0.60, 2),
                    'second_payment_amount'      => round($total * 0.40, 2),
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

            AcademicIncomeItem::updateOrCreate(
                ['plan_id' => $academicIncome->id, 'section_code' => '1.3', 'degree_program_id' => $program->id],
                [
                    'student_count'              => $count,
                    'snap_credit_unit_price'     => $price,
                    'snap_course_credit_unit'    => $creditUnit,
                    'snap_registration_fee_rate' => null,
                    'snap_nuol_pct'              => $nuolBachelor,
                    'total_income'               => $total,
                    'first_payment_amount'       => round($total * 0.60, 2),
                    'second_payment_amount'      => 0,
                ]
            );
        }

        // Section 1.3 master/phd — year 1 has 60% of total program credits (vs 40% in year 2+).
        // Use year1_credit_unit × price/unit so pricing stays consistent with section 1.1.
        $inputs13m = $request->input('s13m', []);
        foreach ($programs13_master as $program) {
            $count      = (int) ($inputs13m[$program->id] ?? 0);
            $creditUnit = $program->latestCourseCredit?->year1_credit_unit ?? 0;
            $price      = $creditPrices[$program->level]?->credit_unit_price ?? 0;
            $total      = $count * $creditUnit * $price * (1 - $nuolMasterPhd);

            AcademicIncomeItem::updateOrCreate(
                ['plan_id' => $academicIncome->id, 'section_code' => '1.3', 'degree_program_id' => $program->id],
                [
                    'student_count'              => $count,
                    'snap_credit_unit_price'     => $price,
                    'snap_course_credit_unit'    => $creditUnit,
                    'snap_registration_fee_rate' => null,
                    'snap_nuol_pct'              => $nuolMasterPhd,
                    'total_income'               => $total,
                    'first_payment_amount'       => round($total * 0.60, 2),
                    'second_payment_amount'      => 0,
                ]
            );
        }

        // Section 1.2 — Year 2-4 registration fee (bachelor rate)
        $feeRate2_4 = $feeYear2_4 ? $feeYear2_4->total_rate : 0;
        $count12    = (int) $request->students_1_2;
        $total12    = $count12 * $feeRate2_4 * (1 - $nuolBachelor);

        AcademicIncomeItem::updateOrCreate(
            ['plan_id' => $academicIncome->id, 'section_code' => '1.2', 'degree_program_id' => null],
            [
                'student_count'              => $count12,
                'snap_credit_unit_price'     => null,
                'snap_course_credit_unit'    => null,
                'snap_registration_fee_rate' => $feeRate2_4,
                'snap_nuol_pct'              => $nuolBachelor,
                'total_income'               => $total12,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // Section 1.4 — Year 1 registration fee (bachelor rate)
        $feeRate1 = $feeYear1 ? $feeYear1->total_rate : 0;
        $count14  = (int) $request->students_1_4;
        $total14  = $count14 * $feeRate1 * (1 - $nuolBachelor);

        AcademicIncomeItem::updateOrCreate(
            ['plan_id' => $academicIncome->id, 'section_code' => '1.4', 'degree_program_id' => null],
            [
                'student_count'              => $count14,
                'snap_credit_unit_price'     => null,
                'snap_course_credit_unit'    => null,
                'snap_registration_fee_rate' => $feeRate1,
                'snap_nuol_pct'              => $nuolBachelor,
                'total_income'               => $total14,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // Sections 2.1–2.4 — income rate based items
        $incomeRates = IncomeRateSetting::allKeyed();

        // 2.1 — count × item3_rate
        $rate21  = (float) ($incomeRates->get('item3_rate')?->rate ?? 0);
        $count21 = (int) $request->students_2_1;
        AcademicIncomeItem::updateOrCreate(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.1', 'degree_program_id' => null],
            [
                'student_count'              => $count21,
                'snap_credit_unit_price'     => $rate21,
                'snap_course_credit_unit'    => null,
                'snap_registration_fee_rate' => null,
                'snap_nuol_pct'              => 0,
                'total_income'               => $count21 * $rate21,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // 2.2 — count(1.2+1.4) × item4_rate
        $rate22  = (float) ($incomeRates->get('item4_rate')?->rate ?? 0);
        $count22 = (int) $request->students_2_2;
        AcademicIncomeItem::updateOrCreate(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.2', 'degree_program_id' => null],
            [
                'student_count'              => $count22,
                'snap_credit_unit_price'     => null,
                'snap_course_credit_unit'    => null,
                'snap_registration_fee_rate' => $rate22,
                'snap_nuol_pct'              => 0,
                'total_income'               => $count22 * $rate22,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // 2.3 — count(1.2+1.4) × item5_rate
        $rate23  = (float) ($incomeRates->get('item5_rate')?->rate ?? 0);
        $count23 = (int) $request->students_2_3;
        AcademicIncomeItem::updateOrCreate(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.3', 'degree_program_id' => null],
            [
                'student_count'              => $count23,
                'snap_credit_unit_price'     => null,
                'snap_course_credit_unit'    => null,
                'snap_registration_fee_rate' => $rate23,
                'snap_nuol_pct'              => 0,
                'total_income'               => $count23 * $rate23,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        // 2.4 — count × item6_rate
        $rate24  = (float) ($incomeRates->get('item6_rate')?->rate ?? 0);
        $count24 = (int) $request->students_2_4;
        AcademicIncomeItem::updateOrCreate(
            ['plan_id' => $academicIncome->id, 'section_code' => '2.4', 'degree_program_id' => null],
            [
                'student_count'              => $count24,
                'snap_credit_unit_price'     => $rate24,
                'snap_course_credit_unit'    => null,
                'snap_registration_fee_rate' => null,
                'snap_nuol_pct'              => 0,
                'total_income'               => $count24 * $rate24,
                'first_payment_amount'       => 0,
                'second_payment_amount'      => 0,
            ]
        );

        return back()->with('success', 'ບັນທຶກປະເມີນລາຍຮັບສຳເລັດ');
    }
}
