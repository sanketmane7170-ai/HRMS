<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;

class EsiRegisterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        return Payslip::where('run_id', $this->runId)
            ->with(['user.indianPayrollProfile', 'components.component'])
            ->get()
            ->filter(fn ($p) => $p->components->contains(fn ($c) => optional($c->component)->code === SalaryComponent::CODE_ESI_EMPLOYEE))
            ->values();
    }

    public function headings(): array
    {
        return ['Employee Name', 'ESI Number', 'Gross Wage', 'Employee ESI (0.75%)', 'Employer ESI (3.25%)'];
    }

    public function map($payslip): array
    {
        $profile = $payslip->user->indianPayrollProfile;
        $amount = fn ($code) => $payslip->components->first(fn ($c) => optional($c->component)->code === $code)?->amount ?? 0;

        // Prorated (LOP-adjusted) gross — gross_earnings is the full contracted figure;
        // the Loss of Pay line is the difference already excluded from net pay.
        $proratedGross = (float) $payslip->gross_earnings - (float) $amount(SalaryComponent::CODE_LOSS_OF_PAY);

        return [
            $payslip->user->name,
            $profile->esi_number ?? '-',
            number_format($proratedGross, 2, '.', ''),
            number_format((float) $amount(SalaryComponent::CODE_ESI_EMPLOYEE), 2, '.', ''),
            number_format((float) $amount(SalaryComponent::CODE_ESI_EMPLOYER), 2, '.', ''),
        ];
    }
}
