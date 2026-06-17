<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Services\PayrollRunService;

class PayrollRunController extends Controller
{
    public function __construct(private PayrollRunService $service)
    {
        view()->share('activeLink', 'indian-payroll.payroll-runs');
    }

    public function index()
    {
        canPerform('Run Payroll');

        $runs = PayrollRun::with('creator')->orderByDesc('year')->orderByDesc('month')->paginate(15);

        return view('indianpayroll::payroll_run.index', compact('runs'));
    }

    public function store(Request $request)
    {
        canPerform('Run Payroll');

        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        try {
            $run = $this->service->createRun($data['month'], $data['year'], auth()->id());
        } catch (QueryException $e) {
            // UNIQUE(month, year) violation — two concurrent requests or a duplicate click.
            return redirect()->route('backend.indian-payroll.payroll-runs.index')
                ->with('error', 'A payroll run for this month already exists.');
        }

        return redirect()->route('backend.indian-payroll.payroll-runs.show', $run)
            ->with('success', createFlashMessage('Payroll Run', 'created'));
    }

    public function show(PayrollRun $run)
    {
        canPerform('Run Payroll');

        $payslips = Payslip::where('run_id', $run->id)->with('user')->paginate(25);
        $totals = [
            'gross' => Payslip::where('run_id', $run->id)->sum('gross_earnings'),
            'statutory_deductions' => Payslip::where('run_id', $run->id)->sum('total_statutory_deductions'),
            'employer_contributions' => Payslip::where('run_id', $run->id)->sum('total_employer_contributions'),
            'net_pay' => Payslip::where('run_id', $run->id)->sum('net_pay'),
        ];

        return view('indianpayroll::payroll_run.show', compact('run', 'payslips', 'totals'));
    }

    public function compute(PayrollRun $run)
    {
        canPerform('Run Payroll');

        if (! $run->isEditable()) {
            return redirect()->route('backend.indian-payroll.payroll-runs.show', $run)
                ->with('error', 'Cannot compute a payroll run that is already approved or locked.');
        }

        try {
            $this->service->compute($run);
            $run->update(['compute_error' => null]);
        } catch (\Throwable $e) {
            Log::error('PayrollRun compute failed', ['run_id' => $run->id, 'error' => $e->getMessage()]);
            $run->update([
                'status' => PayrollRun::STATUS_FAILED,
                'compute_error' => substr($e->getMessage(), 0, 500),
            ]);

            return redirect()->route('backend.indian-payroll.payroll-runs.show', $run)
                ->with('error', 'Payroll compute failed: ' . $e->getMessage());
        }

        return redirect()->route('backend.indian-payroll.payroll-runs.show', $run)
            ->with('success', 'Payroll computed successfully.');
    }

    public function approve(PayrollRun $run)
    {
        canPerform('Approve Payroll');

        try {
            $this->service->approve($run, auth()->id());
        } catch (\RuntimeException $e) {
            return redirect()->route('backend.indian-payroll.payroll-runs.show', $run)->with('error', $e->getMessage());
        }

        return redirect()->route('backend.indian-payroll.payroll-runs.show', $run)->with('success', 'Payroll run approved.');
    }

    public function lock(PayrollRun $run)
    {
        canPerform('Lock Payroll');

        try {
            $this->service->lock($run, auth()->id());
        } catch (\RuntimeException $e) {
            return redirect()->route('backend.indian-payroll.payroll-runs.show', $run)->with('error', $e->getMessage());
        }

        return redirect()->route('backend.indian-payroll.payroll-runs.show', $run)->with('success', 'Payroll run locked. Payslips are now read-only.');
    }

    public function destroy(PayrollRun $run)
    {
        canPerform('Run Payroll');

        if (! $run->isEditable()) {
            return redirect()->route('backend.indian-payroll.payroll-runs.index')
                ->with('error', 'Only a draft or computed run can be deleted.');
        }

        $run->delete();

        return redirect()->route('backend.indian-payroll.payroll-runs.index')
            ->with('success', createFlashMessage('Payroll Run', 'deleted'));
    }
}
