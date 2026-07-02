<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // ──────────────────────────────────────────────
        // 1. salary_plans — fix fiscal_year type mismatch
        //    varchar(10) → smallint unsigned to match all other tables
        // ──────────────────────────────────────────────
        DB::statement('ALTER TABLE salary_plans MODIFY fiscal_year SMALLINT UNSIGNED NOT NULL');

        // ──────────────────────────────────────────────
        // 2. Align column types for FK compatibility
        // ──────────────────────────────────────────────
        // academic_income_plans.created_by: bigint → int to match users.id
        DB::statement('ALTER TABLE academic_income_plans MODIFY created_by INT DEFAULT NULL');

        // expense_plans.created_by: bigint → int to match users.id
        DB::statement('ALTER TABLE expense_plans MODIFY created_by INT DEFAULT NULL');

        // expense_entries.department_id: int unsigned → int to match departments.id
        if (Schema::hasTable('expense_entries')) {
            DB::statement('ALTER TABLE expense_entries MODIFY department_id INT DEFAULT NULL');
        }

        // ──────────────────────────────────────────────
        // 3. Add missing foreign key constraints
        // ──────────────────────────────────────────────
        if (! $this->foreignKeyExists('academic_income_plans', 'academic_income_plans_created_by_foreign')) {
            Schema::table('academic_income_plans', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! $this->foreignKeyExists('expense_plans', 'expense_plans_created_by_foreign')) {
            Schema::table('expense_plans', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasTable('expense_entries') && ! $this->foreignKeyExists('expense_entries', 'expense_entries_department_id_foreign')) {
            Schema::table('expense_entries', function (Blueprint $table) {
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 3. Remove added FKs
        if (Schema::hasTable('expense_entries') && $this->foreignKeyExists('expense_entries', 'expense_entries_department_id_foreign')) {
            Schema::table('expense_entries', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
            });
        }

        if ($this->foreignKeyExists('expense_plans', 'expense_plans_created_by_foreign')) {
            Schema::table('expense_plans', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
            });
        }

        if ($this->foreignKeyExists('academic_income_plans', 'academic_income_plans_created_by_foreign')) {
            Schema::table('academic_income_plans', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
            });
        }

        // 2. Revert column type changes
        if (Schema::hasTable('expense_entries')) {
            DB::statement('ALTER TABLE expense_entries MODIFY department_id INT UNSIGNED DEFAULT NULL');
        }
        DB::statement('ALTER TABLE expense_plans MODIFY created_by BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE academic_income_plans MODIFY created_by BIGINT UNSIGNED NOT NULL');

        // 1. Revert salary_plans.fiscal_year
        DB::statement('ALTER TABLE salary_plans MODIFY fiscal_year VARCHAR(10) NOT NULL');
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};
