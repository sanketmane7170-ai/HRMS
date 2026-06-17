<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomRecruitmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $subject;
    protected string $content;
    protected string $templateType;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $subject, string $content, string $templateType = 'custom_message')
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->templateType = $templateType;
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
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello!')
            ->line($this->content)
            ->line('Thank you for your interest in our company.')
            ->salutation('Best regards, The Recruitment Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->subject,
            'message' => $this->content,
            'url' => '#',
            'route' => '#',
            'template_type' => $this->templateType,
            'type' => 'recruitment_communication'
        ];
    }
}
