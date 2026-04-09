<?php

namespace App\Notifications;

use App\Models\BudgetPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetPlanStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        protected BudgetPlan $budgetPlan,
        protected string $message,
        protected string $url
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
            'message' => $this->message,
            'type' => 'status_changed',
            'url' => $this->url,
        ];
    }
}
