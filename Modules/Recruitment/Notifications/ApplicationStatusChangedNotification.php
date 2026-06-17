<?php

namespace Modules\Recruitment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when application status changes
 * Author: Sanket
 */
class ApplicationStatusChangedNotification extends Notification
{
    use Queueable;

    protected $application;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($application, $oldStatus, $newStatus)
    {
        $this->application = $application;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $statusMessages = [
            'applied' => 'Your application has been received and is under review.',
            'screening' => 'Your application is being screened by our team.',
            'interview' => 'Congratulations! You have been shortlisted for an interview.',
            'offer' => 'Great news! We would like to extend an offer to you.',
            'hired' => 'Welcome aboard! Your hiring process is complete.',
            'rejected' => 'Thank you for your interest. Unfortunately, we have decided to move forward with other candidates.'
        ];

        $message = $statusMessages[$this->newStatus] ?? 'Your application status has been updated.';

        $mail = (new MailMessage)
            ->subject('Application Status Update - ' . $job->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your application for **' . $job->title . '** has been updated.')
            ->line('')
            ->line('**Status:** ' . ucfirst(str_replace('_', ' ', $this->newStatus)))
            ->line($message)
            ->line('');

        if ($this->newStatus === 'interview') {
            $mail->line('You will receive a separate email with interview details shortly.');
        } elseif ($this->newStatus === 'offer') {
            $mail->line('Please check your email for the offer letter.');
        } elseif ($this->newStatus === 'rejected') {
            $mail->line('We encourage you to apply for other positions that match your skills.');
        }

        $mail->action('View Application', route('recruitment.applications.show', $this->application->id))
            ->salutation('Best regards, ' . config('app.name') . ' Recruitment Team');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'application_status_changed',
            'application_id' => $this->application->id,
            'job_title' => $this->application->job->title ?? 'N/A',
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => 'Your application status has been updated to ' . $this->newStatus,
            'action_url' => route('recruitment.applications.show', $this->application->id)
        ];
    }
}
