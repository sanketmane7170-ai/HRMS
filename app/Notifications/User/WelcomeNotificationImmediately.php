<?php

namespace App\Notifications\User;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WelcomeNotificationImmediately extends Notification
{
    protected string $employee_id;
    protected string $email;
    protected string $phone;
    protected string $password;
    protected ?string $unique_code;

    public function __construct(string $employee_id,string $email,string $phone, string $password, ?string $unique_code = null)
    {
        $this->employee_id = $employee_id;
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
        $this->unique_code = $unique_code;

        Log::info('[WelcomeNotificationImmediately] Instance created.', [
            'employee_id' => $employee_id,
            'email' => $email,
            'phone' => $phone,
            'has_unique_code' => $unique_code ? true : false,
        ]);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Do NOT log real passwords in production. Only a placeholder above.
        Log::info('[WelcomeNotificationImmediately] Preparing email for user_id: ' . ($notifiable->id ?? 'unknown'));

        return (new MailMessage)
            ->subject('Welcome to ' . getSetting('site_title'))
            ->view('notifications.welcome', [
                'employee_id' => $this->employee_id,
                'email' => $this->email,
                'phone' => $this->phone,
                'password'    => $this->password,
                'unique_code' => $this->unique_code,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'employee_id' => $this->employee_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'unique_code' => $this->unique_code,
        ];
    }
}
