<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SalaryBudgetCode extends Model
{
    protected $fillable = ['parent_id', 'code', 'name', 'sort_order', 'is_leaf', 'annual_mode'];

    protected $casts = [
        'is_leaf' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SalaryBudgetCode::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(SalaryBudgetCode::class, 'parent_id')->orderBy('sort_order')->orderBy('code');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(SalaryEntry::class, 'budget_code_id');
    }

    public static function roots(): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereNull('parent_id')->orderBy('sort_order')->orderBy('code')->get();
    }
}
