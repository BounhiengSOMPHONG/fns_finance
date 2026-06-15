<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_income_setting_sets')) {
            Schema::create('academic_income_setting_sets', function (Blueprint $table): void {
                $table->id();
                $table->unsignedSmallInteger('fiscal_year')->unique();
                $table->string('name');
                $table->string('gov_doc_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $this->ensureSettingSetColumn('credit_unit_price_settings');
        $this->ensureSettingSetColumn('income_rate_settings');
        $this->ensureSettingSetColumn('nuol_pct_settings');

        if (Schema::hasTable('academic_income_items') && ! Schema::hasColumn('academic_income_items', 'setting_set_id')) {
            Schema::table('academic_income_items', function (Blueprint $table): void {
                $table->foreignId('setting_set_id')
                    ->nullable()
                    ->after('plan_id')
                    ->constrained('academic_income_setting_sets')
                    ->nullOnDelete();
            });
        }

        $this->backfillSettingSets();
    }

    public function down(): void
    {
        if (Schema::hasTable('academic_income_items') && Schema::hasColumn('academic_income_items', 'setting_set_id')) {
            Schema::table('academic_income_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('setting_set_id');
            });
        }
    }

    private function ensureSettingSetColumn(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'setting_set_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->foreignId('setting_set_id')
                ->nullable()
                ->after('id')
                ->constrained('academic_income_setting_sets')
                ->nullOnDelete();
        });
    }

    private function backfillSettingSets(): void
    {
        if (! Schema::hasTable('academic_income_setting_sets')) {
            return;
        }

        $years = collect();
        foreach (['credit_unit_price_settings', 'income_rate_settings', 'nuol_pct_settings'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'start_year')) {
                continue;
            }

            $years = $years->merge(DB::table($tableName)->whereNotNull('start_year')->pluck('start_year'));
        }

        $years->unique()->sort()->values()->each(function ($year): void {
            DB::table('academic_income_setting_sets')->updateOrInsert(
                ['fiscal_year' => (int) $year],
                [
                    'name' => 'ການຕັ້ງຄ່າລາຍຮັບວິຊາການ ' . (int) $year,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });

        $sets = DB::table('academic_income_setting_sets')->pluck('id', 'fiscal_year');
        foreach (['credit_unit_price_settings', 'income_rate_settings', 'nuol_pct_settings'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'setting_set_id') || ! Schema::hasColumn($tableName, 'start_year')) {
                continue;
            }

            DB::table($tableName)
                ->whereNull('setting_set_id')
                ->orderBy('id')
                ->select('id', 'start_year')
                ->chunkById(100, function ($rows) use ($tableName, $sets): void {
                    foreach ($rows as $row) {
                        $setId = $sets[(int) $row->start_year] ?? null;
                        if ($setId) {
                            DB::table($tableName)->where('id', $row->id)->update(['setting_set_id' => $setId]);
                        }
                    }
                });
        }

        if (Schema::hasTable('academic_income_items') && Schema::hasColumn('academic_income_items', 'setting_set_id')) {
            DB::table('academic_income_items')
                ->join('academic_income_plans', 'academic_income_items.plan_id', '=', 'academic_income_plans.id')
                ->whereNull('academic_income_items.setting_set_id')
                ->select('academic_income_items.id', 'academic_income_plans.fiscal_year')
                ->orderBy('academic_income_items.id')
                ->chunkById(100, function ($rows) use ($sets): void {
                    foreach ($rows as $row) {
                        $setId = $sets[(int) $row->fiscal_year] ?? $sets->filter(fn ($id, $year) => (int) $year <= (int) $row->fiscal_year)->last();
                        if ($setId) {
                            DB::table('academic_income_items')->where('id', $row->id)->update(['setting_set_id' => $setId]);
                        }
                    }
                }, 'academic_income_items.id', 'id');
        }
    }
};
