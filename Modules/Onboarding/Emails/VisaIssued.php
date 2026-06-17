<?php

namespace Modules\Onboarding\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VisaIssued extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $portalLink;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->portalLink = route('portal.index'); // They check status here
    }

    public function build()
    {
        return $this->subject('Good News! Your Entry Visa is Issued')
                    ->view('onboarding::emails.visa_issued');
    }
}
