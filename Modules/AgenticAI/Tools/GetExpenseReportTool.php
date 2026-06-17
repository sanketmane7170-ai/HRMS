<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetExpenseReportTool - Generate expense report
 * Author: Sanket
 */
class GetExpenseReportTool extends BaseTool
{
    public function name(): string
    {
        return 'get_expense_report';
    }

    public function description(): string
    {
        return 'Generate expense report by department or employee. Use when finance needs expense analytics.';
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
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date in YYYY-MM-DD format'
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date in YYYY-MM-DD format'
                ],
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Employee ID (optional)'
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Department ID (optional)'
                ],
            ],
            'required' => ['start_date', 'end_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $query = \DB::table('expenses')
                ->join('users', 'expenses.user_id', '=', 'users.id')
                ->whereBetween('expenses.expense_date', [$args['start_date'], $args['end_date']])
                ->where('expenses.status', 'approved')
                ->select(
                    'users.id',
                    'users.name',
                    \DB::raw('SUM(expenses.amount) as total_expenses'),
                    \DB::raw('COUNT(expenses.id) as expense_count')
                )
                ->groupBy('users.id', 'users.name');

            if (!empty($args['user_id'])) {
                $query->where('users.id', $args['user_id']);
            }

            if (!empty($args['department_id'])) {
                $query->where('users.department_id', $args['department_id']);
            }

            $expenseData = $query->get();

            if ($expenseData->isEmpty()) {
                return [
                    'has_data' => false,
                    'period' => "{$args['start_date']} to {$args['end_date']}",
                    'message' => 'No expense data found'
                ];
            }

            $totalExpenses = $expenseData->sum('total_expenses');

            return [
                'has_data' => true,
                'period' => "{$args['start_date']} to {$args['end_date']}",
                'summary' => [
                    'total_employees' => $expenseData->count(),
                    'total_expenses' => number_format($totalExpenses, 2),
                    'average_per_employee' => number_format($totalExpenses / $expenseData->count(), 2)
                ],
                'employees' => $expenseData->map(function($emp) {
                    return [
                        'id' => $emp->id,
                        'name' => $emp->name,
                        'total_expenses' => number_format($emp->total_expenses, 2),
                        'expense_count' => $emp->expense_count
                    ];
                })->toArray(),
                'message' => "Expense report: {$expenseData->count()} employees, Total: " . number_format($totalExpenses, 2)
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
