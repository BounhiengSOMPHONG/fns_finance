<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Reports\AnnualReport;

class AnnualReportController extends Controller
{
    public function show(int $year, AnnualReport $report)
    {
        $sections = $report->build($year);

        return view('reports.annual.master', compact('sections', 'year'));
    }
}
