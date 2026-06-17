<?php

namespace Modules\Onboarding\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when document status changes
 * Author: Sanket
 */
class DocumentStatusNotification extends Notification
{
    use Queueable;

    protected $document;
    protected $status;
    protected $remarks;

    /**
     * Create a new notification instance.
     */
    public function __construct($document, $status, $remarks = null)
    {
        $this->document = $document;
        $this->status = $status;
        $this->remarks = $remarks;
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
            'approved' => 'Your document has been approved.',
            'rejected' => 'Your document requires revision.',
            'pending' => 'Your document is under review.',
            'verified' => 'Your document has been verified.'
        ];

        $message = $statusMessages[$this->status] ?? 'Your document status has been updated.';

        $mail = (new MailMessage)
            ->subject('Document Status Update - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your uploaded document status has been updated.')
            ->line('')
            ->line('**Document Details:**')
            ->line('Document Type: ' . ($this->document->document_type ?? 'N/A'))
            ->line('Status: **' . ucfirst($this->status) . '**')
            ->line('Updated: ' . now()->format('F j, Y g:i A'))
            ->line('')
            ->line($message);

        if ($this->status === 'rejected' && $this->remarks) {
            $mail->line('')
                ->line('**Reason for Rejection:**')
                ->line($this->remarks)
                ->line('')
                ->line('**Action Required:**')
                ->line('Please re-upload the document with the necessary corrections.');
        } elseif ($this->status === 'approved') {
            $mail->line('')
                ->line('✓ No further action required for this document.');
        }

        $mail->line('')
            ->action('View Document Details', route('onboarding.documents.show', $this->document->id))
            ->salutation('Best regards, ' . config('app.name') . ' HR Team');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'document_status_changed',
            'document_id' => $this->document->id,
            'document_type' => $this->document->document_type ?? 'N/A',
            'status' => $this->status,
            'remarks' => $this->remarks,
            'message' => 'Document status updated to ' . $this->status,
            'action_url' => route('onboarding.documents.show', $this->document->id)
        ];
    }
}
