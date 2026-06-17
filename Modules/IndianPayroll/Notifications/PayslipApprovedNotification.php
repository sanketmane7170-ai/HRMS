<?php

namespace Modules\IndianPayroll\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\IndianPayroll\Entities\PayrollRun;

class PayslipApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly PayrollRun $run)
    {
        $this->onQueue('notifications');
    }

    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $period = \Carbon\Carbon::create($this->run->year, $this->run->month, 1)->format('F Y');

        return (new MailMessage)
            ->subject("Your {$period} payslip is ready")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your salary payslip for {$period} has been approved and is now available.")
            ->action('View Payslip', route('backend.indian-payroll.my-payslips.index'))
            ->line('If you have any queries about your payslip, please contact the HR team.');
    }

    public function toDatabase(mixed $notifiable): array
    {
        $period = \Carbon\Carbon::create($this->run->year, $this->run->month, 1)->format('F Y');

        return [
            'title' => "Payslip ready — {$period}",
            'message' => "Your {$period} payslip has been approved. You can now download it.",
            'url' => route('backend.indian-payroll.my-payslips.index'),
            'type' => 'payslip_approved',
        ];
    }

    public function toArray(mixed $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
