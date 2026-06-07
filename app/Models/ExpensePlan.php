<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpensePlan extends Model
{
    protected $fillable = [
        'planning_year_id',
        'section_id',
        'subsection_id',
        'pattern_id',
        'version',
        'plan_type',
        'plan_detail',
        'detail',
        'created_by',
        'updated_by',
    ];

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

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(ExpensePattern::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ExpensePlanValue::class);
    }

    public function value(string $fieldKey): ?ExpensePlanValue
    {
        $values = $this->relationLoaded('values') ? $this->values : $this->values()->get();

        return $values->firstWhere('field_key', $fieldKey);
    }

    public function yearlyTotal(): float
    {
        return (float) ($this->value('yearly_total')?->value_number ?? 0);
    }
}
