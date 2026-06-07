<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCalculationRule extends Model
{
    protected $fillable = [
        'planning_year_id',
        'pattern_id',
        'section_id',
        'subsection_id',
        'target_field_key',
        'formula',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
