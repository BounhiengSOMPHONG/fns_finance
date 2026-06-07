<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningYearFieldSetting extends Model
{
    protected $fillable = [
        'planning_year_id',
        'pattern_field_id',
        'label',
        'display_order',
        'is_required',
        'is_active',
        'default_value',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(ExpensePatternField::class, 'pattern_field_id');
    }
}
