<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Expense\Entities\Expense;

class CancelExpenseTool extends BaseTool
{
    public function name(): string
    {
        return 'cancel_expense';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Cancel a pending expense claim. Use when user wants to cancel their expense.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'expense_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the expense to cancel'
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
            return ['error' => 'Missing expense ID', 'message' => 'Please provide the expense ID to cancel.'];
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
            
            $expense->update(['status' => 'cancelled']);
            
            return [
                'success' => true,
                'message' => "Expense ID {$expenseId} cancelled successfully.",
                'expense' => ['id' => $expense->id, 'status' => 'cancelled']
            ];
        } catch (\Exception $e) {
            \Log::error('CancelExpenseTool failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return ['error' => 'Failed to cancel', 'message' => $e->getMessage()];
        }
    }
}
