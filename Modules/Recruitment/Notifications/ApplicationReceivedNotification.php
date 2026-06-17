<?php

namespace Modules\Recruitment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when application is received
 * Author: Sanket
 */
class ApplicationReceivedNotification extends Notification
{
    use Queueable;

    protected $application;

    /**
     * Create a new notification instance.
     */
    public function __construct($application)
    {
        $this->application = $application;
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
            ->subject('Application Received - ' . $job->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for applying for the position of **' . $job->title . '** at ' . config('app.name') . '.')
            ->line('')
            ->line('**Application Details:**')
            ->line('Position: ' . $job->title)
            ->line('Department: ' . ($job->department->name ?? 'N/A'))
            ->line('Application ID: #' . $this->application->id)
            ->line('Submitted: ' . $this->application->created_at->format('F j, Y g:i A'))
            ->line('')
            ->line('**What Happens Next:**')
            ->line('1. Our recruitment team will review your application')
            ->line('2. Shortlisted candidates will be contacted for interviews')
            ->line('3. You can track your application status anytime')
            ->line('')
            ->line('**Timeline:**')
            ->line('We typically review applications within 5-7 business days.')
            ->line('')
            ->action('Track Application Status', route('recruitment.applications.track', $this->application->id))
            ->line('We appreciate your interest in joining our team!')
            ->salutation('Best regards, ' . config('app.name') . ' Recruitment Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'application_received',
            'application_id' => $this->application->id,
            'job_title' => $this->application->job->title ?? 'N/A',
            'job_id' => $this->application->job_id,
            'submitted_at' => $this->application->created_at,
            'message' => 'Your application has been received',
            'action_url' => route('recruitment.applications.track', $this->application->id)
        ];
    }
}
