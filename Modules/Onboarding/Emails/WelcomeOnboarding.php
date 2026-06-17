<?php

namespace Modules\Onboarding\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WelcomeOnboarding extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $portalLink;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->portalLink = route('portal.login');
    }

    public function build()
    {
        return $this->subject('Welcome to MOM Digital! Start Your Onboarding')
                    ->view('onboarding::emails.welcome_onboarding');
    }
}
