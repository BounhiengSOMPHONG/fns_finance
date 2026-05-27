<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategoryTemplate extends Model
{
    protected $fillable = [
        'parent_id', 'ref_code', 'name', 'sort_order',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategoryTemplate::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ExpenseCategoryTemplate::class, 'parent_id')
            ->orderBy('sort_order')->orderBy('ref_code');
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('sort_order')->orderBy('ref_code');
    }
}
