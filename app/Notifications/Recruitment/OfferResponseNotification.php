<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $candidateName = $this->offer->application->user->name ?? $this->offer->application->candidate_name ?? 'Candidate';
        $statusText = ucfirst($this->status);
        
        return (new MailMessage)
                    ->subject('Offer Response: ' . $statusText . ' - ' . $this->offer->position)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('A candidate has responded to their job offer.')
                    ->line('**Candidate:** ' . $candidateName)
                    ->line('**Position:** ' . $this->offer->position)
                    ->line('**Response:** ' . $statusText)
                    ->action('View Application', route('recruitment.applications.show', $this->offer->application_id))
                    ->line('Thank you for using our recruitment system!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $candidateName = $this->offer->application->user->name ?? $this->offer->application->candidate_name ?? 'Candidate';
        
        return [
            'title' => 'Job Offer ' . ucfirst($this->status),
            'name' => 'Job Offer ' . ucfirst($this->status),
            'message' => $candidateName . ' has ' . $this->status . ' the job offer for ' . $this->offer->position . '.',
            'icon' => $this->status === 'accepted' ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'color' => $this->status === 'accepted' ? 'success' : 'danger',
            'type' => 'offer_response',
            'offer_id' => $this->offer->id,
            'application_id' => $this->offer->application_id,
            'candidate_name' => $candidateName,
            'status' => $this->status,
            'route' => route('recruitment.applications.show', $this->offer->application_id),
            'url' => route('recruitment.applications.show', $this->offer->application_id),
            'action_url' => route('recruitment.applications.show', $this->offer->application_id),
            'time' => now()->diffForHumans()
        ];
    }
}
