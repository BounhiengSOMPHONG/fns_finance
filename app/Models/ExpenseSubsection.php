<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseSubsection extends Model
{
    protected $fillable = [
        'section_id',
        'parent_id',
        'code',
        'name',
        'description',
        'default_pattern_id',
        'summary_period_count',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'summary_period_count' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ExpenseSection::class, 'section_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('display_order');
    }

    public function defaultPattern(): BelongsTo
    {
        return $this->belongsTo(ExpensePattern::class, 'default_pattern_id');
    }

    public function fieldSettings(): HasMany
    {
        return $this->hasMany(ExpenseSubsectionFieldSetting::class, 'subsection_id');
    }
}
