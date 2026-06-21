<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_plan_values') || ! Schema::hasColumn('expense_plan_values', 'value')) {
            return;
        }

        DB::table('expense_plan_values')
            ->whereNull('value')
            ->orWhere('value', '')
            ->delete();
    }

    public function down(): void
    {
        // Empty EAV rows carry no information, so there is nothing useful to restore.
    }
};
