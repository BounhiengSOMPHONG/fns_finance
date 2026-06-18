<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            if (! Schema::hasColumn('period_plan_overrides', 'chart_of_account_id')) {
                $table->unsignedInteger('chart_of_account_id')->nullable()->after('planning_year_id');
            }
        });

        if (Schema::hasColumn('period_plan_overrides', 'account_code')) {
            $accountsByCode = DB::table('chart_of_accounts')->pluck('id', 'account_code');

            DB::table('period_plan_overrides')
                ->select(['id', 'account_code'])
                ->orderBy('id')
                ->chunkById(100, function ($overrides) use ($accountsByCode): void {
                    foreach ($overrides as $override) {
                        $accountId = $accountsByCode[(string) $override->account_code] ?? null;

                        if ($accountId) {
                            DB::table('period_plan_overrides')
                                ->where('id', $override->id)
                                ->update(['chart_of_account_id' => $accountId]);
                        }
                    }
                });
        }

        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->unique(['planning_year_id', 'chart_of_account_id'], 'period_plan_overrides_year_chart_account_unique');
            $table->foreign('chart_of_account_id', 'period_plan_overrides_chart_account_fk')
                ->references('id')
                ->on('chart_of_accounts')
                ->cascadeOnDelete();

            // Keep account_code as a legacy backup column so existing data is not removed.
        });
    }

    public function down(): void
    {
        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            if (! Schema::hasColumn('period_plan_overrides', 'account_code')) {
                $table->string('account_code', 30)->nullable()->after('chart_of_account_id');
            }
        });

        if (Schema::hasColumn('period_plan_overrides', 'chart_of_account_id')) {
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
        }

        Schema::table('period_plan_overrides', function (Blueprint $table): void {
            $table->dropForeign('period_plan_overrides_chart_account_fk');
            $table->dropUnique('period_plan_overrides_year_chart_account_unique');

            if (Schema::hasColumn('period_plan_overrides', 'chart_of_account_id')) {
                $table->dropColumn('chart_of_account_id');
            }
        });
    }
};
