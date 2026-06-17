<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Expense\Entities\Expense;
use Illuminate\Support\Facades\Auth;
use Modules\AgenticAI\Tools\BaseTool;

class FileExpenseTool extends BaseTool
{
    public function name(): string
    {
        return 'file_expense';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'File a new reimbursement/expense claim. Requires title, amount, type_id, and date.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Short title of the expense (e.g., "Client Lunch", "Taxi to Airport").'
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Amount to claim.'
                ],
                'type_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the expense type (1=Travel, 2=Food, etc.). If unknown, the Agent should infer or ask.'
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Date of expense (YYYY-MM-DD).'
                ],
                'remarks' => [
                    'type' => 'string',
                    'description' => 'Optional details / description.'
                ]
            ],
            'required' => ['title', 'amount', 'type_id', 'date'],
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (!$user) {
            return ['error' => 'User not logged in.'];
        }

        try {
            $expense = Expense::create([
                'name' => $args['title'],
                'amount' => $args['amount'],
                'expense_type_id' => $args['type_id'],
                'date' => $args['date'],
                'remark' => $args['remarks'] ?? null,
                'user_id' => $user->id,
                'created_by' => $user->id,
                'payment_mode' => 'cash', // Default
                'hr_status' => 'pending',
                'lm_status' => 'pending'
            ]);

            return [
                'success' => true,
                'message' => 'Expense claim filed successfully.',
                'expense_id' => $expense->id,
                'status' => 'pending'
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to file expense', 'message' => $e->getMessage()];
        }
    }
}
