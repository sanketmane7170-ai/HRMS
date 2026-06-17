<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;

/**
 * Full salary register for a run: one row per employee with every salary
 * component as its own column (built dynamically from the components actually
 * present in the run), plus paid/LOP days and net pay.
 */
class SalaryRegisterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var \Illuminate\Support\Collection<int,SalaryComponent> */
    private $componentColumns;

    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        $payslips = Payslip::where('run_id', $this->runId)
            ->with(['user', 'components.component'])
            ->get();

        // Distinct components present in the run, ordered for a stable layout.
        $codes = $payslips->flatMap(fn ($p) => $p->components->pluck('component.code'))->filter()->unique();
        $this->componentColumns = SalaryComponent::whereIn('code', $codes)
            ->orderBy('display_order')
            ->get();

        return $payslips;
    }

    public function headings(): array
    {
        return array_merge(
            ['Employee', 'Department', 'Designation', 'Paid Days', 'LOP Days'],
            $this->componentColumns->pluck('name')->all(),
            ['Gross Earnings', 'Total Deductions', 'Net Pay'],
        );
    }

    public function map($payslip): array
    {
        $byCode = $payslip->components->keyBy(fn ($c) => optional($c->component)->code);

        $amounts = $this->componentColumns->map(function (SalaryComponent $col) use ($byCode) {
            return number_format((float) optional($byCode->get($col->code))->amount, 2, '.', '');
        })->all();

        $deductions = round((float) $payslip->total_statutory_deductions + (float) $payslip->total_other_deductions, 2);

        return array_merge(
            [
                $payslip->user->name ?? 'N/A',
                optional(optional($payslip->user)->department)->name ?? '-',
                optional(optional($payslip->user)->designation)->name ?? '-',
                $payslip->paid_days,
                $payslip->loss_of_pay_days,
            ],
            $amounts,
            [
                number_format((float) $payslip->gross_earnings, 2, '.', ''),
                number_format($deductions, 2, '.', ''),
                number_format((float) $payslip->net_pay, 2, '.', ''),
            ],
        );
    }
}
