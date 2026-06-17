<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;

/**
 * Payroll accounting Journal Voucher for a run. Aggregates component totals into
 * a balanced set of debit/credit ledger lines:
 *   Dr  Salaries & Wages (gross earnings)
 *   Dr  Employer statutory contributions (PF/ESI/LWF + gratuity/NPS/superann)
 *       Cr  Each statutory payable (employee + employer shares)
 *       Cr  Employee loan/advance recovery
 *       Cr  Net salary payable (balancing figure = total Dr − other Cr)
 * Built from component sums so it always balances regardless of LOP treatment.
 */
class JournalVoucherExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        $sums = $this->componentSums();
        $get = fn (string $code) => round($sums[$code] ?? 0.0, 2);

        // Gross = all earning components (basic + every allowance + reimbursement etc.)
        $gross = round($this->earningTotal(), 2);

        $employerPf = $get(SalaryComponent::CODE_EPF_EMPLOYER) + $get(SalaryComponent::CODE_EPS_EMPLOYER);
        $employerEsi = $get(SalaryComponent::CODE_ESI_EMPLOYER);
        $employerLwf = $get(SalaryComponent::CODE_LWF_EMPLOYER);
        $gratuity = $get(SalaryComponent::CODE_GRATUITY_PROVISION);
        $nps = $get(SalaryComponent::CODE_EMPLOYER_NPS);
        $superann = $get(SalaryComponent::CODE_SUPERANNUATION);

        $employerTotal = $employerPf + $employerEsi + $employerLwf + $gratuity + $nps + $superann;

        $pfPayable = $get(SalaryComponent::CODE_EPF_EMPLOYEE) + $employerPf;
        $esiPayable = $get(SalaryComponent::CODE_ESI_EMPLOYEE) + $employerEsi;
        $ptPayable = $get(SalaryComponent::CODE_PROFESSIONAL_TAX);
        $tdsPayable = $get(SalaryComponent::CODE_TDS);
        $lwfPayable = $get(SalaryComponent::CODE_LWF_EMPLOYEE) + $employerLwf;
        $loanRecovery = $get(SalaryComponent::CODE_LOAN_RECOVERY);

        $totalDebit = round($gross + $employerTotal, 2);

        $otherCredits = $pfPayable + $esiPayable + $ptPayable + $tdsPayable + $lwfPayable
            + $gratuity + $nps + $superann + $loanRecovery;
        $netPayable = round($totalDebit - $otherCredits, 2);

        $rows = collect();
        $line = fn ($account, $dr, $cr) => ['Account' => $account, 'Debit' => $dr ? number_format($dr, 2, '.', '') : '', 'Credit' => $cr ? number_format($cr, 2, '.', '') : ''];

        $rows->push($line('Salaries & Wages A/c', $gross, 0));
        if ($employerPf) $rows->push($line('Employer PF Contribution A/c', $employerPf, 0));
        if ($employerEsi) $rows->push($line('Employer ESI Contribution A/c', $employerEsi, 0));
        if ($employerLwf) $rows->push($line('Employer LWF Contribution A/c', $employerLwf, 0));
        if ($gratuity) $rows->push($line('Gratuity Expense A/c', $gratuity, 0));
        if ($nps) $rows->push($line('Employer NPS Contribution A/c', $nps, 0));
        if ($superann) $rows->push($line('Superannuation Expense A/c', $superann, 0));

        if ($pfPayable) $rows->push($line('PF Payable A/c', 0, $pfPayable));
        if ($esiPayable) $rows->push($line('ESI Payable A/c', 0, $esiPayable));
        if ($ptPayable) $rows->push($line('Professional Tax Payable A/c', 0, $ptPayable));
        if ($tdsPayable) $rows->push($line('TDS Payable A/c', 0, $tdsPayable));
        if ($lwfPayable) $rows->push($line('LWF Payable A/c', 0, $lwfPayable));
        if ($gratuity) $rows->push($line('Gratuity Provision A/c', 0, $gratuity));
        if ($nps) $rows->push($line('NPS Payable A/c', 0, $nps));
        if ($superann) $rows->push($line('Superannuation Payable A/c', 0, $superann));
        if ($loanRecovery) $rows->push($line('Employee Loan / Advance A/c', 0, $loanRecovery));
        $rows->push($line('Net Salary Payable A/c (Bank)', 0, $netPayable));

        $rows->push($line('TOTAL', $totalDebit, $totalDebit));

        return $rows;
    }

    public function headings(): array
    {
        return ['Account', 'Debit', 'Credit'];
    }

    private function payslips()
    {
        return Payslip::where('run_id', $this->runId)->with('components.component')->get();
    }

    /** code => total amount across all payslips in the run. */
    private function componentSums(): array
    {
        $sums = [];
        foreach ($this->payslips() as $payslip) {
            foreach ($payslip->components as $c) {
                $code = optional($c->component)->code;
                if (! $code) {
                    continue;
                }
                $sums[$code] = ($sums[$code] ?? 0.0) + (float) $c->amount;
            }
        }

        return $sums;
    }

    private function earningTotal(): float
    {
        $total = 0.0;
        foreach ($this->payslips() as $payslip) {
            $total += (float) $payslip->components
                ->where('type', SalaryComponent::TYPE_EARNING)
                ->sum('amount');
        }

        return $total;
    }
}
