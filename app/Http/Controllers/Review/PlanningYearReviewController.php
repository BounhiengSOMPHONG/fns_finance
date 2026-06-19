<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Models\PlanningYear;
use App\Models\PlanningYearReviewComment;
use App\Models\PlanningYearReviewRound;
use App\Models\PlanningYearReviewer;
use App\Services\AcademicIncomeReportBuilder;
use App\Services\ExpenseReportBuilder;
use App\Services\PlanYearReportBuilder;
use App\Services\SalaryReportBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PlanningYearReviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perPage = 12;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $rounds = PlanningYearReviewRound::with([
            'planningYear.currentReviewRound',
            'requester',
        ])
            ->whereHas('planningYear')
            ->latest('id')
            ->get()
            ->filter(fn (PlanningYearReviewRound $round): bool => $round->hasReviewer($user))
            ->unique(fn (PlanningYearReviewRound $round): int => (int) $round->planning_year_id)
            ->values();

        $assignments = new LengthAwarePaginator(
            $rounds
                ->forPage($page, $perPage)
                ->values()
                ->map(function (PlanningYearReviewRound $round) use ($user): PlanningYearReviewer {
                    $assignment = new PlanningYearReviewer([
                        'planning_year_review_round_id' => $round->id,
                        'user_id' => $user->id,
                        'notified_at' => null,
                    ]);
                    $assignment->exists = true;
                    $assignment->setRelation('reviewRound', $round);
                    $assignment->setRelation('user', $user);

                    return $assignment;
                }),
            $rounds->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('reviews.planning-years.index', compact('assignments'));
    }

    public function show(
        PlanningYear $planningYear,
        AcademicIncomeReportBuilder $reportBuilder,
        ExpenseReportBuilder $expenseReportBuilder,
        SalaryReportBuilder $salaryReportBuilder,
        PlanYearReportBuilder $planYearReportBuilder
    ) {
        $user = Auth::user();

        abort_unless(
            $user?->role?->role_name === 'head_of_finance' || $planningYear->hasCurrentReviewer($user),
            403,
            'ທ່ານບໍ່ມີສິດເບິ່ງຮອບຂໍຄວາມເຫັນນີ້'
        );

        $planningYear->load([
            'academicIncomePlans.items.degreeProgram',
            'currentReviewRound.comments.user.role',
            'reviewRounds.requester',
            'reviewRounds.closer',
            'reviewRounds.comments.user.role',
        ]);

        $report = $reportBuilder->buildForPlans($planningYear->academicIncomePlans);
        $expenseReport = $expenseReportBuilder->buildForPlanningYear($planningYear);
        $salaryReport = $salaryReportBuilder->buildForPlanningYear($planningYear);
        $planYearReport = $planYearReportBuilder->buildForPlanningYear($planningYear);
        $reviewerUsers = collect();
        $reviewContext = [
            'mode' => 'reviewer',
            'can_manage_review' => false,
            'can_comment' => $planningYear->isPendingReview() && $planningYear->hasCurrentReviewer($user),
            'can_agree' => $planningYear->isPendingReview() && $planningYear->hasCurrentReviewer($user),
            'show_review_panel' => true,
            'current_user_id' => $user->id,
        ];

        return view('dashboards.finance_head.manage-plan.preview', compact(
            'planningYear',
            'report',
            'expenseReport',
            'salaryReport',
            'planYearReport',
            'reviewerUsers',
            'reviewContext',
        ));
    }

    public function storeComment(Request $request, PlanningYear $planningYear)
    {
        $user = Auth::user();

        abort_unless($planningYear->isPendingReview() && $planningYear->hasCurrentReviewer($user), 403);

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:3000'],
        ]);

        $planningYear->currentReviewRound->comments()->create([
            'planning_year_id' => $planningYear->id,
            'user_id' => $user->id,
            'comment' => $data['comment'],
        ]);

        return back()->with('success', 'ບັນທຶກຄວາມເຫັນສຳເລັດ');
    }

    public function toggleAgreement(PlanningYear $planningYear, PlanningYearReviewComment $comment)
    {
        $user = Auth::user();

        abort_unless(
            $planningYear->isPendingReview()
                && $planningYear->hasCurrentReviewer($user)
                && (int) $comment->planning_year_id === (int) $planningYear->id
                && (int) $comment->planning_year_review_round_id === (int) $planningYear->current_review_round_id
                && (int) $comment->user_id !== (int) $user->id,
            403
        );

        if (! $comment->toggleAgreement($user)) {

            return back()->with('success', 'ຍົກເລີກການເຫັນດີແລ້ວ');
        }

        return back()->with('success', 'ບັນທຶກການເຫັນດີແລ້ວ');
    }
}
