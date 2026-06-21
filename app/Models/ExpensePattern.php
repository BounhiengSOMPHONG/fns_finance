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

    public function calculateTotal(array $values, ?array $snapshot = null): float
    {
        $formula = $snapshot['formula_schema'] ?? $this->formula_schema ?? [];
        $fields = collect($formula['fields'] ?? [])
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
