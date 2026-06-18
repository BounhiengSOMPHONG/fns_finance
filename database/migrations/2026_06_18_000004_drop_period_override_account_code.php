<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('period_plan_overrides', 'account_code')) {
            return;
        }

        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->dropUnique('period_plan_overrides_year_account_unique');
            $table->dropColumn('account_code');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('period_plan_overrides', 'account_code')) {
            return;
        }

        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->string('account_code', 30)->nullable()->after('chart_of_account_id');
        });

        $codesById = DB::table('chart_of_accounts')->pluck('account_code', 'id');

        DB::table('period_plan_overrides')
            ->select(['id', 'chart_of_account_id'])
            ->orderBy('id')
            ->chunkById(100, function ($overrides) use ($codesById): void {
                foreach ($overrides as $override) {
                    $accountCode = $codesById[(int) $override->chart_of_account_id] ?? null;

                    if ($accountCode) {
                        DB::table('period_plan_overrides')
                            ->where('id', $override->id)
                            ->update(['account_code' => $accountCode]);
                    }
                }
            });

        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->unique(['planning_year_id', 'account_code'], 'period_plan_overrides_year_account_unique');
        });
    }
};
