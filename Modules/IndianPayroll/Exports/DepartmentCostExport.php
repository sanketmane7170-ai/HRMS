<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\IndianPayroll\Entities\Payslip;

/**
 * Department-wise cost report for a run: headcount, gross, employer
 * contributions and total employer cost (CTC) grouped by department — the
 * management view of where payroll spend lands.
 */
class DepartmentCostExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private int $runId)
    {
    }

    public function collection()
    {
        $payslips = Payslip::where('run_id', $this->runId)
            ->with('user.department')
            ->get();

        $groups = [];
        foreach ($payslips as $p) {
            $dept = optional(optional($p->user)->department)->name ?? 'Unassigned';
            $groups[$dept] ??= ['count' => 0, 'gross' => 0.0, 'employer' => 0.0, 'net' => 0.0];
            $groups[$dept]['count']++;
            $groups[$dept]['gross'] += (float) $p->gross_earnings;
            $groups[$dept]['employer'] += (float) $p->total_employer_contributions;
            $groups[$dept]['net'] += (float) $p->net_pay;
        }

        ksort($groups);

        $rows = collect();
        $totals = ['count' => 0, 'gross' => 0.0, 'employer' => 0.0, 'net' => 0.0, 'ctc' => 0.0];

        foreach ($groups as $dept => $g) {
            $ctc = $g['gross'] + $g['employer'];
            $rows->push([
                'Department' => $dept,
                'Employees' => $g['count'],
                'Gross' => number_format($g['gross'], 2, '.', ''),
                'Employer Contributions' => number_format($g['employer'], 2, '.', ''),
                'Net Pay' => number_format($g['net'], 2, '.', ''),
                'Total Cost (CTC)' => number_format($ctc, 2, '.', ''),
            ]);
            $totals['count'] += $g['count'];
            $totals['gross'] += $g['gross'];
            $totals['employer'] += $g['employer'];
            $totals['net'] += $g['net'];
            $totals['ctc'] += $ctc;
        }

        $rows->push([
            'Department' => 'TOTAL',
            'Employees' => $totals['count'],
            'Gross' => number_format($totals['gross'], 2, '.', ''),
            'Employer Contributions' => number_format($totals['employer'], 2, '.', ''),
            'Net Pay' => number_format($totals['net'], 2, '.', ''),
            'Total Cost (CTC)' => number_format($totals['ctc'], 2, '.', ''),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return ['Department', 'Employees', 'Gross', 'Employer Contributions', 'Net Pay', 'Total Cost (CTC)'];
    }
}
