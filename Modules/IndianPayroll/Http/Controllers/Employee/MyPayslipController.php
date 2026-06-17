<?php

namespace Modules\IndianPayroll\Http\Controllers\Employee;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\Payslip;

class MyPayslipController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'my-indian-payroll.payslips');
    }

    public function index()
    {
        $payslips = Payslip::where('user_id', auth()->id())
            ->whereIn('status', ['approved', 'locked'])
            ->with('run')
            ->orderByDesc('id')
            ->paginate(12);

        return view('indianpayroll::payroll_run.my_payslips', compact('payslips'));
    }

    public function download(Payslip $payslip)
    {
        abort_unless($payslip->user_id === auth()->id(), 403);
        abort_unless(in_array($payslip->status, ['approved', 'locked']), 403, 'Payslip is not yet finalized.');

        $payslip->load('user', 'run', 'components.component');

        $pdf = Pdf::loadView('indianpayroll::payroll_run.payslip_pdf', compact('payslip'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('payslip_'.$payslip->run->month.'_'.$payslip->run->year.'.pdf');
    }
}
