<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetTaxReportTool - Generate tax deduction report
 * Author: Sanket
 */
class GetTaxReportTool extends BaseTool
{
    public function name(): string
    {
        return 'get_tax_report';
    }

    public function description(): string
    {
        return 'Generate tax deduction report for employees. Use when finance needs tax reports.';
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
                'financial_year' => [
                    'type' => 'string',
                    'description' => 'Financial year (e.g., "2025-2026", optional, defaults to current FY)'
                ],
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Specific employee ID (optional, all employees if not provided)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $currentYear = now()->year;
            $fy = $args['financial_year'] ?? "{$currentYear}-" . ($currentYear + 1);

            $query = \DB::table('employee_taxes')
                ->join('users', 'employee_taxes.user_id', '=', 'users.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    \DB::raw('SUM(employee_taxes.amount) as total_tax'),
                    \DB::raw('COUNT(employee_taxes.id) as tax_entries')
                )
                ->where('employee_taxes.status', 'active')
                ->groupBy('users.id', 'users.name', 'users.email');

            if (!empty($args['user_id'])) {
                $query->where('users.id', $args['user_id']);
            }

            $taxData = $query->get();

            if ($taxData->isEmpty()) {
                return [
                    'has_data' => false,
                    'financial_year' => $fy,
                    'message' => 'No tax data found'
                ];
            }

            $totalTax = $taxData->sum('total_tax');

            return [
                'has_data' => true,
                'financial_year' => $fy,
                'summary' => [
                    'total_employees' => $taxData->count(),
                    'total_tax_deducted' => number_format($totalTax, 2),
                    'average_tax_per_employee' => number_format($totalTax / $taxData->count(), 2)
                ],
                'employees' => $taxData->map(function($emp) {
                    return [
                        'id' => $emp->id,
                        'name' => $emp->name,
                        'email' => $emp->email,
                        'total_tax' => number_format($emp->total_tax, 2),
                        'tax_entries' => $emp->tax_entries
                    ];
                })->toArray(),
                'message' => "Tax report for FY {$fy}: {$taxData->count()} employees, Total tax: " . number_format($totalTax, 2)
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
