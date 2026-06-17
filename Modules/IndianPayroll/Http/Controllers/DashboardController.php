<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\FullFinalSettlement;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;

class DashboardController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll');
    }

    public function index()
    {
        canPerform('Manage Indian Payroll');

        // ── Headcount ────────────────────────────────────────────────
        $activeEmployees = EmployeeProfile::whereNull('date_of_exit')->count();
        $totalProfiles   = EmployeeProfile::count();

        $activeUserIds = EmployeeProfile::whereNull('date_of_exit')->pluck('user_id');

        // Employees missing an active salary structure (can't be paid correctly)
        $withStructure = EmployeeSalaryStructure::where('is_active', true)
            ->whereIn('user_id', $activeUserIds)
            ->distinct('user_id')
            ->count('user_id');
        $missingStructure = max($activeEmployees - $withStructure, 0);

        // ── Latest run + its payslip aggregates ──────────────────────
        $latestRun = PayrollRun::orderByDesc('year')->orderByDesc('month')->first();

        $agg = (object) [
            'count'      => 0,
            'gross'      => 0,
            'stat_ded'   => 0,
            'other_ded'  => 0,
            'employer'   => 0,
            'net'        => 0,
            'lop_days'   => 0,
        ];
        if ($latestRun) {
            $row = Payslip::where('run_id', $latestRun->id)->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(gross_earnings),0) as gross,
                COALESCE(SUM(total_statutory_deductions),0) as stat_ded,
                COALESCE(SUM(total_other_deductions),0) as other_ded,
                COALESCE(SUM(total_employer_contributions),0) as employer,
                COALESCE(SUM(net_pay),0) as net,
                COALESCE(SUM(loss_of_pay_days),0) as lop_days
            ')->first();
            if ($row) {
                $agg = $row;
            }
        }

        // Total employer cost (CTC outflow) and statutory remittance for the run
        $employerCost      = $agg->gross + $agg->employer;
        $statutoryRemitted = $agg->stat_ded + $agg->employer;
        $payslipCoverage   = $activeEmployees > 0 ? round(($agg->count / $activeEmployees) * 100) : 0;

        // ── Net-pay trend (last 6 runs, chronological) ───────────────
        $recentRuns = PayrollRun::orderByDesc('year')->orderByDesc('month')->limit(6)->get();

        $trend = $recentRuns->sortBy([['year', 'asc'], ['month', 'asc']])->values()->map(function ($run) {
            return (object) [
                'label' => Carbon::create($run->year, $run->month, 1)->format('M'),
                'net'   => (float) Payslip::where('run_id', $run->id)->sum('net_pay'),
            ];
        });
        $trendMax = $trend->max('net') ?: 1;

        // ── Compliance / action items ────────────────────────────────
        $financialYear = FinancialYearHelper::forDate(now());

        $declaredUsers = EmployeeTaxDeclaration::where('financial_year', $financialYear)
            ->whereIn('user_id', $activeUserIds)
            ->distinct('user_id')
            ->count('user_id');
        $pendingDeclarations = max($activeEmployees - $declaredUsers, 0);

        $pendingSettlements = FullFinalSettlement::whereNotIn('status', ['approved', 'paid'])->count();

        return view('indianpayroll::dashboard', compact(
            'activeEmployees', 'totalProfiles', 'missingStructure', 'withStructure',
            'latestRun', 'agg', 'employerCost', 'statutoryRemitted', 'payslipCoverage',
            'recentRuns', 'trend', 'trendMax',
            'financialYear', 'pendingDeclarations', 'pendingSettlements'
        ));
    }
}
