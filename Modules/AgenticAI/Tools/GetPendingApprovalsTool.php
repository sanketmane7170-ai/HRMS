<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;

class GetPendingApprovalsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_pending_approvals';
    }

    public function description(): string
    {
        return 'Get all pending approval requests that require your action. Use when user asks about pending approvals, requests to approve, or what needs their attention.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        try {
            $approvals = [];
            
            // Get pending leave approvals
            try {
                if (class_exists('\Modules\Leave\Entities\LeaveRequest')) {
                    $leaveApprovals = \Modules\Leave\Entities\LeaveRequest::query()
                        ->where('current_approver_id', $user->id)
                        ->where('status', 'pending')
                        ->with('user', 'leaveType')
                        ->get();
                        
                    foreach ($leaveApprovals as $leave) {
                        $approvals[] = [
                            'type' => 'Leave Request',
                            'employee' => $leave->user->name ?? 'Unknown',
                            'leave_type' => $leave->leaveType->name ?? 'N/A',
                            'from' => $leave->from_date,
                            'to' => $leave->to_date,
                            'days' => $leave->days,
                            'reason' => $leave->reason,
                            'submitted' => $leave->created_at->diffForHumans()
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch leave approvals: ' . $e->getMessage());
            }
            
            // Get pending expense approvals
            try {
                if (class_exists('\Modules\Expense\Entities\Expense')) {
                    // Check for expenses pending HR approval
                    $hrExpenses = \Modules\Expense\Entities\Expense::query()
                        ->where('hr_id', $user->id)
                        ->where('hr_status', 'pending')
                        ->with(['user', 'type'])
                        ->get();
                        
                    // Check for expenses pending Line Manager approval
                    $lmExpenses = \Modules\Expense\Entities\Expense::query()
                        ->where('lm_id', $user->id)
                        ->where('lm_status', 'pending')
                        ->with(['user', 'type'])
                        ->get();
                        
                    $allExpenses = $hrExpenses->merge($lmExpenses);
                        
                    foreach ($allExpenses as $expense) {
                        $approvals[] = [
                            'type' => 'Expense Claim',
                            'employee' => $expense->user->name ?? 'Unknown',
                            'amount' => number_format($expense->amount, 2),
                            'category' => $expense->type->name ?? 'N/A',
                            'description' => $expense->name,
                            'date' => $expense->date,
                            'submitted' => $expense->created_at->diffForHumans()
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch expense approvals: ' . $e->getMessage());
            }
            
            if (empty($approvals)) {
                return [
                    'message' => 'You have no pending approvals at the moment.',
                    'approvals' => [],
                    'count' => 0
                ];
            }
            
            return [
                'approvals' => $approvals,
                'count' => count($approvals)
            ];
            
        } catch (\Exception $e) {
            \Log::error('GetPendingApprovalsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch approvals',
                'message' => 'Unable to retrieve pending approvals at this time.'
            ];
        }
    }
}
