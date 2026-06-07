<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePlanValue extends Model
{
    protected $fillable = [
        'expense_plan_id',
        'field_key',
        'value_text',
        'value_number',
        'value_date',
        'value_boolean',
    ];

    protected $casts = [
        'value_number' => 'decimal:2',
        'value_date' => 'date',
        'value_boolean' => 'boolean',
    ];

    public function expensePlan(): BelongsTo
    {
        return $this->belongsTo(ExpensePlan::class);
    }
}
