<?php

namespace Modules\AgenticAI\Tools;

use Modules\Payroll\Entities\UserPayslip;
use App\Models\User;
use Exception;

/**
 * GetPayrollSummaryTool - Get payroll overview
 * Author: Sanket
 */
class GetPayrollSummaryTool extends BaseTool
{
    public function name(): string
    {
        return 'get_payroll_summary';
    }

    public function description(): string
    {
        return 'Get monthly payroll summary and statistics. Use when admin wants payroll overview.';
    }

    public function isSensitive(): bool
    {
        return false;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'month' => [
                    'type' => 'string',
                    'description' => 'Month in YYYY-MM format (optional, defaults to current month)'
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Filter by department (optional)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $month = $args['month'] ?? now()->format('Y-m');

            // First check if Indian Payroll run table exists
            if (\Schema::hasTable('ip_payroll_runs')) {
                $yearVal = null;
                $monthVal = null;
                if (!empty($args['month'])) {
                    $parts = explode('-', $args['month']);
                    if (count($parts) === 2) {
                        $yearVal = (int)$parts[0];
                        $monthVal = (int)$parts[1];
                    }
                }
                if (!$yearVal || !$monthVal) {
                    $yearVal = (int)now()->format('Y');
                    $monthVal = (int)now()->format('n');
                }

                $run = \Modules\IndianPayroll\Entities\PayrollRun::where('month', $monthVal)
                    ->where('year', $yearVal)
                    ->first();

                if ($run) {
                    $payslipQuery = \Modules\IndianPayroll\Entities\Payslip::where('run_id', $run->id);
                    if (!empty($args['department_id'])) {
                        $payslipQuery->whereHas('user', function($q) use ($args) {
                            $q->where('department_id', $args['department_id']);
                        });
                    }

                    $payslips = $payslipQuery->get();

                    if ($payslips->isNotEmpty()) {
                        $totalGross = $payslips->sum('gross_earnings');
                        $totalDeductions = $payslips->sum('total_statutory_deductions');
                        $totalNet = $payslips->sum('net_pay');

                        $statusBreakdown = [
                            'draft' => $payslips->where('status', 'draft')->count(),
                            'computed' => $payslips->where('status', 'computed')->count(),
                            'approved' => $payslips->where('status', 'approved')->count(),
                            'locked' => $payslips->where('status', 'locked')->count(),
                        ];

                        return [
                            'payroll_type' => 'Indian Payroll',
                            'has_payroll' => true,
                            'month' => sprintf('%04d-%02d', $yearVal, $monthVal),
                            'run_status' => $run->status,
                            'summary' => [
                                'total_employees' => $payslips->count(),
                                'total_gross_earnings' => number_format($totalGross, 2),
                                'total_statutory_deductions' => number_format($totalDeductions, 2),
                                'total_net_pay' => number_format($totalNet, 2),
                                'average_net_pay' => number_format($totalNet / $payslips->count(), 2),
                            ],
                            'status_breakdown' => $statusBreakdown,
                            'message' => "Indian Payroll summary for " . sprintf('%04d-%02d', $yearVal, $monthVal) . ": {$payslips->count()} employees, Total: " . number_format($totalNet, 2)
                        ];
                    }
                }
            }

            $query = UserPayslip::where('month', $month);

            if (!empty($args['department_id'])) {
                $query->whereHas('user', function($q) use ($args) {
                    $q->where('department_id', $args['department_id']);
                });
            }

            $payslips = $query->get();

            if ($payslips->isEmpty()) {
                return [
                    'has_payroll' => false,
                    'month' => $month,
                    'message' => "No payroll data found for {$month}"
                ];
            }

            $totalGross = $payslips->sum('gross_salary');
            $totalDeductions = $payslips->sum('deductions');
            $totalNet = $payslips->sum('net_salary');
            
            $statusBreakdown = [
                'pending' => $payslips->where('status', 'pending')->count(),
                'approved' => $payslips->where('status', 'approved')->count(),
                'paid' => $payslips->where('status', 'paid')->count(),
            ];

            return [
                'has_payroll' => true,
                'month' => $month,
                'summary' => [
                    'total_employees' => $payslips->count(),
                    'total_gross_salary' => number_format($totalGross, 2),
                    'total_deductions' => number_format($totalDeductions, 2),
                    'total_net_salary' => number_format($totalNet, 2),
                    'average_salary' => number_format($totalNet / $payslips->count(), 2),
                ],
                'status_breakdown' => $statusBreakdown,
                'message' => "Payroll summary for {$month}: {$payslips->count()} employees, Total: " . number_format($totalNet, 2)
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
