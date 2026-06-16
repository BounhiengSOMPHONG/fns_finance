<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningYearReviewer extends Model
{
    protected $fillable = [
        'planning_year_review_round_id',
        'user_id',
        'notified_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function reviewRound(): BelongsTo
    {
        return $this->belongsTo(PlanningYearReviewRound::class, 'planning_year_review_round_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
