<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpensePlan extends Model
{
    protected $fillable = [
        'planning_year_id',
        'fiscal_year',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
    ];

    public function planningYear(): BelongsTo
    {
        return $this->belongsTo(PlanningYear::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ExpensePlanRow::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
