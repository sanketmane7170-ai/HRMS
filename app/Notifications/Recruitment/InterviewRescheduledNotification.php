<?php

namespace App\Notifications\Recruitment;

use Modules\Recruitment\Entities\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class InterviewRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Interview $interview;
    protected Carbon $originalScheduledAt;
    protected string $reason;

    /**
     * Create a new notification instance.
     *
     * @param Interview $interview
     * @param Carbon $originalScheduledAt
     * @param string $reason
     */
    public function __construct(Interview $interview, Carbon $originalScheduledAt, string $reason = '')
    {
        $this->interview = $interview;
        $this->originalScheduledAt = $originalScheduledAt;
        $this->reason = $reason;
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $candidateName = $application->candidate_name ?? $application->user?->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';
        
        $message = (new MailMessage)
            ->subject("Interview Rescheduled: {$candidateName} - {$jobTitle}")
            ->greeting('Interview Rescheduled!')
            ->line("The interview for {$candidateName} applying for the {$jobTitle} position has been rescheduled.")
            ->line('**Original Interview Details:**')
            ->line("📅 **Original Date:** {$this->originalScheduledAt->format('l, F j, Y \a\t g:i A')}")
            ->line('**New Interview Details:**')
            ->line("📅 **New Date & Time:** {$this->interview->scheduled_at->format('l, F j, Y \a\t g:i A')}")
            ->line("⏱️ **Duration:** {$this->interview->duration_text}")
            ->line("📍 **Type:** {$this->interview->type_text}");

        if ($this->interview->location) {
            $message->line("🏢 **Location:** {$this->interview->location}");
        }

        if ($this->interview->meeting_link) {
            $message->line("🔗 **Meeting Link:** {$this->interview->meeting_link}");
        }

        if ($this->reason) {
            $message->line('**Reason for Rescheduling:**')
                   ->line($this->reason);
        }

        if ($this->interview->agenda) {
            $message->line('**Agenda:**')
                   ->line($this->interview->agenda);
        }

        if ($this->interview->preparation_notes) {
            $message->line('**Preparation Notes:**')
                   ->line($this->interview->preparation_notes);
        }

        // Add time comparison
        $timeDiff = $this->originalScheduledAt->diffForHumans($this->interview->scheduled_at, true);
        if ($this->interview->scheduled_at->greaterThan($this->originalScheduledAt)) {
            $message->line("ℹ️ **Change:** The interview has been moved {$timeDiff} later.");
        } else {
            $message->line("ℹ️ **Change:** The interview has been moved {$timeDiff} earlier.");
        }

        $message->line('Please update your calendar and confirm your availability for the new time.')
               ->action('View Interview Details', route('recruitment.interviews.show', $this->interview->id))
               ->line('Thank you for your flexibility and understanding!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->application_id,
            'title' => 'Interview Rescheduled',
            'message' => $this->getDatabaseMessage(),
            'url' => route('recruitment.interviews.show', $this->interview->id),
            'route' => route('recruitment.interviews.show', $this->interview->id),
            'reason' => $this->reason,
            'original_scheduled_at' => $this->originalScheduledAt->toISOString(),
            'new_scheduled_at' => $this->interview->scheduled_at->toISOString(),
            'time_change' => $this->getTimeChange(),
            'type' => 'interview_rescheduled',
            'action_url' => route('recruitment.interviews.show', $this->interview->id)
        ];
    }

    /**
     * Get database message
     */
    private function getDatabaseMessage(): string
    {
        $application = $this->interview->application;
        $candidateName = $application->candidate_name ?? $application->user?->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';
        
        $newTime = $this->interview->scheduled_at->format('M j, g:i A');
        return "Interview rescheduled for {$candidateName} - {$jobTitle} to {$newTime}";
    }

    /**
     * Get time change description
     */
    private function getTimeChange(): string
    {
        $timeDiff = $this->originalScheduledAt->diffForHumans($this->interview->scheduled_at, true);
        
        if ($this->interview->scheduled_at->greaterThan($this->originalScheduledAt)) {
            return "moved {$timeDiff} later";
        } else {
            return "moved {$timeDiff} earlier";
        }
    }
}
