<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Entities\Payslip;

class DashboardController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll');
    }

    public function index()
    {
        canPerform('Manage Indian Payroll');

        $employeeCount = EmployeeProfile::whereNull('date_of_exit')->count();
        $latestRun = PayrollRun::orderByDesc('year')->orderByDesc('month')->first();
        $latestRunNetPay = $latestRun ? Payslip::where('run_id', $latestRun->id)->sum('net_pay') : 0;
        $recentRuns = PayrollRun::orderByDesc('year')->orderByDesc('month')->limit(6)->get();

        return view('indianpayroll::dashboard', compact('employeeCount', 'latestRun', 'latestRunNetPay', 'recentRuns'));
    }
}
