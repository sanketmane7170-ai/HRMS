<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Offer;
use Carbon\Carbon;

class OfferSentNotification extends Notification implements ShouldQueue
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
        if ($notifiable instanceof \App\Models\Recruitment\ExternalCandidate) {
            return ['mail'];
        }
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $candidateName = $notifiable->name ?? 'Candidate';
        $application = $this->offer->application;
        
        $mailMessage = (new MailMessage)
                    ->subject('Official Job Offer - ' . $this->offer->position)
                    ->greeting('Hello ' . $candidateName . '!')
                    ->line('We are excited to extend an official job offer to you for the position of **' . $this->offer->position . '**.')
                    ->line('**Salary:** ' . number_format($this->offer->salary, 2) . ' ' . ($this->offer->currency ?? 'USD'))
                    ->line('**Proposed Start Date:** ' . ($this->offer->start_date ? $this->offer->start_date->format('M d, Y') : ($this->offer->joining_date ? $this->offer->joining_date->format('M d, Y') : 'TBD')))
                    ->line('Please review the full offer details and let us know your decision by ' . 
                           ($this->offer->response_deadline ? 
                            Carbon::parse($this->offer->response_deadline)->format('M d, Y') : 
                            'the specified deadline') . '.');

        if ($notifiable instanceof \App\Models\Recruitment\ExternalCandidate) {
            $mailMessage->action('Track Application & View Details', route('career.track-application'))
                        ->line('Your Application ID is: **#' . $this->offer->application_id . '**');
        } else {
            $mailMessage->action('View & Respond to Offer', route('backend.employee.applications.show', $this->offer->application_id));
        }

        return $mailMessage->line('We are looking forward to the possibility of you joining our team!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Official Job Offer Received',
            'message' => 'You have received an official job offer for ' . $this->offer->position . '. Please review and respond.',
            'icon' => 'fas fa-gift',
            'color' => 'success',
            'type' => 'offer_sent',
            'offer_id' => $this->offer->id,
            'application_id' => $this->offer->application_id,
            'position' => $this->offer->position,
            'action_url' => route('backend.employee.applications.show', $this->offer->application_id),
            'url' => route('backend.employee.applications.show', $this->offer->application_id),
            'route' => route('backend.employee.applications.show', $this->offer->application_id)
        ];
    }
}
