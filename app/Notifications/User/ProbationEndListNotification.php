<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProbationEndListNotification extends Notification
{
    use Queueable;

    public $records;
    /**
     * Create a new notification instance.
     */
    public function __construct($records)
    {
        $this->records = $records;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $records = $this->records;
        return (new MailMessage)
            ->subject("List of user completing probation in next 40 days")
            ->view('notifications.probation-end-list', compact('records'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
