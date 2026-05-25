<?php

namespace App\Reports\Sections;

use App\Models\AcademicIncomePlan;
use App\Models\RegistrationFeeSetting;

class IncomeSection
{
    public string $id    = 'income';
    public string $title = 'ລາຍຮັບວິຊາການ';
    public string $view  = 'reports.annual.sections.income';

    public function data(int $year): array
    {
        $plan    = AcademicIncomePlan::with('items.degreeProgram')
                     ->where('fiscal_year', $year)->first();
        $grouped = $plan?->items->groupBy('section_code')->sortKeys() ?? collect();

        $feeYear2_4 = RegistrationFeeSetting::with('items')
                        ->where('section_type', 'year2_4')
                        ->orderByDesc('start_year')->first();

        $feeYear1 = RegistrationFeeSetting::with('items')
                      ->where('section_type', 'year1')
                      ->orderByDesc('start_year')->first();

        return compact('plan', 'grouped', 'feeYear2_4', 'feeYear1');
    }

    public function totals(int $year): array
    {
        $plan = AcademicIncomePlan::with('items')->where('fiscal_year', $year)->first();
        if (! $plan) {
            return ['gross' => 0, 'faculty' => 0, 'p1' => 0, 'p2' => 0];
        }

        $items   = $plan->items;
        $faculty = $items->sum('total_income');
        $p1      = $items->sum('first_payment_amount');
        $p2      = $items->sum('second_payment_amount');
        $gross   = $items->sum(fn ($it) =>
            in_array($it->section_code, ['1.2', '1.4', '4', '5'])
                ? $it->student_count * $it->snap_registration_fee_rate
                : $it->student_count * $it->snap_course_credit_unit * $it->snap_credit_unit_price
        );

        return compact('gross', 'faculty', 'p1', 'p2');
    }
}
