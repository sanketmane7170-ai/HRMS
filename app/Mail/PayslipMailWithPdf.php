<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipMailWithPdf extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $request;
    public $pdfContent;

    public function __construct($user, $request, $pdfContent)
    {
        $this->user = $user;
        $this->request = $request;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        $monthNumber = $this->request->month ?: date('m');
        $year = $this->request->year ?: date('Y');

        // Convert month number to month name
        $monthName = \Carbon\Carbon::createFromFormat('!m', $monthNumber)->format('F');

        $emailBody = "
            <p>Dear {$this->user->name},</p>
            <p>Please find attached your payslip for {$monthName} {$year}.</p>
            <p>Thank you.</p>
        ";

        // return $this->subject('Your Payslip for ' . $monthName . ' ' . $year)
        //     ->html($emailBody)
        //     ->attachData($this->pdfContent, 'Payslip.pdf', [
        //         'mime' => 'application/pdf',
        //     ]);
        // return $this->subject('Your Payslip for ' . $monthName . ' ' . $year)
        //     ->html($emailBody)
        //     ->attach($this->pdfContent, [
        //         'as' => 'Payslip.pdf',
        //         'mime' => 'application/pdf'
        //     ]);
        return $this->subject('Your Payslip for ' . $monthName . ' ' . $year)
            ->html($emailBody)
            ->attachData($this->pdfContent, 'Payslip.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
