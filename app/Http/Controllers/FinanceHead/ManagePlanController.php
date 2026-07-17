<?php

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\AcademicIncomePlan;
use App\Models\ExpenseCatalogItem;
use App\Models\ExpensePattern;
use App\Models\ExpensePlan;
use App\Models\ExpensePlanRow;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\PeriodPlanOverride;
use App\Models\PlanningYear;
use App\Models\PlanningYearReviewRound;
use App\Models\SalaryPlan;
use App\Models\User;
use App\Services\AcademicIncomeReportBuilder;
use App\Services\ExpenseReportBuilder;
use App\Services\PeriodPlanReportBuilder;
use App\Services\PlanYearReportBuilder;
use App\Services\SalaryReportBuilder;
use App\Support\ExpenseStructureNames;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagePlanController extends Controller
{
    public function index()
    {
        $plans = PlanningYear::with([
            'academicIncomePlans.items',
            'salaryPlans.entries',
            'expensePlanRows.pattern',
        ])
            ->withCount(['expensePlanRows as expense_plans_count'])
            ->orderByDesc('year')
            ->paginate(12);

        return view('dashboards.finance_head.manage-plan.index', compact('plans'));
    }

    public function preview(
        PlanningYear $planningYear,
        AcademicIncomeReportBuilder $reportBuilder,
        ExpenseReportBuilder $expenseReportBuilder,
        SalaryReportBuilder $salaryReportBuilder,
        PlanYearReportBuilder $planYearReportBuilder
    ) {
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
        $reviewerUsers = User::with('role')
            ->where('is_active', true)
            ->whereKeyNot(Auth::id())
            ->orderBy('full_name')
            ->get();
        $reviewContext = [
            'mode' => 'finance',
            'can_manage_review' => true,
            'can_comment' => false,
            'can_agree' => false,
            'show_review_panel' => true,
            'current_user_id' => Auth::id(),
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

    public function previewView(PlanningYear $planningYear): RedirectResponse
    {
        return redirect()->route('head_of_finance.manage-plan.preview', $planningYear);
    }

    public function redirectAcademicIncomeIndex(): RedirectResponse
    {
        return redirect()->route('head_of_finance.manage-plan.index');
    }

    public function periodOneTwo(PlanningYear $planningYear, PeriodPlanReportBuilder $periodPlanReportBuilder)
    {
        if ($planningYear->canEditPeriods()) {
            $periodPlanReportBuilder->ensureDefaultOverrides($planningYear, Auth::id());
        }

        $periodReport = $periodPlanReportBuilder->buildForPlanningYear($planningYear);

        return view('dashboards.finance_head.manage-plan.period', [
            'planningYear' => $planningYear,
            'periodKey' => 'period-1-2',
            'periodTitle' => 'ງວດ 1-2',
            'periodReport' => $periodReport,
            'canEditPeriod' => $planningYear->canEditPeriodOneTwo(),
        ]);
    }

    public function updatePeriodOneTwoOverride(
        Request $request,
        PlanningYear $planningYear,
        string $accountCode,
        PeriodPlanReportBuilder $periodPlanReportBuilder
    ) {
        abort_if(
            $planningYear->canEditPeriodOneTwo() === false,
            423,
            'ຕ້ອງບັນທຶກແຜນກ່ອນ ແລະ ງວດ 1-2 ຕ້ອງຍັງບໍ່ຖືກບັນທຶກ'
        );

        $data = $request->validate([
            'period_1_amount' => ['required', 'numeric', 'min:0'],
            'period_2_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $row = $periodPlanReportBuilder->findEditableRow($planningYear, $accountCode);
        abort_if(! $row, 404, 'ບໍ່ພົບບັນຊີວິຊາການສຳລັບແຜນນີ້');

        $period1Amount = (float) $data['period_1_amount'];
        $period2Amount = (float) $data['period_2_amount'];
        $yearlyAmount = (float) $row['yearly_amount'];

        if (($period1Amount + $period2Amount) > $yearlyAmount) {
            return response()->json([
                'message' => 'ຍອດງວດ 1 ແລະ ງວດ 2 ຕ້ອງບໍ່ເກີນງົບປີ',
                'errors' => [
                    'period_1_amount' => ['ຍອດງວດ 1 ແລະ ງວດ 2 ຕ້ອງບໍ່ເກີນງົບປີ'],
                    'period_2_amount' => ['ຍອດງວດ 1 ແລະ ງວດ 2 ຕ້ອງບໍ່ເກີນງົບປີ'],
                ],
            ], 422);
        }

        $override = PeriodPlanOverride::query()->firstOrNew([
            'planning_year_id' => $planningYear->id,
            'chart_of_account_id' => (int) $row['chart_of_account_id'],
        ]);

        if (! $override->exists) {
            $override->created_by = Auth::id();
        }

        $override->period_1_amount = $period1Amount;
        $override->period_2_amount = $period2Amount;
        $override->updated_by = Auth::id();
        $override->save();

        $firstHalfAmount = $period1Amount + $period2Amount;

        return response()->json([
            'success' => true,
            'row' => [
                'account_code' => $accountCode,
                'yearly_amount' => $yearlyAmount,
                'period_1_amount' => $period1Amount,
                'period_2_amount' => $period2Amount,
                'first_half_amount' => $firstHalfAmount,
                'second_half_amount' => $yearlyAmount - $firstHalfAmount,
                'has_override' => true,
            ],
        ]);
    }

    public function savePeriodOneTwo(Request $request, PlanningYear $planningYear, PeriodPlanReportBuilder $periodPlanReportBuilder)
    {
        if (! $planningYear->canEditPeriodOneTwo()) {
            return back()->with('error', 'ຕ້ອງບັນທຶກແຜນກ່ອນ ຈຶ່ງຈະບັນທຶກງວດ 1-2 ໄດ້');
        }

        $error = $this->persistPeriodOneTwoRows($request, $planningYear, $periodPlanReportBuilder);
        if ($error) {
            return back()->with('error', $error);
        }

        $periodPlanReportBuilder->ensureDefaultOverrides($planningYear, Auth::id());

        $planningYear->update([
            'period_1_2_saved_at' => now(),
        ]);

        return back()->with('success', 'ບັນທຶກງວດ 1-2 ສຳເລັດ ສາມາດເຂົ້າງວດ 3-4 ໄດ້ແລ້ວ');
    }

    public function periodThreeFour(PlanningYear $planningYear, PeriodPlanReportBuilder $periodPlanReportBuilder)
    {
        if (! $planningYear->canOpenPeriodThreeFour()) {
            return redirect()
                ->route('head_of_finance.manage-plan.index')
                ->with('error', 'ກະລຸນາບັນທຶກງວດ 1-2 ກ່ອນ ຈຶ່ງຈະເຂົ້າງວດ 3-4 ໄດ້');
        }

        if ($planningYear->canEditPeriodThreeFour()) {
            $periodPlanReportBuilder->ensureDefaultOverrides($planningYear, Auth::id());
        }

        $periodReport = $periodPlanReportBuilder->buildForPlanningYear($planningYear);

        return view('dashboards.finance_head.manage-plan.period', [
            'planningYear' => $planningYear,
            'periodKey' => 'period-3-4',
            'periodTitle' => 'ງວດ 3-4',
            'periodReport' => $periodReport,
            'canEditPeriod' => $planningYear->canEditPeriodThreeFour(),
        ]);
    }

    public function updatePeriodThreeFourOverride(
        Request $request,
        PlanningYear $planningYear,
        string $accountCode,
        PeriodPlanReportBuilder $periodPlanReportBuilder
    ) {
        abort_if(
            $planningYear->canEditPeriodThreeFour() === false,
            423,
            'ຕ້ອງບັນທຶກງວດ 1-2 ກ່ອນ ແລະ ງວດ 3-4 ຕ້ອງຍັງບໍ່ຖືກບັນທຶກ'
        );

        $data = $request->validate([
            'average_increase_amount' => ['required', 'numeric', 'min:0'],
            'average_decrease_amount' => ['required', 'numeric', 'min:0'],
            'requested_decrease_amount' => ['required', 'numeric', 'min:0'],
            'requested_increase_amount' => ['required', 'numeric', 'min:0'],
            'period_3_amount' => ['required', 'numeric', 'min:0'],
            'period_4_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $row = $periodPlanReportBuilder->findEditableRow($planningYear, $accountCode);
        abort_if(! $row, 404, 'ບໍ່ພົບບັນຊີວິຊາການສຳລັບແຜນນີ້');

        $secondHalfAmount = (float) $row['second_half_amount'];
        $averageIncreaseAmount = (float) $data['average_increase_amount'];
        $averageDecreaseAmount = (float) $data['average_decrease_amount'];
        $requestedDecreaseAmount = (float) $data['requested_decrease_amount'];
        $requestedIncreaseAmount = (float) $data['requested_increase_amount'];
        $period3Amount = (float) $data['period_3_amount'];
        $period4Amount = (float) $data['period_4_amount'];

        if (($averageDecreaseAmount + $requestedDecreaseAmount) > ($secondHalfAmount + $averageIncreaseAmount + $requestedIncreaseAmount)) {
            return response()->json([
                'message' => 'ຍອດຫຼຸດຕ້ອງບໍ່ເກີນແຜນ 06 ເດືອນທ້າຍປີຫຼັງລວມຍອດເພີ່ມ',
                'errors' => [
                    'average_decrease_amount' => ['ຍອດຫຼຸດຕ້ອງບໍ່ເກີນແຜນ 06 ເດືອນທ້າຍປີຫຼັງລວມຍອດເພີ່ມ'],
                    'requested_decrease_amount' => ['ຍອດຫຼຸດຕ້ອງບໍ່ເກີນແຜນ 06 ເດືອນທ້າຍປີຫຼັງລວມຍອດເພີ່ມ'],
                ],
            ], 422);
        }

        $adjustedSecondHalfAmount = $secondHalfAmount
            - $averageDecreaseAmount
            + $averageIncreaseAmount
            - $requestedDecreaseAmount
            + $requestedIncreaseAmount;
        $period34TotalAmount = $period3Amount + $period4Amount;
        $actualFullYearAmount = ((float) $row['first_half_amount']) + $period34TotalAmount;

        $override = PeriodPlanOverride::query()->firstOrNew([
            'planning_year_id' => $planningYear->id,
            'chart_of_account_id' => (int) $row['chart_of_account_id'],
        ]);

        if (! $override->exists) {
            $override->period_1_amount = (float) $row['period_1_amount'];
            $override->period_2_amount = (float) $row['period_2_amount'];
            $override->created_by = Auth::id();
        }

        $override->average_increase_amount = $averageIncreaseAmount;
        $override->average_decrease_amount = $averageDecreaseAmount;
        $override->requested_decrease_amount = $requestedDecreaseAmount;
        $override->requested_increase_amount = $requestedIncreaseAmount;
        $override->period_3_amount = $period3Amount;
        $override->period_4_amount = $period4Amount;
        $override->updated_by = Auth::id();
        $override->save();

        return response()->json([
            'success' => true,
            'row' => [
                'account_code' => $accountCode,
                'second_half_amount' => $secondHalfAmount,
                'average_increase_amount' => $averageIncreaseAmount,
                'average_decrease_amount' => $averageDecreaseAmount,
                'requested_decrease_amount' => $requestedDecreaseAmount,
                'requested_increase_amount' => $requestedIncreaseAmount,
                'adjusted_second_half_amount' => $adjustedSecondHalfAmount,
                'period_3_amount' => $period3Amount,
                'period_4_amount' => $period4Amount,
                'period_3_4_total_amount' => $period34TotalAmount,
                'actual_full_year_amount' => $actualFullYearAmount,
                'reduction_percent' => $actualFullYearAmount > 0
                    ? (((float) $row['yearly_amount']) / $actualFullYearAmount) * 100
                    : 0.0,
                'has_override' => true,
            ],
        ]);
    }

    public function savePeriodThreeFour(Request $request, PlanningYear $planningYear, PeriodPlanReportBuilder $periodPlanReportBuilder)
    {
        if (! $planningYear->canEditPeriodThreeFour()) {
            return back()->with('error', 'ຕ້ອງບັນທຶກງວດ 1-2 ກ່ອນ ຈຶ່ງຈະບັນທຶກງວດ 3-4 ໄດ້');
        }

        $error = $this->persistPeriodThreeFourRows($request, $planningYear, $periodPlanReportBuilder);
        if ($error) {
            return back()->with('error', $error);
        }

        $periodPlanReportBuilder->ensureDefaultOverrides($planningYear, Auth::id());
        $periodReport = $periodPlanReportBuilder->buildForPlanningYear($planningYear);
        $totals = $periodReport['totals'] ?? [];

        if (abs(((float) ($totals['average_increase_amount'] ?? 0)) - ((float) ($totals['average_decrease_amount'] ?? 0))) > 0.01) {
            return back()->with('error', 'ຍອດເພີ່ມ ແລະ ຍອດຫຼຸດ ໃນແຜນດັດແກ້ສະເລ່ຍຕ້ອງເທົ່າກັນ');
        }

        $unbalancedRow = collect($periodReport['rows'] ?? [])
            ->first(fn (array $row): bool => ! $row['is_group']
                && abs(((float) $row['period_3_4_total_amount']) - ((float) $row['adjusted_second_half_amount'])) > 0.01);

        if ($unbalancedRow) {
            return back()->with('error', 'ແຜນງວດ 3 ແລະ ແຜນງວດ 4 ຂອງແຕ່ລະລາຍການຕ້ອງລວມເທົ່າກັບແຜນດັດແກ້ 6 ເດືອນທ້າຍປີ');
        }

        $planningYear->update([
            'period_3_4_saved_at' => now(),
        ]);

        return back()->with('success', 'ບັນທຶກງວດ 3-4 ສຳເລັດ');
    }

    private function persistPeriodOneTwoRows(
        Request $request,
        PlanningYear $planningYear,
        PeriodPlanReportBuilder $periodPlanReportBuilder
    ): ?string {
        $rows = $this->periodRowsPayload($request);
        if ($rows === null) {
            return 'ຮູບແບບຂໍ້ມູນງວດ 1-2 ບໍ່ຖືກຕ້ອງ ກະລຸນາລອງບັນທຶກໃໝ່';
        }

        foreach ($rows as $payload) {
            $accountCode = (string) ($payload['account_code'] ?? '');
            $row = $periodPlanReportBuilder->findEditableRow($planningYear, $accountCode);
            if (! $row) {
                return 'ບໍ່ພົບບັນຊີວິຊາການບາງແຖວ ກະລຸນາໂຫຼດໜ້າໃໝ່';
            }

            $period1Amount = $this->payloadAmount($payload, 'period_1_amount');
            $period2Amount = $this->payloadAmount($payload, 'period_2_amount');
            if ($period1Amount === null || $period2Amount === null) {
                return 'ຍອດງວດ 1-2 ຕ້ອງເປັນຕົວເລກ';
            }

            $yearlyAmount = (float) $row['yearly_amount'];
            if (($period1Amount + $period2Amount) > $yearlyAmount) {
                return 'ຍອດງວດ 1 ແລະ ງວດ 2 ຕ້ອງບໍ່ເກີນງົບປີ';
            }

            $override = PeriodPlanOverride::query()->firstOrNew([
                'planning_year_id' => $planningYear->id,
                'chart_of_account_id' => (int) $row['chart_of_account_id'],
            ]);

            if (! $override->exists) {
                $override->created_by = Auth::id();
            }

            $override->period_1_amount = $period1Amount;
            $override->period_2_amount = $period2Amount;
            $override->updated_by = Auth::id();
            $override->save();
        }

        return null;
    }

    private function persistPeriodThreeFourRows(
        Request $request,
        PlanningYear $planningYear,
        PeriodPlanReportBuilder $periodPlanReportBuilder
    ): ?string {
        $rows = $this->periodRowsPayload($request);
        if ($rows === null) {
            return 'ຮູບແບບຂໍ້ມູນງວດ 3-4 ບໍ່ຖືກຕ້ອງ ກະລຸນາລອງບັນທຶກໃໝ່';
        }

        foreach ($rows as $payload) {
            $accountCode = (string) ($payload['account_code'] ?? '');
            $row = $periodPlanReportBuilder->findEditableRow($planningYear, $accountCode);
            if (! $row) {
                return 'ບໍ່ພົບບັນຊີວິຊາການບາງແຖວ ກະລຸນາໂຫຼດໜ້າໃໝ່';
            }

            $averageIncreaseAmount = $this->payloadAmount($payload, 'average_increase_amount');
            $averageDecreaseAmount = $this->payloadAmount($payload, 'average_decrease_amount');
            $requestedDecreaseAmount = $this->payloadAmount($payload, 'requested_decrease_amount');
            $requestedIncreaseAmount = $this->payloadAmount($payload, 'requested_increase_amount');
            $period3Amount = $this->payloadAmount($payload, 'period_3_amount');
            $period4Amount = $this->payloadAmount($payload, 'period_4_amount');

            if (in_array(null, [
                $averageIncreaseAmount,
                $averageDecreaseAmount,
                $requestedDecreaseAmount,
                $requestedIncreaseAmount,
                $period3Amount,
                $period4Amount,
            ], true)) {
                return 'ຍອດງວດ 3-4 ຕ້ອງເປັນຕົວເລກ';
            }

            $secondHalfAmount = (float) $row['second_half_amount'];
            if (($averageDecreaseAmount + $requestedDecreaseAmount) > ($secondHalfAmount + $averageIncreaseAmount + $requestedIncreaseAmount)) {
                return 'ຍອດຫຼຸດຕ້ອງບໍ່ເກີນແຜນ 06 ເດືອນທ້າຍປີຫຼັງລວມຍອດເພີ່ມ';
            }

            $override = PeriodPlanOverride::query()->firstOrNew([
                'planning_year_id' => $planningYear->id,
                'chart_of_account_id' => (int) $row['chart_of_account_id'],
            ]);

            if (! $override->exists) {
                $override->period_1_amount = (float) $row['period_1_amount'];
                $override->period_2_amount = (float) $row['period_2_amount'];
                $override->created_by = Auth::id();
            }

            $override->average_increase_amount = $averageIncreaseAmount;
            $override->average_decrease_amount = $averageDecreaseAmount;
            $override->requested_decrease_amount = $requestedDecreaseAmount;
            $override->requested_increase_amount = $requestedIncreaseAmount;
            $override->period_3_amount = $period3Amount;
            $override->period_4_amount = $period4Amount;
            $override->updated_by = Auth::id();
            $override->save();
        }

        return null;
    }

    private function periodRowsPayload(Request $request): ?array
    {
        $payload = $request->input('period_rows');
        if ($payload === null || $payload === '') {
            return [];
        }

        $rows = json_decode((string) $payload, true);

        return is_array($rows) ? $rows : null;
    }

    private function payloadAmount(array $payload, string $key): ?float
    {
        if (! array_key_exists($key, $payload) || ! is_numeric($payload[$key]) || (float) $payload[$key] < 0) {
            return null;
        }

        return (float) $payload[$key];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100', 'unique:planning_years,year'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $planningYear = DB::transaction(function () use ($data) {
            $planningYear = PlanningYear::create([
                'year' => (int) $data['year'],
                'name' => $data['name'] ?: 'Planning '.$data['year'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
                'status' => PlanningYear::STATUS_DRAFT,
            ]);

            $this->ensureCompanionPlans($planningYear);
            $this->ensureExpenseStructure($planningYear);

            return $planningYear;
        });

        return redirect()
            ->route('head_of_finance.manage-plan.index')
            ->with('success', 'ສ້າງແຜນລວມປະຈຳປີ '.$planningYear->year.' ສຳເລັດ');
    }

    public function requestReview(Request $request, PlanningYear $planningYear)
    {
        $data = $request->validate([
            'reviewer_ids' => ['required', 'array', 'min:1'],
            'reviewer_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $reviewers = User::query()
            ->whereIn('id', $data['reviewer_ids'])
            ->where('is_active', true)
            ->get();

        if ($reviewers->count() !== count(array_unique($data['reviewer_ids']))) {
            return back()->with('error', 'ກະລຸນາເລືອກຜູ້ກວດສອບທີ່ຍັງເປີດໃຊ້ງານ');
        }

        if (! $planningYear->canRequestReview()) {
            return back()->with('error', 'ສາມາດສົ່ງຂໍຄວາມເຫັນໄດ້ຈາກສະຖານະ Draft ຫຼື Modifying ເທົ່ານັ້ນ');
        }

        $reviewRound = DB::transaction(function () use ($planningYear, $reviewers, $data): PlanningYearReviewRound {
            $roundNumber = ((int) $planningYear->reviewRounds()->max('round_number')) + 1;

            $reviewRound = $planningYear->reviewRounds()->create([
                'requested_by' => Auth::id(),
                'round_number' => $roundNumber,
                'note' => $data['note'] ?? null,
                'reviewer_user_ids' => $reviewers->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
                'requested_at' => now(),
            ]);

            $planningYear->update([
                'status' => PlanningYear::STATUS_PENDING_REVIEW,
                'current_review_round_id' => $reviewRound->id,
                'review_requested_at' => now(),
                'review_closed_at' => null,
            ]);

            return $reviewRound->load('planningYear');
        });

        return back()->with('success', 'ສົ່ງຂໍຄວາມເຫັນໃຫ້ຜູ້ກວດສອບສຳເລັດ');
    }

    public function closeReview(PlanningYear $planningYear)
    {
        if (! $planningYear->isPendingReview() || ! $planningYear->current_review_round_id) {
            return back()->with('error', 'ແຜນນີ້ບໍ່ໄດ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ');
        }

        DB::transaction(function () use ($planningYear): void {
            $planningYear->currentReviewRound()->update([
                'closed_by' => Auth::id(),
                'closed_at' => now(),
            ]);

            $planningYear->update([
                'status' => PlanningYear::STATUS_MODIFYING,
                'review_closed_at' => now(),
            ]);
        });

        return back()->with('success', 'ປິດຮອບຂໍຄວາມເຫັນ ແລະ ເຂົ້າສະຖານະກຳລັງແກ້ໄຂແລ້ວ');
    }

    public function savePlan(PlanningYear $planningYear)
    {
        if (! $planningYear->canBeEdited()) {
            return back()->with('error', 'ແຜນນີ້ຖືກບັນທຶກ ຫຼື ຢູ່ໃນສະຖານະກວດສອບແລ້ວ');
        }

        $planningYear->update([
            'status' => PlanningYear::STATUS_SAVED,
            'current_review_round_id' => null,
            'review_requested_at' => null,
            'review_closed_at' => null,
        ]);

        return back()->with('success', 'ບັນທຶກແຜນປີ '.$planningYear->year.' ສຳເລັດ');
    }

    public function sync(PlanningYear $planningYear)
    {
        abort_if(
            $planningYear->canBeEdited() === false,
            403,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );

        DB::transaction(function () use ($planningYear): void {
            $this->ensureCompanionPlans($planningYear);
            $this->ensureExpenseStructure($planningYear);
        });

        return back()->with('success', 'ກວດແລະສ້າງແຜນທີ່ຂາດສຳເລັດ');
    }

    public function destroy(PlanningYear $planningYear)
    {
        if ($planningYear->isPendingReview()) {
            return redirect()
                ->route('head_of_finance.manage-plan.index')
                ->with('error', 'ບໍ່ສາມາດລຶບແຜນທີ່ຢູ່ສະຖານະລໍຖ້າກວດໄດ້');
        }

        $year = $planningYear->year;

        DB::transaction(function () use ($planningYear): void {
            $reviewRoundIds = $planningYear->reviewRounds()->pluck('id');
            if ($reviewRoundIds->isNotEmpty()) {
                $planningYear->update(['current_review_round_id' => null]);

                DB::table('planning_year_review_comments')
                    ->whereIn('planning_year_review_round_id', $reviewRoundIds)
                    ->delete();
                DB::table('planning_year_review_rounds')
                    ->whereIn('id', $reviewRoundIds)
                    ->delete();
            }

            DB::table('period_plan_overrides')
                ->where('planning_year_id', $planningYear->id)
                ->delete();

            $incomePlanIds = $planningYear->academicIncomePlans()->pluck('id');
            if ($incomePlanIds->isNotEmpty()) {
                DB::table('academic_income_items')->whereIn('plan_id', $incomePlanIds)->delete();
                DB::table('academic_income_plans')->whereIn('id', $incomePlanIds)->delete();
            }

            $salaryPlanIds = $planningYear->salaryPlans()->pluck('id');
            if ($salaryPlanIds->isNotEmpty()) {
                DB::table('salary_entries')->whereIn('plan_id', $salaryPlanIds)->delete();
                DB::table('salary_plans')->whereIn('id', $salaryPlanIds)->delete();
            }

            $expensePlanRowIds = $planningYear->expensePlanRows()->pluck('id');
            if ($expensePlanRowIds->isNotEmpty()) {
                DB::table('expense_plan_rows')->whereIn('id', $expensePlanRowIds)->delete();
            }

            $expensePlanIds = $planningYear->expensePlans()->pluck('id');
            if ($expensePlanIds->isNotEmpty()) {
                DB::table('expense_plans')->whereIn('id', $expensePlanIds)->delete();
            }

            $hasOtherYearStructure = ExpenseSection::whereNotNull('planning_year_id')
                ->where('planning_year_id', '!=', $planningYear->id)
                ->exists();

            if ($hasOtherYearStructure) {
                $this->deleteExpenseStructureForYear($planningYear);
            } else {
                $this->deleteDetachedExpenseStructure();
                $planningYear->sections()->update(['planning_year_id' => null]);
            }

            $planningYear->delete();
        });

        return redirect()
            ->route('head_of_finance.manage-plan.index')
            ->with('success', 'ລຶບແຜນປະຈຳປີ '.$year.' ສຳເລັດ');
    }

    private function ensureCompanionPlans(PlanningYear $planningYear): void
    {
        AcademicIncomePlan::firstOrCreate(
            ['fiscal_year' => $planningYear->year],
            [
                'planning_year_id' => $planningYear->id,
                'notes' => null,
                'created_by' => Auth::id(),
            ]
        )->update(['planning_year_id' => $planningYear->id]);

        SalaryPlan::firstOrCreate(
            [
                'fiscal_year' => $planningYear->year,
                'month' => 1,
            ],
            [
                'planning_year_id' => $planningYear->id,
                'notes' => null,
                'created_by' => Auth::id(),
            ]
        )->update(['planning_year_id' => $planningYear->id]);

        ExpensePlan::firstOrCreate(
            ['planning_year_id' => $planningYear->id],
            [
                'fiscal_year' => $planningYear->year,
                'notes' => null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        )->update(['planning_year_id' => $planningYear->id, 'fiscal_year' => $planningYear->year]);
    }

    private function deleteExpenseStructureForYear(PlanningYear $planningYear): void
    {
        $this->deleteExpenseStructure(
            ExpenseSection::where('planning_year_id', $planningYear->id)->pluck('id')
        );
    }

    private function deleteDetachedExpenseStructure(): void
    {
        $this->deleteExpenseStructure(
            ExpenseSection::whereNull('planning_year_id')->pluck('id')
        );
    }

    private function deleteExpenseStructure(Collection $sectionIds): void
    {
        if ($sectionIds->isEmpty()) {
            return;
        }

        $subsectionIds = ExpenseSubsection::whereIn('section_id', $sectionIds)->pluck('id');

        if ($subsectionIds->isNotEmpty()) {
            ExpensePlanRow::whereIn('subsection_id', $subsectionIds)->delete();
            ExpenseCatalogItem::whereIn('subsection_id', $subsectionIds)->delete();
            ExpenseSubsection::whereIn('id', $subsectionIds)->delete();
        }

        ExpenseSection::whereIn('id', $sectionIds)->delete();
        ExpenseCatalogItem::whereDoesntHave('subsection')->delete();
    }

    private function ensureExpenseStructure(PlanningYear $planningYear): void
    {
        if (ExpenseSection::where('planning_year_id', $planningYear->id)->exists()) {
            return;
        }

        $sourceYear = PlanningYear::where('year', '<', $planningYear->year)
            ->whereHas('sections')
            ->orderByDesc('year')
            ->first();

        if ($sourceYear) {
            $this->copyExpenseStructure($sourceYear, $planningYear);

            return;
        }

        $detachedSections = $this->latestDetachedExpenseSections();
        if ($detachedSections->isNotEmpty()) {
            $this->copyExpenseStructureFromSections($detachedSections, $planningYear);
            $this->deleteDetachedExpenseStructure();

            return;
        }

        $this->buildExpenseStructureFromDefaultRows($planningYear);
    }

    private function buildExpenseStructureFromDefaultRows(PlanningYear $planningYear): void
    {
        $codes = ExpenseCatalogItem::query()
            ->join('expense_subsections', 'expense_subsections.id', '=', 'expense_catalog_items.subsection_id')
            ->select('expense_subsections.code')
            ->distinct()
            ->orderBy('expense_subsections.code')
            ->pluck('expense_subsections.code')
            ->filter()
            ->values();

        if ($codes->isEmpty()) {
            return;
        }

        $defaultPatternId = ExpensePattern::systemDefaults()->where('is_active', true)->orderBy('id')->value('id');

        $sectionCodes = $codes
            ->map(fn (string $code) => implode('.', array_slice(explode('.', $code), 0, 2)))
            ->unique()
            ->values();

        $sectionsByCode = [];
        foreach ($sectionCodes as $index => $sectionCode) {
            $sectionsByCode[$sectionCode] = ExpenseSection::create([
                'planning_year_id' => $planningYear->id,
                'code' => $sectionCode,
                'name' => ExpenseStructureNames::fallbackSectionName($sectionCode),
                'description' => null,
                'display_order' => $index + 1,
                'summary_period_count' => 12,
                'is_active' => true,
            ]);
        }

        $subsectionCodes = collect();
        foreach ($codes as $code) {
            $parts = explode('.', $code);
            for ($length = 3; $length <= count($parts); $length++) {
                $subsectionCodes->push(implode('.', array_slice($parts, 0, $length)));
            }
        }

        $subsectionsByCode = [];
        foreach ($subsectionCodes->unique()->sortBy(fn (string $code) => ExpenseStructureNames::codeSortKey($code))->values() as $index => $code) {
            $sectionCode = implode('.', array_slice(explode('.', $code), 0, 2));
            if (! isset($sectionsByCode[$sectionCode])) {
                continue;
            }

            $subsectionsByCode[$code] = ExpenseSubsection::create([
                'section_id' => $sectionsByCode[$sectionCode]->id,
                'parent_id' => null,
                'code' => $code,
                'name' => ExpenseStructureNames::fallbackSubsectionName($code),
                'description' => null,
                'default_pattern_id' => $defaultPatternId,
                'summary_period_count' => 12,
                'display_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        foreach ($subsectionsByCode as $code => $subsection) {
            $parts = explode('.', $code);
            if (count($parts) <= 3) {
                continue;
            }

            $parentCode = implode('.', array_slice($parts, 0, -1));
            if (isset($subsectionsByCode[$parentCode])) {
                $subsection->update(['parent_id' => $subsectionsByCode[$parentCode]->id]);
            }
        }

        $sourceCatalogItems = ExpenseCatalogItem::with('subsection')
            ->whereHas('subsection', fn ($query) => $query->whereIn('code', array_keys($subsectionsByCode)))
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (ExpenseCatalogItem $item): ?string => $item->subsection?->code)
            ->map(fn ($items) => $items
                ->unique(fn (ExpenseCatalogItem $item): string => $item->sort_order.'|'.$item->item_name)
                ->values());

        foreach ($subsectionsByCode as $code => $subsection) {
            foreach ($sourceCatalogItems->get($code, collect()) as $catalogItem) {
                ExpenseCatalogItem::create([
                    'subsection_id' => $subsection->id,
                    'item_name' => $catalogItem->item_name,
                    'chart_of_account_id' => $catalogItem->chart_of_account_id,
                    'pattern_id' => ExpensePattern::systemDefaultIdOrFallback(
                        $catalogItem->pattern_id,
                        $subsection->default_pattern_id
                    ),
                    'default_values' => $catalogItem->default_values ?? [],
                    'sort_order' => $catalogItem->sort_order,
                    'is_active' => $catalogItem->is_active,
                ]);
            }
        }
    }

    private function copyExpenseStructure(PlanningYear $sourceYear, PlanningYear $targetYear): void
    {
        $sourceSections = ExpenseSection::with('subsections.catalogItems')
            ->where('planning_year_id', $sourceYear->id)
            ->orderBy('display_order')
            ->get();

        $this->copyExpenseStructureFromSections($sourceSections, $targetYear);
    }

    private function latestDetachedExpenseSections(): Collection
    {
        return ExpenseSection::with('subsections.catalogItems')
            ->whereNull('planning_year_id')
            ->orderBy('code')
            ->orderByDesc('id')
            ->get()
            ->unique('code')
            ->sortBy('display_order')
            ->values();
    }

    private function copyExpenseStructureFromSections(Collection $sourceSections, PlanningYear $targetYear): void
    {
        $subsectionIdMap = [];

        foreach ($sourceSections as $sourceSection) {
            $section = ExpenseSection::create([
                'planning_year_id' => $targetYear->id,
                'code' => $sourceSection->code,
                'name' => $sourceSection->name,
                'description' => $sourceSection->description,
                'display_order' => $sourceSection->display_order,
                'summary_period_count' => $sourceSection->summary_period_count ?? 12,
                'is_active' => $sourceSection->is_active,
            ]);

            foreach ($sourceSection->subsections->sortBy('display_order') as $sourceSubsection) {
                $subsection = ExpenseSubsection::create([
                    'section_id' => $section->id,
                    'parent_id' => null,
                    'code' => $sourceSubsection->code,
                    'name' => $sourceSubsection->name,
                    'description' => $sourceSubsection->description,
                    'default_pattern_id' => ExpensePattern::systemDefaultIdOrFallback($sourceSubsection->default_pattern_id),
                    'summary_period_count' => $sourceSubsection->summary_period_count ?? 12,
                    'display_order' => $sourceSubsection->display_order,
                    'is_active' => $sourceSubsection->is_active,
                ]);

                $subsectionIdMap[$sourceSubsection->id] = $subsection->id;

                foreach ($sourceSubsection->catalogItems->sortBy('sort_order') as $catalogItem) {
                    ExpenseCatalogItem::create([
                        'subsection_id' => $subsection->id,
                        'item_name' => $catalogItem->item_name,
                        'chart_of_account_id' => $catalogItem->chart_of_account_id,
                        'pattern_id' => ExpensePattern::systemDefaultIdOrFallback(
                            $catalogItem->pattern_id,
                            $subsection->default_pattern_id
                        ),
                        'default_values' => $catalogItem->default_values ?? [],
                        'sort_order' => $catalogItem->sort_order,
                        'is_active' => $catalogItem->is_active,
                    ]);
                }
            }
        }

        foreach ($sourceSections as $sourceSection) {
            foreach ($sourceSection->subsections as $sourceSubsection) {
                if ($sourceSubsection->parent_id && isset($subsectionIdMap[$sourceSubsection->id], $subsectionIdMap[$sourceSubsection->parent_id])) {
                    ExpenseSubsection::whereKey($subsectionIdMap[$sourceSubsection->id])
                        ->update(['parent_id' => $subsectionIdMap[$sourceSubsection->parent_id]]);
                }
            }
        }

    }
}
