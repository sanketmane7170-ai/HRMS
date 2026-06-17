<?php

namespace App\Notifications\Recruitment;

use Modules\Recruitment\Entities\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Interview $interview;
    protected string $reason;
    protected bool $reschedule;

    /**
     * Create a new notification instance.
     *
     * @param Interview $interview
     * @param string $reason
     * @param bool $reschedule
     */
    public function __construct(Interview $interview, string $reason = '', bool $reschedule = false)
    {
        $this->interview = $interview;
        $this->reason = $reason;
        $this->reschedule = $reschedule;
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
        $application = $this->interview->application;
        $candidateName = $application->candidate_name ?? $application->user->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';
        
        $subject = $this->reschedule 
            ? "Interview Rescheduled: {$jobTitle}"
            : "Interview Cancelled: {$jobTitle}";

        $greeting = $this->reschedule 
            ? "Interview Rescheduled"
            : "Interview Cancelled";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($this->getMainMessage($candidateName, $jobTitle))
            ->line('**Original Interview Details:**')
            ->line("👤 **Candidate:** {$candidateName}")
            ->line("💼 **Position:** {$jobTitle}")
            ->line("📅 **Scheduled Date:** {$this->interview->scheduled_at->format('l, F j, Y \a\t g:i A')}")
            ->line("⏱️ **Duration:** {$this->interview->duration_text}")
            ->line("📍 **Type:** {$this->interview->type_text}");

        if ($this->interview->location) {
            $message->line("🏢 **Location:** {$this->interview->location}");
        }

        if ($this->reason) {
            $reasonLabel = $this->reschedule ? 'Reason for Rescheduling' : 'Reason for Cancellation';
            $message->line("**{$reasonLabel}:**")
                   ->line($this->reason);
        }

        if ($this->reschedule) {
            $message->line('We will contact you shortly with new available time slots.')
                   ->line('Thank you for your flexibility and understanding.');
        } else {
            $message->line('We apologize for any inconvenience this may have caused.')
                   ->line('Your application is still active and under consideration.');
        }

        $message->action('View Application Status', route('recruitment.applications.show', $this->interview->application_id))
               ->line('If you have any questions, please feel free to contact us.');

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
            'title' => $this->reschedule ? 'Interview Rescheduled' : 'Interview Cancelled',
            'message' => $this->getDatabaseMessage(),
            'url' => route('recruitment.applications.show', $this->interview->application_id),
            'route' => route('recruitment.applications.show', $this->interview->application_id),
            'reason' => $this->reason,
            'reschedule' => $this->reschedule,
            'original_scheduled_at' => $this->interview->scheduled_at->toISOString(),
            'type' => $this->reschedule ? 'interview_rescheduled' : 'interview_cancelled',
            'action_url' => route('recruitment.applications.show', $this->interview->application_id)
        ];
    }

    /**
     * Get the main message for the notification
     */
    private function getMainMessage(string $candidateName, string $jobTitle): string
    {
        if ($this->reschedule) {
            return "We need to reschedule the interview for {$candidateName} applying for the {$jobTitle} position.";
        }
        
        return "Unfortunately, we need to cancel the interview for {$candidateName} applying for the {$jobTitle} position.";
    }

    /**
     * Get database message
     */
    private function getDatabaseMessage(): string
    {
        $application = $this->interview->application;
        $candidateName = $application->candidate_name ?? $application->user->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';
        
        $action = $this->reschedule ? 'rescheduled' : 'cancelled';
        return "Interview {$action} for {$candidateName} - {$jobTitle}";
    }
}
