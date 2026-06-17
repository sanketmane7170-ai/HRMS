<?php

namespace Modules\Recruitment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Recruitment\Entities\CandidateScore;

class ScoringCompleteNotification extends Notification
{
    use Queueable;

    public $candidateScore;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(CandidateScore $candidateScore)
    {
        $this->candidateScore = $candidateScore;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function viaMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Candidate Scoring Complete: ' . $this->candidateScore->application->candidate_name)
                    ->line('A candidate has been scored for the position: ' . $this->candidateScore->application->job->title)
                    ->line('Overall Score: ' . $this->candidateScore->overall_score)
                    ->line('Recommendation: ' . $this->candidateScore->recommendation_text)
                    ->action('View Application', route('recruitment.applications.show', $this->candidateScore->application_id))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'application_id' => $this->candidateScore->application_id,
            'candidate_name' => $this->candidateScore->application->candidate_name,
            'score_id' => $this->candidateScore->id,
            'overall_score' => $this->candidateScore->overall_score,
            'recommendation' => $this->candidateScore->recommendation,
        ];
    }
}
