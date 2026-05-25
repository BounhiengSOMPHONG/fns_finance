<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'plan_id', 'parent_id', 'ref_code', 'name', 'sort_order',
        'formula_type', 'col_a_label', 'col_b_label', 'col_c_label',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ExpensePlan::class, 'plan_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class, 'parent_id')->orderBy('sort_order')->orderBy('ref_code');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'category_id')->orderBy('sort_order');
    }

    public function labelA(): string { return $this->col_a_label ?: 'ຕໍ່ເດືອນ (ກີບ)'; }
    public function labelB(): string { return $this->col_b_label ?: 'ຈ/ນ'; }
    public function labelC(): string { return $this->col_c_label ?: 'ຄັ້ງ'; }
    public function isABC(): bool    { return $this->formula_type === 'ABC'; }

    public function subtotal(): float
    {
        if ($this->relationLoaded('children') && $this->children->isNotEmpty()) {
            return (float) $this->children->sum(fn ($c) => $c->subtotal());
        }
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum('annual_amount');
        }
        return (float) ExpenseItem::where('category_id', $this->id)->sum('annual_amount');
    }
}
