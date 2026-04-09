<?php

namespace App\Notifications;

use App\Models\BudgetPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetPlanReviewRequested extends Notification
{
    use Queueable;

    public function __construct(
        protected BudgetPlan $budgetPlan
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'budget_plan_id' => $this->budgetPlan->id,
            'fiscal_year' => $this->budgetPlan->fiscal_year,
            'message' => 'ແຜນງົບປະມານປະຈຳປີ ' . $this->budgetPlan->fiscal_year . ' ຕ້ອງການຄວາມຄິດເຫັນຈາກທ່ານ',
            'type' => 'review_requested',
            'url' => route('head_of_department.annual-budget.show', $this->budgetPlan->id),
        ];
    }
}
