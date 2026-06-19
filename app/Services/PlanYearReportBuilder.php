<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\ExpensePlan;
use App\Models\PlanningYear;
use App\Models\SalaryEntry;
use Illuminate\Support\Collection;

class PlanYearReportBuilder
{
    public function buildForPlanningYear(PlanningYear $planningYear): array
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get(['id', 'account_code', 'account_name', 'parent_id']);
        $accountsById = $accounts->keyBy('id');
        $accountsByCode = $accounts->keyBy('account_code');
        $parentIds = $accounts->pluck('parent_id')->filter()->unique()->flip();

        $rows = $accounts
            ->mapWithKeys(fn (ChartOfAccount $account): array => [
                $account->id => $this->emptyRow($account, $accountsById, $parentIds->has($account->id)),
            ])
            ->all();

        $warnings = [
            'unlinked_expenses' => [],
            'reference_fallbacks' => [],
        ];

        $this->addSalaryEntries($planningYear, $rows, $accountsById);
        $this->addExpensePlans($planningYear, $rows, $accountsById, $accountsByCode, $warnings);

        $visibleAccountIds = collect($rows)
            ->filter(fn (array $row): bool => (float) $row['total_amount'] !== 0.0 || $this->isMainExpenseRoot($row['code']))
            ->keys()
            ->flip();

        $reportRows = collect($rows)
            ->filter(fn (array $row, int $id): bool => $visibleAccountIds->has($id))
            ->sortBy(fn (array $row): string => $this->codeSortKey($row['code']))
            ->values();

        return [
            'rows' => $reportRows,
            'totals' => [
                'total_amount' => (float) $reportRows->where('level', 0)->sum('total_amount'),
                'state_amount' => (float) $reportRows->where('level', 0)->sum('state_amount'),
                'faculty_amount' => (float) $reportRows->where('level', 0)->sum('faculty_amount'),
            ],
            'warnings' => $warnings,
        ];
    }

    private function addSalaryEntries(PlanningYear $planningYear, array &$rows, Collection $accountsById): void
    {
        SalaryEntry::with('chartOfAccount')
            ->whereHas('plan', fn ($query) => $query->where('planning_year_id', $planningYear->id))
            ->get()
            ->each(function (SalaryEntry $entry) use (&$rows, $accountsById): void {
                if (! $entry->chart_of_account_id || ! $accountsById->has($entry->chart_of_account_id)) {
                    return;
                }

                $this->addAmountToAccountAndAncestors(
                    $rows,
                    $accountsById,
                    (int) $entry->chart_of_account_id,
                    (float) $entry->annual_amount,
                    'state_amount'
                );
            });
    }

    private function addExpensePlans(
        PlanningYear $planningYear,
        array &$rows,
        Collection $accountsById,
        Collection $accountsByCode,
        array &$warnings
    ): void {
        $plans = ExpensePlan::with(['chartOfAccount', 'subsection', 'pattern'])
            ->where('planning_year_id', $planningYear->id)
            ->get();

        if ($plans->isEmpty()) {
            return;
        }

        foreach ($plans as $plan) {
            $subsectionCode = (string) ($plan->subsection?->code ?? '');
            $itemName = (string) ($plan->item_name ?: $plan->plan_detail ?: '');
            $account = $plan->chartOfAccount;

            if (! $account) {
                $reference = trim((string) (($plan->calculation_values ?? [])['reference'] ?? ''));
                $account = $reference !== '' ? $accountsByCode->get($reference) : null;
            }

            if (! $account || ! $accountsById->has($account->id)) {
                $warnings['unlinked_expenses'][] = [
                    'plan_id' => $plan->id,
                    'subsection_code' => $subsectionCode,
                    'item_name' => $itemName ?: $plan->plan_detail,
                    'reference' => ($plan->calculation_values ?? [])['reference'] ?? null,
                    'amount' => $plan->yearlyTotal(),
                ];

                continue;
            }

            $this->addAmountToAccountAndAncestors(
                $rows,
                $accountsById,
                (int) $account->id,
                $plan->yearlyTotal(),
                'faculty_amount'
            );
        }
    }

    private function addAmountToAccountAndAncestors(
        array &$rows,
        Collection $accountsById,
        int $accountId,
        float $amount,
        string $bucket
    ): void {
        $account = $accountsById->get($accountId);

        while ($account && isset($rows[$account->id])) {
            $rows[$account->id][$bucket] += $amount;
            $rows[$account->id]['total_amount'] += $amount;
            $account = $account->parent_id ? $accountsById->get($account->parent_id) : null;
        }
    }

    private function emptyRow(ChartOfAccount $account, Collection $accountsById, bool $isGroup): array
    {
        return [
            'id' => $account->id,
            'code' => (string) $account->account_code,
            'title' => (string) $account->account_name,
            'level' => $this->levelFor($account, $accountsById),
            'is_group' => $isGroup,
            'total_amount' => 0.0,
            'state_amount' => 0.0,
            'faculty_amount' => 0.0,
        ];
    }

    private function levelFor(ChartOfAccount $account, Collection $accountsById): int
    {
        $level = 0;
        $parent = $account->parent_id ? $accountsById->get($account->parent_id) : null;

        while ($parent && $level < 8) {
            $level++;
            $parent = $parent->parent_id ? $accountsById->get($parent->parent_id) : null;
        }

        return $level;
    }

    private function isMainExpenseRoot(string $code): bool
    {
        return in_array($code, ['60000000', '61000000', '62000000', '63000000', '66000000'], true);
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }

    private function codeSortKey(string $code): string
    {
        return preg_replace_callback('/\d+/', fn (array $match): string => str_pad($match[0], 12, '0', STR_PAD_LEFT), $code) ?? $code;
    }
}
