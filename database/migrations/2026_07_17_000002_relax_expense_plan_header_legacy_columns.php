<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_plans')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasColumn('expense_plans', 'section_id')) {
            DB::statement('ALTER TABLE expense_plans MODIFY section_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('expense_plans', 'plan_detail')) {
            DB::statement('ALTER TABLE expense_plans MODIFY plan_detail VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        //
    }
};
