<?php

namespace App\Services;

use App\Models\PeriodPlanOverride;
use App\Models\PlanningYear;
use Illuminate\Support\Collection;

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
            ->keyBy('account_code');

        $rows = collect($yearlyReport['rows'] ?? [])
            ->filter(fn (array $row): bool => $this->isAcademicAccount((string) ($row['code'] ?? '')))
            ->map(fn (array $row): array => $this->periodRow($row, $overrides))
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
            ],
            'warnings' => $yearlyReport['warnings'] ?? ['unlinked_expenses' => [], 'reference_fallbacks' => []],
        ];
    }

    public function findEditableRow(PlanningYear $planningYear, string $accountCode): ?array
    {
        return $this->buildForPlanningYear($planningYear)['rows']
            ->firstWhere('account_code', $accountCode);
    }

    private function periodRow(array $row, Collection $overrides): array
    {
        $accountCode = (string) ($row['code'] ?? '');
        $yearlyAmount = (float) ($row['faculty_amount'] ?? 0);
        $defaultPeriodAmount = $yearlyAmount / 4;
        $override = $overrides->get($accountCode);
        $period1Amount = $override ? (float) $override->period_1_amount : $defaultPeriodAmount;
        $period2Amount = $override ? (float) $override->period_2_amount : $defaultPeriodAmount;
        $firstHalfAmount = $period1Amount + $period2Amount;

        return [
            'account_code' => $accountCode,
            'title' => (string) ($row['title'] ?? ''),
            'level' => (int) ($row['level'] ?? 0),
            'is_group' => (bool) ($row['is_group'] ?? false),
            'yearly_amount' => $yearlyAmount,
            'period_1_amount' => $period1Amount,
            'period_2_amount' => $period2Amount,
            'first_half_amount' => $firstHalfAmount,
            'second_half_amount' => $yearlyAmount - $firstHalfAmount,
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
}
