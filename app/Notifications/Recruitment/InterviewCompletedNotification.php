<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Interview;

class InterviewCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $interview;
    protected $recommendation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Interview $interview, $recommendation)
    {
        $this->interview = $interview;
        $this->recommendation = $recommendation;
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
        $application = $this->interview->application;
        $jobTitle = $application->job->title ?? 'Position';
        
        return (new MailMessage)
            ->subject("Interview Completed - Update: {$jobTitle}")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("The interview for the {$jobTitle} position has been completed and feedback has been recorded.")
            ->line('**Interview Details:**')
            ->line("📅 **Date:** {$this->interview->scheduled_at->format('M d, Y')}")
            ->line("⏱️ **Type:** " . ucfirst($this->interview->type))
            ->line($this->getRecommendationMessage())
            ->action('View Application Status', route('career.track-application'))
            ->line('Thank you for your time and interest in joining our team.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Interview Completed',
            'message' => 'The interview for ' . ($this->interview->application->job->title ?? 'Position') . ' has been completed.',
            'url' => route('recruitment.interviews.show', $this->interview->id),
            'route' => route('recruitment.interviews.show', $this->interview->id),
            'action_url' => route('recruitment.interviews.show', $this->interview->id),
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->application_id,
            'recommendation' => $this->recommendation,
            'type' => 'interview_completed'
        ];
    }

    /**
     * Get recommendation message for the candidate
     */
    private function getRecommendationMessage(): string
    {
        return match($this->recommendation) {
            'hire', 'second_interview' => 'We are reviewing your interview performance and will contact you shortly regarding the next steps.',
            'reject' => 'Thank you for your interest. While we were impressed with your background, we have decided to move forward with other candidates at this time.',
            'on_hold' => 'Your application is currently on hold while we evaluate other candidates. We will update you as soon as a decision is made.',
            default => 'We have received the interviewer\'s feedback and are currently reviewing your application.'
        };
    }
}
