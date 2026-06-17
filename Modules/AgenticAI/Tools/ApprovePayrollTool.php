<?php

namespace Modules\AgenticAI\Tools;

use Modules\Payroll\Entities\UserPayslip;
use Exception;

/**
 * ApprovePayrollTool - Approve payroll for processing
 * Author: Sanket
 */
class ApprovePayrollTool extends BaseTool
{
    public function name(): string
    {
        return 'approve_payroll';
    }

    public function description(): string
    {
        return 'Approve payroll for a specific month. Use when manager needs to approve payroll before payment.';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'month' => [
                    'type' => 'string',
                    'description' => 'Month to approve in YYYY-MM format'
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Approve for specific department only (optional)'
                ],
            ],
            'required' => ['month']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can approve payroll
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required to approve payroll'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to approve payroll'];
            }

            if (empty($args['month'])) {
                return ['error' => 'month is required'];
            }

            // Check if Indian Payroll is active
            if (\Schema::hasTable('ip_payroll_runs')) {
                $parts = explode('-', $args['month']);
                $yearVal = (int)$parts[0];
                $monthVal = (int)$parts[1];

                $run = \Modules\IndianPayroll\Entities\PayrollRun::where('month', $monthVal)
                    ->where('year', $yearVal)
                    ->first();

                if (!$run) {
                    return [
                        'error' => "No payroll run found for {$args['month']}",
                        'success' => false
                    ];
                }

                if ($run->status === 'approved' || $run->status === 'locked') {
                    return [
                        'error' => "Payroll run for {$args['month']} is already {$run->status}.",
                        'success' => false
                    ];
                }

                if ($run->status !== 'computed') {
                    return [
                        'error' => "Payroll run for {$args['month']} must be computed before approval. Current status: {$run->status}",
                        'success' => false
                    ];
                }

                $payrollService = app(\Modules\IndianPayroll\Services\PayrollRunService::class);
                $payrollService->approve($run, $currentUser->id);

                return [
                    'payroll_type' => 'Indian Payroll',
                    'success' => true,
                    'month' => $args['month'],
                    'run_id' => $run->id,
                    'status' => $run->refresh()->status,
                    'message' => "Indian Payroll run approved successfully for {$args['month']}."
                ];
            }

            $query = UserPayslip::where('month', $args['month'])
                ->where('status', 'pending');

            if (!empty($args['department_id'])) {
                $query->whereHas('user', function($q) use ($args) {
                    $q->where('department_id', $args['department_id']);
                });
            }

            $payslips = $query->get();

            if ($payslips->isEmpty()) {
                return [
                    'error' => "No pending payslips found for {$args['month']}",
                    'month' => $args['month']
                ];
            }

            $approvedCount = 0;
            foreach ($payslips as $payslip) {
                $payslip->update([
                    'status' => 'approved',
                    'approved_by' => $currentUser->id,
                    'approved_at' => now()
                ]);
                $approvedCount++;
            }

            return [
                'success' => true,
                'month' => $args['month'],
                'approved_count' => $approvedCount,
                'message' => "Approved {$approvedCount} payslips for {$args['month']}"
            ];

        } catch (Exception $e) {
            \Log::error('ApprovePayrollTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to approve payroll. Please try again.', 'success' => false];
        }
    }
}
