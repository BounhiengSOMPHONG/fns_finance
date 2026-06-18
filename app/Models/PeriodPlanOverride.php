<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodPlanOverride extends Model
{
    protected $fillable = [
        'planning_year_id',
        'chart_of_account_id',
        'period_1_amount',
        'period_2_amount',
        'average_increase_amount',
        'average_decrease_amount',
        'requested_decrease_amount',
        'requested_increase_amount',
        'period_3_amount',
        'period_4_amount',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'planning_year_id' => 'integer',
        'chart_of_account_id' => 'integer',
        'period_1_amount' => 'float',
        'period_2_amount' => 'float',
        'average_increase_amount' => 'float',
        'average_decrease_amount' => 'float',
        'requested_decrease_amount' => 'float',
        'requested_increase_amount' => 'float',
        'period_3_amount' => 'float',
        'period_4_amount' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function planningYear(): BelongsTo
    {
        return $this->belongsTo(PlanningYear::class);
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
