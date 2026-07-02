<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SalaryPlan extends Model
{
    protected $fillable = ['planning_year_id', 'fiscal_year', 'month', 'notes', 'created_by'];

    protected $casts = [
        'month' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function planningYear(): BelongsTo
    {
        return $this->belongsTo(PlanningYear::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(SalaryEntry::class, 'plan_id');
    }

    public function isApproved(): bool
    {
        return false;
    }

    public function monthLabel(): string
    {
        return str_pad((string) $this->month, 2, '0', STR_PAD_LEFT).'/'.$this->fiscal_year;
    }

    public function grandTotal(): float
    {
        return (float) $this->entries()->sum('annual_amount');
    }

    public function monthlyTotal(): float
    {
        return (float) $this->entries()->sum('monthly_total');
    }
}
