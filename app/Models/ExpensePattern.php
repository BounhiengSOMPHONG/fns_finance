<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ExpensePattern extends Model
{
    protected $fillable = ['key', 'name', 'description', 'fields_schema', 'formula_schema', 'is_active'];

    protected $casts = [
        'fields_schema' => 'array',
        'formula_schema' => 'array',
        'is_active' => 'boolean',
    ];

    public function getFieldsAttribute(): Collection
    {
        return $this->fieldDefinitions();
    }

    public function fieldDefinitions(?array $snapshot = null): Collection
    {
        $fields = $snapshot['fields_schema'] ?? $this->fields_schema ?? [];

        return collect($fields)
            ->sortBy(fn (array $field): int => (int) ($field['display_order'] ?? 0))
            ->map(fn (array $field): object => (object) [
                'field_key' => (string) ($field['field_key'] ?? ''),
                'default_label' => (string) ($field['default_label'] ?? str_replace('_', ' ', (string) ($field['field_key'] ?? ''))),
                'data_type' => (string) ($field['data_type'] ?? 'text'),
                'display_order' => (int) ($field['display_order'] ?? 0),
                'is_required' => (bool) ($field['is_required'] ?? false),
                'is_calculated' => (bool) ($field['is_calculated'] ?? false),
                'default_value' => $field['default_value'] ?? null,
            ])
            ->values();
    }

    public function snapshot(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'fields_schema' => $this->fields_schema ?? [],
            'formula_schema' => $this->formula_schema ?? ['operation' => 'multiply', 'fields' => []],
        ];
    }

    public function defaultInputValues(?array $snapshot = null): array
    {
        return $this->fieldDefinitions($snapshot)
            ->reject(fn (object $field): bool => $field->is_calculated)
            ->reject(fn (object $field): bool => in_array($field->field_key, ['item_name', 'reference', 'note'], true))
            ->filter(fn (object $field): bool => $field->default_value !== null && $field->default_value !== '')
            ->mapWithKeys(fn (object $field): array => [$field->field_key => $field->default_value])
            ->all();
    }

    public function calculateTotal(array $values, ?array $snapshot = null): float
    {
        $formula = $snapshot['formula_schema'] ?? $this->formula_schema ?? [];
        $fields = collect($formula['fields'] ?? [])
            ->filter(fn ($field): bool => is_string($field) && $field !== '')
            ->values();

        if ($fields->isEmpty()) {
            return (float) ($values['yearly_total'] ?? 0);
        }

        $schema = $snapshot['fields_schema'] ?? $this->fields_schema ?? [];

        return (float) $fields->reduce(
            function (float $carry, string $field) use ($values, $schema): float {
                $value = $values[$field] ?? null;
                if ($value === null || $value === '') {
                    $fieldDef = collect($schema)->firstWhere('field_key', $field);
                    $value = $fieldDef['default_value'] ?? 0;
                }
                return $carry * (float) $value;
            },
            1.0
        );
    }

    public function defaultSubsections(): HasMany
    {
        return $this->hasMany(ExpenseSubsection::class, 'default_pattern_id');
    }

    public function leafDefaultSubsections(): HasMany
    {
        return $this->hasMany(ExpenseSubsection::class, 'default_pattern_id')
            ->whereDoesntHave('children')
            ->orderBy('code');
    }
}
