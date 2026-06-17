<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Application;

class ApplicationReceivedNotification extends Notification implements ShouldQueue
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
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];
        
        // Only use database channel if notifiable is an Eloquent model (has morphMany)
        if ($notifiable instanceof \Illuminate\Database\Eloquent\Model) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $candidateName = $this->application->candidate_name ?? $this->application->user->name ?? 'Candidate';
        
        return (new MailMessage)
            ->subject('Application Received - ' . $this->application->job->title)
            ->greeting('Hello ' . $candidateName . '!')
            ->line('Thank you for your interest in the position of **' . $this->application->job->title . '** at our company.')
            ->line('We have successfully received your application. Your Application ID is: **#' . $this->application->id . '**')
            ->line('Our recruitment team will review it shortly. You can use your Application ID and email address to track your progress.')
            ->line('**Application Summary:**')
            ->line('Position: ' . $this->application->job->title)
            ->line('Applied on: ' . $this->application->created_at->format('M d, Y'))
            ->action('Track Your Application', route('career.track-application'))
            ->line('Thank you for your interest in joining our team!')
            ->salutation('Best regards, The Recruitment Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Application Received',
            'message' => 'Your application for ' . ($this->application->job->title ?? 'Position') . ' has been received.',
            'url' => route('career.track-application'),
            'route' => route('career.track-application'),
            'action_url' => route('career.track-application'),
            'application_id' => $this->application->id,
            'job_title' => $this->application->job->title,
            'candidate_name' => $this->application->candidate_name ?? $this->application->user->name ?? 'Candidate',
            'applied_at' => $this->application->created_at->toDateTimeString(),
            'type' => 'application_received'
        ];
    }
}
