<?php

namespace Modules\Onboarding\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when document upload is required
 * Author: Sanket
 */
class DocumentUploadRequiredNotification extends Notification
{
    use Queueable;

    protected $onboardingRecord;
    protected $documentTypes;

    /**
     * Create a new notification instance.
     */
    public function __construct($onboardingRecord, $documentTypes = [])
    {
        $this->onboardingRecord = $onboardingRecord;
        $this->documentTypes = $documentTypes;
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
        $daysRemaining = \Carbon\Carbon::now()->diffInDays($joiningDate, false);

        $mail = (new MailMessage)
            ->subject('Action Required: Upload Documents - ' . config('app.name'))
            ->greeting('Hello ' . $this->onboardingRecord->full_name . ',')
            ->line('Please upload the following required documents to complete your onboarding process.')
            ->line('')
            ->line('**Required Documents:**');

        // Add document types if provided
        if (!empty($this->documentTypes)) {
            foreach ($this->documentTypes as $docType) {
                $mail->line('• ' . $docType);
            }
        } else {
            $mail->line('• National ID / Passport')
                ->line('• Educational Certificates')
                ->line('• Previous Employment Letters')
                ->line('• Passport Size Photo')
                ->line('• Bank Account Details');
        }

        $mail->line('')
            ->line('**Important:**')
            ->line('⏰ Deadline: ' . $joiningDate->subDays(3)->format('F j, Y'))
            ->line('📋 All documents must be uploaded before your joining date')
            ->line('✓ Ensure documents are clear and readable')
            ->line('✓ Accepted formats: PDF, JPG, PNG')
            ->line('')
            ->action('Upload Documents Now', route('onboarding.documents', $this->onboardingRecord->id))
            ->line('If you have any questions, please contact HR.')
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'document_upload_required',
            'onboarding_id' => $this->onboardingRecord->id,
            'document_types' => $this->documentTypes,
            'joining_date' => $this->onboardingRecord->joining_date,
            'message' => 'Please upload required documents',
            'action_url' => route('onboarding.documents', $this->onboardingRecord->id)
        ];
    }
}
