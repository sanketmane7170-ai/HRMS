<?php

namespace App\Mail\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for sending offer letters to candidates
 * Author: Sanket
 */
class OfferLetter extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $candidate;
    public $job;

    /**
     * Create a new message instance.
     */
    public function __construct($offer)
    {
        $this->offer = $offer;
        $this->candidate = $offer->application->user ?? $offer->application->candidate_name;
        $this->job = $offer->application->job;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Job Offer - ' . $this->offer->position . ' at ' . config('app.name'))
            ->view('emails.recruitment.offer_letter')
            ->with([
                'offer' => $this->offer,
                'candidate' => $this->candidate,
                'job' => $this->job,
                'candidateName' => is_object($this->candidate) ? $this->candidate->name : $this->candidate,
                'salary' => number_format($this->offer->salary),
                'currency' => $this->offer->currency ?? 'USD',
                'joiningDate' => \Carbon\Carbon::parse($this->offer->joining_date)->format('F j, Y'),
                'responseDeadline' => $this->offer->response_deadline ? \Carbon\Carbon::parse($this->offer->response_deadline)->format('F j, Y') : 'N/A'
            ]);

        // Attach offer letter PDF if exists
        if ($this->offer->offer_letter_url) {
            $filePath = storage_path('app/public/' . $this->offer->offer_letter_url);
            if (file_exists($filePath)) {
                $mail->attach($filePath, [
                    'as' => 'Offer_Letter_' . $this->offer->position . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
