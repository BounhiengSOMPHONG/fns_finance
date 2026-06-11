<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseSection extends Model
{
    protected $fillable = [
        'planning_year_id',
        'code',
        'name',
        'description',
        'display_order',
        'summary_period_count',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'summary_period_count' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function planningYear(): BelongsTo
    {
        return $this->belongsTo(PlanningYear::class);
    }

    public function subsections(): HasMany
    {
        return $this->hasMany(ExpenseSubsection::class, 'section_id')->orderBy('display_order');
    }
}
