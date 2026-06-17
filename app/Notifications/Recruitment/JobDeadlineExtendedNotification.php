<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Recruitment\Entities\Job;

class JobDeadlineExtendedNotification extends Notification
{
    use Queueable;

    protected $job;
    protected $newDeadline;

    /**
     * Create a new notification instance.
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
        // Ensure we format the date
        $this->newDeadline = $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'extended indefinitely';
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        $channels = ['mail'];
        if ($notifiable instanceof \Illuminate\Database\Eloquent\Model) {
            $channels[] = 'database';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Application Deadline Extended - ' . $this->job->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Great news! The application deadline for the "' . $this->job->title . '" position has been extended.')
            ->line('The new deadline is: **' . $this->newDeadline . '**.')
            ->line('If you have already applied, no action is needed. Your application is still valid.')
            ->line('If you know someone perfect for this role, there is still time for them to apply!')
            ->action('View Job Details', route('career.job-detail', $this->job->id))
            ->line('Thank you for your interest in ' . config('app.name', 'WorkPilot') . '.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Deadline Extended',
            'message' => 'The deadline for "' . $this->job->title . '" is now ' . $this->newDeadline . '.',
            'job_id' => $this->job->id,
            'new_deadline' => $this->newDeadline,
            'action_url' => route('career.job-detail', $this->job->id),
            'url' => route('career.job-detail', $this->job->id),
            'route' => route('career.job-detail', $this->job->id),
            'type' => 'deadline_extended',
            'icon' => 'fas fa-clock',
            'color' => 'info'
        ];
    }
}
