<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_plans')) {
            return;
        }

        $this->ensureExpensePlanHeaderColumns();
        $this->createExpensePlanRows();
        $this->backupLegacyExpensePlanRows();
        $this->copyRowsFromExpensePlans();
        $this->collapseExpensePlansToYearHeaders();
        $this->ensureExpensePlanUniqueYear();
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_plan_rows');
    }

    private function ensureExpensePlanHeaderColumns(): void
    {
        Schema::table('expense_plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('expense_plans', 'planning_year_id')) {
                $table->foreignId('planning_year_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('planning_years')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('expense_plans', 'fiscal_year')) {
                $table->unsignedSmallInteger('fiscal_year')->nullable()->after('planning_year_id');
            }

            if (! Schema::hasColumn('expense_plans', 'status')) {
                $table->string('status', 20)->default('DRAFT')->after('fiscal_year');
            }

            if (! Schema::hasColumn('expense_plans', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }

            if (! Schema::hasColumn('expense_plans', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }

    private function createExpensePlanRows(): void
    {
        if (Schema::hasTable('expense_plan_rows')) {
            Schema::table('expense_plan_rows', function (Blueprint $table): void {
                if (! Schema::hasColumn('expense_plan_rows', 'source_expense_plan_id')) {
                    $table->unsignedBigInteger('source_expense_plan_id')->nullable();
                    $table->unique('source_expense_plan_id');
                }
            });

            return;
        }

        Schema::create('expense_plan_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('expense_plan_id')->constrained('expense_plans')->cascadeOnDelete();
            $table->foreignId('planning_year_id')->constrained('planning_years')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('expense_sections')->cascadeOnDelete();
            $table->foreignId('subsection_id')->nullable()->constrained('expense_subsections')->nullOnDelete();
            $table->foreignId('catalog_item_id')->nullable()->constrained('expense_catalog_items')->nullOnDelete();
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreignId('pattern_id')->nullable()->constrained('expense_patterns')->nullOnDelete();
            $table->string('version', 50)->nullable();
            $table->string('plan_type', 100)->nullable();
            $table->string('item_name')->nullable();
            $table->string('plan_detail');
            $table->text('detail')->nullable();
            $table->json('calculation_values')->nullable();
            $table->json('pattern_snapshot')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedBigInteger('source_expense_plan_id')->nullable();
            $table->timestamps();

            $table->index(['planning_year_id', 'section_id', 'subsection_id'], 'expense_rows_year_section_idx');
            $table->index(['catalog_item_id'], 'expense_rows_catalog_item_idx');
            $table->unique('source_expense_plan_id', 'expense_rows_source_plan_unique');
        });
    }

    private function backupLegacyExpensePlanRows(): void
    {
        if (! Schema::hasColumn('expense_plans', 'section_id')) {
            return;
        }

        if (! Schema::hasTable('expense_plan_legacy_rows')) {
            Schema::create('expense_plan_legacy_rows', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('source_expense_plan_id')->unique();
                $table->unsignedBigInteger('planning_year_id')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                $table->unsignedBigInteger('subsection_id')->nullable();
                $table->unsignedBigInteger('catalog_item_id')->nullable();
                $table->unsignedInteger('chart_of_account_id')->nullable();
                $table->unsignedBigInteger('pattern_id')->nullable();
                $table->string('version', 50)->nullable();
                $table->string('plan_type', 100)->nullable();
                $table->string('item_name')->nullable();
                $table->string('plan_detail')->nullable();
                $table->text('detail')->nullable();
                $table->json('calculation_values')->nullable();
                $table->json('pattern_snapshot')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamp('original_created_at')->nullable();
                $table->timestamp('original_updated_at')->nullable();
                $table->timestamps();
            });
        }

        DB::table('expense_plans')
            ->whereNotNull('section_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $legacyRow): void {
                $alreadyBackedUp = DB::table('expense_plan_legacy_rows')
                    ->where('source_expense_plan_id', $legacyRow->id)
                    ->exists();

                if ($alreadyBackedUp) {
                    return;
                }

                DB::table('expense_plan_legacy_rows')->insert([
                    'source_expense_plan_id' => $legacyRow->id,
                    'planning_year_id' => $legacyRow->planning_year_id ?? null,
                    'section_id' => $legacyRow->section_id ?? null,
                    'subsection_id' => $legacyRow->subsection_id ?? null,
                    'catalog_item_id' => $legacyRow->catalog_item_id ?? null,
                    'chart_of_account_id' => $legacyRow->chart_of_account_id ?? null,
                    'pattern_id' => $legacyRow->pattern_id ?? null,
                    'version' => $legacyRow->version ?? null,
                    'plan_type' => $legacyRow->plan_type ?? null,
                    'item_name' => $legacyRow->item_name ?? null,
                    'plan_detail' => $legacyRow->plan_detail ?? null,
                    'detail' => $legacyRow->detail ?? null,
                    'calculation_values' => $legacyRow->calculation_values ?? null,
                    'pattern_snapshot' => $legacyRow->pattern_snapshot ?? null,
                    'created_by' => $legacyRow->created_by ?? null,
                    'updated_by' => $legacyRow->updated_by ?? null,
                    'original_created_at' => $legacyRow->created_at ?? null,
                    'original_updated_at' => $legacyRow->updated_at ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    private function copyRowsFromExpensePlans(): void
    {
        if (! Schema::hasColumn('expense_plans', 'section_id')) {
            return;
        }

        $existingSourceIds = DB::table('expense_plan_rows')
            ->whereNotNull('source_expense_plan_id')
            ->pluck('source_expense_plan_id')
            ->flip();

        DB::table('expense_plans')
            ->whereNotNull('planning_year_id')
            ->whereNotNull('section_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $legacyRow) use ($existingSourceIds): void {
                if ($existingSourceIds->has($legacyRow->id)) {
                    return;
                }

                $headerId = $this->expensePlanHeaderId($legacyRow);

                DB::table('expense_plan_rows')->insert([
                    'expense_plan_id' => $headerId,
                    'planning_year_id' => $legacyRow->planning_year_id,
                    'section_id' => $legacyRow->section_id,
                    'subsection_id' => $legacyRow->subsection_id ?? null,
                    'catalog_item_id' => $legacyRow->catalog_item_id ?? null,
                    'chart_of_account_id' => $legacyRow->chart_of_account_id ?? null,
                    'pattern_id' => $legacyRow->pattern_id ?? null,
                    'version' => $legacyRow->version ?? null,
                    'plan_type' => $legacyRow->plan_type ?? null,
                    'item_name' => $legacyRow->item_name ?? null,
                    'plan_detail' => $legacyRow->plan_detail ?? ($legacyRow->item_name ?? 'Expense row'),
                    'detail' => $legacyRow->detail ?? null,
                    'calculation_values' => $legacyRow->calculation_values ?? null,
                    'pattern_snapshot' => $legacyRow->pattern_snapshot ?? null,
                    'created_by' => $legacyRow->created_by ?? null,
                    'updated_by' => $legacyRow->updated_by ?? null,
                    'created_at' => $legacyRow->created_at ?? now(),
                    'updated_at' => $legacyRow->updated_at ?? now(),
                    'source_expense_plan_id' => $legacyRow->id,
                ]);
            });
    }

    private function collapseExpensePlansToYearHeaders(): void
    {
        if (! Schema::hasColumn('expense_plans', 'section_id')) {
            return;
        }

        $headerIds = DB::table('expense_plan_rows')
            ->pluck('expense_plan_id')
            ->unique()
            ->values();

        if ($headerIds->isEmpty()) {
            return;
        }

        DB::table('expense_plans')
            ->whereNotIn('id', $headerIds)
            ->delete();

        DB::table('expense_plans')
            ->whereIn('id', $headerIds)
            ->orderBy('id')
            ->get()
            ->each(function (object $plan): void {
                $year = $this->yearForPlanningYear((int) $plan->planning_year_id) ?? (int) ($plan->version ?? 0) ?: null;

                DB::table('expense_plans')
                    ->where('id', $plan->id)
                    ->update([
                        'fiscal_year' => $plan->fiscal_year ?? $year,
                        'status' => $plan->status ?? 'DRAFT',
                        'notes' => $plan->notes ?? null,
                        'updated_at' => now(),
                    ]);
            });
    }

    private function ensureExpensePlanUniqueYear(): void
    {
        if (! Schema::hasColumn('expense_plans', 'planning_year_id')) {
            return;
        }

        $this->dropIndexIfExists('expense_plans', 'expense_plans_fiscal_year_unique');
        $this->dropIndexIfExists('expense_plans', 'expense_plans_planning_year_id_unique');

        Schema::table('expense_plans', function (Blueprint $table): void {
            $table->unique('planning_year_id');
        });
    }

    private function expensePlanHeaderId(object $legacyRow): int
    {
        $existingHeaderId = DB::table('expense_plans')
            ->where('planning_year_id', $legacyRow->planning_year_id)
            ->orderBy('id')
            ->value('id');

        if ($existingHeaderId) {
            return (int) $existingHeaderId;
        }

        $year = $this->yearForPlanningYear((int) $legacyRow->planning_year_id) ?? (int) ($legacyRow->version ?? 0) ?: null;

        return (int) DB::table('expense_plans')->insertGetId([
            'planning_year_id' => $legacyRow->planning_year_id,
            'fiscal_year' => $year,
            'status' => 'DRAFT',
            'notes' => null,
            'created_by' => $legacyRow->created_by ?? null,
            'updated_by' => $legacyRow->updated_by ?? null,
            'created_at' => $legacyRow->created_at ?? now(),
            'updated_at' => $legacyRow->updated_at ?? now(),
        ]);
    }

    private function yearForPlanningYear(int $planningYearId): ?int
    {
        if (! Schema::hasTable('planning_years')) {
            return null;
        }

        $year = DB::table('planning_years')->where('id', $planningYearId)->value('year');

        return $year ? (int) $year : null;
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $driver = DB::connection()->getDriverName();
        $exists = $driver === 'sqlite'
            ? collect(DB::select("PRAGMA index_list('{$table}')"))->contains(fn (object $row): bool => ($row->name ?? null) === $index)
            : DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();

        if (! $exists) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index): void {
            $table->dropIndex($index);
        });
    }
};
