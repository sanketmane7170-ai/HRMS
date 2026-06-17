<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Interview;

class CandidateInterviewScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $interview;

    /**
     * Create a new notification instance.
     */
    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->interview->application;
        $jobTitle = $application->job->title ?? 'Position';
        
        $message = (new MailMessage)
            ->subject("Interview Invitation: " . $jobTitle)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("We are pleased to invite you for an interview for the **{$jobTitle}** position.")
            ->line('Below are your interview details:')
            ->line("📅 **Date & Time:** {$this->interview->scheduled_at->format('l, F j, Y \a\t g:i A')}")
            ->line("📍 **Interview Type:** " . ucfirst($this->interview->type));

        if ($this->interview->location) {
            $message->line("🏢 **Location:** {$this->interview->location}");
        }

        if ($this->interview->meeting_link) {
            $message->line("🔗 **Meeting Link:** [Join Interview]({$this->interview->meeting_link})");
        }

        if ($this->interview->preparation_notes) {
            $message->line('**Notes for you:**')
                   ->line($this->interview->preparation_notes);
        }

        $message->line('Please confirm your availability. We look forward to speaking with you!')
               ->action('View Details & Track Application', route('career.track-application'))
               ->line('Thank you for your interest in joining our team!')
               ->salutation('Best regards, The Recruitment Team');

        return $message;
    }
}
