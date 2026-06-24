<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\PlanningYear;
use App\Services\PlanYearReportBuilder;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(PlanYearReportBuilder $planYearReportBuilder)
    {
        $latestPlan = PlanningYear::query()
            ->orderByDesc('year')
            ->first();

        $budgetSummary = $this->budgetSummary($latestPlan, $planYearReportBuilder);

        return view('dashboards.finance_head.home', compact('latestPlan', 'budgetSummary'));
    }

    private function budgetSummary(?PlanningYear $planningYear, PlanYearReportBuilder $planYearReportBuilder): array
    {
        if (! $planningYear) {
            return [
                'year' => null,
                'budget_total' => 0.0,
                'committed_total' => 0.0,
                'actual_expense_total' => 0.0,
                'remaining_total' => 0.0,
            ];
        }

        $report = $planYearReportBuilder->buildForPlanningYear($planningYear);
        $budgetTotal = (float) ($report['totals']['total_amount'] ?? 0);
        $committedTotal = (float) DB::table('advance_requests')
            ->whereYear('request_date', $planningYear->year)
            ->where('status', '!=', 'rejected')
            ->sum('requested_amount');
        $actualExpenseTotal = (float) DB::table('transactions')
            ->whereYear('transaction_date', $planningYear->year)
            ->where('type', 'expense')
            ->sum('amount');

        return [
            'year' => $planningYear->year,
            'budget_total' => $budgetTotal,
            'committed_total' => $committedTotal,
            'actual_expense_total' => $actualExpenseTotal,
            'remaining_total' => $budgetTotal - $actualExpenseTotal - $committedTotal,
        ];
    }
}
