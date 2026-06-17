<?php

namespace App\Notifications\Leave;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class GenerateNotification extends Notification
{
    use Queueable;

    protected $userData;
    protected $adminId;

    /**
     * Create a new notification instance.
     */
    public function __construct($userData,$adminId)
    {
        $this->userData = $userData;
        $this->adminId = $adminId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'admin_id' => $this->adminId,
            'user_id' => $this->userData['id'],
            'name' => $this->userData['name'],
            'email' => $this->userData['email'],
            'message' => $this->userData['message'],
            'route' => $this->userData['route'],
            'time' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
