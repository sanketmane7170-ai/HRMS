<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipMailRawHtml extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $template;

    public function __construct($user, $template)
    {
        $this->user = $user;
        $this->template = $template;  // full payslip HTML from controller
    }

    public function build()
    {
        $monthNumber = request()->month ?? date('m');
        $year        = request()->year ?? date('Y');

        $monthName = \Carbon\Carbon::createFromFormat('!m', $monthNumber)->format('F');

        

        return $this->subject('Your Payslip for ' . $monthName . ' ' . $year)
            ->html(view('emails.payslip-html-email', [
                'user'     => $this->user,
                'template' => $this->template
            ])->render());
    }
}
