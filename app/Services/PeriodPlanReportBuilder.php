<?php

namespace App\Services;

use App\Models\PeriodPlanOverride;
use App\Models\PlanningYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PeriodPlanReportBuilder
{
    public function __construct(
        private readonly PlanYearReportBuilder $planYearReportBuilder
    ) {
    }

    public function buildForPlanningYear(PlanningYear $planningYear): array
    {
        $yearlyReport = $this->planYearReportBuilder->buildForPlanningYear($planningYear);
        $overrides = PeriodPlanOverride::query()
            ->where('planning_year_id', $planningYear->id)
            ->get()
            ->keyBy('chart_of_account_id');

        $rows = collect($yearlyReport['rows'] ?? [])
            ->filter(fn (array $row): bool => $this->isAcademicAccount((string) ($row['code'] ?? '')))
            ->map(fn (array $row): array => $this->periodRow($row, $overrides))
            ->pipe(fn (Collection $rows): Collection => $this->rollUpGroupRows($rows))
            ->values();
        $totalRows = $rows->where('level', 0);

        return [
            'rows' => $rows,
            'totals' => [
                'yearly_amount' => (float) $totalRows->sum('yearly_amount'),
                'period_1_amount' => (float) $totalRows->sum('period_1_amount'),
                'period_2_amount' => (float) $totalRows->sum('period_2_amount'),
                'first_half_amount' => (float) $totalRows->sum('first_half_amount'),
                'second_half_amount' => (float) $totalRows->sum('second_half_amount'),
                'requested_decrease_amount' => (float) $totalRows->sum('requested_decrease_amount'),
                'requested_increase_amount' => (float) $totalRows->sum('requested_increase_amount'),
                'adjusted_second_half_amount' => (float) $totalRows->sum('adjusted_second_half_amount'),
                'period_3_amount' => (float) $totalRows->sum('period_3_amount'),
                'period_4_amount' => (float) $totalRows->sum('period_4_amount'),
                'period_3_4_total_amount' => (float) $totalRows->sum('period_3_4_total_amount'),
            ],
            'warnings' => $yearlyReport['warnings'] ?? ['unlinked_expenses' => [], 'reference_fallbacks' => []],
        ];
    }

    public function ensureDefaultOverrides(PlanningYear $planningYear, ?int $userId = null): int
    {
        $now = now();
        $rows = collect($this->buildForPlanningYear($planningYear)['rows'] ?? [])
            ->filter(fn (array $row): bool => ! $row['is_group']
                && ! $row['has_override']
                && (int) $row['chart_of_account_id'] > 0)
            ->map(fn (array $row): array => [
                'planning_year_id' => $planningYear->id,
                'chart_of_account_id' => (int) $row['chart_of_account_id'],
                'period_1_amount' => (float) $row['period_1_amount'],
                'period_2_amount' => (float) $row['period_2_amount'],
                'requested_decrease_amount' => (float) $row['requested_decrease_amount'],
                'requested_increase_amount' => (float) $row['requested_increase_amount'],
                'period_3_amount' => (float) $row['period_3_amount'],
                'period_4_amount' => (float) $row['period_4_amount'],
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values();

        if ($rows->isEmpty()) {
            return 0;
        }

        return DB::table('period_plan_overrides')->insertOrIgnore($rows->all());
    }

    public function findEditableRow(PlanningYear $planningYear, string $accountCode): ?array
    {
        return $this->buildForPlanningYear($planningYear)['rows']
            ->first(fn (array $row): bool => $row['account_code'] === $accountCode && ! $row['is_group']);
    }

    private function periodRow(array $row, Collection $overrides): array
    {
        $accountCode = (string) ($row['code'] ?? '');
        $accountId = (int) ($row['id'] ?? 0);
        $yearlyAmount = (float) ($row['faculty_amount'] ?? 0);
        $defaultPeriodAmount = $yearlyAmount / 4;
        $override = $overrides->get($accountId);
        $period1Amount = $override ? (float) $override->period_1_amount : $defaultPeriodAmount;
        $period2Amount = $override ? (float) $override->period_2_amount : $defaultPeriodAmount;
        $firstHalfAmount = $period1Amount + $period2Amount;
        $secondHalfAmount = $yearlyAmount - $firstHalfAmount;
        $requestedDecreaseAmount = $override ? (float) $override->requested_decrease_amount : 0.0;
        $requestedIncreaseAmount = $override ? (float) $override->requested_increase_amount : 0.0;
        $adjustedSecondHalfAmount = $secondHalfAmount - $requestedDecreaseAmount + $requestedIncreaseAmount;
        $period3Amount = $override ? (float) $override->period_3_amount : ($adjustedSecondHalfAmount / 2);
        $period4Amount = $override ? (float) $override->period_4_amount : ($adjustedSecondHalfAmount - $period3Amount);
        $period34TotalAmount = $period3Amount + $period4Amount;
        $reductionPercent = $secondHalfAmount > 0
            ? ($requestedDecreaseAmount / $secondHalfAmount) * 100
            : 0.0;

        return [
            'account_code' => $accountCode,
            'chart_of_account_id' => $accountId,
            'title' => (string) ($row['title'] ?? ''),
            'level' => (int) ($row['level'] ?? 0),
            'is_group' => (bool) ($row['is_group'] ?? false),
            'yearly_amount' => $yearlyAmount,
            'period_1_amount' => $period1Amount,
            'period_2_amount' => $period2Amount,
            'first_half_amount' => $firstHalfAmount,
            'second_half_amount' => $secondHalfAmount,
            'requested_decrease_amount' => $requestedDecreaseAmount,
            'requested_increase_amount' => $requestedIncreaseAmount,
            'adjusted_second_half_amount' => $adjustedSecondHalfAmount,
            'period_3_amount' => $period3Amount,
            'period_4_amount' => $period4Amount,
            'period_3_4_total_amount' => $period34TotalAmount,
            'reduction_percent' => $reductionPercent,
            'has_override' => (bool) $override,
        ];
    }

    private function isAcademicAccount(string $accountCode): bool
    {
        if (! preg_match('/^\d{2}/', $accountCode, $matches)) {
            return false;
        }

        return (int) $matches[0] >= 62;
    }

    private function rollUpGroupRows(Collection $rows): Collection
    {
        return $rows->map(function (array $row) use ($rows): array {
            if (! $row['is_group']) {
                return $row;
            }

            $children = $rows->filter(fn (array $child): bool => ! $child['is_group']
                && (int) $child['level'] > (int) $row['level']
                && $this->isDescendantCode((string) $row['account_code'], (string) $child['account_code'], (int) $row['level']));

            if ($children->isEmpty()) {
                return $row;
            }

            $period1Amount = (float) $children->sum('period_1_amount');
            $period2Amount = (float) $children->sum('period_2_amount');
            $firstHalfAmount = $period1Amount + $period2Amount;
            $secondHalfAmount = (float) $row['yearly_amount'] - $firstHalfAmount;
            $requestedDecreaseAmount = (float) $children->sum('requested_decrease_amount');
            $requestedIncreaseAmount = (float) $children->sum('requested_increase_amount');
            $adjustedSecondHalfAmount = $secondHalfAmount - $requestedDecreaseAmount + $requestedIncreaseAmount;
            $period3Amount = (float) $children->sum('period_3_amount');
            $period4Amount = (float) $children->sum('period_4_amount');
            $period34TotalAmount = $period3Amount + $period4Amount;

            $row['period_1_amount'] = $period1Amount;
            $row['period_2_amount'] = $period2Amount;
            $row['first_half_amount'] = $firstHalfAmount;
            $row['second_half_amount'] = $secondHalfAmount;
            $row['requested_decrease_amount'] = $requestedDecreaseAmount;
            $row['requested_increase_amount'] = $requestedIncreaseAmount;
            $row['adjusted_second_half_amount'] = $adjustedSecondHalfAmount;
            $row['period_3_amount'] = $period3Amount;
            $row['period_4_amount'] = $period4Amount;
            $row['period_3_4_total_amount'] = $period34TotalAmount;
            $row['reduction_percent'] = $secondHalfAmount > 0
                ? ($requestedDecreaseAmount / $secondHalfAmount) * 100
                : 0.0;
            $row['has_override'] = false;

            return $row;
        });
    }

    private function isDescendantCode(string $parentCode, string $childCode, int $parentLevel): bool
    {
        $prefixLength = min(($parentLevel + 1) * 2, strlen($parentCode));

        return $parentCode !== $childCode
            && str_starts_with($childCode, substr($parentCode, 0, $prefixLength));
    }
}
