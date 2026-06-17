<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendOtpNotification extends Notification
{
    use Queueable;

    protected int $code;
    protected string $viaChannel;

    public function __construct(int $code, string $viaChannel = 'mail')
    {
        $this->code = $code;
        $this->viaChannel = $viaChannel;
    }

    public function via($notifiable)
    {
        // return ['mail', 'database']; // add channels as configured
        return [$this->viaChannel];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your OTP code')
            ->line('Your login OTP is: ' . $this->code)
            ->line('This code will expire in 5 minutes.')
            ->line('If you did not request this, ignore this message.');
    }

    // Example SMS channel (if you've set up Nexmo/Twilio)
    // public function toNexmo($notifiable) { ... }
}
