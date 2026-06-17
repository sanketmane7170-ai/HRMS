<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Entities\Payslip;

/**
 * Month-on-month salary variance: net pay this run vs the immediately previous
 * run, per employee, with the delta and % change. New joiners and exits show as
 * the appropriate one-sided variance. The finance team's exception report.
 */
class SalaryVarianceExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private PayrollRun $run)
    {
    }

    public function collection()
    {
        $previous = PayrollRun::where('period_start', '<', $this->run->period_start)
            ->orderByDesc('period_start')
            ->first();

        $current = Payslip::where('run_id', $this->run->id)->with('user')->get()->keyBy('user_id');
        $prior = $previous
            ? Payslip::where('run_id', $previous->id)->with('user')->get()->keyBy('user_id')
            : collect();

        $userIds = $current->keys()->merge($prior->keys())->unique();

        $rows = collect();
        foreach ($userIds as $uid) {
            $cur = $current->get($uid);
            $pre = $prior->get($uid);

            $curNet = (float) optional($cur)->net_pay;
            $preNet = (float) optional($pre)->net_pay;
            $delta = round($curNet - $preNet, 2);
            $pct = $preNet > 0 ? round(($delta / $preNet) * 100, 2) : ($curNet > 0 ? 100.0 : 0.0);

            $rows->push([
                'Employee' => optional(optional($cur ?? $pre)->user)->name ?? ('User #'.$uid),
                'Previous Net' => number_format($preNet, 2, '.', ''),
                'Current Net' => number_format($curNet, 2, '.', ''),
                'Variance' => number_format($delta, 2, '.', ''),
                'Variance %' => number_format($pct, 2, '.', ''),
                'Remark' => ! $pre ? 'New' : (! $cur ? 'Exited' : ($delta == 0 ? 'No change' : '')),
            ]);
        }

        return $rows->sortByDesc(fn ($r) => abs((float) str_replace(',', '', $r['Variance'])))->values();
    }

    public function headings(): array
    {
        return ['Employee', 'Previous Net', 'Current Net', 'Variance', 'Variance %', 'Remark'];
    }
}
