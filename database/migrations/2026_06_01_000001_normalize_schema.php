<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────────
        // 1. salary_plans — fix fiscal_year type mismatch
        //    varchar(10) → smallint unsigned to match all other tables
        // ──────────────────────────────────────────────
        DB::statement("ALTER TABLE salary_plans MODIFY fiscal_year SMALLINT UNSIGNED NOT NULL");

        // ──────────────────────────────────────────────
        // 2. Align column types for FK compatibility
        // ──────────────────────────────────────────────
        // academic_income_plans.created_by: bigint → int to match users.id
        DB::statement("ALTER TABLE academic_income_plans MODIFY created_by INT NOT NULL");

        // expense_plans.created_by: bigint → int to match users.id
        DB::statement("ALTER TABLE expense_plans MODIFY created_by INT NOT NULL");

        // expense_entries.department_id: int unsigned → int to match departments.id
        DB::statement("ALTER TABLE expense_entries MODIFY department_id INT DEFAULT NULL");

        // ──────────────────────────────────────────────
        // 3. Add missing foreign key constraints
        // ──────────────────────────────────────────────
        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('expense_plans', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('expense_entries', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        // 3. Remove added FKs
        Schema::table('expense_entries', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });

        Schema::table('expense_plans', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('academic_income_plans', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        // 2. Revert column type changes
        DB::statement("ALTER TABLE expense_entries MODIFY department_id INT UNSIGNED DEFAULT NULL");
        DB::statement("ALTER TABLE expense_plans MODIFY created_by BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE academic_income_plans MODIFY created_by BIGINT UNSIGNED NOT NULL");

        // 1. Revert salary_plans.fiscal_year
        DB::statement("ALTER TABLE salary_plans MODIFY fiscal_year VARCHAR(10) NOT NULL");
    }
};
