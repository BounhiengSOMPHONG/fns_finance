<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomePlan;

class AcademicIncomeSummaryController extends Controller
{
    public function summary(AcademicIncomePlan $academicIncome)
    {
        $academicIncome->load(['items.degreeProgram', 'creator']);

        $grouped = $academicIncome->items->groupBy('section_code')->sortKeys();

        return view('dashboards.finance_head.academic-income.summary', compact('academicIncome', 'grouped'));
    }

    public function printView(AcademicIncomePlan $academicIncome)
    {
        $academicIncome->load(['items.degreeProgram', 'creator']);

        $grouped = $academicIncome->items->groupBy('section_code')->sortKeys();

        return view('dashboards.finance_head.academic-income.print', compact('academicIncome', 'grouped'));
    }
}
