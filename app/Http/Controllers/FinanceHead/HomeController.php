<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\PlanningYear;

class HomeController extends Controller
{
    public function index()
    {
        $latestPlan = PlanningYear::query()
            ->orderByDesc('year')
            ->first();

        $planStats = [
            'total' => PlanningYear::query()->count(),
            'draft' => PlanningYear::query()->whereIn('status', [
                PlanningYear::STATUS_DRAFT,
                PlanningYear::STATUS_MODIFYING,
            ])->count(),
            'pending_review' => PlanningYear::query()
                ->where('status', PlanningYear::STATUS_PENDING_REVIEW)
                ->count(),
            'saved' => PlanningYear::query()
                ->where('status', PlanningYear::STATUS_SAVED)
                ->count(),
        ];

        return view('dashboards.finance_head.home', compact('latestPlan', 'planStats'));
    }
}
