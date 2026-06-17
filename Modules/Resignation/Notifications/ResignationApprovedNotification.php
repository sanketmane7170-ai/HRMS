<?php

namespace Modules\Resignation\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when a resignation is approved
 * Author: Sanket
 */
class ResignationApprovedNotification extends Notification
{
    use Queueable;

    protected $resignation;
    protected $approver;

    /**
     * Create a new notification instance.
     */
    public function __construct($resignation, $approver = null)
    {
        $this->resignation = $resignation;
        $this->approver = $approver;
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
        $lastWorkingDay = \Carbon\Carbon::parse($this->resignation->last_working_day);
        $daysRemaining = \Carbon\Carbon::now()->diffInDays($lastWorkingDay, false);

        return (new MailMessage)
            ->subject('Resignation Approved - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your resignation has been **approved**.')
            ->line('')
            ->line('**Resignation Details:**')
            ->line('Submission Date: ' . $this->resignation->created_at->format('F j, Y'))
            ->line('Last Working Day: ' . $lastWorkingDay->format('F j, Y'))
            ->line('Notice Period: ' . abs($daysRemaining) . ' days')
            ->line('Approved By: ' . ($this->approver->name ?? 'HR Department'))
            ->line('')
            ->line('**Next Steps:**')
            ->line('✓ Complete all pending tasks')
            ->line('✓ Hand over responsibilities')
            ->line('✓ Return company assets')
            ->line('✓ Complete exit interview')
            ->line('✓ Collect final settlement')
            ->line('')
            ->action('View Resignation Details', route('resignation.show', $this->resignation->id))
            ->line('We wish you all the best in your future endeavors.')
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'resignation_approved',
            'resignation_id' => $this->resignation->id,
            'last_working_day' => $this->resignation->last_working_day,
            'approved_by' => $this->approver->name ?? 'HR',
            'message' => 'Your resignation has been approved',
            'action_url' => route('resignation.show', $this->resignation->id)
        ];
    }
}
