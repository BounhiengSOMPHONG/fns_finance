<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns with correct UNSIGNED BIGINT type using raw SQL
        if (!Schema::hasColumn('budget_plan_reviewers', 'budget_plan_id')) {
            DB::statement('ALTER TABLE `budget_plan_reviewers` ADD `budget_plan_id` BIGINT UNSIGNED NOT NULL AFTER `id`');
        }
        if (!Schema::hasColumn('budget_plan_reviewers', 'user_id')) {
            DB::statement('ALTER TABLE `budget_plan_reviewers` ADD `user_id` BIGINT UNSIGNED NOT NULL AFTER `budget_plan_id`');
        }
        if (!Schema::hasColumn('budget_plan_reviewers', 'assigned_by')) {
            DB::statement('ALTER TABLE `budget_plan_reviewers` ADD `assigned_by` BIGINT UNSIGNED NOT NULL AFTER `user_id`');
        }

        // Add foreign keys (ignore if they already exist)
        try {
            DB::statement('ALTER TABLE `budget_plan_reviewers` ADD CONSTRAINT `bpr_budget_plan_id_fk` FOREIGN KEY (`budget_plan_id`) REFERENCES `budget_plans`(`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE `budget_plan_reviewers` ADD CONSTRAINT `bpr_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE `budget_plan_reviewers` ADD CONSTRAINT `bpr_assigned_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}

        // Add unique constraint (ignore if already exists)
        try {
            DB::statement('ALTER TABLE `budget_plan_reviewers` ADD UNIQUE `bpr_plan_user_unique` (`budget_plan_id`, `user_id`)');
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        try { DB::statement('ALTER TABLE `budget_plan_reviewers` DROP FOREIGN KEY `bpr_budget_plan_id_fk`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `budget_plan_reviewers` DROP FOREIGN KEY `bpr_user_id_fk`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `budget_plan_reviewers` DROP FOREIGN KEY `bpr_assigned_by_fk`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `budget_plan_reviewers` DROP INDEX `bpr_plan_user_unique`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `budget_plan_reviewers` DROP COLUMN `budget_plan_id`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `budget_plan_reviewers` DROP COLUMN `user_id`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `budget_plan_reviewers` DROP COLUMN `assigned_by`'); } catch (\Exception $e) {}
    }
};
