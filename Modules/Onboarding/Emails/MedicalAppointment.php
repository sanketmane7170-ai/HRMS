<?php

namespace Modules\Onboarding\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class MedicalAppointment extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $appointmentDate;
    public $portalLink;

    public function __construct(User $user, $appointmentDate)
    {
        $this->user = $user;
        $this->appointmentDate = $appointmentDate;
        $this->portalLink = route('portal.index');
    }

    public function build()
    {
        return $this->subject('Action Required: Medical Appointment Confirmed')
                    ->view('onboarding::emails.medical_appointment');
    }
}
