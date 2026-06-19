<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NUMBER_FIELD_KEYS = [
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
        if (! Schema::hasTable('expense_plan_values')) {
            return;
        }

        if (! Schema::hasColumn('expense_plan_values', 'value')) {
            Schema::table('expense_plan_values', function (Blueprint $table): void {
                $table->text('value')->nullable()->after('field_key');
            });
        }

        $hasLegacyColumns = collect(['value_text', 'value_number', 'value_date', 'value_boolean'])
            ->every(fn (string $column): bool => Schema::hasColumn('expense_plan_values', $column));

        if ($hasLegacyColumns) {
            DB::table('expense_plan_values')
                ->select(['id', 'value_text', 'value_number', 'value_date', 'value_boolean'])
                ->orderBy('id')
                ->chunkById(500, function ($values): void {
                    foreach ($values as $value) {
                        DB::table('expense_plan_values')
                            ->where('id', $value->id)
                            ->update([
                                'value' => $value->value_text
                                    ?? $value->value_number
                                    ?? $value->value_date
                                    ?? ($value->value_boolean === null ? null : (string) (int) $value->value_boolean),
                            ]);
                    }
                });

            Schema::table('expense_plan_values', function (Blueprint $table): void {
                $table->dropColumn(['value_text', 'value_number', 'value_date', 'value_boolean']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('expense_plan_values')) {
            return;
        }

        Schema::table('expense_plan_values', function (Blueprint $table): void {
            if (! Schema::hasColumn('expense_plan_values', 'value_text')) {
                $table->text('value_text')->nullable()->after('field_key');
            }

            if (! Schema::hasColumn('expense_plan_values', 'value_number')) {
                $table->decimal('value_number', 18, 2)->nullable()->after('value_text');
            }

            if (! Schema::hasColumn('expense_plan_values', 'value_date')) {
                $table->date('value_date')->nullable()->after('value_number');
            }

            if (! Schema::hasColumn('expense_plan_values', 'value_boolean')) {
                $table->boolean('value_boolean')->nullable()->after('value_date');
            }
        });

        if (Schema::hasColumn('expense_plan_values', 'value')) {
            DB::table('expense_plan_values')
                ->select(['id', 'field_key', 'value'])
                ->orderBy('id')
                ->chunkById(500, function ($values): void {
                    foreach ($values as $value) {
                        $payload = [
                            'value_text' => null,
                            'value_number' => null,
                            'value_date' => null,
                            'value_boolean' => null,
                        ];

                        if (in_array($value->field_key, self::NUMBER_FIELD_KEYS, true)) {
                            $payload['value_number'] = is_numeric($value->value) ? $value->value : 0;
                        } else {
                            $payload['value_text'] = $value->value;
                        }

                        DB::table('expense_plan_values')->where('id', $value->id)->update($payload);
                    }
                });

            Schema::table('expense_plan_values', function (Blueprint $table): void {
                $table->dropColumn('value');
            });
        }
    }
};
