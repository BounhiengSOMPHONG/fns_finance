<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_income_items')) {
            return;
        }

        Schema::table('academic_income_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('academic_income_items', 'credit_unit_price_setting_id')) {
                $table->foreignId('credit_unit_price_setting_id')
                    ->nullable()
                    ->after('degree_program_id')
                    ->constrained('credit_unit_price_settings')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('academic_income_items', 'income_rate_setting_id')) {
                $table->foreignId('income_rate_setting_id')
                    ->nullable()
                    ->after('credit_unit_price_setting_id')
                    ->constrained('income_rate_settings')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('academic_income_items', 'registration_fee_setting_id')) {
                $table->foreignId('registration_fee_setting_id')
                    ->nullable()
                    ->after('income_rate_setting_id')
                    ->constrained('registration_fee_settings')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('academic_income_items', 'nuol_pct_setting_id')) {
                $table->foreignId('nuol_pct_setting_id')
                    ->nullable()
                    ->after('registration_fee_setting_id')
                    ->constrained('nuol_pct_settings')
                    ->nullOnDelete();
            }
        });

        $this->backfillSettingReferences();
    }

    public function down(): void
    {
        if (! Schema::hasTable('academic_income_items')) {
            return;
        }

        Schema::table('academic_income_items', function (Blueprint $table): void {
            foreach ([
                'credit_unit_price_setting_id',
                'income_rate_setting_id',
                'registration_fee_setting_id',
                'nuol_pct_setting_id',
            ] as $column) {
                if (Schema::hasColumn('academic_income_items', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }

    private function backfillSettingReferences(): void
    {
        DB::table('academic_income_items')
            ->leftJoin('academic_income_plans', 'academic_income_items.plan_id', '=', 'academic_income_plans.id')
            ->leftJoin('degree_programs', 'academic_income_items.degree_program_id', '=', 'degree_programs.id')
            ->select(
                'academic_income_items.id',
                'academic_income_items.section_code',
                'academic_income_plans.fiscal_year',
                'degree_programs.level'
            )
            ->orderBy('academic_income_items.id')
            ->chunkById(100, function ($items): void {
                foreach ($items as $item) {
                    DB::table('academic_income_items')
                        ->where('id', $item->id)
                        ->update($this->referencesFor($item));
                }
            }, 'academic_income_items.id', 'id');
    }

    private function referencesFor(object $item): array
    {
        $payload = [
            'credit_unit_price_setting_id' => null,
            'income_rate_setting_id' => null,
            'registration_fee_setting_id' => null,
            'nuol_pct_setting_id' => null,
        ];

        if (in_array($item->section_code, ['1.1', '1.3'], true) && $item->level) {
            $payload['credit_unit_price_setting_id'] = $this->latestSettingId(
                'credit_unit_price_settings',
                ['level' => $item->level],
                $item->fiscal_year !== null ? (int) $item->fiscal_year : null
            );
            $payload['nuol_pct_setting_id'] = $this->latestSettingId(
                'nuol_pct_settings',
                ['level' => $item->level],
                $item->fiscal_year !== null ? (int) $item->fiscal_year : null
            );
        }

        if ($item->section_code === '1.2') {
            $payload['registration_fee_setting_id'] = $this->latestSettingId(
                'registration_fee_settings',
                ['section_type' => 'year2_4'],
                $item->fiscal_year !== null ? (int) $item->fiscal_year : null
            );
        }

        if ($item->section_code === '1.4') {
            $payload['registration_fee_setting_id'] = $this->latestSettingId(
                'registration_fee_settings',
                ['section_type' => 'year1'],
                $item->fiscal_year !== null ? (int) $item->fiscal_year : null
            );
        }

        $incomeRateKey = match ($item->section_code) {
            '2.1' => 'item3_rate',
            '2.2' => 'item4_rate',
            '2.3' => 'item5_rate',
            '2.4' => 'item6_rate',
            default => null,
        };

        if ($incomeRateKey) {
            $payload['income_rate_setting_id'] = DB::table('income_rate_settings')
                ->where('key', $incomeRateKey)
                ->value('id');
        }

        return $payload;
    }

    private function latestSettingId(string $tableName, array $where, ?int $fiscalYear): ?int
    {
        if (! Schema::hasTable($tableName)) {
            return null;
        }

        $query = DB::table($tableName);
        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        $row = (clone $query)
            ->when($fiscalYear !== null && Schema::hasColumn($tableName, 'start_year'), fn ($q) => $q->where('start_year', '<=', $fiscalYear))
            ->when(Schema::hasColumn($tableName, 'start_year'), fn ($q) => $q->orderByDesc('start_year'))
            ->orderByDesc('id')
            ->first();

        return $row?->id
            ?? (clone $query)->orderByDesc('id')->value('id');
    }
};
