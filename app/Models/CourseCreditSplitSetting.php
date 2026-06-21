<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseCreditSplitSetting extends Model
{
    protected $fillable = ['level', 'year1_percentage', 'year2_percentage', 'gov_doc_id', 'start_year'];

    protected $casts = [
        'year1_percentage' => 'decimal:4',
        'year2_percentage' => 'decimal:4',
    ];

    public static function year1For(string $level): float
    {
        return static::percentageFor($level, 'year1_percentage', 0.60);
    }

    public static function year2For(string $level): float
    {
        return static::percentageFor($level, 'year2_percentage', 0.40);
    }

    private static function percentageFor(string $level, string $column, float $default): float
    {
        if (! in_array($level, ['master', 'phd'], true)) {
            return 1.0;
        }

        return (float) (static::where('level', $level)
            ->orderByDesc('start_year')
            ->value($column) ?? $default);
    }
}
