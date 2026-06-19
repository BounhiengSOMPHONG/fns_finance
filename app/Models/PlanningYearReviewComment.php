<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class PlanningYearReviewComment extends Model
{
    protected $fillable = [
        'planning_year_review_round_id',
        'planning_year_id',
        'user_id',
        'comment',
        'agreement_user_ids',
    ];

    protected $casts = [
        'agreement_user_ids' => 'array',
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

    public function getAgreementsAttribute(): Collection
    {
        if ($this->relationLoaded('agreements')) {
            return $this->relations['agreements'];
        }

        $agreements = User::query()
            ->whereIn('id', $this->agreementIds())
            ->get()
            ->map(function (User $user): PlanningYearReviewCommentAgreement {
                $agreement = new PlanningYearReviewCommentAgreement([
                    'planning_year_review_comment_id' => $this->id,
                    'user_id' => $user->id,
                ]);
                $agreement->exists = true;
                $agreement->setRelation('comment', $this);
                $agreement->setRelation('user', $user);

                return $agreement;
            });

        $this->setRelation('agreements', $agreements);

        return $agreements;
    }

    public function agreementIds(): array
    {
        return collect($this->agreement_user_ids ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function hasAgreement(User $user): bool
    {
        return in_array((int) $user->id, $this->agreementIds(), true);
    }

    public function toggleAgreement(User $user): bool
    {
        $ids = collect($this->agreementIds());
        $userId = (int) $user->id;
        $exists = $ids->contains($userId);

        $this->agreement_user_ids = $exists
            ? $ids->reject(fn (int $id): bool => $id === $userId)->values()->all()
            : $ids->push($userId)->unique()->values()->all();
        $this->unsetRelation('agreements');
        $this->save();

        return ! $exists;
    }
}
