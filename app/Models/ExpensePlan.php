<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpensePlan extends Model
{
    protected $fillable = ['fiscal_year', 'status', 'notes', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function topCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class, 'plan_id')->whereNull('parent_id')->orderBy('sort_order')->orderBy('ref_code');
    }

    public function allCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class, 'plan_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function grandTotal(): float
    {
        return (float) $this->allCategories->flatMap->items->sum('annual_amount');
    }
}
