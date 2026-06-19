<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePatternField extends Model
{
    protected $fillable = [
        'pattern_id',
        'field_key',
        'default_label',
        'data_type',
        'display_order',
        'is_required',
        'is_calculated',
        'default_value',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_required' => 'boolean',
        'is_calculated' => 'boolean',
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(ExpensePattern::class, 'pattern_id');
    }

}
