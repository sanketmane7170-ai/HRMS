<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Announcement\Entities\Announcement;
use Modules\Announcement\Entities\AnnouncementType;

use Illuminate\Support\Facades\Mail;

class AnnouncementEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $announcement;
    public $types;

    /**
     * Create a new message instance.
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
        $this->types = AnnouncementType::Where('id',$announcement->announcement_type_id)->first();
        // $this->announcement->type = $this->types->name;
        // dd($this->announcement->type);

    }


    public function build()
    {
        $logo = getLogo();
        if($this->types->name == 'Increment Announcement'){
            return $this->view('announcement::announcement.increment')
            ->subject('Subject of the Email') // Set the email subject here
            ->with([
                'announcement' => $this->announcement,
                'types' => $this->types->name,
                'logo' => $logo
            ]);
        } 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Announcement Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if($this->types->name == 'Increment Announcement'){
            return new Content(
                view: 'announcement::announcement.increment',
            );
        } 
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
