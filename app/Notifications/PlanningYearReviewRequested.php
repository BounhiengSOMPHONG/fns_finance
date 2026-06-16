<?php

namespace App\Notifications;

use App\Models\PlanningYearReviewRound;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanningYearReviewRequested extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PlanningYearReviewRound $reviewRound
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planningYear = $this->reviewRound->planningYear;

        return (new MailMessage)
            ->subject('Planning review requested')
            ->line('A planning year is waiting for your review.')
            ->action('Open review', route('reviews.planning-years.show', $planningYear));
    }

    public function toArray(object $notifiable): array
    {
        $planningYear = $this->reviewRound->planningYear;

        return [
            'planning_year_id' => $planningYear->id,
            'planning_year' => $planningYear->year,
            'review_round_id' => $this->reviewRound->id,
            'round_number' => $this->reviewRound->round_number,
            'title' => 'ຂໍຄວາມເຫັນແຜນປີ '.$planningYear->year,
            'message' => 'ກະລຸນາກວດແຜນ ແລະ ໃຫ້ຄວາມເຫັນ',
            'url' => route('reviews.planning-years.show', $planningYear),
        ];
    }
}
