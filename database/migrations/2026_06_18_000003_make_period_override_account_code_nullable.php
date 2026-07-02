<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('period_plan_overrides', 'account_code')) {
            DB::statement('ALTER TABLE period_plan_overrides MODIFY account_code VARCHAR(30) NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('period_plan_overrides', 'account_code')) {
            DB::statement('ALTER TABLE period_plan_overrides MODIFY account_code VARCHAR(30) NOT NULL');
        }
    }
};
