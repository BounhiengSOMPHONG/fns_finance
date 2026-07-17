<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SalaryEntry extends Model
{
    protected $fillable = [
        'plan_id', 'chart_of_account_id',
        'person_count', 'payment_type', 'amount',
        'monthly_total', 'annual_amount',
    ];

    protected $casts = [
        'person_count' => 'integer',
        'amount' => 'float',
        'monthly_total' => 'float',
        'annual_amount' => 'float',
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::saving(function (self $entry): void {
            $entry->monthly_total = max(0, (int) $entry->person_count) * (float) $entry->amount;
            $entry->annual_amount = $entry->monthly_total * 12;
        });
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SalaryPlan::class, 'plan_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
