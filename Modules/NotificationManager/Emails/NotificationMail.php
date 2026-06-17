<?php

namespace Modules\NotificationManager\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($userData,$subject)
    {
        $this->userData = $userData;
        $this->subject = $subject;
    }


    public function build()
    {

        return $this->view('notificationmanager::template')
        ->subject($this->subject)
        ->with([
            'userData' => $this->userData,
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'notificationmanager::template',
        );
    }
}
