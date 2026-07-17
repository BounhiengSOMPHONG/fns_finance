<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\PlanningYear;
use App\Models\SalaryEntry;
use App\Models\SalaryPlan;
use Illuminate\Support\Collection;

class SalaryReportBuilder
{
    public function buildForPlanningYear(PlanningYear $planningYear): array
    {
        $accounts = ChartOfAccount::query()
            ->where(function ($query): void {
                $query->where('account_code', 'like', '60%')
                    ->orWhere('account_code', 'like', '61%');
            })
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name', 'parent_id']);

        $plans = SalaryPlan::with(['entries.chartOfAccount'])
            ->where('planning_year_id', $planningYear->id)
            ->orderBy('month')
            ->get();

        $accountsById = $accounts->keyBy('id');
        $parentIds = $accounts->pluck('parent_id')->filter()->unique()->flip();
        $baseRows = $accounts
            ->mapWithKeys(fn (ChartOfAccount $account): array => [
                $account->id => $this->emptyRow($account, $this->levelFor($account, $accountsById), $parentIds->has($account->id)),
            ])
            ->all();

        foreach ($plans->flatMap(fn (SalaryPlan $plan): Collection => $plan->entries) as $entry) {
            $this->addEntryToAccountAndAncestors($baseRows, $entry, $accountsById);
        }

        $rows = collect($baseRows)
            ->sortBy(fn (array $row): string => $this->codeSortKey($row['code']))
            ->values();

        return [
            'month' => $plans->first()?->month,
            'fiscal_year' => $plans->first()?->fiscal_year ?? $planningYear->year,
            'rows' => $rows,
            'totals' => [
                'person_count' => (int) $rows
                    ->where('level', 0)
                    ->sum('person_count'),
                'transfer_amount' => (float) $rows
                    ->where('level', 0)
                    ->sum('transfer_amount'),
                'cash_amount' => (float) $rows
                    ->where('level', 0)
                    ->sum('cash_amount'),
                'monthly_total' => (float) $rows
                    ->where('level', 0)
                    ->sum('monthly_total'),
                'annual_total' => (float) $rows
                    ->where('level', 0)
                    ->sum('annual_total'),
            ],
        ];
    }

    private function emptyRow(ChartOfAccount $account, int $level, bool $isGroup): array
    {
        return [
            'id' => $account->id,
            'code' => (string) $account->account_code,
            'title' => (string) $account->account_name,
            'level' => $level,
            'is_group' => $isGroup,
            'person_count' => 0,
            'transfer_amount' => 0.0,
            'cash_amount' => 0.0,
            'monthly_total' => 0.0,
            'annual_total' => 0.0,
        ];
    }

    private function addEntryToAccountAndAncestors(array &$rows, SalaryEntry $entry, Collection $accountsById): void
    {
        $account = $entry->chartOfAccount;

        while ($account && isset($rows[$account->id])) {
            $rows[$account->id]['is_group'] = $rows[$account->id]['is_group']
                || $account->id !== $entry->chart_of_account_id;
            $rows[$account->id]['person_count'] += (int) $entry->person_count;

            $monthlyTotal = (float) $entry->monthly_total;
            if ($entry->payment_type === 'cash') {
                $rows[$account->id]['cash_amount'] += (float) $entry->amount;
            } else {
                $rows[$account->id]['transfer_amount'] += (float) $entry->amount;
            }

            $rows[$account->id]['monthly_total'] += $monthlyTotal;
            $rows[$account->id]['annual_total'] += (float) $entry->annual_amount;

            $account = $account->parent_id ? $accountsById->get($account->parent_id) : null;
        }
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

    private function codeSortKey(string $code): string
    {
        return preg_replace_callback('/\d+/', fn (array $match): string => str_pad($match[0], 12, '0', STR_PAD_LEFT), $code) ?? $code;
    }
}
