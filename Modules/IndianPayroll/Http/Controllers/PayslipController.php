<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\PayslipComponent;
use Modules\IndianPayroll\Entities\SalaryComponent;

class PayslipController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.payroll-runs');
    }

    public function show(Payslip $payslip)
    {
        canPerform('View Payslips');

        $payslip->load([
            'user.indianPayrollProfile.state',
            'user.indianPayrollProfile.bankDetail',
            'user.designation',
            'user.department',
            'run',
            'components.component'
        ]);

        return view('indianpayroll::payroll_run.payslip', compact('payslip'));
    }

    public function edit(Payslip $payslip)
    {
        canPerform('Run Payroll');

        if (! $payslip->run->isEditable()) {
            return redirect()->route('backend.indian-payroll.payslips.show', $payslip)
                ->with('error', 'Approved or locked payslips cannot be edited.');
        }

        $payslip->load(['user', 'run', 'components.component']);

        $usedIds = $payslip->components->pluck('salary_component_id')->filter()->values()->toArray();

        $availableComponents = SalaryComponent::where('type', SalaryComponent::TYPE_DEDUCTION)
            ->where('is_active', true)
            ->whereNotIn('id', $usedIds)
            ->orderBy('name')
            ->get();

        $availableEarningComponents = SalaryComponent::where('type', SalaryComponent::TYPE_EARNING)
            ->where('is_active', true)
            ->whereNotIn('id', $usedIds)
            ->orderBy('name')
            ->get();

        return view('indianpayroll::payroll_run.payslip_edit', compact('payslip', 'availableComponents', 'availableEarningComponents'));
    }

    public function update(Request $request, Payslip $payslip)
    {
        canPerform('Run Payroll');

        if (! $payslip->run->isEditable()) {
            return redirect()->back()->with('error', 'Approved or locked payslips cannot be edited.');
        }

        $request->validate([
            'paid_days'                          => 'required|numeric|min:0',
            'loss_of_pay_days'                   => 'required|numeric|min:0',
            'components'                         => 'nullable|array',
            'components.*.id'                    => 'required|integer',
            'components.*.amount'                => 'required|numeric|min:0',
            'new_deductions'                     => 'nullable|array',
            'new_deductions.*.salary_component_id' => 'nullable|exists:ip_salary_components,id',
            'new_deductions.*.label'             => 'nullable|string|max:150',
            'new_deductions.*.amount'            => 'required|numeric|min:0',
            'new_earnings'                       => 'nullable|array',
            'new_earnings.*.salary_component_id' => 'nullable|exists:ip_salary_components,id',
            'new_earnings.*.label'                => 'nullable|string|max:150',
            'new_earnings.*.amount'               => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $payslip) {
            $payslip->update([
                'paid_days'        => $request->paid_days,
                'loss_of_pay_days' => $request->loss_of_pay_days,
            ]);

            // Update existing component amounts
            foreach ($request->components ?? [] as $row) {
                PayslipComponent::where('id', $row['id'])
                    ->where('payslip_id', $payslip->id)
                    ->update(['amount' => $row['amount']]);
            }

            $this->addManualRows($payslip, $request->new_deductions ?? [], SalaryComponent::TYPE_DEDUCTION);
            $this->addManualRows($payslip, $request->new_earnings ?? [], SalaryComponent::TYPE_EARNING);

            $payslip->recalculateTotals();
        });

        return redirect()->route('backend.indian-payroll.payslips.show', $payslip)
            ->with('success', 'Payslip updated. Net pay has been recalculated.');
    }

    public function destroyManualDeduction(Payslip $payslip, PayslipComponent $component)
    {
        canPerform('Run Payroll');

        abort_if(! $payslip->run->isEditable(), 403, 'Payslip is locked.');
        abort_if($component->payslip_id !== $payslip->id, 403);
        abort_if(! $component->is_manual, 403, 'Only manually added rows can be deleted.');

        $component->delete();
        $payslip->recalculateTotals();

        return redirect()->route('backend.indian-payroll.payslips.edit', $payslip)
            ->with('success', 'Row removed and net pay recalculated.');
    }

    /**
     * Adds manually-entered one-off rows (deductions or bonus/earning lines) to a draft
     * payslip. Each row identifies itself either by a catalog component or a free-text
     * label (e.g. an ad-hoc adjustment with no matching SalaryComponent).
     */
    private function addManualRows(Payslip $payslip, array $rows, string $type): void
    {
        foreach ($rows as $row) {
            if (! $row['amount'] || $row['amount'] <= 0) {
                continue;
            }
            $label = $row['label'] ?? null;
            $componentId = $row['salary_component_id'] ?? null;

            if (! $label && ! $componentId) {
                continue; // nothing to identify this row — skip
            }

            PayslipComponent::create([
                'payslip_id'          => $payslip->id,
                'salary_component_id' => $componentId,
                'label'               => $label,
                'type'                => $type,
                'amount'              => $row['amount'],
                'is_manual'           => true,
            ]);
        }
    }

    public function download(Payslip $payslip)
    {
        canPerform('View Payslips');

        $payslip->load([
            'user.indianPayrollProfile.state',
            'user.indianPayrollProfile.bankDetail',
            'user.designation',
            'user.department',
            'run',
            'components.component'
        ]);

        $pdf = Pdf::loadView('indianpayroll::payroll_run.payslip_pdf', compact('payslip'));
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'payslip_'.str_replace(' ', '_', strtolower($payslip->user->name)).'_'.$payslip->run->month.'_'.$payslip->run->year.'.pdf';

        return $pdf->download($fileName);
    }
}
