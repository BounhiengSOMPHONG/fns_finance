<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('salary_entries')) {
            return;
        }

        $duplicates = DB::table('salary_entries')
            ->select('plan_id', 'chart_of_account_id', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('plan_id', 'chart_of_account_id')
            ->having('duplicate_count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $keepId = DB::table('salary_entries')
                ->where('plan_id', $duplicate->plan_id)
                ->where('chart_of_account_id', $duplicate->chart_of_account_id)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->value('id');

            DB::table('salary_entries')
                ->where('plan_id', $duplicate->plan_id)
                ->where('chart_of_account_id', $duplicate->chart_of_account_id)
                ->where('id', '<>', $keepId)
                ->delete();
        }

        Schema::table('salary_entries', function (Blueprint $table): void {
            $table->unique(['plan_id', 'chart_of_account_id'], 'salary_entries_plan_account_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('salary_entries')) {
            return;
        }

        Schema::table('salary_entries', function (Blueprint $table): void {
            $table->dropUnique('salary_entries_plan_account_unique');
        });
    }
};
