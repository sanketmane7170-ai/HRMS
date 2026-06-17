<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Offer;

class OfferResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $offer;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(Offer $offer, $status)
    {
        $this->offer = $offer;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Offer ' . ucfirst($this->status) . ': ' . $this->offer->position)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->offer->application->user->name . ' has ' . $this->status . ' the job offer for ' . $this->offer->position . '.')
            ->action('View Details', route('recruitment.offers.show', $this->offer->id))
            ->line('Please take the necessary next steps.');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Offer ' . ucfirst($this->status),
            'message' => $this->offer->application->user->name . ' has ' . $this->status . ' the job offer for ' . $this->offer->position,
            'url' => route('recruitment.offers.show', $this->offer->id),
            'route' => route('recruitment.offers.show', $this->offer->id),
            'action_url' => route('recruitment.offers.show', $this->offer->id),
            'offer_id' => $this->offer->id,
            'application_id' => $this->offer->application_id,
            'employee_name' => $this->offer->application->user->name,
            'position' => $this->offer->position,
            'status' => $this->status,
            'type' => 'offer_response'
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
