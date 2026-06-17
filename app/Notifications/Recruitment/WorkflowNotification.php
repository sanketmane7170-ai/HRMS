<?php

namespace App\Notifications\Recruitment;

use Modules\Recruitment\Entities\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Application $application;
    protected string $message;
    protected string $workflowId;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application, string $message, string $workflowId)
    {
        $this->application = $application;
        $this->message = $message;
        $this->workflowId = $workflowId;
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
        $jobTitle = $this->application->job?->title ?? 'Position';
        $candidateName = $this->application->candidate_name ?? 'Candidate';
        
        return (new MailMessage)
            ->subject("Workflow Alert: {$candidateName} - {$jobTitle}")
            ->greeting('Workflow Notification')
            ->line($this->message)
            ->line("**Application Details:**")
            ->line("Candidate: {$candidateName}")
            ->line("Position: {$jobTitle}")
            ->line("Current Stage: {$this->application->formatted_stage}")
            ->action('View Application', route('recruitment.applications.show', $this->application->id))
            ->line('This notification was generated automatically by your recruitment workflow.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'workflow_id' => $this->workflowId,
            'title' => 'Workflow Notification',
            'message' => $this->message,
            'url' => route('recruitment.applications.show', $this->application->id),
            'route' => route('recruitment.applications.show', $this->application->id),
            'action_url' => route('recruitment.applications.show', $this->application->id),
            'candidate_name' => $this->application->candidate_name,
            'job_title' => $this->application->job?->title,
            'type' => 'workflow_notification'
        ];
    }
}
