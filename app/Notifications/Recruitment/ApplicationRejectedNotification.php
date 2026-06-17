<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Application;

class ApplicationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $application;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application, $reason = null)
    {
        $this->application = $application;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof \App\Models\Recruitment\ExternalCandidate) {
            return ['mail'];
        }
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $candidateName = $this->application->candidate_name ?? $notifiable->name ?? 'Candidate';
        $jobTitle = $this->application->job->title ?? 'Position';
        
        $message = (new MailMessage)
            ->subject('Update on your application for ' . $jobTitle)
            ->greeting('Hello ' . $candidateName . '!')
            ->line('Thank you for giving us the opportunity to review your application for the ' . $jobTitle . ' position.')
            ->line('After careful consideration, we have decided not to move forward with your application at this time.');

        if ($this->reason) {
            $message->line('**Feedback:** ' . $this->reason);
        }

        $message->line('We appreciate your interest in our company and wish you the best of luck in your job search.')
            ->salutation('Best regards, The Recruitment Team');

        return $message;
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Application Update',
            'message' => 'Your application for ' . ($this->application->job->title ?? 'Position') . ' has been reviewed.',
            'url' => route('career.track-application'),
            'route' => route('career.track-application'),
            'action_url' => route('career.track-application'),
            'application_id' => $this->application->id,
            'job_title' => $this->application->job->title,
            'status' => 'rejected',
            'type' => 'application_rejected'
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
