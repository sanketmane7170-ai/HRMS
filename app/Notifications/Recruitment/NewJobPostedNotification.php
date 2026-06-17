<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Job;

class NewJobPostedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $job;

    /**
     * Create a new notification instance.
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
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
            ->subject('New Job Opening - ' . $this->job->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new job position has been posted that might interest you.')
            ->line('**Job Title:** ' . $this->job->title)
            ->line('**Department:** ' . $this->job->department->name)
            ->line('**Job Type:** ' . ucfirst(str_replace('_', ' ', $this->job->job_type)))
            ->when($this->job->experience_level, function($message) {
                return $message->line('**Experience Level:** ' . ucfirst($this->job->experience_level));
            })
            ->when($this->job->location, function($message) {
                return $message->line('**Location:** ' . $this->job->location);
            })
            ->when($this->job->min_salary && $this->job->max_salary, function($message) {
                return $message->line('**Salary Range:** $' . number_format($this->job->min_salary) . ' - $' . number_format($this->job->max_salary));
            })
            ->when($this->job->remote_work, function($message) {
                return $message->line('**Remote Work:** Available');
            })
            ->line('**Job Description:**')
            ->line(strip_tags($this->job->description))
            ->action('View Job Details', route('career.job-detail', $this->job->id))
            ->line('Don\'t miss this opportunity to advance your career!')
            ->salutation('Best regards, HR Team');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Job Opening',
            'name' => 'New Job Opening',
            'message' => 'A new job position "' . $this->job->title . '" has been posted in ' . $this->job->department->name . ' department.',
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
            'department' => $this->job->department->name,
            'job_type' => $this->job->job_type,
            'experience_level' => $this->job->experience_level,
            'location' => $this->job->location,
            'remote_work' => $this->job->remote_work,
            'application_deadline' => $this->job->application_deadline?->format('Y-m-d'),
            'url' => route('career.job-detail', $this->job->id),
            'route' => route('career.job-detail', $this->job->id),
            'action_url' => route('career.job-detail', $this->job->id),
            'time' => now()->diffForHumans(),
            'type' => 'job_posted',
            'icon' => 'fas fa-briefcase',
            'color' => 'success'
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
