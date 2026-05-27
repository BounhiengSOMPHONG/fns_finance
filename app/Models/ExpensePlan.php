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

    public function entries(): HasMany
    {
        return $this->hasMany(ExpenseEntry::class, 'plan_id')
            ->orderBy('main_cat')->orderBy('ref_code')->orderBy('sort_order');
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function grandTotal(): float
    {
        return (float) ($this->relationLoaded('entries')
            ? $this->entries->sum('total')
            : $this->entries()->sum('total'));
    }
}
