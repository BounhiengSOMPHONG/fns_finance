<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_income_setting_sets') || ! Schema::hasTable('academic_income_items') || ! Schema::hasColumn('academic_income_items', 'setting_set_id')) {
            return;
        }

        $populatedYears = collect();
        foreach (['credit_unit_price_settings', 'income_rate_settings', 'nuol_pct_settings'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'setting_set_id')) {
                continue;
            }

            $populatedYears = $populatedYears->merge(
                DB::table($tableName)
                    ->join('academic_income_setting_sets', "{$tableName}.setting_set_id", '=', 'academic_income_setting_sets.id')
                    ->select('academic_income_setting_sets.fiscal_year', 'academic_income_setting_sets.id')
                    ->get()
            );
        }

        $setsByYear = $populatedYears
            ->unique('id')
            ->sortBy('fiscal_year')
            ->values();

        if ($setsByYear->isEmpty()) {
            return;
        }

        DB::table('academic_income_items')
            ->join('academic_income_plans', 'academic_income_items.plan_id', '=', 'academic_income_plans.id')
            ->select('academic_income_items.id', 'academic_income_plans.fiscal_year')
            ->orderBy('academic_income_items.id')
            ->chunkById(100, function ($rows) use ($setsByYear): void {
                foreach ($rows as $row) {
                    $set = $setsByYear
                        ->filter(fn ($settingSet) => (int) $settingSet->fiscal_year <= (int) $row->fiscal_year)
                        ->last()
                        ?? $setsByYear->last();

                    DB::table('academic_income_items')
                        ->where('id', $row->id)
                        ->update(['setting_set_id' => $set->id]);
                }
            }, 'academic_income_items.id', 'id');

        $referencedSetIds = collect();
        foreach (['credit_unit_price_settings', 'income_rate_settings', 'nuol_pct_settings', 'academic_income_items'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'setting_set_id')) {
                $referencedSetIds = $referencedSetIds->merge(DB::table($tableName)->whereNotNull('setting_set_id')->pluck('setting_set_id'));
            }
        }

        DB::table('academic_income_setting_sets')
            ->whereNotIn('id', $referencedSetIds->unique()->values())
            ->delete();
    }

    public function down(): void
    {
        //
    }
};
