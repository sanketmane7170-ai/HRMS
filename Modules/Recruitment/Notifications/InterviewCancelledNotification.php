<?php

namespace Modules\Recruitment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

/**
 * Notification sent when an interview is cancelled
 * Author: Sanket
 */
class InterviewCancelledNotification extends Notification
{
    use Queueable;

    protected $interview;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($interview, $reason = null)
    {
        $this->interview = $interview;
        $this->reason = $reason ?? 'No reason provided';
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $application = $this->interview->application;
        $job = $application->job;
        $scheduledAt = Carbon::parse($this->interview->scheduled_at);

        return (new MailMessage)
            ->subject('Interview Cancelled - ' . $job->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We regret to inform you that your interview for the position of **' . $job->title . '** has been cancelled.')
            ->line('')
            ->line('**Cancelled Interview Details:**')
            ->line('📅 Date: ' . $scheduledAt->format('l, F j, Y'))
            ->line('🕐 Time: ' . $scheduledAt->format('g:i A'))
            ->line('Type: ' . ucfirst(str_replace('_', ' ', $this->interview->type)))
            ->line('')
            ->line('**Reason:** ' . $this->reason)
            ->line('')
            ->line('We will contact you soon to discuss next steps.')
            ->action('View Application Status', route('recruitment.applications.show', $application->id))
            ->line('We apologize for any inconvenience this may have caused.')
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'interview_cancelled',
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->application_id,
            'job_title' => $this->interview->application->job->title ?? 'N/A',
            'scheduled_at' => $this->interview->scheduled_at,
            'reason' => $this->reason,
            'message' => 'Your interview has been cancelled',
            'action_url' => route('recruitment.applications.show', $this->interview->application_id)
        ];
    }
}
