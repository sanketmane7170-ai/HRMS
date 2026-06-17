<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Payroll\Entities\UserSalary;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserSalaryIncrement;
use App\Models\User;

class GetSalaryBreakdownTool extends BaseTool
{
    public function name(): string
    {
        return 'get_salary_breakdown';
    }

    public function description(): string
    {
        return 'Get detailed salary breakdown including base salary, allowances, deductions, and increment history. Employees can view their own salary, admins can view any employee\'s salary.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Employee ID to query (defaults to current user; admin can query others)'
                ],
                'include_history' => [
                    'type' => 'boolean',
                    'description' => 'Optional: Include salary increment history (default: false)'
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $currentUser = Auth::user();
        $targetUserId = $args['user_id'] ?? $currentUser->id;
        $includeHistory = $args['include_history'] ?? false;

        try {
            // Permission check
            $roles = $currentUser->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);

            // Only admin can view other users' salaries
            if ($targetUserId != $currentUser->id && !$isAdmin) {
                return [
                    'error' => 'Access denied',
                    'message' => 'You can only view your own salary information.'
                ];
            }

            $targetUser = User::find($targetUserId);
            if (!$targetUser) {
                return [
                    'error' => 'User not found',
                    'message' => "Employee ID {$targetUserId} not found."
                ];
            }

            // First check if Indian Payroll table exists and contains records for this user
            if (\Schema::hasTable('ip_employee_salary_structures')) {
                $indianStructure = \Modules\IndianPayroll\Entities\EmployeeSalaryStructure::where('user_id', $targetUserId)
                    ->where('is_active', true)
                    ->with('components.component')
                    ->first();

                if ($indianStructure) {
                    $components = $indianStructure->components->map(fn($c) => [
                        'name' => $c->component->name,
                        'code' => $c->component->code,
                        'type' => $c->component->type,
                        'monthly_amount' => (float)$c->monthly_amount,
                        'annual_amount' => (float)$c->annual_amount
                    ])->toArray();

                    return [
                        'payroll_type' => 'Indian Payroll',
                        'employee' => [
                            'id' => $targetUser->id,
                            'name' => $targetUser->name,
                            'email' => $targetUser->email,
                            'department' => $targetUser->department->name ?? 'N/A'
                        ],
                        'salary_structure' => [
                            'annual_ctc' => (float)$indianStructure->annual_ctc,
                            'monthly_ctc' => (float)$indianStructure->monthly_ctc,
                            'effective_from' => $indianStructure->effective_from->format('Y-m-d'),
                            'effective_to' => $indianStructure->effective_to ? $indianStructure->effective_to->format('Y-m-d') : null,
                            'is_active' => $indianStructure->is_active,
                            'currency' => 'INR'
                        ],
                        'components' => $components
                    ];
                }
            }

            // Get user salary
            $userSalary = UserSalary::where('user_id', $targetUserId)->first();

            if (!$userSalary) {
                return [
                    'error' => 'No salary information',
                    'message' => "No salary information found for {$targetUser->name}."
                ];
            }

            // Get allowances
            $allowances = UserSalaryAllowance::where('user_id', $targetUserId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get deductions
            $deductions = UserDeduction::where('user_id', $targetUserId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate totals
            $totalAllowances = $allowances->sum('amount');
            $totalDeductions = $deductions->sum('amount');
            $basicSalary = $userSalary->basic ?? 0;
            $grossSalary = $userSalary->gross ?? ($basicSalary + $totalAllowances);
            $netSalary = $grossSalary - $totalDeductions;

            $result = [
                'employee' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'department' => $targetUser->department->name ?? 'N/A'
                ],
                'salary_structure' => [
                    'basic_salary' => $basicSalary,
                    'gross_salary' => $grossSalary,
                    'total_allowances' => $totalAllowances,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'currency' => 'AED' // Default currency, can be made configurable
                ],
                'allowances' => $allowances->map(function ($allowance) {
                    return [
                        'id' => $allowance->id,
                        'title' => $allowance->title,
                        'type' => $allowance->allowance_type ?? 'fixed',
                        'amount' => $allowance->amount,
                        'percentage_amount' => $allowance->percentage_amount ?? null,
                        'is_fixed' => $allowance->is_fixed_for_current_month ?? false,
                        'month' => $allowance->month_code ?? null,
                        'year' => $allowance->year ?? null,
                        'date' => $allowance->date ?? null
                    ];
                })->toArray(),
                'deductions' => $deductions->map(function ($deduction) {
                    return [
                        'id' => $deduction->id,
                        'title' => $deduction->title ?? 'Deduction',
                        'type' => $deduction->deduction_type ?? 'fixed',
                        'amount' => $deduction->amount,
                        'percentage_amount' => $deduction->percentage_amount ?? null,
                        'is_fixed' => $deduction->is_fixed_for_current_month ?? false,
                        'month' => $deduction->month_code ?? null,
                        'year' => $deduction->year ?? null
                    ];
                })->toArray()
            ];

            // Include increment history if requested
            if ($includeHistory) {
                $increments = UserSalaryIncrement::where('user_id', $targetUserId)
                    ->orderBy('effective_date', 'desc')
                    ->get();

                $result['increment_history'] = $increments->map(function ($increment) {
                    return [
                        'id' => $increment->id,
                        'previous_salary' => $increment->previous_salary ?? 0,
                        'new_salary' => $increment->new_salary ?? 0,
                        'increment_amount' => $increment->increment_amount ?? 0,
                        'increment_percentage' => $increment->increment_percentage ?? 0,
                        'effective_date' => $increment->effective_date ? \Carbon\Carbon::parse($increment->effective_date)->format('Y-m-d') : null,
                        'reason' => $increment->reason ?? 'N/A',
                        'approved_by' => $increment->approved_by_name ?? 'N/A'
                    ];
                })->toArray();

                $result['total_increments'] = $increments->count();
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error('GetSalaryBreakdownTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser->id,
                'target_user_id' => $targetUserId
            ]);

            return [
                'error' => 'Failed to fetch salary breakdown',
                'message' => 'Unable to retrieve salary information. Error: ' . $e->getMessage()
            ];
        }
    }
}
