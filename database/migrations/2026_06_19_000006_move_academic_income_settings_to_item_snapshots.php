<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureSnapshotColumns();
        $this->backfillItemSnapshots();

        $this->dropSettingSetColumn('academic_income_items');
        $this->dropSettingSetColumn('credit_unit_price_settings');
        $this->dropSettingSetColumn('income_rate_settings');
        $this->dropSettingSetColumn('nuol_pct_settings');

        if (DB::getDriverName() === 'mysql') {
            Schema::dropIfExists('academic_income_setting_sets');
        }
    }

    public function down(): void
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
        $this->ensureSettingSetColumn('academic_income_items', after: 'plan_id');

        if (Schema::hasTable('academic_income_items')) {
            Schema::table('academic_income_items', function (Blueprint $table): void {
                foreach ([
                    'snap_credit_unit_price',
                    'snap_course_credit_unit',
                    'snap_registration_fee_rate',
                    'snap_nuol_pct',
                ] as $column) {
                    if (Schema::hasColumn('academic_income_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function ensureSnapshotColumns(): void
    {
        if (! Schema::hasTable('academic_income_items')) {
            return;
        }

        Schema::table('academic_income_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('academic_income_items', 'snap_credit_unit_price')) {
                $table->decimal('snap_credit_unit_price', 15, 2)->nullable()->after('student_count');
            }

            if (! Schema::hasColumn('academic_income_items', 'snap_course_credit_unit')) {
                $table->decimal('snap_course_credit_unit', 8, 2)->nullable()->after('snap_credit_unit_price');
            }

            if (! Schema::hasColumn('academic_income_items', 'snap_registration_fee_rate')) {
                $table->decimal('snap_registration_fee_rate', 15, 2)->nullable()->after('snap_course_credit_unit');
            }

            if (! Schema::hasColumn('academic_income_items', 'snap_nuol_pct')) {
                $table->decimal('snap_nuol_pct', 5, 4)->nullable()->after('snap_registration_fee_rate');
            }
        });
    }

    private function backfillItemSnapshots(): void
    {
        if (! Schema::hasTable('academic_income_items')) {
            return;
        }

        $feeYear2To4 = $this->registrationFeeSnapshot('year2_4');
        $feeYear1 = $this->registrationFeeSnapshot('year1');
        $incomeRates = Schema::hasTable('income_rate_settings')
            ? DB::table('income_rate_settings')->get()->keyBy('key')
            : collect();

        DB::table('academic_income_items')
            ->leftJoin('academic_income_plans', 'academic_income_items.plan_id', '=', 'academic_income_plans.id')
            ->leftJoin('degree_programs', 'academic_income_items.degree_program_id', '=', 'degree_programs.id')
            ->select(
                'academic_income_items.id',
                'academic_income_items.section_code',
                'academic_income_items.setting_set_id',
                'academic_income_plans.fiscal_year',
                'degree_programs.id as program_id',
                'degree_programs.level'
            )
            ->orderBy('academic_income_items.id')
            ->chunkById(100, function (Collection $items) use ($feeYear2To4, $feeYear1, $incomeRates): void {
                foreach ($items as $item) {
                    $payload = [
                        'snap_credit_unit_price' => null,
                        'snap_course_credit_unit' => null,
                        'snap_registration_fee_rate' => null,
                        'snap_nuol_pct' => 0,
                    ];

                    if (in_array($item->section_code, ['1.1', '1.3'], true) && $item->program_id) {
                        $credit = $this->latestCourseCredit((int) $item->program_id);
                        $payload['snap_course_credit_unit'] = $item->section_code === '1.3' && in_array($item->level, ['master', 'phd'], true)
                            ? ($credit?->year1_credit_unit ?? 0)
                            : ($credit?->course_credit_unit ?? 0);
                        $payload['snap_credit_unit_price'] = $this->creditUnitPriceFor((string) $item->level, $item->setting_set_id, (int) $item->fiscal_year);
                        $payload['snap_nuol_pct'] = $this->nuolFor((string) $item->level, $item->setting_set_id);
                    } elseif ($item->section_code === '1.2') {
                        $payload['snap_registration_fee_rate'] = $feeYear2To4['rate'];
                        $payload['snap_nuol_pct'] = $feeYear2To4['nuol'];
                    } elseif ($item->section_code === '1.4') {
                        $payload['snap_registration_fee_rate'] = $feeYear1['rate'];
                        $payload['snap_nuol_pct'] = $feeYear1['nuol'];
                    } elseif ($item->section_code === '2.1') {
                        $payload['snap_credit_unit_price'] = (float) ($incomeRates->get('item3_rate')?->rate ?? 0);
                    } elseif ($item->section_code === '2.2') {
                        $payload['snap_registration_fee_rate'] = (float) ($incomeRates->get('item4_rate')?->rate ?? 0);
                    } elseif ($item->section_code === '2.3') {
                        $payload['snap_registration_fee_rate'] = (float) ($incomeRates->get('item5_rate')?->rate ?? 0);
                    } elseif ($item->section_code === '2.4') {
                        $payload['snap_credit_unit_price'] = (float) ($incomeRates->get('item6_rate')?->rate ?? 0);
                    }

                    DB::table('academic_income_items')->where('id', $item->id)->update($payload);
                }
            }, 'academic_income_items.id', 'id');
    }

    private function latestCourseCredit(int $programId): ?object
    {
        if (! Schema::hasTable('course_credit_settings')) {
            return null;
        }

        return DB::table('course_credit_settings')
            ->where('degree_program_id', $programId)
            ->orderByDesc('start_year')
            ->first();
    }

    private function creditUnitPriceFor(string $level, ?int $settingSetId, int $fiscalYear): float
    {
        if (! Schema::hasTable('credit_unit_price_settings')) {
            return 0;
        }

        $query = DB::table('credit_unit_price_settings')->where('level', $level);
        if ($settingSetId && Schema::hasColumn('credit_unit_price_settings', 'setting_set_id')) {
            $scoped = DB::table('credit_unit_price_settings')
                ->where('setting_set_id', $settingSetId)
                ->where('level', $level);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        $row = $query
            ->when(Schema::hasColumn('credit_unit_price_settings', 'start_year'), fn ($q) => $q->where('start_year', '<=', $fiscalYear))
            ->orderByDesc('start_year')
            ->first()
            ?? DB::table('credit_unit_price_settings')->where('level', $level)->orderByDesc('start_year')->first();

        return (float) ($row?->credit_unit_price ?? 0);
    }

    private function nuolFor(string $level, ?int $settingSetId): float
    {
        if (! Schema::hasTable('nuol_pct_settings')) {
            return match ($level) {
                'bachelor' => 0.17,
                'master', 'phd' => 0.10,
                default => 0.0,
            };
        }

        $query = DB::table('nuol_pct_settings')->where('level', $level);
        if ($settingSetId && Schema::hasColumn('nuol_pct_settings', 'setting_set_id')) {
            $scoped = DB::table('nuol_pct_settings')
                ->where('setting_set_id', $settingSetId)
                ->where('level', $level);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        return (float) ($query->orderByDesc('start_year')->first()?->percentage ?? match ($level) {
            'bachelor' => 0.17,
            'master', 'phd' => 0.10,
            default => 0.0,
        });
    }

    private function registrationFeeSnapshot(string $sectionType): array
    {
        if (! Schema::hasTable('registration_fee_settings') || ! Schema::hasTable('registration_fee_items')) {
            return ['rate' => 0.0, 'nuol' => 0.0];
        }

        $setting = DB::table('registration_fee_settings')
            ->where('section_type', $sectionType)
            ->orderByDesc('start_year')
            ->first();

        if (! $setting) {
            return ['rate' => 0.0, 'nuol' => 0.0];
        }

        $items = DB::table('registration_fee_items')->where('fee_setting_id', $setting->id)->get();
        $rate = (float) $items->sum('amount');

        return [
            'rate' => $rate,
            'nuol' => $rate > 0 ? (float) ($items->sum(fn ($item) => (float) $item->amount * (float) $item->nuol_pct) / $rate) : 0.0,
        ];
    }

    private function dropSettingSetColumn(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'setting_set_id')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if ($this->foreignKeyExists($tableName, "{$tableName}_setting_set_id_foreign")) {
                $table->dropForeign("{$tableName}_setting_set_id_foreign");
            }

            $table->dropColumn('setting_set_id');
        });
    }

    private function ensureSettingSetColumn(string $tableName, string $after = 'id'): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'setting_set_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($after): void {
            $table->foreignId('setting_set_id')
                ->nullable()
                ->after($after)
                ->constrained('academic_income_setting_sets')
                ->nullOnDelete();
        });
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};
