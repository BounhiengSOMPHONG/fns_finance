<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetPlanReviewer extends Model
{
    protected $fillable = [
        'budget_plan_id',
        'user_id',
        'assigned_by',
    ];

    public function budgetPlan()
    {
        return $this->belongsTo(BudgetPlan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
