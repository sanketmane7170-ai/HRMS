<?php

namespace Modules\Recruitment\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Recruitment\Entities\Offer;

class OfferLetter extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Job Offer: ' . $this->offer->job->title)
                    ->view('recruitment::emails.offer-letter');
    }
}
