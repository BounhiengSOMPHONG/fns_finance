<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\ExpenseCatalogItem;
use App\Models\ExpensePlanRow;
use App\Support\ExpenseAccountLinkCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExpenseAccountLinks extends Command
{
    protected $signature = 'expense:sync-account-links {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Fill safe best-fit chart account links for expense catalog items and sync plan accounts.';

    public function handle(ExpenseAccountLinkCatalog $catalog): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $accountsByCode = ChartOfAccount::orderBy('account_code')->get()->keyBy('account_code');
        $rows = ExpenseCatalogItem::with(['chartOfAccount', 'subsection'])
            ->join('expense_subsections', 'expense_subsections.id', '=', 'expense_catalog_items.subsection_id')
            ->select('expense_catalog_items.*')
            ->orderBy('expense_subsections.code')
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
                $row->subsection_code ?: '-',
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
                /** @var ExpenseCatalogItem $row */
                $row = $change['row'];
                DB::table('expense_catalog_items')
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

    private function syncPlanRowsFromDefaultRow(ExpenseCatalogItem $defaultRow): void
    {
        ExpensePlanRow::where('catalog_item_id', $defaultRow->id)
            ->orWhere(function ($query) use ($defaultRow): void {
                $query->where('subsection_id', $defaultRow->subsection_id)
                    ->where(function ($nested) use ($defaultRow): void {
                        $nested->where('item_name', $defaultRow->item_name)
                            ->orWhere('plan_detail', $defaultRow->item_name);
                    });
            })
            ->get()
            ->each(function (ExpensePlanRow $plan) use ($defaultRow): void {
                $plan->update([
                    'catalog_item_id' => $defaultRow->id,
                    'chart_of_account_id' => $defaultRow->chart_of_account_id,
                ]);
            });
    }
}
