<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Expense\Entities\Expense;

class RejectExpenseTool extends BaseTool
{
    public function name(): string
    {
        return 'reject_expense';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Reject a pending expense claim. Use when user wants to reject or deny an expense they are authorized to review.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'expense_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the expense to reject'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for rejection (required)'
                ]
            ],
            'required' => ['expense_id', 'reason']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $expenseId = $args['expense_id'] ?? null;
        $reason = $args['reason'] ?? '';
        
        if (!$expenseId || empty($reason)) {
            return [
                'error' => 'Missing required fields',
                'message' => 'Please provide both expense ID and reason for rejection.'
            ];
        }
        
        try {
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);

            $query = Expense::query()->where('id', $expenseId);

            // Admin override: Admins can see/reject any expense
            if (!$isAdmin) {
                $query->where(function ($q) use ($user) {
                    $q->where('hr_id', $user->id)
                      ->orWhere('lm_id', $user->id);
                });
            }

            $expense = $query->first();

            if (!$expense) {
                return [
                    'error' => 'Expense not found',
                    'message' => "Expense ID {$expenseId} not found or you don't have permission to reject it."
                ];
            }

            // Determine what the user is rejecting
            $updateData = [];
            if ($isAdmin) {
                // Admin rejects both if they are pending
                if ($expense->hr_status == 'pending') $updateData['hr_status'] = 'rejected';
                if ($expense->lm_status == 'pending') $updateData['lm_status'] = 'rejected';
                $updateData['hr_comments'] = $reason;
                $updateData['lm_comments'] = $reason;
            } else {
                if ($expense->hr_id == $user->id) {
                    $updateData['hr_status'] = 'rejected';
                    $updateData['hr_comments'] = $reason;
                }
                if ($expense->lm_id == $user->id) {
                    $updateData['lm_status'] = 'rejected';
                    $updateData['lm_comments'] = $reason;
                }
            }

            if (empty($updateData)) {
                return [
                    'error' => 'Already processed',
                    'message' => "You have already processed this expense or it's not in a state where you can reject it."
                ];
            }

            $expense->update($updateData);

            return [
                'success' => true,
                'message' => "Expense ID {$expenseId} has been rejected.",
                'expense' => [
                    'id' => $expense->id,
                    'employee' => $expense->user->name ?? 'Unknown',
                    'amount' => $expense->amount,
                    'status' => 'rejected',
                    'reason' => $reason
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('RejectExpenseTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'expense_id' => $expenseId
            ]);
            
            return [
                'error' => 'Failed to reject expense',
                'message' => 'Unable to reject the expense. Error: ' . $e->getMessage()
            ];
        }
    }
}
