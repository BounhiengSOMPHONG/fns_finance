<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicIncomePlan extends Model
{
    protected $fillable = [
        'fiscal_year', 'status', 'notes', 'created_by',
    ];

    protected $casts = [];

    public function items(): HasMany
    {
        return $this->hasMany(AcademicIncomeItem::class, 'plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }
}
