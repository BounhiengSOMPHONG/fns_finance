<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('expense_items') || ! Schema::hasTable('chart_of_accounts')) {
            return;
        }

        $this->dropChartOfAccountForeignIfExists();

        DB::statement('ALTER TABLE `expense_items` MODIFY `chart_of_account_id` INT UNSIGNED NULL');

        DB::statement(
            'ALTER TABLE `expense_items`
             ADD CONSTRAINT `expense_items_chart_of_account_id_foreign`
             FOREIGN KEY (`chart_of_account_id`) REFERENCES `chart_of_accounts` (`id`)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );

        DB::statement('CREATE INDEX `expense_items_chart_of_account_id_index` ON `expense_items` (`chart_of_account_id`)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('expense_items')) {
            return;
        }

        $this->dropChartOfAccountForeignIfExists();
        $this->dropChartOfAccountIndexIfExists();

        DB::statement('ALTER TABLE `expense_items` MODIFY `chart_of_account_id` BIGINT UNSIGNED NULL');
    }

    private function dropChartOfAccountForeignIfExists(): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS c
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = "FOREIGN KEY"',
            ['expense_items', 'expense_items_chart_of_account_id_foreign']
        );

        if ((int) ($exists->c ?? 0) > 0) {
            DB::statement('ALTER TABLE `expense_items` DROP FOREIGN KEY `expense_items_chart_of_account_id_foreign`');
        }
    }

    private function dropChartOfAccountIndexIfExists(): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS c
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?',
            ['expense_items', 'expense_items_chart_of_account_id_index']
        );

        if ((int) ($exists->c ?? 0) > 0) {
            DB::statement('DROP INDEX `expense_items_chart_of_account_id_index` ON `expense_items`');
        }
    }
};
