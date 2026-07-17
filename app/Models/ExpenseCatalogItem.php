<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCatalogItem extends Model
{
    protected $fillable = [
        'subsection_id',
        'item_name',
        'chart_of_account_id',
        'pattern_id',
        'default_values',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'default_values' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getSubsectionCodeAttribute(): ?string
    {
        return $this->subsection?->code;
    }

    public function subsection(): BelongsTo
    {
        return $this->belongsTo(ExpenseSubsection::class);
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(ExpensePattern::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(ExpensePlanRow::class, 'catalog_item_id');
    }
}
