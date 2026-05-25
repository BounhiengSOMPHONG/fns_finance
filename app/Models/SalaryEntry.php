<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SalaryEntry extends Model
{
    protected $fillable = [
        'plan_id', 'budget_code_id',
        'person_count', 'atm_amount', 'cash_amount',
        'monthly_total', 'annual_amount', 'remark',
    ];

    protected $casts = [
        'person_count' => 'integer',
        'atm_amount' => 'float',
        'cash_amount' => 'float',
        'monthly_total' => 'float',
        'annual_amount' => 'float',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $entry): void {
            $entry->monthly_total = (float) $entry->atm_amount + (float) $entry->cash_amount;

            $mode = $entry->budgetCode?->annual_mode ?? 'x12';

            if ($mode === 'x12') {
                $entry->annual_amount = $entry->monthly_total * 12;
            } elseif ($mode === 'x1') {
                $entry->annual_amount = $entry->monthly_total;
            }
            // 'direct' mode: annual_amount is set explicitly, leave untouched
        });
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SalaryPlan::class, 'plan_id');
    }

    public function budgetCode(): BelongsTo
    {
        return $this->belongsTo(SalaryBudgetCode::class, 'budget_code_id');
    }
}
