<?php

namespace Modules\AgenticAI\Tools;

use Modules\Payroll\Entities\UserPayslip;
use App\Models\User;
use Exception;

/**
 * ProcessPayrollTool - Process monthly payroll
 * Author: Sanket
 * 
 * Allows HR/Admin to process payroll for all employees
 * CRITICAL: This is a sensitive operation that generates payslips
 */
class ProcessPayrollTool extends BaseTool
{
    public function name(): string
    {
        return 'process_payroll';
    }

    public function description(): string
    {
        return 'Process monthly payroll for all employees or specific department. Use when HR wants to generate payslips for the month.';
    }

    public function isSensitive(): bool
    {
        return true; // CRITICAL operation, requires admin approval
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'month' => [
                    'type' => 'string',
                    'description' => 'Month to process in YYYY-MM format (e.g., "2026-01")'
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Process for specific department only (optional, processes all if not provided)'
                ],
                'dry_run' => [
                    'type' => 'boolean',
                    'description' => 'If true, only shows summary without creating payslips (optional, default false)'
                ],
            ],
            'required' => ['month']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR can process payroll (most critical operation)
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required to process payroll'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'super-admin'])) {
                return ['error' => 'You do not have permission to process payroll'];
            }

            // Validate month format
            if (!preg_match('/^\d{4}-\d{2}$/', $args['month'])) {
                return ['error' => 'Month must be in YYYY-MM format (e.g., "2026-01")'];
            }

            $month = $args['month'];
            $dryRun = $args['dry_run'] ?? false;

            // Check if Indian Payroll is active
            if (\Schema::hasTable('ip_payroll_runs')) {
                $parts = explode('-', $args['month']);
                $yearVal = (int)$parts[0];
                $monthVal = (int)$parts[1];

                if ($dryRun) {
                    // Summarize current structure state for active employees
                    $employeesCount = \Modules\IndianPayroll\Entities\EmployeeProfile::whereNull('date_of_exit')
                        ->where('date_of_joining', '<=', \Carbon\Carbon::create($yearVal, $monthVal, 1)->endOfMonth())
                        ->count();

                    return [
                        'payroll_type' => 'Indian Payroll',
                        'success' => true,
                        'dry_run' => true,
                        'month' => $month,
                        'message' => "Dry run: Ready to process Indian Payroll for {$employeesCount} employees in {$month}."
                    ];
                }

                // Get or create run
                $run = \Modules\IndianPayroll\Entities\PayrollRun::firstOrCreate(
                    ['month' => $monthVal, 'year' => $yearVal],
                    [
                        'period_start' => \Carbon\Carbon::create($yearVal, $monthVal, 1)->startOfMonth(),
                        'period_end' => \Carbon\Carbon::create($yearVal, $monthVal, 1)->endOfMonth(),
                        'status' => 'draft',
                        'created_by' => $currentUser->id
                    ]
                );

                if ($run->status === 'approved' || $run->status === 'locked') {
                    return [
                        'error' => "Payroll run for {$month} is already {$run->status} and cannot be computed.",
                        'success' => false
                    ];
                }

                // Reset to draft if computed to allow re-compute
                if ($run->status === 'computed') {
                    $run->update(['status' => 'draft']);
                }

                $payrollService = app(\Modules\IndianPayroll\Services\PayrollRunService::class);
                $payrollService->compute($run);

                $payslipsCount = \Modules\IndianPayroll\Entities\Payslip::where('run_id', $run->id)->count();

                return [
                    'payroll_type' => 'Indian Payroll',
                    'success' => true,
                    'dry_run' => false,
                    'month' => $month,
                    'run_id' => $run->id,
                    'status' => $run->refresh()->status,
                    'message' => "Indian Payroll processed successfully for {$month}. {$payslipsCount} payslips computed."
                ];
            }

            // Get employees to process
            $query = User::where('status', 'active')
                ->whereNotNull('salary');

            if (!empty($args['department_id'])) {
                $query->where('department_id', $args['department_id']);
            }

            $employees = $query->get();

            if ($employees->isEmpty()) {
                return [
                    'error' => 'No active employees with salary found',
                    'success' => false
                ];
            }

            // Check if payroll already processed for this month
            $existingCount = UserPayslip::where('month', $month)
                ->whereIn('user_id', $employees->pluck('id'))
                ->count();

            if ($existingCount > 0 && !$dryRun) {
                return [
                    'error' => "Payroll already processed for {$existingCount} employees in {$month}. Use dry_run to see summary.",
                    'existing_payslips' => $existingCount
                ];
            }

            // Calculate totals
            $totalGross = 0;
            $totalDeductions = 0;
            $totalNet = 0;
            $processedCount = 0;

            foreach ($employees as $employee) {
                $gross = $employee->salary ?? 0;
                $deductions = $this->calculateDeductions($employee);
                $net = $gross - $deductions;

                $totalGross += $gross;
                $totalDeductions += $deductions;
                $totalNet += $net;

                // Create payslip if not dry run
                if (!$dryRun) {
                    UserPayslip::create([
                        'user_id' => $employee->id,
                        'month' => $month,
                        'gross_salary' => $gross,
                        'deductions' => $deductions,
                        'net_salary' => $net,
                        'status' => 'pending',
                        'generated_by' => $currentUser->id,
                        'generated_at' => now()
                    ]);
                }

                $processedCount++;
            }

            $message = $dryRun 
                ? "Payroll summary for {$month} (DRY RUN - no payslips created)"
                : "Payroll processed successfully for {$month}. {$processedCount} payslips generated.";

            return [
                'success' => true,
                'dry_run' => $dryRun,
                'month' => $month,
                'summary' => [
                    'total_employees' => $processedCount,
                    'total_gross_salary' => number_format($totalGross, 2),
                    'total_deductions' => number_format($totalDeductions, 2),
                    'total_net_salary' => number_format($totalNet, 2),
                ],
                'message' => $message
            ];

        } catch (Exception $e) {
            \Log::error('ProcessPayrollTool error', ['error' => $e->getMessage()]);
            return [
                'error' => 'Failed to process payroll. Please try again.',
                'success' => false
            ];
        }
    }

    /**
     * Calculate deductions for employee
     * Author: Sanket
     * 
     * This is a simplified calculation. In production, this should:
     * - Get tax deductions from employee_taxes table
     * - Get other deductions from salary_deductions table
     * - Calculate provident fund, insurance, etc.
     */
    private function calculateDeductions(User $employee): float
    {
        $deductions = 0;

        // Get tax deductions
        $taxDeductions = \DB::table('employee_taxes')
            ->where('user_id', $employee->id)
            ->where('status', 'active')
            ->sum('amount');

        // Get other deductions
        $otherDeductions = \DB::table('salary_deductions')
            ->where('user_id', $employee->id)
            ->where('status', 'active')
            ->sum('amount');

        $deductions = $taxDeductions + $otherDeductions;

        return $deductions;
    }
}
