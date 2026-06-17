<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\PfSetting;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;

class PfRegisterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private ?PfSetting $pfSettings = null;

    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        $payslips = Payslip::where('run_id', $this->runId)
            ->with(['user.indianPayrollProfile', 'run', 'components.component'])
            ->get();

        if ($payslips->isNotEmpty()) {
            $this->pfSettings = PfSetting::effectiveAsOf($payslips->first()->run->period_end);
        }

        return $payslips
            ->filter(fn ($p) => $p->components->contains(fn ($c) => optional($c->component)->code === SalaryComponent::CODE_EPF_EMPLOYEE))
            ->values();
    }

    public function headings(): array
    {
        return ['Employee Name', 'UAN', 'PF Number', 'PF Wage', 'Employee EPF', 'Employer EPF', 'Employer EPS', 'Total Employer Contribution'];
    }

    public function map($payslip): array
    {
        $profile = $payslip->user->indianPayrollProfile;
        $amount = fn ($code) => $payslip->components->first(fn ($c) => optional($c->component)->code === $code)?->amount ?? 0;

        $employeeEpf = (float) $amount(SalaryComponent::CODE_EPF_EMPLOYEE);
        $employerEpf = (float) $amount(SalaryComponent::CODE_EPF_EMPLOYER);
        $employerEps = (float) $amount(SalaryComponent::CODE_EPS_EMPLOYER);

        $pfWage = $this->pfSettings && (float) $this->pfSettings->employee_rate > 0
            ? round($employeeEpf / ((float) $this->pfSettings->employee_rate / 100), 2)
            : 0.0;

        return [
            $payslip->user->name,
            $profile->uan ?? '-',
            $profile->pf_number ?? '-',
            number_format($pfWage, 2, '.', ''),
            number_format($employeeEpf, 2, '.', ''),
            number_format($employerEpf, 2, '.', ''),
            number_format($employerEps, 2, '.', ''),
            number_format($employerEpf + $employerEps, 2, '.', ''),
        ];
    }
}
