<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\FileManager\Entities\FileManager;

use Illuminate\Support\Facades\Mail;

class FileManagerDocExpireEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $filemanager;
    public $userData;

    /**
     * Create a new message instance.
     */
    public function __construct(FileManager $filemanager,$userData)
    {
        $this->filemanager = $filemanager;
        $this->userData = $userData;

    }


    public function build()
    {
        $logo = getLogo();
        return $this->view('filemanager::filemanager')
            ->subject('Subject of the Email') // Set the email subject here
            ->with([
                'filemanager' => $this->filemanager,
                'userData' => $this->userData,
                'logo' => $logo
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Filemanager Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'filemanager::filemanager',
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
