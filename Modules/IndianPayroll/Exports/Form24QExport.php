<?php

namespace Modules\IndianPayroll\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;

/**
 * Quarterly Form 24Q salary-TDS data, in the column layout an employer/CA needs to key
 * into the NSDL/Income-Tax-Department e-TDS filing utility (RPU). This is the computed
 * data export, not the binary FVU file the utility itself produces.
 */
class Form24QExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private string $financialYear, private int $quarter)
    {
    }

    public function collection()
    {
        [$quarterStart, $quarterEnd] = $this->quarterBounds();

        return Payslip::whereHas('run', fn ($q) => $q->where('period_start', '>=', $quarterStart)->where('period_start', '<=', $quarterEnd))
            ->with('user', 'run', 'components.component')
            ->get()
            ->groupBy('user_id')
            ->map(function ($payslips) {
                $first = $payslips->first();
                $grossTotal = $payslips->sum('gross_earnings');
                $tdsTotal = $payslips->sum(fn ($p) => $p->components->first(fn ($c) => optional($c->component)->code === SalaryComponent::CODE_TDS)?->amount ?? 0);

                return [
                    'user' => $first->user,
                    'gross' => $grossTotal,
                    'tds' => $tdsTotal,
                    'regime' => $first->tax_regime,
                ];
            })
            ->values();
    }

    public function headings(): array
    {
        return ['Employee Name', 'PAN', 'Tax Regime', 'Quarter Gross Salary', 'Quarter TDS Deducted'];
    }

    public function map($row): array
    {
        $profile = EmployeeProfile::where('user_id', $row['user']->id)->first();

        return [
            $row['user']->name,
            $profile->pan ?? 'NOT AVAILABLE',
            strtoupper($row['regime'] ?? ''),
            number_format((float) $row['gross'], 2, '.', ''),
            number_format((float) $row['tds'], 2, '.', ''),
        ];
    }

    private function quarterBounds(): array
    {
        $fyStart = FinancialYearHelper::startDate($this->financialYear);
        $quarterStart = $fyStart->copy()->addMonths(($this->quarter - 1) * 3);
        $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay();

        return [$quarterStart, $quarterEnd];
    }
}
