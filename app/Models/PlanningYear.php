<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningYear extends Model
{
    protected $fillable = ['year', 'name', 'description', 'is_active'];

    protected $casts = [
        'year' => 'integer',
        'is_active' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(ExpenseSection::class);
    }

    public function expensePlans(): HasMany
    {
        return $this->hasMany(ExpensePlan::class);
    }

    public function totalAmount(): float
    {
        $planIds = $this->expensePlans()->pluck('id');

        if ($planIds->isEmpty()) {
            return 0.0;
        }

        return (float) ExpensePlanValue::whereIn('expense_plan_id', $planIds)
            ->where('field_key', 'yearly_total')
            ->sum('value_number');
    }
}
