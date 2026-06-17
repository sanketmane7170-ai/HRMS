<?php

namespace Modules\Recruitment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when application is rejected
 * Author: Sanket
 */
class ApplicationRejectedNotification extends Notification
{
    use Queueable;

    protected $application;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($application, $reason = null)
    {
        $this->application = $application;
        $this->reason = $reason;
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
        $job = $this->application->job;

        return (new MailMessage)
            ->subject('Application Update - ' . $job->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for your interest in the position of **' . $job->title . '** at ' . config('app.name') . '.')
            ->line('')
            ->line('After careful consideration of your application, we regret to inform you that we have decided to move forward with other candidates whose qualifications more closely match our current needs.')
            ->line('')
            ->line('**Application Details:**')
            ->line('Position: ' . $job->title)
            ->line('Application ID: #' . $this->application->id)
            ->line('Status: Closed')
            ->line('')
            ->line('**We Encourage You To:**')
            ->line('• Keep an eye on our careers page for future opportunities')
            ->line('• Apply for other positions that match your skills')
            ->line('• Connect with us on professional networks')
            ->line('')
            ->line('We appreciate the time and effort you invested in your application and wish you the very best in your job search.')
            ->line('')
            ->action('View Other Opportunities', route('recruitment.jobs.index'))
            ->line('Thank you again for considering ' . config('app.name') . ' as your potential employer.')
            ->salutation('Best regards, ' . config('app.name') . ' Recruitment Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'application_rejected',
            'application_id' => $this->application->id,
            'job_title' => $this->application->job->title ?? 'N/A',
            'job_id' => $this->application->job_id,
            'reason' => $this->reason,
            'message' => 'Your application was not successful',
            'action_url' => route('recruitment.jobs.index')
        ];
    }
}
