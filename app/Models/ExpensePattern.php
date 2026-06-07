<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpensePattern extends Model
{
    protected $fillable = ['key', 'name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(ExpensePatternField::class, 'pattern_id')->orderBy('display_order');
    }

    public function defaultSubsections(): HasMany
    {
        return $this->hasMany(ExpenseSubsection::class, 'default_pattern_id');
    }

    public function leafDefaultSubsections(): HasMany
    {
        return $this->hasMany(ExpenseSubsection::class, 'default_pattern_id')
            ->whereDoesntHave('children')
            ->orderBy('code');
    }
}
