<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $legacyNumberKeys = [
        'amount_per_month',
        'event_count',
        'frequency_count',
        'month_count',
        'people_count',
        'quantity',
        'times_per_year',
        'unit_price',
        'yearly_total',
    ];

    public function up(): void
    {
        $this->ensureExpenseStructureTables();
        $this->ensureExpensePatternJsonColumns();
        $this->backfillPatternSchemas();
        $this->createExpenseCatalogItems();
        $this->ensureExpensePlanColumns();
        $this->backfillCatalogItems();
        $this->backfillExpensePlans();

        Schema::dropIfExists('expense_plan_values');
        Schema::dropIfExists('expense_pattern_fields');
        Schema::dropIfExists('expense_subsection_default_rows');
    }

    public function down(): void
    {
        if (! Schema::hasTable('expense_subsection_default_rows')) {
            Schema::create('expense_subsection_default_rows', function (Blueprint $table): void {
                $table->id();
                $table->string('subsection_code');
                $table->string('item_name');
                $table->unsignedInteger('chart_of_account_id')->nullable();
                $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
                $table->unsignedSmallInteger('sort_order')->default(1);
                $table->json('default_values')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('expense_pattern_fields')) {
            Schema::create('expense_pattern_fields', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('pattern_id')->constrained('expense_patterns')->cascadeOnDelete();
                $table->string('field_key', 50);
                $table->string('default_label');
                $table->string('data_type', 20)->default('text');
                $table->unsignedSmallInteger('display_order')->default(0);
                $table->boolean('is_required')->default(false);
                $table->boolean('is_calculated')->default(false);
                $table->string('default_value')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('expense_plan_values')) {
            Schema::create('expense_plan_values', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('expense_plan_id')->constrained('expense_plans')->cascadeOnDelete();
                $table->string('field_key', 50);
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        Schema::dropIfExists('expense_catalog_items');

        if (Schema::hasTable('expense_patterns')) {
            Schema::table('expense_patterns', function (Blueprint $table): void {
                foreach (['fields_schema', 'formula_schema'] as $column) {
                    if (Schema::hasColumn('expense_patterns', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('expense_plans')) {
            Schema::table('expense_plans', function (Blueprint $table): void {
                foreach ([
                    'catalog_item_id',
                    'chart_of_account_id',
                    'item_name',
                    'calculation_values',
                    'pattern_snapshot',
                ] as $column) {
                    if (Schema::hasColumn('expense_plans', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function ensureExpenseStructureTables(): void
    {
        if (! Schema::hasTable('expense_patterns')) {
            Schema::create('expense_patterns', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 50)->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('fields_schema')->nullable();
                $table->json('formula_schema')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('expense_sections')) {
            Schema::create('expense_sections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('planning_year_id')->nullable()->constrained('planning_years')->nullOnDelete();
                $table->string('code', 30);
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedSmallInteger('display_order')->default(0);
                $table->decimal('summary_period_count', 8, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['planning_year_id', 'code']);
            });
        }

        if (! Schema::hasTable('expense_subsections')) {
            Schema::create('expense_subsections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('section_id')->constrained('expense_sections')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('expense_subsections')->nullOnDelete();
                $table->string('code', 30);
                $table->string('name');
                $table->text('description')->nullable();
                $table->foreignId('default_pattern_id')->nullable()->constrained('expense_patterns')->nullOnDelete();
                $table->decimal('summary_period_count', 8, 2)->nullable();
                $table->unsignedSmallInteger('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['section_id', 'code']);
            });
        }
    }

    private function ensureExpensePatternJsonColumns(): void
    {
        if (! Schema::hasTable('expense_patterns')) {
            return;
        }

        Schema::table('expense_patterns', function (Blueprint $table): void {
            if (! Schema::hasColumn('expense_patterns', 'fields_schema')) {
                $table->json('fields_schema')->nullable()->after('description');
            }

            if (! Schema::hasColumn('expense_patterns', 'formula_schema')) {
                $table->json('formula_schema')->nullable()->after('fields_schema');
            }
        });
    }

    private function ensureExpensePlanColumns(): void
    {
        if (! Schema::hasTable('expense_plans')) {
            return;
        }

        Schema::table('expense_plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('expense_plans', 'catalog_item_id')) {
                $table->foreignId('catalog_item_id')->nullable()->after('subsection_id')->constrained('expense_catalog_items')->nullOnDelete();
            }

            if (! Schema::hasColumn('expense_plans', 'chart_of_account_id')) {
                $table->unsignedInteger('chart_of_account_id')->nullable()->after('catalog_item_id');
                $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            }

            if (! Schema::hasColumn('expense_plans', 'item_name')) {
                $table->string('item_name')->nullable()->after('plan_type');
            }

            if (! Schema::hasColumn('expense_plans', 'calculation_values')) {
                $table->json('calculation_values')->nullable()->after('detail');
            }

            if (! Schema::hasColumn('expense_plans', 'pattern_snapshot')) {
                $table->json('pattern_snapshot')->nullable()->after('calculation_values');
            }
        });
    }

    private function createExpenseCatalogItems(): void
    {
        if (Schema::hasTable('expense_catalog_items')) {
            return;
        }

        if (! Schema::hasTable('expense_subsections')) {
            return;
        }

        Schema::create('expense_catalog_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subsection_id')->constrained('expense_subsections')->cascadeOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreignId('pattern_id')->nullable()->constrained('expense_patterns')->nullOnDelete();
            $table->json('default_values')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['subsection_id', 'sort_order']);
        });
    }

    private function backfillPatternSchemas(): void
    {
        if (! Schema::hasTable('expense_patterns')) {
            return;
        }

        $fieldsByPattern = Schema::hasTable('expense_pattern_fields')
            ? DB::table('expense_pattern_fields')->orderBy('display_order')->get()->groupBy('pattern_id')
            : collect();

        DB::table('expense_patterns')
            ->orderBy('id')
            ->get()
            ->each(function (object $pattern) use ($fieldsByPattern): void {
                $fields = $fieldsByPattern->get($pattern->id, collect());
                $schema = $fields->isNotEmpty()
                    ? $fields->map(fn (object $field): array => [
                        'field_key' => (string) $field->field_key,
                        'default_label' => (string) $field->default_label,
                        'data_type' => (string) $field->data_type,
                        'display_order' => (int) $field->display_order,
                        'is_required' => (bool) $field->is_required,
                        'is_calculated' => (bool) $field->is_calculated,
                        'default_value' => $field->default_value,
                    ])->values()->all()
                    : $this->defaultFieldsFor((string) $pattern->key);

                DB::table('expense_patterns')
                    ->where('id', $pattern->id)
                    ->update([
                        'fields_schema' => json_encode($schema, JSON_UNESCAPED_UNICODE),
                        'formula_schema' => json_encode([
                            'operation' => 'multiply',
                            'fields' => $this->formulaFieldsFor((string) $pattern->key, $schema),
                        ], JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);
            });
    }

    private function backfillCatalogItems(): void
    {
        if (! Schema::hasTable('expense_catalog_items') || ! Schema::hasTable('expense_subsections')) {
            return;
        }

        $subsectionsByCode = DB::table('expense_subsections')->get()->keyBy('code');
        $now = now();

        if (Schema::hasTable('expense_subsection_default_rows')) {
            DB::table('expense_subsection_default_rows')
                ->orderBy('id')
                ->get()
                ->each(function (object $row) use ($subsectionsByCode, $now): void {
                    $subsection = $subsectionsByCode->get($row->subsection_code);
                    if (! $subsection) {
                        return;
                    }

                    DB::table('expense_catalog_items')->insert([
                        'subsection_id' => $subsection->id,
                        'item_name' => $row->item_name,
                        'chart_of_account_id' => $row->chart_of_account_id,
                        'pattern_id' => $subsection->default_pattern_id,
                        'default_values' => $row->default_values,
                        'sort_order' => $row->sort_order,
                        'is_active' => $row->is_active,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });
        }
    }

    private function backfillExpensePlans(): void
    {
        if (! Schema::hasTable('expense_plans') || ! Schema::hasTable('expense_patterns')) {
            return;
        }

        $values = $this->planValuesByPlanId();
        $catalogItems = Schema::hasTable('expense_catalog_items')
            ? DB::table('expense_catalog_items')->get()->groupBy('subsection_id')
            : collect();
        $accountsByCode = Schema::hasTable('chart_of_accounts')
            ? DB::table('chart_of_accounts')->get()->keyBy('account_code')
            : collect();
        $patterns = DB::table('expense_patterns')->get()->keyBy('id');

        DB::table('expense_plans')
            ->orderBy('id')
            ->get()
            ->each(function (object $plan) use ($values, $catalogItems, $accountsByCode, $patterns): void {
                $planValues = $values->get($plan->id, collect());
                $valuesByKey = $planValues->mapWithKeys(fn (object $value): array => [$value->field_key => $this->typedValue($value)]);
                $itemName = trim((string) ($valuesByKey->get('item_name') ?: $plan->plan_detail ?: $plan->item_name ?? ''));
                $catalogItem = $catalogItems
                    ->get($plan->subsection_id, collect())
                    ->first(fn (object $item): bool => $this->normalize($item->item_name) === $this->normalize($itemName));
                $reference = trim((string) $valuesByKey->get('reference', ''));
                $referenceAccount = $reference !== '' ? $accountsByCode->get($reference) : null;
                $chartAccountId = $catalogItem?->chart_of_account_id ?: $referenceAccount?->id;
                $pattern = $patterns->get($plan->pattern_id);
                $calculationValues = $valuesByKey
                    ->reject(fn ($value, string $key): bool => in_array($key, ['item_name', 'reference', 'note'], true))
                    ->all();

                DB::table('expense_plans')
                    ->where('id', $plan->id)
                    ->update([
                        'catalog_item_id' => $catalogItem?->id,
                        'chart_of_account_id' => $chartAccountId,
                        'item_name' => $itemName ?: $plan->plan_detail,
                        'calculation_values' => json_encode($calculationValues, JSON_UNESCAPED_UNICODE),
                        'pattern_snapshot' => $pattern ? json_encode([
                            'key' => $pattern->key,
                            'name' => $pattern->name,
                            'fields_schema' => json_decode($pattern->fields_schema ?? '[]', true) ?: [],
                            'formula_schema' => json_decode($pattern->formula_schema ?? '[]', true) ?: [],
                        ], JSON_UNESCAPED_UNICODE) : null,
                        'updated_at' => now(),
                    ]);
            });
    }

    private function planValuesByPlanId(): Collection
    {
        if (! Schema::hasTable('expense_plan_values')) {
            return collect();
        }

        return DB::table('expense_plan_values')->get()->groupBy('expense_plan_id');
    }

    private function typedValue(object $value): mixed
    {
        if (property_exists($value, 'value')) {
            if (in_array($value->field_key, $this->legacyNumberKeys, true)) {
                return is_numeric($value->value) ? (float) $value->value : 0.0;
            }

            return $value->value;
        }

        return $value->value_text
            ?? (property_exists($value, 'value_number') ? $value->value_number : null)
            ?? (property_exists($value, 'value_date') ? $value->value_date : null)
            ?? (property_exists($value, 'value_boolean') && $value->value_boolean !== null ? (bool) $value->value_boolean : null);
    }

    private function defaultFieldsFor(string $patternKey): array
    {
        $labels = [
            'item_name' => ['ລາຍການ', 'text'],
            'reference' => ['ບັນຊີ', 'text'],
            'amount_per_month' => ['ຕໍ່ເດືອນ', 'number'],
            'month_count' => ['ຈ/ນເດືອນ', 'number'],
            'unit_price' => ['ລາຄາຕໍ່ໜ່ວຍ', 'number'],
            'quantity' => ['ຈຳນວນ', 'number'],
            'times_per_year' => ['ຈຳນວນຄັ້ງ', 'number'],
            'frequency_count' => ['ຈຳນວນເດືອນ/ຄັ້ງ', 'number'],
            'event_count' => ['ຈຳນວນຄັ້ງ', 'number'],
            'people_count' => ['ຈຳນວນຄົນ', 'number'],
            'yearly_total' => ['ຍອດລວມປີ', 'number'],
        ];

        $keys = match ($patternKey) {
            'monthly' => ['item_name', 'reference', 'amount_per_month', 'month_count', 'yearly_total'],
            'unit_quantity' => ['item_name', 'reference', 'unit_price', 'quantity', 'yearly_total'],
            'unit_quantity_frequency' => ['item_name', 'reference', 'unit_price', 'quantity', 'times_per_year', 'yearly_total'],
            'frequency_based' => ['item_name', 'reference', 'unit_price', 'quantity', 'frequency_count', 'yearly_total'],
            'event_based' => ['item_name', 'reference', 'unit_price', 'event_count', 'people_count', 'yearly_total'],
            default => ['item_name', 'reference', 'yearly_total'],
        };

        return collect($keys)->map(fn (string $key, int $index): array => [
            'field_key' => $key,
            'default_label' => $labels[$key][0],
            'data_type' => $labels[$key][1],
            'display_order' => $index + 1,
            'is_required' => ! in_array($key, ['reference'], true),
            'is_calculated' => $key === 'yearly_total',
            'default_value' => null,
        ])->all();
    }

    private function formulaFieldsFor(string $patternKey, array $schema): array
    {
        return match ($patternKey) {
            'monthly' => ['amount_per_month', 'month_count'],
            'unit_quantity' => ['unit_price', 'quantity'],
            'unit_quantity_frequency' => ['unit_price', 'quantity', 'times_per_year'],
            'frequency_based' => ['unit_price', 'quantity', 'frequency_count'],
            'event_based' => ['unit_price', 'event_count', 'people_count'],
            default => collect($schema)
                ->filter(fn (array $field): bool => ($field['data_type'] ?? null) === 'number'
                    && ! in_array($field['field_key'] ?? '', ['yearly_total'], true)
                    && ! (bool) ($field['is_calculated'] ?? false))
                ->pluck('field_key')
                ->values()
                ->all(),
        };
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }
};
