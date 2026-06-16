<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningYearReviewComment extends Model
{
    protected $fillable = [
        'planning_year_review_round_id',
        'planning_year_id',
        'user_id',
        'comment',
    ];

    public function reviewRound(): BelongsTo
    {
        return $this->belongsTo(PlanningYearReviewRound::class, 'planning_year_review_round_id');
    }

    public function planningYear(): BelongsTo
    {
        return $this->belongsTo(PlanningYear::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(PlanningYearReviewCommentAgreement::class);
    }
}
