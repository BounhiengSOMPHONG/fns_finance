<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseSubsectionFieldSetting extends Model
{
    protected $fillable = [
        'subsection_id',
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

    public function subsection(): BelongsTo
    {
        return $this->belongsTo(ExpenseSubsection::class);
    }

    public function patternField(): BelongsTo
    {
        return $this->belongsTo(ExpensePatternField::class, 'pattern_field_id');
    }
}
