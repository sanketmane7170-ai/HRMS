<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class JobDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $jobTitle;
    protected $companyName;

    /**
     * Create a new notification instance.
     * We pass string title instead of Job model because the job might be deleted.
     */
    public function __construct(string $jobTitle)
    {
        $this->jobTitle = $jobTitle;
        $this->companyName = config('app.name', 'WorkPilot');
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
            ->subject('Job Position Closed - ' . $this->jobTitle)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We are writing to inform you that the job position for "' . $this->jobTitle . '" at ' . $this->companyName . ' has been closed or removed.')
            ->line('This means the recruitment process for this specific role has ended.')
            ->line('We appreciate your interest in joining our team and encourage you to apply for other future openings that match your skills.')
            ->action('View Career Page', route('career.index'))
            ->line('Thank you for your understanding.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Job Position Closed',
            'message' => 'The job position "' . $this->jobTitle . '" is no longer available.',
            'job_title' => $this->jobTitle,
            'action_url' => route('career.index'),
            'url' => route('career.index'),
            'route' => route('career.index'),
            'type' => 'job_deleted',
            'icon' => 'fas fa-briefcase',
            'color' => 'danger'
        ];
    }
}
