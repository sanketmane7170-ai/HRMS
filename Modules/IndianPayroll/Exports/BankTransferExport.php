<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\IndianPayroll\Entities\BankDetail;
use Modules\IndianPayroll\Entities\Payslip;

/**
 * Salary disbursement (NEFT/bank upload) file for a payroll run: one row per
 * employee with their net pay and bank account. The column order mirrors a
 * generic bank bulk-transfer template; tweak headings to a specific bank's
 * format as needed.
 */
class BankTransferExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private $bankByUser;

    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        $payslips = Payslip::where('run_id', $this->runId)
            ->where('net_pay', '>', 0)
            ->with('user')
            ->get();

        $this->bankByUser = BankDetail::whereIn('user_id', $payslips->pluck('user_id'))->get()->keyBy('user_id');

        return $payslips;
    }

    public function headings(): array
    {
        return ['Employee Name', 'Beneficiary Name', 'Bank Name', 'Account Number', 'IFSC', 'Amount', 'Payment Mode', 'Remarks'];
    }

    public function map($payslip): array
    {
        $bank = $this->bankByUser->get($payslip->user_id);

        return [
            $payslip->user->name ?? 'N/A',
            $bank->account_holder_name ?? ($payslip->user->name ?? 'N/A'),
            $bank->bank_name ?? '',
            $bank ? $bank->account_number : '',     // encrypted cast decrypts on access
            $bank->ifsc ?? '',
            number_format((float) $payslip->net_pay, 2, '.', ''),
            'NEFT',
            'Salary',
        ];
    }
}
