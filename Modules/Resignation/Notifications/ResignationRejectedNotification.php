<?php

namespace Modules\Resignation\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when a resignation is rejected
 * Author: Sanket
 */
class ResignationRejectedNotification extends Notification
{
    use Queueable;

    protected $resignation;
    protected $rejector;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($resignation, $rejector = null, $reason = null)
    {
        $this->resignation = $resignation;
        $this->rejector = $rejector;
        $this->reason = $reason ?? 'No reason provided';
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Resignation Not Approved - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We have reviewed your resignation request submitted on ' . $this->resignation->created_at->format('F j, Y') . '.')
            ->line('')
            ->line('After careful consideration, we are **unable to approve** your resignation at this time.')
            ->line('')
            ->line('**Reason:**')
            ->line($this->reason)
            ->line('')
            ->line('**Next Steps:**')
            ->line('Please schedule a meeting with your manager or HR to discuss this matter further.')
            ->line('')
            ->action('View Resignation Details', route('resignation.show', $this->resignation->id))
            ->line('If you have any questions or concerns, please contact HR.')
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'resignation_rejected',
            'resignation_id' => $this->resignation->id,
            'rejected_by' => $this->rejector->name ?? 'HR',
            'reason' => $this->reason,
            'message' => 'Your resignation request was not approved',
            'action_url' => route('resignation.show', $this->resignation->id)
        ];
    }
}
