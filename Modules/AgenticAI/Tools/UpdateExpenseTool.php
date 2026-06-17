<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Expense\Entities\Expense;

class UpdateExpenseTool extends BaseTool
{
    public function name(): string
    {
        return 'update_expense';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Update a pending expense claim. Use when user wants to modify expense amount, category, or remark.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'expense_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the expense to update'
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'New expense amount'
                ],
                'remark' => [
                    'type' => 'string',
                    'description' => 'Updated remark/description'
                ]
            ],
            'required' => ['expense_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $expenseId = $args['expense_id'] ?? null;
        
        if (!$expenseId) {
            return ['error' => 'Missing expense ID', 'message' => 'Please provide the expense ID to update.'];
        }
        
        try {
            $expense = Expense::query()
                ->where('id', $expenseId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();
                
            if (!$expense) {
                return ['error' => 'Expense not found', 'message' => "Expense ID {$expenseId} not found or already processed."];
            }
            
            $updates = [];
            if (isset($args['amount'])) $updates['amount'] = $args['amount'];
            if (isset($args['remark'])) $updates['remark'] = $args['remark'];
            
            if (empty($updates)) {
                return ['error' => 'No updates', 'message' => 'Please provide at least one field to update.'];
            }
            
            $expense->update($updates);
            
            return [
                'success' => true,
                'message' => "Expense ID {$expenseId} updated successfully.",
                'expense' => [
                    'id' => $expense->id,
                    'amount' => $expense->amount,
                    'remark' => $expense->remark,
                    'status' => 'pending'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('UpdateExpenseTool failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return ['error' => 'Failed to update', 'message' => $e->getMessage()];
        }
    }
}
