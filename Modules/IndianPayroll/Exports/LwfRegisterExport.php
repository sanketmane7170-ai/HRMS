<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;

class LwfRegisterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        return Payslip::where('run_id', $this->runId)
            ->with('user', 'components.component')
            ->get()
            ->filter(fn ($p) => $p->components->contains(fn ($c) => optional($c->component)->code === SalaryComponent::CODE_LWF_EMPLOYEE))
            ->values();
    }

    public function headings(): array
    {
        return ['Employee Name', 'State', 'Employee LWF', 'Employer LWF'];
    }

    public function map($payslip): array
    {
        $profile = EmployeeProfile::with('state')->where('user_id', $payslip->user_id)->first();
        $amount = fn ($code) => $payslip->components->first(fn ($c) => optional($c->component)->code === $code)?->amount ?? 0;

        return [
            $payslip->user->name,
            $profile->state->name ?? '-',
            number_format((float) $amount(SalaryComponent::CODE_LWF_EMPLOYEE), 2, '.', ''),
            number_format((float) $amount(SalaryComponent::CODE_LWF_EMPLOYER), 2, '.', ''),
        ];
    }
}
