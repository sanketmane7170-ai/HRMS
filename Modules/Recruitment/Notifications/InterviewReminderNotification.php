<?php

namespace Modules\Recruitment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

/**
 * Notification sent 24 hours before an interview
 * Author: Sanket
 */
class InterviewReminderNotification extends Notification
{
    use Queueable;

    protected $interview;

    /**
     * Create a new notification instance.
     */
    public function __construct($interview)
    {
        $this->interview = $interview;
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
        $hoursUntil = Carbon::now()->diffInHours($scheduledAt);

        return (new MailMessage)
            ->subject('Interview Reminder - Tomorrow at ' . $scheduledAt->format('g:i A'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a friendly reminder about your upcoming interview.')
            ->line('')
            ->line('**Interview Details:**')
            ->line('Position: **' . $job->title . '**')
            ->line('📅 Date: ' . $scheduledAt->format('l, F j, Y'))
            ->line('🕐 Time: ' . $scheduledAt->format('g:i A'))
            ->line('⏱️ Duration: ' . ($this->interview->duration_minutes ?? 60) . ' minutes')
            ->line('Type: ' . ucfirst(str_replace('_', ' ', $this->interview->type)))
            ->line('Location/Link: ' . $this->interview->location)
            ->line('Interviewer: ' . ($this->interview->interviewer->name ?? 'TBD'))
            ->line('')
            ->line('**Preparation Checklist:**')
            ->line('✓ Review the job description')
            ->line('✓ Research our company')
            ->line('✓ Prepare questions to ask')
            ->line('✓ Test your video/audio (if video interview)')
            ->line('✓ Have a copy of your resume ready')
            ->line('')
            ->action('View Interview Details', route('recruitment.interviews.show', $this->interview->id))
            ->line('Good luck! We look forward to speaking with you.')
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'interview_reminder',
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->application_id,
            'job_title' => $this->interview->application->job->title ?? 'N/A',
            'scheduled_at' => $this->interview->scheduled_at,
            'location' => $this->interview->location,
            'message' => 'Your interview is scheduled for tomorrow',
            'action_url' => route('recruitment.interviews.show', $this->interview->id)
        ];
    }
}
