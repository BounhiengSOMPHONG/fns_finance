<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePlanRow extends Model
{
    protected $fillable = [
        'expense_plan_id',
        'planning_year_id',
        'section_id',
        'subsection_id',
        'catalog_item_id',
        'chart_of_account_id',
        'pattern_id',
        'version',
        'plan_type',
        'item_name',
        'plan_detail',
        'detail',
        'calculation_values',
        'pattern_snapshot',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'calculation_values' => 'array',
        'pattern_snapshot' => 'array',
    ];

    public function expensePlan(): BelongsTo
    {
        return $this->belongsTo(ExpensePlan::class);
    }

    public function planningYear(): BelongsTo
    {
        return $this->belongsTo(PlanningYear::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ExpenseSection::class);
    }

    public function subsection(): BelongsTo
    {
        return $this->belongsTo(ExpenseSubsection::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(ExpenseCatalogItem::class, 'catalog_item_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(ExpensePattern::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function yearlyTotal(): float
    {
        if ($this->pattern_snapshot) {
            $pattern = $this->pattern ?? new ExpensePattern;

            return $pattern->calculateTotal($this->calculation_values ?? [], $this->pattern_snapshot);
        }

        return (float) (($this->calculation_values ?? [])['yearly_total'] ?? 0);
    }
}
