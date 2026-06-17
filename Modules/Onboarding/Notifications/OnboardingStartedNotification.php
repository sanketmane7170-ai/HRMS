<?php

namespace Modules\Onboarding\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when onboarding process starts
 * Author: Sanket
 */
class OnboardingStartedNotification extends Notification
{
    use Queueable;

    protected $onboardingRecord;

    /**
     * Create a new notification instance.
     */
    public function __construct($onboardingRecord)
    {
        $this->onboardingRecord = $onboardingRecord;
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
        $joiningDate = \Carbon\Carbon::parse($this->onboardingRecord->joining_date);
        $daysUntilJoining = \Carbon\Carbon::now()->diffInDays($joiningDate, false);

        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' - Onboarding Started')
            ->greeting('Hello ' . $this->onboardingRecord->full_name . ',')
            ->line('Congratulations! Your onboarding process has officially started.')
            ->line('')
            ->line('**Joining Details:**')
            ->line('📅 Joining Date: ' . $joiningDate->format('l, F j, Y'))
            ->line('🏢 Department: ' . ($this->onboardingRecord->department->name ?? 'TBD'))
            ->line('⏳ Days until joining: ' . abs($daysUntilJoining) . ' days')
            ->line('')
            ->line('**What to Expect:**')
            ->line('✓ Document upload requirements')
            ->line('✓ Background verification process')
            ->line('✓ System access provisioning')
            ->line('✓ Welcome kit preparation')
            ->line('✓ First day orientation details')
            ->line('')
            ->line('**Next Steps:**')
            ->line('1. Complete your profile information')
            ->line('2. Upload required documents')
            ->line('3. Review company policies')
            ->line('4. Complete pre-joining formalities')
            ->line('')
            ->action('Login to Onboarding Portal', route('portal.index'))
            ->line('We look forward to having you on the team!')
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'onboarding_started',
            'onboarding_id' => $this->onboardingRecord->id,
            'joining_date' => $this->onboardingRecord->joining_date,
            'department' => $this->onboardingRecord->department->name ?? 'N/A',
            'message' => 'Your onboarding process has started',
            'action_url' => route('portal.index')
        ];
    }
}
