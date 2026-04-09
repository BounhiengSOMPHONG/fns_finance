<?php

namespace App\Notifications;

use App\Models\BudgetPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetPlanFinalApprovalRequested extends Notification
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
            'message' => 'ແຜນງົບປະມານປະຈຳປີ ' . $this->budgetPlan->fiscal_year . ' ລໍຖ້າການອະນຸມັດຂັ້ນສຸດທ້າຍ',
            'type' => 'final_approval_requested',
            'url' => route('head_of_faculty.annual-budget.show', $this->budgetPlan->id),
        ];
    }
}
