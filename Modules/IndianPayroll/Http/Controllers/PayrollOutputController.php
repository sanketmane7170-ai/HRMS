<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\PfSetting;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Exports\BankTransferExport;
use Modules\IndianPayroll\Exports\DepartmentCostExport;
use Modules\IndianPayroll\Exports\JournalVoucherExport;
use Modules\IndianPayroll\Exports\PayrollSummaryExport;
use Modules\IndianPayroll\Exports\SalaryRegisterExport;
use Modules\IndianPayroll\Exports\SalaryVarianceExport;

/**
 * Downloadable outputs generated from a computed payroll run: the bank
 * disbursement (NEFT) file, the accounting journal voucher, and the
 * management/finance registers.
 */
class PayrollOutputController extends Controller
{
    public function bankTransferFile(PayrollRun $run)
    {
        canPerform('Run Payroll');

        return Excel::download(new BankTransferExport($run->id), "bank_transfer_{$run->month}_{$run->year}.xlsx");
    }

    public function journalVoucher(PayrollRun $run)
    {
        canPerform('Run Payroll');

        return Excel::download(new JournalVoucherExport($run->id), "journal_voucher_{$run->month}_{$run->year}.xlsx");
    }

    public function salaryRegister(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new SalaryRegisterExport($run->id), "salary_register_{$run->month}_{$run->year}.xlsx");
    }

    public function payrollSummary(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new PayrollSummaryExport($run->id), "payroll_summary_{$run->month}_{$run->year}.xlsx");
    }

    public function departmentCost(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new DepartmentCostExport($run->id), "department_cost_{$run->month}_{$run->year}.xlsx");
    }

    public function salaryVariance(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new SalaryVarianceExport($run), "salary_variance_{$run->month}_{$run->year}.xlsx");
    }

    /**
     * EPFO ECR (Electronic Challan-cum-Return) v2.0 upload file — one #~# delimited
     * line per member. Plain text, the format the EPFO portal ingests.
     */
    public function pfEcr(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        $rate = (float) optional(PfSetting::effectiveAsOf($run->period_end))->employee_rate ?: 12.0;

        $payslips = Payslip::where('run_id', $run->id)
            ->with(['user.indianPayrollProfile', 'components.component'])
            ->get();

        $lines = [];
        foreach ($payslips as $p) {
            $amount = fn ($code) => (float) ($p->components->first(fn ($c) => optional($c->component)->code === $code)?->amount ?? 0);

            $employeeEpf = $amount(SalaryComponent::CODE_EPF_EMPLOYEE);
            if ($employeeEpf <= 0) {
                continue; // not a PF member this month
            }

            $epfWages = $rate > 0 ? round($employeeEpf / ($rate / 100)) : 0;
            $cappedWages = min($epfWages, 15000);
            $employerEps = $amount(SalaryComponent::CODE_EPS_EMPLOYER);
            $employerEpf = $amount(SalaryComponent::CODE_EPF_EMPLOYER);
            $ncpDays = (int) round((float) $p->loss_of_pay_days);
            $uan = optional(optional($p->user)->indianPayrollProfile)->uan ?? '';

            $lines[] = implode('#~#', [
                $uan,
                $p->user->name ?? '',
                (int) round((float) $p->gross_earnings), // gross wages
                (int) $epfWages,                          // EPF wages
                (int) $cappedWages,                       // EPS wages
                (int) $cappedWages,                       // EDLI wages
                (int) round($employeeEpf),                // EE EPF contribution
                (int) round($employerEps),                // ER EPS contribution
                (int) round($employerEpf),                // ER EPF (diff) contribution
                $ncpDays,                                 // non-contributing period days
                0,                                        // refund of advances
            ]);
        }

        $content = implode("\n", $lines)."\n";
        $filename = "ecr_{$run->month}_{$run->year}.txt";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
