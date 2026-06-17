<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailType;
    public $frequency;
    public $data;
    public $filePath;
    public $email_user;

    /**
     * Create a new message instance.
     */
    public function __construct($emailType, $frequency, $data, $filePath,$email_user)
    {
        $this->emailType = $emailType;
        $this->frequency = $frequency;
        $this->data = $data;
        $this->filePath = $filePath;
        $this->email_user = $email_user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $currentDate = Carbon::now();
        $formattedMonth = $currentDate->format('F Y');

        $result = $this->view('emails.email_layout')
            ->subject("{$this->emailType} - {$formattedMonth}")
            ->with('data', $this->data)
            ->with('emailType', $this->emailType)
            ->with('frequency', $this->frequency)
            ->with('email_user', $this->email_user)
            ->attach($this->filePath, [
                'as' => $this->emailType.'.csv',
                'mime' => 'text/csv',
            ]);

        return $result;
    }
}
