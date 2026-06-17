<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Offer;

class OfferCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $offer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Draft Job Offer - ' . $this->offer->position)
                    ->greeting('Hello!')
                    ->line('A draft job offer has been created for you for the position of ' . $this->offer->position . '.')
                    ->line('Salary: ' . number_format($this->offer->salary, 2) . ' ' . ($this->offer->currency ?? 'USD'))
                    ->line('This is for your information. You will receive an official notification once the offer is finalized and sent.')
                    ->action('View Progress', route('backend.employee.applications.show', $this->offer->application_id));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Job Offer Prepared',
            'message' => 'A job offer for ' . $this->offer->position . ' is being prepared for you.',
            'icon' => 'fas fa-file-invoice-dollar',
            'color' => 'info',
            'type' => 'offer_created',
            'offer_id' => $this->offer->id,
            'application_id' => $this->offer->application_id,
            'position' => $this->offer->position,
            'salary' => $this->offer->salary,
            'currency' => $this->offer->currency ?? 'USD',
            'joining_date' => $this->offer->joining_date,
            'response_deadline' => $this->offer->response_deadline,
            'action_url' => route('backend.employee.applications.show', $this->offer->application_id),
            'url' => route('backend.employee.applications.show', $this->offer->application_id),
            'route' => route('backend.employee.applications.show', $this->offer->application_id)
        ];
    }
}
