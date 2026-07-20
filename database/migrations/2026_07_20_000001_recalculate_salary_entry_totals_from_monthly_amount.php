<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('salary_entries')) {
            return;
        }

        DB::table('salary_entries')->update([
            'monthly_total' => DB::raw('COALESCE(amount, 0)'),
            'annual_amount' => DB::raw('COALESCE(amount, 0) * 12'),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('salary_entries')) {
            return;
        }

        DB::table('salary_entries')->update([
            'monthly_total' => DB::raw('COALESCE(amount, 0)'),
            'annual_amount' => DB::raw('COALESCE(amount, 0) * 12'),
        ]);
    }
};
