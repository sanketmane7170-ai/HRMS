<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\CandidateScore;

class ScoringCompleteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $candidateScore;

    /**
     * Create a new notification instance.
     */
    public function __construct(CandidateScore $candidateScore)
    {
        $this->candidateScore = $candidateScore;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->candidateScore->application;
        $candidateName = $application->candidate_name ?? $application->user->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';
        
        return (new MailMessage)
            ->subject('Candidate Scoring Completed - ' . $candidateName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Candidate scoring has been completed for ' . $candidateName . ' applying for the ' . $jobTitle . ' position.')
            ->line('**Scoring Summary:**')
            ->line('**Overall Score:** ' . $this->candidateScore->overall_score . '/100')
            ->line('**Recommendation:** ' . $this->candidateScore->recommendation_text)
            ->line('**Round:** ' . $this->candidateScore->interview_round)
            ->action('View Scoring Details', route('recruitment.scoring.show', $this->candidateScore->id))
            ->line('Thank you for participating in the recruitment process!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase(object $notifiable): array
    {
        $application = $this->candidateScore->application;
        $candidateName = $application->candidate_name ?? $application->user->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';

        return [
            'title' => 'Candidate Scoring Completed',
            'message' => 'Scoring completed for ' . $candidateName . ' (Score: ' . $this->candidateScore->overall_score . ')',
            'url' => route('recruitment.scoring.show', $this->candidateScore->id),
            'route' => route('recruitment.scoring.show', $this->candidateScore->id),
            'action_url' => route('recruitment.scoring.show', $this->candidateScore->id),
            'application_id' => $this->candidateScore->application_id,
            'candidate_name' => $candidateName,
            'job_title' => $jobTitle,
            'overall_score' => $this->candidateScore->overall_score,
            'recommendation' => $this->candidateScore->recommendation_text,
            'type' => 'scoring_completed'
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
