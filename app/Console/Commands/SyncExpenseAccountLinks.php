<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\ExpensePlan;
use App\Models\ExpensePlanValue;
use App\Models\ExpenseSubsection;
use App\Models\ExpenseSubsectionDefaultRow;
use App\Support\ExpenseAccountLinkCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExpenseAccountLinks extends Command
{
    protected $signature = 'expense:sync-account-links {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Fill safe best-fit chart account links for expense default rows and sync plan references.';

    public function handle(ExpenseAccountLinkCatalog $catalog): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $accountsByCode = ChartOfAccount::orderBy('account_code')->get()->keyBy('account_code');
        $rows = ExpenseSubsectionDefaultRow::with('chartOfAccount')
            ->orderBy('subsection_code')
            ->orderBy('sort_order')
            ->get();

        $changes = [];
        foreach ($catalog->decorateRows($rows, $accountsByCode) as $row) {
            $suggestedCode = $row->getAttribute('suggested_account_code');
            $suggestedAccount = $suggestedCode ? $accountsByCode->get($suggestedCode) : null;

            if (! $suggestedAccount || ! $catalog->canAutoUpdate($row)) {
                continue;
            }

            $changes[] = [
                'row' => $row,
                'old_code' => $row->chartOfAccount?->account_code,
                'new_account' => $suggestedAccount,
                'needs_review' => (bool) $row->getAttribute('needs_review'),
            ];
        }

        $this->line($dryRun ? 'DRY RUN: no database writes will be made.' : 'Applying expense account link sync.');
        $this->info('Account link changes: '.count($changes));

        foreach ($changes as $change) {
            $row = $change['row'];
            $this->line(sprintf(
                '%s #%d %s: %s -> %s%s',
                $row->subsection_code,
                $row->sort_order,
                $row->item_name,
                $change['old_code'] ?: '-',
                $change['new_account']->account_code,
                $change['needs_review'] ? ' (review)' : ''
            ));
        }

        if ($dryRun) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes): void {
            foreach ($changes as $change) {
                /** @var ExpenseSubsectionDefaultRow $row */
                $row = $change['row'];
                DB::table('expense_subsection_default_rows')
                    ->where('id', $row->id)
                    ->update([
                        'chart_of_account_id' => $change['new_account']->id,
                        'updated_at' => now(),
                    ]);

                $row->chart_of_account_id = $change['new_account']->id;
                $row->setRelation('chartOfAccount', $change['new_account']);

                $this->syncPlanRowsFromDefaultRow($row);
            }
        });

        $this->info('Expense account links synced.');

        return self::SUCCESS;
    }

    private function syncPlanRowsFromDefaultRow(ExpenseSubsectionDefaultRow $defaultRow): void
    {
        $subsectionIds = ExpenseSubsection::where('code', $defaultRow->subsection_code)->pluck('id');
        if ($subsectionIds->isEmpty()) {
            return;
        }

        ExpensePlan::whereIn('subsection_id', $subsectionIds)
            ->where('plan_detail', $defaultRow->item_name)
            ->get()
            ->each(function (ExpensePlan $plan) use ($defaultRow): void {
                ExpensePlanValue::updateOrCreate([
                    'expense_plan_id' => $plan->id,
                    'field_key' => 'reference',
                ], [
                    'value_text' => $defaultRow->chartOfAccount?->account_code,
                    'value_number' => null,
                    'value_date' => null,
                    'value_boolean' => null,
                ]);
            });
    }
}
