<?php
namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        Log::info('[WelcomeNotification] Instance created.');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        Log::info('[WelcomeNotification] Determining delivery channels.', [
            'user_id'     => $notifiable->id ?? null,
            'email'       => $notifiable->email ?? null,
            'employee_id' => $notifiable->employee_id ?? null,
            'phone'       => $notifiable->phone ?? null,
        ]);

        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::info('[WelcomeNotification] Preparing email.', [
            'user_id'     => $notifiable->id ?? null,
            'email'       => $notifiable->email ?? null,
            'employee_id' => $notifiable->employee_id ?? null,
            'phone'       => $notifiable->phone ?? null,
        ]);
        $response = Http::asForm()->post('https://superadmin.WorkPilot.io/api/v1/portal_data', [
            'subdomain' => request()->getHost(),
        ]);

        $unique_code = null;
        if ($response->successful()) {
            // safer extraction
            $unique_code = data_get($response->json(), 'data.data.unique_code');
        } else {
            Log::error('Failed to fetch portal data', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        $email       = $notifiable->email;
        $employee_id = $notifiable->employee_id;
        $phone       = $notifiable->phone;
        $password    = "Welcome" . date('Y');

        $notifiable->password = Hash::make($password);
        $notifiable->save();

        Log::info('[WelcomeNotification] Email data prepared.', [
            'email'       => $email,
            'employee_id' => $employee_id,
            'phone'       => $phone,
            'password'    => $password,
            'unique_code' => $unique_code,
        ]);

        $mailMessage = (new MailMessage)
            ->subject('Welcome to ' . getSetting('site_title'))
            ->view('notifications.welcome', compact('unique_code', 'email', 'employee_id', 'phone', 'password'));

        Log::info('[WelcomeNotification] MailMessage object created successfully.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        Log::info('[WelcomeNotification] toArray called.', [
            'user_id'     => $notifiable->id ?? null,
            'email'       => $notifiable->email ?? null,
            'employee_id' => $notifiable->employee_id ?? null,
            'phone'       => $notifiable->phone ?? null,
        ]);

        return [];
    }
}
