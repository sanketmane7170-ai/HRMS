<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class ProbationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $filePath;
    public function __construct(User $user, $filePath)
    {
        $this->user = $user;
        $this->filePath = $filePath;
    }
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Probation Confirmation Letter');
    }
    public function content(): Content
    {
        return new Content(view: 'emails.probation_confirmation');
    }
    public function attachments(): array
    {
        return [
            Attachment::fromPath(storage_path('app/public/' . $this->filePath)),
        ];
    }
}
