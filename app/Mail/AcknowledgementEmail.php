<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcknowledgementEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $userAcknowledgement;
    public $attachmentPath;

    public function __construct($userAcknowledgement, $attachmentPath = null)
    {
        $this->userAcknowledgement = $userAcknowledgement;
        $this->attachmentPath = $attachmentPath;
    }

    public function build()
    {
        $email = $this->subject('User Warning Notification')
                      ->markdown('emails.acknowledgement')
                      ->with([
                          'user' => $this->userAcknowledgement->user,
                          'message' => $this->userAcknowledgement->detail,
                      ]);


        if (!empty($this->attachmentPath) && file_exists($this->attachmentPath)) {
            $email->attach($this->attachmentPath);
        } else {
            \Log::error('File does not exist: ' . $this->attachmentPath);
        }

        return $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Acknowledgement Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.acknowledgement',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
