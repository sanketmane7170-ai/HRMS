<?php

namespace Modules\IndianPayroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Services\PayrollRunService;

/**
 * Runs the payroll compute off the web request so an HTTP timeout can never
 * silently abort a partially-processed run.  The run's status is set to
 * 'computing' before dispatch so HR can see progress in the UI, then updated
 * to 'computed' on success or 'failed' on error.
 *
 * Queue: use a dedicated 'payroll' queue so compute jobs don't compete with
 * notification or email jobs.  Add to config/queue.php worker config if needed.
 */
class ComputePayrollRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // 30 minutes — generous ceiling for large companies

    public int $tries = 1; // Do not auto-retry payroll — partial re-runs replace, but explicit retry is safer

    public function __construct(public readonly int $runId)
    {
        $this->onQueue('payroll');
    }

    public function handle(PayrollRunService $service): void
    {
        $run = PayrollRun::findOrFail($this->runId);

        if (! $run->isEditable()) {
            // Stale job fired after the run was already approved/locked — harmless, just bail.
            return;
        }

        try {
            $service->compute($run);

            // compute() already updates status to 'computed'. Clear any previous error.
            $run->update(['compute_error' => null]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => PayrollRun::STATUS_FAILED,
                'compute_error' => substr($e->getMessage(), 0, 500),
            ]);

            // Re-throw so the queue worker marks the job as failed and logs a stack trace.
            throw $e;
        }
    }
}
