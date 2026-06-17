<?php

namespace Modules\Onboarding\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Modules\Onboarding\Entities\ProbationReview;

class ProbationReviewReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $manager;
    public $employee;
    public $review;

    public function __construct(User $manager, User $employee, ProbationReview $review)
    {
        $this->manager = $manager;
        $this->employee = $employee;
        $this->review = $review;
    }

    public function build()
    {
        return $this->subject("Probation Review Due: {$this->employee->name}")
                    ->view('onboarding::emails.probation_reminder');
    }
}
