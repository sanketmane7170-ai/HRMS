<?php

namespace Modules\Onboarding\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VisaExpiringSoon extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $days;

    public function __construct(User $user, $days)
    {
        $this->user = $user;
        $this->days = $days;
    }

    public function build()
    {
        return $this->subject("Action Required: Visa Expiry Warning ({$this->days} Days)")
                    ->view('onboarding::emails.visa_expiring');
    }
}
