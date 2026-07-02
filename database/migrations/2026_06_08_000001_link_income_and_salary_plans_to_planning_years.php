<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('planning_years')) {
            Schema::create('planning_years', function (Blueprint $table): void {
                $table->id();
                $table->unsignedSmallInteger('year')->unique();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('academic_income_plans') && ! Schema::hasColumn('academic_income_plans', 'planning_year_id')) {
            Schema::table('academic_income_plans', function (Blueprint $table): void {
                $table->foreignId('planning_year_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('planning_years')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('salary_plans') && ! Schema::hasColumn('salary_plans', 'planning_year_id')) {
            Schema::table('salary_plans', function (Blueprint $table): void {
                $table->foreignId('planning_year_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('planning_years')
                    ->nullOnDelete();
            });
        }

        $this->backfillPlanningYears();
    }

    public function down(): void
    {
        if (Schema::hasTable('salary_plans') && Schema::hasColumn('salary_plans', 'planning_year_id')) {
            Schema::table('salary_plans', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('planning_year_id');
            });
        }

        if (Schema::hasTable('academic_income_plans') && Schema::hasColumn('academic_income_plans', 'planning_year_id')) {
            Schema::table('academic_income_plans', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('planning_year_id');
            });
        }
    }

    private function backfillPlanningYears(): void
    {
        $years = collect();

        if (Schema::hasTable('academic_income_plans')) {
            $years = $years->merge(
                DB::table('academic_income_plans')
                    ->whereNotNull('fiscal_year')
                    ->pluck('fiscal_year')
            );
        }

        if (Schema::hasTable('salary_plans')) {
            $years = $years->merge(
                DB::table('salary_plans')
                    ->whereNotNull('fiscal_year')
                    ->pluck('fiscal_year')
            );
        }

        $years->map(fn ($year) => (int) $year)
            ->filter(fn ($year) => $year >= 2000 && $year <= 2100)
            ->unique()
            ->each(function (int $year): void {
                $planningYearId = DB::table('planning_years')->where('year', $year)->value('id');

                if (! $planningYearId) {
                    $planningYearId = DB::table('planning_years')->insertGetId([
                        'year' => $year,
                        'name' => 'Planning '.$year,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (Schema::hasTable('academic_income_plans') && Schema::hasColumn('academic_income_plans', 'planning_year_id')) {
                    DB::table('academic_income_plans')
                        ->whereNull('planning_year_id')
                        ->where('fiscal_year', $year)
                        ->update([
                            'planning_year_id' => $planningYearId,
                            'updated_at' => now(),
                        ]);
                }

                if (Schema::hasTable('salary_plans') && Schema::hasColumn('salary_plans', 'planning_year_id')) {
                    DB::table('salary_plans')
                        ->whereNull('planning_year_id')
                        ->where('fiscal_year', $year)
                        ->update([
                            'planning_year_id' => $planningYearId,
                            'updated_at' => now(),
                        ]);
                }
            });
    }
};
