<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropAcademicIncomePlanLegacyPercentages();
        $this->dropPlanningYearLegacyReviewColumns();
        $this->deleteEmptyExpensePlanNotes();
    }

    public function down(): void
    {
        if (Schema::hasTable('planning_years')) {
            Schema::table('planning_years', function (Blueprint $table): void {
                if (! Schema::hasColumn('planning_years', 'review_status')) {
                    $table->string('review_status', 30)->default('draft')->after('period_3_4_saved_at');
                }

                if (! Schema::hasColumn('planning_years', 'submitted_by')) {
                    $table->unsignedInteger('submitted_by')->nullable()->after('review_status');
                }
            });
        }

        if (Schema::hasTable('academic_income_plans')) {
            Schema::table('academic_income_plans', function (Blueprint $table): void {
                if (! Schema::hasColumn('academic_income_plans', 'nuol_pct_1_1')) {
                    $table->decimal('nuol_pct_1_1', 5, 4)->default(0.1700)->after('fiscal_year');
                }

                if (! Schema::hasColumn('academic_income_plans', 'nuol_pct_1_2')) {
                    $table->decimal('nuol_pct_1_2', 5, 4)->default(0.1700)->after('nuol_pct_1_1');
                }

                if (! Schema::hasColumn('academic_income_plans', 'nuol_pct_1_3')) {
                    $table->decimal('nuol_pct_1_3', 5, 4)->default(0.1700)->after('nuol_pct_1_2');
                }

                if (! Schema::hasColumn('academic_income_plans', 'nuol_pct_1_4')) {
                    $table->decimal('nuol_pct_1_4', 5, 4)->default(0.1700)->after('nuol_pct_1_3');
                }
            });
        }
    }

    private function dropAcademicIncomePlanLegacyPercentages(): void
    {
        if (! Schema::hasTable('academic_income_plans')) {
            return;
        }

        $columns = collect(['nuol_pct_1_1', 'nuol_pct_1_2', 'nuol_pct_1_3', 'nuol_pct_1_4'])
            ->filter(fn (string $column): bool => Schema::hasColumn('academic_income_plans', $column))
            ->values()
            ->all();

        if ($columns === []) {
            return;
        }

        Schema::table('academic_income_plans', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    private function dropPlanningYearLegacyReviewColumns(): void
    {
        if (! Schema::hasTable('planning_years')) {
            return;
        }

        $columns = collect(['review_status', 'submitted_by'])
            ->filter(fn (string $column): bool => Schema::hasColumn('planning_years', $column))
            ->values()
            ->all();

        if ($columns === []) {
            return;
        }

        Schema::table('planning_years', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    private function deleteEmptyExpensePlanNotes(): void
    {
        if (! Schema::hasTable('expense_plan_values')) {
            return;
        }

        DB::table('expense_plan_values')
            ->where('field_key', 'note')
            ->whereNull('value_text')
            ->whereNull('value_number')
            ->whereNull('value_date')
            ->whereNull('value_boolean')
            ->delete();
    }
};
