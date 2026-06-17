<?php

namespace App\Notifications\Recruitment;

use Modules\Recruitment\Entities\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidatePortalInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    protected Application $application;
    protected string $portalUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application, string $portalUrl)
    {
        $this->application = $application;
        $this->portalUrl = $portalUrl;
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $candidateName = $this->application->candidate_name ?? $notifiable->name ?? 'Candidate';
        $jobTitle = $this->application->job?->title ?? 'Position';
        
        return (new MailMessage)
            ->subject("Track Your Application - {$jobTitle}")
            ->greeting("Hello {$candidateName}!")
            ->line("Thank you for applying to the {$jobTitle} position at " . config('app.name') . ".")
            ->line('We have created a personalized candidate portal where you can:')
            ->line('• Track your application status in real-time')
            ->line('• View upcoming interviews and important dates')
            ->line('• Upload additional documents if needed')
            ->line('• Update your profile information')
            ->line('• Receive feedback and next steps')
            ->action('Access Your Candidate Portal', $this->portalUrl)
            ->line('This portal link is secure and personalized for your application.')
            ->line('You can bookmark this link for easy access throughout the recruitment process.')
            ->line('If you have any questions, please feel free to contact our recruitment team.')
            ->salutation('Best regards, The Recruitment Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'portal_url' => $this->portalUrl,
            'url' => route('career.track-application'),
            'route' => route('career.track-application'),
            'action_url' => route('career.track-application'),
            'job_title' => $this->application->job?->title,
            'title' => 'Candidate Portal Access',
            'message' => 'You can now track your application for ' . ($this->application->job?->title ?? 'Position') . ' via your candidate portal.',
            'type' => 'candidate_portal_invitation'
        ];
    }
}
