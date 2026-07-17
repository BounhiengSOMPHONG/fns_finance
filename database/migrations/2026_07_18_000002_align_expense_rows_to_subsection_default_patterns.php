<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('expense_plan_rows')
            || ! Schema::hasTable('expense_catalog_items')
            || ! Schema::hasTable('expense_patterns')
            || ! Schema::hasTable('expense_subsections')
            || ! Schema::hasColumn('expense_subsections', 'default_pattern_id')
        ) {
            return;
        }

        $patterns = DB::table('expense_patterns')->get()->keyBy('id');

        DB::table('expense_catalog_items')
            ->join('expense_subsections', 'expense_subsections.id', '=', 'expense_catalog_items.subsection_id')
            ->whereNotNull('expense_subsections.default_pattern_id')
            ->whereColumn('expense_catalog_items.pattern_id', '!=', 'expense_subsections.default_pattern_id')
            ->select([
                'expense_catalog_items.id',
                'expense_subsections.default_pattern_id',
            ])
            ->orderBy('expense_catalog_items.id')
            ->chunkById(200, function ($items): void {
                foreach ($items as $item) {
                    DB::table('expense_catalog_items')
                        ->where('id', $item->id)
                        ->update([
                            'pattern_id' => $item->default_pattern_id,
                            'updated_at' => now(),
                        ]);
                }
            }, 'expense_catalog_items.id', 'id');

        DB::table('expense_plan_rows')
            ->join('expense_subsections', 'expense_subsections.id', '=', 'expense_plan_rows.subsection_id')
            ->whereNotNull('expense_subsections.default_pattern_id')
            ->whereColumn('expense_plan_rows.pattern_id', '!=', 'expense_subsections.default_pattern_id')
            ->select([
                'expense_plan_rows.id',
                'expense_plan_rows.calculation_values',
                'expense_subsections.default_pattern_id',
            ])
            ->orderBy('expense_plan_rows.id')
            ->chunkById(200, function ($rows) use ($patterns): void {
                foreach ($rows as $row) {
                    $pattern = $patterns->get($row->default_pattern_id);
                    if (! $pattern) {
                        continue;
                    }

                    $values = json_decode((string) $row->calculation_values, true) ?: [];
                    $snapshot = $this->snapshotFor($pattern);
                    $values = array_merge($this->defaultInputValues($snapshot), $values);
                    $values['yearly_total'] = $this->calculateTotal($snapshot, $values);

                    DB::table('expense_plan_rows')
                        ->where('id', $row->id)
                        ->update([
                            'pattern_id' => $pattern->id,
                            'plan_type' => $pattern->key,
                            'calculation_values' => json_encode($values, JSON_UNESCAPED_UNICODE),
                            'pattern_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                            'updated_at' => now(),
                        ]);
                }
            }, 'expense_plan_rows.id', 'id');
    }

    public function down(): void
    {
        // This migration normalizes stale pattern references to the current DEF pattern.
        // Reverting would require historical pattern choices that are no longer reliable.
    }

    private function snapshotFor(object $pattern): array
    {
        return [
            'key' => $pattern->key,
            'name' => $pattern->name,
            'fields_schema' => json_decode((string) $pattern->fields_schema, true) ?: [],
            'formula_schema' => json_decode((string) $pattern->formula_schema, true) ?: [
                'operation' => 'multiply',
                'fields' => [],
            ],
        ];
    }

    private function defaultInputValues(array $snapshot): array
    {
        $values = [];

        foreach ($snapshot['fields_schema'] ?? [] as $field) {
            $key = (string) ($field['field_key'] ?? '');
            $defaultValue = $field['default_value'] ?? null;
            $isCalculated = (bool) ($field['is_calculated'] ?? false);

            if ($key === '' || $isCalculated || in_array($key, ['item_name', 'reference', 'note'], true)) {
                continue;
            }

            if ($defaultValue !== null && $defaultValue !== '') {
                $values[$key] = $defaultValue;
            }
        }

        return $values;
    }

    private function calculateTotal(array $snapshot, array $values): float
    {
        $fields = collect($snapshot['formula_schema']['fields'] ?? [])
            ->filter(fn ($field): bool => is_string($field) && $field !== '')
            ->values();

        if ($fields->isEmpty()) {
            return (float) ($values['yearly_total'] ?? 0);
        }

        return (float) $fields->reduce(
            fn (float $carry, string $field): float => $carry * (float) ($values[$field] ?? 0),
            1.0
        );
    }
};
