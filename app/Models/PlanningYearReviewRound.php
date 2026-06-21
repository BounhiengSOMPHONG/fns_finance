<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PlanningYearReviewRound extends Model
{
    protected $fillable = [
        'planning_year_id',
        'requested_by',
        'closed_by',
        'round_number',
        'note',
        'reviewer_user_ids',
        'requested_at',
        'closed_at',
    ];

    protected $casts = [
        'reviewer_user_ids' => 'array',
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

    public function comments(): HasMany
    {
        return $this->hasMany(PlanningYearReviewComment::class);
    }

    public function getReviewersAttribute(): Collection
    {
        if ($this->relationLoaded('reviewers')) {
            return $this->relations['reviewers'];
        }

        $reviewers = User::with('role')
            ->whereIn('id', $this->reviewerIds())
            ->get()
            ->map(function (User $user): PlanningYearReviewer {
                $reviewer = new PlanningYearReviewer([
                    'planning_year_review_round_id' => $this->id,
                    'user_id' => $user->id,
                    'notified_at' => null,
                ]);
                $reviewer->exists = true;
                $reviewer->setRelation('reviewRound', $this);
                $reviewer->setRelation('user', $user);

                return $reviewer;
            });

        $this->setRelation('reviewers', $reviewers);

        return $reviewers;
    }

    public function reviewerIds(): array
    {
        return collect($this->reviewer_user_ids ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function hasReviewer(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return in_array((int) $user->id, $this->reviewerIds(), true);
    }
}
