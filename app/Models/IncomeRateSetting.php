<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeRateSetting extends Model
{
    protected $fillable = ['setting_set_id', 'key', 'label', 'rate'];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

    /**
     * Get the rate for a given key, falling back to a default.
     */
    public static function rateFor(string $key, float $default = 0): float
    {
        return (float) (static::where('key', $key)->value('rate') ?? $default);
    }

    /**
     * Get all 4 income rate settings keyed by their key column.
     */
    public static function allKeyed(): \Illuminate\Support\Collection
    {
        return static::all()->keyBy('key');
    }

    public function settingSet(): BelongsTo
    {
        return $this->belongsTo(AcademicIncomeSettingSet::class, 'setting_set_id');
    }
}
