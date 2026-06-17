<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Expense\Entities\Expense;
use Illuminate\Support\Facades\Auth;
use Modules\AgenticAI\Tools\BaseTool;

class GetExpenseStatusTool extends BaseTool
{
    public function name(): string
    {
        return 'get_expense_status';
    }

    public function description(): string
    {
        return 'Get MY recent expense claims and their status.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => [
                    'type' => 'integer',
                    'default' => 5,
                    'description' => 'Number of recent expenses to show.'
                ]
            ],
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (!$user) {
            return ['error' => 'User not logged in.'];
        }

        try {
            $limit = $args['limit'] ?? 5;
            $expenses = Expense::with('type')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return [
                'expenses' => $expenses->map(function($e) {
                    return [
                        'id' => $e->id,
                        'title' => $e->name,
                        'amount' => $e->amount,
                        'date' => $e->date,
                        'type' => $e->type->name ?? 'N/A',
                        'status' => $e->status->value ?? $e->status,
                        'remark' => $e->remark
                    ];
                }),
                'count' => $expenses->count()
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to fetch expenses', 'message' => $e->getMessage()];
        }
    }
}
