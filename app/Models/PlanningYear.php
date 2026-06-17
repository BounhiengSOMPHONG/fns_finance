<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningYear extends Model
{
    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_PENDING_REVIEW = 'PENDING_REVIEW';

    public const STATUS_MODIFYING = 'MODIFYING';

    public const STATUS_SAVED = 'SAVED';

    protected $fillable = [
        'year',
        'name',
        'description',
        'is_active',
        'status',
        'current_review_round_id',
        'review_requested_at',
        'review_closed_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'is_active' => 'boolean',
        'review_requested_at' => 'datetime',
        'review_closed_at' => 'datetime',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(ExpenseSection::class);
    }

    public function expensePlans(): HasMany
    {
        return $this->hasMany(ExpensePlan::class);
    }

    public function academicIncomePlans(): HasMany
    {
        return $this->hasMany(AcademicIncomePlan::class);
    }

    public function salaryPlans(): HasMany
    {
        return $this->hasMany(SalaryPlan::class);
    }

    public function reviewRounds(): HasMany
    {
        return $this->hasMany(PlanningYearReviewRound::class);
    }

    public function currentReviewRound(): BelongsTo
    {
        return $this->belongsTo(PlanningYearReviewRound::class, 'current_review_round_id');
    }

    public function reviewComments(): HasMany
    {
        return $this->hasMany(PlanningYearReviewComment::class);
    }

    public function isPendingReview(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    public function canRequestReview(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_MODIFYING], true);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_MODIFYING], true);
    }

    public function hasCurrentReviewer(User $user): bool
    {
        if (! $this->current_review_round_id) {
            return false;
        }

        return PlanningYearReviewer::query()
            ->where('planning_year_review_round_id', $this->current_review_round_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function totalAmount(): float
    {
        $planIds = $this->expensePlans()->pluck('id');

        if ($planIds->isEmpty()) {
            return 0.0;
        }

        return (float) ExpensePlanValue::whereIn('expense_plan_id', $planIds)
            ->where('field_key', 'yearly_total')
            ->sum('value_number');
    }
}
