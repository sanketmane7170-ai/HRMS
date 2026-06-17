<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Application;

class ApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $application;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
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
        return (new MailMessage)
            ->subject('New Job Application Received')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new job application has been submitted.')
            ->line('**Job Position:** ' . $this->application->job->title)
            ->line('**Applicant:** ' . ($this->application->user->name ?? $this->application->candidate_name))
            ->line('**Department:** ' . ($this->application->job->department->name ?? 'N/A'))
            ->line('**Applied On:** ' . $this->application->created_at->format('M d, Y \a\t g:i A'))
            ->action('Review Application', route('recruitment.applications.show', $this->application->id))
            ->line('Please review the application at your earliest convenience.');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Job Application',
            'message' => ($this->application->user->name ?? $this->application->candidate_name) . ' applied for ' . $this->application->job->title,
            'url' => route('recruitment.applications.show', $this->application->id),
            'route' => route('recruitment.applications.show', $this->application->id),
            'action_url' => route('recruitment.applications.show', $this->application->id),
            'application_id' => $this->application->id,
            'job_title' => $this->application->job->title,
            'applicant_name' => ($this->application->user->name ?? $this->application->candidate_name),
            'type' => 'application_submitted',
            'icon' => 'fas fa-file-alt',
            'color' => 'primary'
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
