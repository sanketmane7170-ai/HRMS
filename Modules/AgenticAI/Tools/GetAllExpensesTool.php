<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetAllExpensesTool extends BaseTool
{
    public function name(): string
    {
        return 'get_all_expenses';
    }

    public function description(): string
    {
        return 'Get ALL expense claims in the system (Admin only). Use when admin/manager asks to see all expenses, company expenses, or expense overview. Shows expenses from all employees.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'description' => 'Filter by status: pending, approved, rejected, or all (default)',
                    'enum' => ['pending', 'approved', 'rejected', 'all']
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of expenses to show (default 20)',
                    'default' => 20
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        // Check if user has admin/manager permissions (case-insensitive)
        $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
        $isAdmin = in_array('admin', $roles);
        $isAuthorized = $isAdmin || array_intersect(['hr', 'manager', 'ceo', 'owner', 'director'], $roles);

        if (!$isAuthorized) {
            return [
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to view all expenses. Use get_expense_status to see your own expenses.'
            ];
        }
        
        try {
            $status = $args['status'] ?? 'all';
            $limit = min($args['limit'] ?? 20, 100);
            
            $query = DB::table('expenses')
                ->join('users', 'expenses.user_id', '=', 'users.id')
                ->leftJoin('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id')
                ->select(
                    'expenses.id',
                    'expenses.name as description',
                    'expenses.amount',
                    'expenses.date',
                    'expenses.hr_status',
                    'expenses.lm_status',
                    'expenses.hr_comments',
                    'expenses.lm_comments',
                    'expenses.created_at',
                    'users.name as employee_name',
                    'expense_types.name as expense_type'
                )
                ->orderBy('expenses.created_at', 'desc');
            
            // Apply status filter
            if ($status !== 'all') {
                if ($status === 'pending') {
                    $query->where(function($q) {
                        $q->where('expenses.hr_status', 'pending')
                          ->orWhere('expenses.lm_status', 'pending');
                    });
                } elseif ($status === 'approved') {
                    $query->where('expenses.hr_status', 'approved')
                          ->where('expenses.lm_status', 'approved');
                } elseif ($status === 'rejected') {
                    $query->where(function($q) {
                        $q->where('expenses.hr_status', 'rejected')
                          ->orWhere('expenses.lm_status', 'rejected');
                    });
                }
            }
            
            $expenses = $query->limit($limit)->get();
            
            if ($expenses->isEmpty()) {
                return [
                    'message' => 'No expense claims found in the system.',
                    'expenses' => [],
                    'count' => 0
                ];
            }
            
            $result = $expenses->map(function($e) {
                // Determine overall status
                $overallStatus = 'Pending';
                if ($e->hr_status == 'rejected' || $e->lm_status == 'rejected') {
                    $overallStatus = 'Rejected';
                } elseif ($e->hr_status == 'approved' && $e->lm_status == 'approved') {
                    $overallStatus = 'Approved';
                } elseif ($e->lm_status == 'approved' && $e->hr_status == 'pending') {
                    $overallStatus = 'Manager Approved, Pending HR';
                } elseif ($e->hr_status == 'approved' && $e->lm_status == 'pending') {
                    $overallStatus = 'HR Approved, Pending Manager';
                }
                
                return [
                    'id' => $e->id,
                    'employee' => $e->employee_name,
                    'description' => $e->description,
                    'type' => $e->expense_type ?? 'N/A',
                    'amount' => '₹' . number_format($e->amount, 2),
                    'date' => $e->date,
                    'status' => $overallStatus,
                    'hr_status' => ucfirst($e->hr_status),
                    'lm_status' => ucfirst($e->lm_status),
                    'submitted' => \Carbon\Carbon::parse($e->created_at)->diffForHumans()
                ];
            })->toArray();
            
            return [
                'expenses' => $result,
                'count' => count($result),
                'total_amount' => '₹' . number_format($expenses->sum('amount'), 2)
            ];
            
        } catch (\Exception $e) {
            \Log::error('GetAllExpensesTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch expenses',
                'message' => 'Unable to retrieve expense data. Error: ' . $e->getMessage()
            ];
        }
    }
}
