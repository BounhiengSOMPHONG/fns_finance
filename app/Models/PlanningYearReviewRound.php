<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningYearReviewRound extends Model
{
    protected $fillable = [
        'planning_year_id',
        'requested_by',
        'closed_by',
        'round_number',
        'note',
        'requested_at',
        'closed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function planningYear(): BelongsTo
    {
        return $this->belongsTo(PlanningYear::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reviewers(): HasMany
    {
        return $this->hasMany(PlanningYearReviewer::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PlanningYearReviewComment::class);
    }
}
