<?php

namespace App\Http\Controllers\HeadOfFaculty;

use App\Http\Controllers\Controller;
use App\Models\BudgetPlan;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 1. ຍອດງົບປະມານ — sum of all APPROVED budget plan line items
        $approvedPlan = BudgetPlan::where('status', 'APPROVED')->latest('fiscal_year')->first();

        $totalBudget = 0;
        if ($approvedPlan) {
            $totalBudget = DB::table('budget_line_items')
                ->where('budget_plan_id', $approvedPlan->id)
                ->sum(DB::raw('COALESCE(amount_regular, 0) + COALESCE(amount_academic, 0)'));
        }

        // 2. ຍອດຜູກພັນ — advance_requests that are NOT cleared/rejected (pending/approved but not yet paid)
        $totalCommitted = DB::table('advance_requests')
            ->whereNotIn('status', ['cleared', 'rejected'])
            ->sum('requested_amount');

        // 3. ຍອດໃຊ້ຈ່າຍຈິງ — transactions + cleared advance_requests
        $totalTransactions = DB::table('transactions')->sum('amount');
        $totalCleared = DB::table('advance_requests')
            ->where('status', 'cleared')
            ->sum('requested_amount');
        $totalSpent = $totalTransactions + $totalCleared;

        // 4. ຍອດຄົງເຫຼືອ
        $totalRemaining = $totalBudget - $totalCommitted - $totalSpent;

        // Additional context data
        $fiscalYear = $approvedPlan?->fiscal_year ?? date('Y');

        // Recent advance requests for the activity feed
        $recentAdvances = DB::table('advance_requests')
            ->leftJoin('users', 'advance_requests.requester_id', '=', 'users.id')
            ->leftJoin('departments', 'advance_requests.department_id', '=', 'departments.id')
            ->select(
                'advance_requests.*',
                'users.full_name as requester_name',
                'departments.department_name'
            )
            ->orderByDesc('advance_requests.request_date')
            ->limit(5)
            ->get();

        // Budget plans awaiting approval
        $pendingPlans = BudgetPlan::where('status', 'PENDING_FINAL_APPROVAL')
            ->orderByDesc('fiscal_year')
            ->get();

        return view('head_of_faculty.home', compact(
            'totalBudget',
            'totalCommitted',
            'totalSpent',
            'totalRemaining',
            'fiscalYear',
            'recentAdvances',
            'pendingPlans'
        ));
    }
}
