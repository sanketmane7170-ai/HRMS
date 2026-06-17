<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;

/**
 * Component-wise payroll summary for a run: total of every salary component
 * across all employees, grouped by earning / deduction / employer contribution,
 * with a headcount and net-pay total. The finance team's one-page control sheet.
 */
class PayrollSummaryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private const TYPE_LABELS = [
        SalaryComponent::TYPE_EARNING => 'Earning',
        SalaryComponent::TYPE_DEDUCTION => 'Deduction',
        SalaryComponent::TYPE_EMPLOYER_CONTRIBUTION => 'Employer Contribution',
    ];

    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        $payslips = Payslip::where('run_id', $this->runId)->with('components.component')->get();

        // code => [name, type, total]
        $totals = [];
        foreach ($payslips as $payslip) {
            foreach ($payslip->components as $c) {
                $comp = $c->component;
                if (! $comp) {
                    continue;
                }
                $totals[$comp->code] ??= ['name' => $comp->name, 'type' => $comp->type, 'order' => $comp->display_order, 'total' => 0.0];
                $totals[$comp->code]['total'] += (float) $c->amount;
            }
        }

        $rows = collect(array_values($totals))
            ->sortBy([['type', 'asc'], ['order', 'asc']])
            ->map(fn ($r) => [
                'Component' => $r['name'],
                'Type' => self::TYPE_LABELS[$r['type']] ?? $r['type'],
                'Total' => number_format($r['total'], 2, '.', ''),
            ]);

        // Trailing control totals.
        $rows->push(['Component' => '', 'Type' => '', 'Total' => '']);
        $rows->push(['Component' => 'Employees', 'Type' => '', 'Total' => $payslips->count()]);
        $rows->push(['Component' => 'Total Gross', 'Type' => '', 'Total' => number_format((float) $payslips->sum('gross_earnings'), 2, '.', '')]);
        $rows->push(['Component' => 'Total Net Pay', 'Type' => '', 'Total' => number_format((float) $payslips->sum('net_pay'), 2, '.', '')]);
        $rows->push(['Component' => 'Total Employer Cost (CTC)', 'Type' => '', 'Total' => number_format((float) $payslips->sum('gross_earnings') + (float) $payslips->sum('total_employer_contributions'), 2, '.', '')]);

        return $rows;
    }

    public function headings(): array
    {
        return ['Component', 'Type', 'Total'];
    }
}
