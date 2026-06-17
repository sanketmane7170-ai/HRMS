<?php

namespace Modules\Resignation\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when resignation status changes
 * Author: Sanket
 */
class ResignationStatusChangedNotification extends Notification
{
    use Queueable;

    protected $resignation;
    protected $oldStatus;
    protected $newStatus;
    protected $changedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct($resignation, $oldStatus, $newStatus, $changedBy = null)
    {
        $this->resignation = $resignation;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
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
        $statusMessages = [
            'pending' => 'Your resignation is under review',
            'approved' => 'Your resignation has been approved',
            'rejected' => 'Your resignation has been rejected',
            'withdrawn' => 'Your resignation has been withdrawn',
            'completed' => 'Your resignation process is complete'
        ];

        $message = $statusMessages[$this->newStatus] ?? 'Your resignation status has been updated';

        $mail = (new MailMessage)
            ->subject('Resignation Status Update - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your resignation status has been updated.')
            ->line('')
            ->line('**Status Change:**')
            ->line('From: **' . ucfirst($this->oldStatus) . '**')
            ->line('To: **' . ucfirst($this->newStatus) . '**');

        if ($this->changedBy) {
            $mail->line('Updated by: ' . $this->changedBy->name);
        }

        $mail->line('')
            ->line($message);

        // Status-specific information
        switch ($this->newStatus) {
            case 'approved':
                $mail->line('')
                    ->line('**Next Steps:**')
                    ->line('• Complete pending work assignments')
                    ->line('• Hand over responsibilities')
                    ->line('• Return company assets')
                    ->line('• Schedule exit interview');
                break;

            case 'rejected':
                if ($this->resignation->rejection_reason) {
                    $mail->line('')
                        ->line('**Reason:**')
                        ->line($this->resignation->rejection_reason);
                }
                break;

            case 'completed':
                $mail->line('')
                    ->line('Thank you for your service at ' . config('app.name') . '.')
                    ->line('We wish you all the best in your future endeavors.');
                break;
        }

        $mail->line('')
            ->action('View Resignation Details', route('resignation.employee'))
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'resignation_status_changed',
            'resignation_id' => $this->resignation->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_by' => $this->changedBy ? $this->changedBy->name : 'System',
            'message' => 'Resignation status changed from ' . $this->oldStatus . ' to ' . $this->newStatus,
            'action_url' => route('resignation.employee')
        ];
    }
}
