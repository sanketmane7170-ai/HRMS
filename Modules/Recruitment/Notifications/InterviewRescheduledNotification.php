<?php

namespace Modules\Recruitment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

/**
 * Notification sent when an interview is rescheduled
 * Author: Sanket
 */
class InterviewRescheduledNotification extends Notification
{
    use Queueable;

    protected $interview;
    protected $oldScheduledAt;

    /**
     * Create a new notification instance.
     */
    public function __construct($interview, $oldScheduledAt)
    {
        $this->interview = $interview;
        $this->oldScheduledAt = $oldScheduledAt;
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
        $candidateName = $application->candidate_name ?? ($application->user->name ?? 'Candidate');
        
        $oldTime = Carbon::parse($this->oldScheduledAt);
        $newTime = Carbon::parse($this->interview->scheduled_at);

        return (new MailMessage)
            ->subject('Interview Rescheduled - ' . $job->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your interview for the position of **' . $job->title . '** has been rescheduled.')
            ->line('')
            ->line('**Previous Schedule:**')
            ->line('📅 ' . $oldTime->format('l, F j, Y'))
            ->line('🕐 ' . $oldTime->format('g:i A'))
            ->line('')
            ->line('**New Schedule:**')
            ->line('📅 ' . $newTime->format('l, F j, Y'))
            ->line('🕐 ' . $newTime->format('g:i A'))
            ->line('⏱️ Duration: ' . ($this->interview->duration_minutes ?? 60) . ' minutes')
            ->line('')
            ->line('**Interview Details:**')
            ->line('Type: ' . ucfirst(str_replace('_', ' ', $this->interview->type)))
            ->line('Location/Link: ' . $this->interview->location)
            ->line('Interviewer: ' . ($this->interview->interviewer->name ?? 'TBD'))
            ->line('')
            ->action('View Interview Details', route('recruitment.interviews.show', $this->interview->id))
            ->line('We apologize for any inconvenience caused by this change.')
            ->line('If you have any questions, please contact us.')
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'interview_rescheduled',
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->application_id,
            'job_title' => $this->interview->application->job->title ?? 'N/A',
            'old_scheduled_at' => $this->oldScheduledAt,
            'new_scheduled_at' => $this->interview->scheduled_at,
            'location' => $this->interview->location,
            'message' => 'Your interview has been rescheduled',
            'action_url' => route('recruitment.interviews.show', $this->interview->id)
        ];
    }
}
