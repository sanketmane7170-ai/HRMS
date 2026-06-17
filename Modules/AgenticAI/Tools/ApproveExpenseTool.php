<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Expense\Entities\Expense;

class ApproveExpenseTool extends BaseTool
{
    public function name(): string
    {
        return 'approve_expense';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Approve a pending expense claim. Use when user wants to approve an expense they are authorized to approve.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'expense_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the expense to approve'
                ],
                'comment' => [
                    'type' => 'string',
                    'description' => 'Optional comment for the approval'
                ]
            ],
            'required' => ['expense_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $expenseId = $args['expense_id'] ?? null;
        $comment = $args['comment'] ?? '';
        
        if (!$expenseId) {
            return [
                'error' => 'Missing expense ID',
                'message' => 'Please provide the expense ID to approve.'
            ];
        }
        
        try {
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);

            $query = Expense::query()->where('id', $expenseId);

            // Admin override: Admins can see/approve any expense
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
                    'message' => "Expense ID {$expenseId} not found or you don't have permission to approve it."
                ];
            }

            // Determine what the user is approving
            $updateData = [];
            if ($isAdmin) {
                // Admin approves both if they are pending
                if ($expense->hr_status == 'pending') $updateData['hr_status'] = 'approved';
                if ($expense->lm_status == 'pending') $updateData['lm_status'] = 'approved';
                $updateData['hr_comments'] = $comment;
                $updateData['lm_comments'] = $comment;
            } else {
                if ($expense->hr_id == $user->id) {
                    $updateData['hr_status'] = 'approved';
                    $updateData['hr_comments'] = $comment;
                }
                if ($expense->lm_id == $user->id) {
                    $updateData['lm_status'] = 'approved';
                    $updateData['lm_comments'] = $comment;
                }
            }

            if (empty($updateData)) {
                return [
                    'error' => 'Already approved',
                    'message' => "You have already approved this expense or it's not in a state where you can approve it."
                ];
            }

            $expense->update($updateData);

            return [
                'success' => true,
                'message' => "Expense ID {$expenseId} has been approved successfully.",
                'expense' => [
                    'id' => $expense->id,
                    'employee' => $expense->user->name ?? 'Unknown',
                    'amount' => $expense->amount,
                    'status' => 'approved'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('ApproveExpenseTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'expense_id' => $expenseId
            ]);
            
            return [
                'error' => 'Failed to approve expense',
                'message' => 'Unable to approve the expense. Error: ' . $e->getMessage()
            ];
        }
    }
}
