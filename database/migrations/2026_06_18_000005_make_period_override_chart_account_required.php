<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('period_plan_overrides', 'chart_of_account_id')) {
            DB::statement('ALTER TABLE period_plan_overrides MODIFY chart_of_account_id INT UNSIGNED NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('period_plan_overrides', 'chart_of_account_id')) {
            DB::statement('ALTER TABLE period_plan_overrides MODIFY chart_of_account_id INT UNSIGNED NULL');
        }
    }
};
