<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;

class PtRegisterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        return Payslip::where('run_id', $this->runId)
            ->with('user', 'components.component')
            ->get()
            ->filter(fn ($p) => $p->components->contains(fn ($c) => optional($c->component)->code === SalaryComponent::CODE_PROFESSIONAL_TAX))
            ->values();
    }

    public function headings(): array
    {
        return ['Employee Name', 'State', 'PT Enrollment Number', 'Gross Wage', 'Professional Tax'];
    }

    public function map($payslip): array
    {
        $profile = EmployeeProfile::with('state')->where('user_id', $payslip->user_id)->first();
        $amount = fn ($code) => $payslip->components->first(fn ($c) => optional($c->component)->code === $code)?->amount ?? 0;
        $proratedGross = (float) $payslip->gross_earnings - (float) $amount(SalaryComponent::CODE_LOSS_OF_PAY);

        return [
            $payslip->user->name,
            $profile->state->name ?? '-',
            $profile->pt_enrollment_number ?? '-',
            number_format($proratedGross, 2, '.', ''),
            number_format((float) $amount(SalaryComponent::CODE_PROFESSIONAL_TAX), 2, '.', ''),
        ];
    }
}
