<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppreciationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $appreciation;
    public $attachmentPath;

    public function __construct($appreciation, $attachmentPath = null)
    {
        $this->appreciation = $appreciation;
        $this->attachmentPath = $attachmentPath;
    }

    public function build()
    {
        $email = $this->subject('Appreciation Notification')
                      ->markdown('emails.appreciation')
                      ->with([
                          'user' => $this->appreciation->user,
                          'message' => $this->appreciation->detail,
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
            subject: 'Appreciation Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appreciation',
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
