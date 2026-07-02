<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class IncomeRateSetting extends Model
{
    protected $fillable = ['key', 'label', 'rate'];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

    public function academicIncomeItems(): HasMany
    {
        return $this->hasMany(AcademicIncomeItem::class);
    }

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
    public static function allKeyed(): Collection
    {
        return static::all()->keyBy('key');
    }
}
