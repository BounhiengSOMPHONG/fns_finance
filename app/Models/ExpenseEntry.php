<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseEntry extends Model
{
    protected $fillable = [
        'plan_id', 'ref_code', 'chart_of_account_id',
        'main_cat_code', 'main_cat', 'main_item_code', 'main_item', 'sub_item',
        'expense_ref_code_id', 'department_id',
        'expense_type', 'disbursement_mode', 'paid_at', 'code',
        'rate1', 'rate2', 'qty', 'period', 'frequency', 'add_on', 'total',
        'note', 'sort_order',
    ];

    protected $casts = [
        'rate1'      => 'decimal:2',
        'rate2'      => 'decimal:2',
        'qty'        => 'decimal:2',
        'period'     => 'decimal:2',
        'frequency'  => 'decimal:2',
        'add_on'     => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function (self $entry) {
            // A row with a note is a descriptive line — it never carries an amount.
            if (filled($entry->note)) {
                $entry->total = 0;
                return;
            }
            // Total = (Rate1 + Rate2) × Qty × Period × Frequency + AddOn
            $entry->total = ((float) $entry->rate1 + (float) $entry->rate2)
                * (float) $entry->qty
                * (float) $entry->period
                * (float) $entry->frequency
                + (float) $entry->add_on;
        });
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ExpensePlan::class, 'plan_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
