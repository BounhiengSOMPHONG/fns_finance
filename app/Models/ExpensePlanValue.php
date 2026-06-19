<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePlanValue extends Model
{
    protected $fillable = [
        'expense_plan_id',
        'field_key',
        'value',
    ];

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

    private const DATE_FIELD_KEYS = [];

    private const BOOLEAN_FIELD_KEYS = [];

    public function expensePlan(): BelongsTo
    {
        return $this->belongsTo(ExpensePlan::class);
    }

    public function getValueNumberAttribute($value = null): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

        if (! in_array($this->field_key, self::NUMBER_FIELD_KEYS, true)) {
            return null;
        }

        $raw = $this->attributes['value'] ?? null;

        return is_numeric($raw) ? (float) $raw : 0.0;
    }

    public function getValueTextAttribute($value = null): ?string
    {
        if ($value !== null) {
            return (string) $value;
        }

        if (in_array($this->field_key, array_merge(self::NUMBER_FIELD_KEYS, self::DATE_FIELD_KEYS, self::BOOLEAN_FIELD_KEYS), true)) {
            return null;
        }

        $raw = $this->attributes['value'] ?? null;

        return $raw === null ? null : (string) $raw;
    }

    public function getValueDateAttribute($value = null): ?string
    {
        if ($value !== null) {
            return (string) $value;
        }

        if (! in_array($this->field_key, self::DATE_FIELD_KEYS, true)) {
            return null;
        }

        $raw = $this->attributes['value'] ?? null;

        return $raw === null || $raw === '' ? null : (string) $raw;
    }

    public function getValueBooleanAttribute($value = null): ?bool
    {
        if ($value !== null) {
            return (bool) $value;
        }

        if (! in_array($this->field_key, self::BOOLEAN_FIELD_KEYS, true)) {
            return null;
        }

        $raw = $this->attributes['value'] ?? null;

        return $raw === null || $raw === '' ? null : (bool) $raw;
    }

    public function typedValue(): mixed
    {
        return $this->value_number
            ?? $this->value_text
            ?? $this->value_date
            ?? $this->value_boolean;
    }
}
