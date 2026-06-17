<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Warning\Entities\UserWarning;
use Modules\Warning\Enums\WarningType;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailables\Attachment;

class WarningEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userWarning;
    public $types;
    public $attachmentPath;

    /**
     * Create a new message instance.
     */
    public function __construct(UserWarning $userWarning,$attachmentPath = null)
    {
        $this->userWarning = $userWarning;
        $this->types = WarningType::cases();
        $this->attachmentPath = $attachmentPath;
    }


    public function build()
    {
        $logo = getLogo();
        if($this->userWarning->type->value == 'performance'){
            return $this->view('warning::warning.performance')
            ->subject('Subject of the Email') // Set the email subject here
            ->with([
                'userWarning' => $this->userWarning,
                'types' => $this->types,
                'logo' => $logo
            ]);
        } else if($this->userWarning->type->value == 'notice_of_termination'){
            return $this->view('warning::warning.termination')
            ->subject('Subject of the Email') // Set the email subject here
            ->with([
                'userWarning' => $this->userWarning,
                'types' => $this->types,
                'logo' => $logo
            ]);
        } else if($this->userWarning->type->value == 'attendance_issue'){
            return $this->view('warning::warning.attendance')
            ->subject('Verbal Warning – Attendance Policy')
            ->with([
                'userWarning' => $this->userWarning,
                'types' => $this->types,
                'logo' => $logo
            ]);
        } else if($this->userWarning->type->value == 'termination'){
            return $this->view('warning::warning.termination')
            ->subject('Verbal Warning – Termination')
            ->with([
                'userWarning' => $this->userWarning,
                'types' => $this->types,
                'logo' => $logo
            ]);
        } else {
            return $this->view('warning::warning.template')
            ->subject('Subject of the Email') // Set the email subject here
            ->with([
                'userWarning' => $this->userWarning,
                'types' => $this->types,
            ]);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Warning Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if($this->userWarning->type->value == 'performance'){
            return new Content(
                view: 'warning::warning.performance',
            );
        } else if($this->userWarning->type->value == 'notice_of_termination'){
            return new Content(
                view: 'warning::warning.termination',
            );
        } else if($this->userWarning->type->value == 'attendance_issue'){
            return new Content(
                view: 'warning::warning.attendance',
            );
        } else if($this->userWarning->type->value == 'termination'){
            return new Content(
                view: 'warning::warning.termination',
            );
        } else {
            return new Content(
                view: 'warning::warning.template',
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
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            return [
                Attachment::fromPath($this->attachmentPath)
                    ->as(basename($this->attachmentPath))
                    ->withMime(mime_content_type($this->attachmentPath)),
            ];
        }
        return [];
    }
}
