<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Application;

class ApplicationStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $application;
    protected $oldStage;
    protected $newStage;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application, $oldStage, $newStage)
    {
        $this->application = $application;
        $this->oldStage = $oldStage;
        $this->newStage = $newStage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // External candidates don't have a database profile to store notifications
        if ($notifiable instanceof \App\Models\Recruitment\ExternalCandidate) {
            return ['mail'];
        }

        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusMessage = $this->getStatusMessage();
        
        $mailMessage = (new MailMessage)
            ->subject('Application Status Update - ' . $this->application->job->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your job application status has been updated.')
            ->line('**Job Position:** ' . $this->application->job->title)
            ->line('**Previous Status:** ' . ucfirst(str_replace('_', ' ', $this->oldStage)))
            ->line('**Current Status:** ' . ucfirst(str_replace('_', ' ', $this->newStage)))
            ->line($statusMessage);

        if ($notifiable instanceof \App\Models\Recruitment\ExternalCandidate) {
            $mailMessage->action('Track Application', route('career.track-application'))
                ->line('You can track your application status using your Application ID: **#' . $this->application->id . '** and your registered email address.');
        } else {
            $mailMessage->action('View Application', route('backend.employee.applications.show', $this->application->id));
        }

        return $mailMessage->line('Thank you for your interest in joining our team!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Application Status Updated',
            'name' => 'Application Status Updated',
            'message' => 'Your application for ' . $this->application->job->title . ' is now ' . ucfirst(str_replace('_', ' ', $this->newStage)),
            'action_url' => route('backend.employee.applications.show', $this->application->id),
            'url' => route('backend.employee.applications.show', $this->application->id),
            'route' => route('backend.employee.applications.show', $this->application->id),
            'time' => now()->diffForHumans(),
            'application_id' => $this->application->id,
            'job_title' => $this->application->job->title,
            'old_stage' => $this->oldStage,
            'new_stage' => $this->newStage,
            'type' => 'application_status_changed'
        ];
    }

    /**
     * Get appropriate message based on stage change
     */
    private function getStatusMessage(): string
    {
        switch ($this->newStage) {
            case 'screening':
                return 'Great news! Your application is now under review by our HR team.';
            case 'shortlisted':
                return 'Congratulations! You have been shortlisted for the next stage.';
            case 'interview':
                return 'Excellent! You have been selected for an interview. We will contact you soon with details.';
            case 'offer':
                return 'Fantastic news! We would like to extend a job offer to you.';
            case 'hired':
                return 'Welcome to the team! We are excited to have you on board.';
            case 'rejected':
                return 'Thank you for your interest. While we were impressed with your application, we have decided to move forward with other candidates. We encourage you to apply for future opportunities.';
            default:
                return 'Your application status has been updated. Please check your application portal for more details.';
        }
    }
}
