<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Payroll\Entities\UserPaySlip;
use Illuminate\Support\Facades\Auth;

class GetSalarySlipTool extends BaseTool
{
    public function name(): string
    {
        return 'get_salary_slip';
    }

    public function description(): string
    {
        return 'Get the salary slip/payslip for the current user. defaults to the latest slip if month/year not provided.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'month' => [
                    'type' => 'string',
                    'description' => 'Month name or code (e.g., "01" for Jan, "12" for Dec). Optional.'
                ],
                'year' => [
                    'type' => 'integer',
                    'description' => 'Year (e.g., 2025). Optional.'
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
            // First check if Indian Payroll table exists and contains records for this user
            if (\Schema::hasTable('ip_payslips')) {
                $indianQuery = \Modules\IndianPayroll\Entities\Payslip::where('user_id', $user->id);
                if (isset($args['month'])) {
                    $m = $args['month'];
                    // If it is a string month name (e.g. "June")
                    if (!is_numeric($m)) {
                        try {
                            $m = (int)\Carbon\Carbon::parse($m)->format('n');
                        } catch (\Exception $ex) {
                            $m = 0;
                        }
                    } else {
                        $m = (int)$m;
                    }
                    if ($m > 0 && $m <= 12) {
                        $indianQuery->whereHas('run', fn($q) => $q->where('month', $m));
                    }
                }
                if (isset($args['year'])) {
                    $indianQuery->whereHas('run', fn($q) => $q->where('year', (int)$args['year']));
                }

                $indianSlip = $indianQuery->with('run', 'components.component')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($indianSlip) {
                    $components = $indianSlip->components->map(fn($c) => [
                        'name' => $c->component->name,
                        'code' => $c->component->code,
                        'type' => $c->type,
                        'amount' => (float)$c->amount
                    ])->toArray();

                    return [
                        'payroll_type' => 'Indian Payroll',
                        'payslip' => [
                            'id' => $indianSlip->id,
                            'month' => \Carbon\Carbon::create(null, $indianSlip->run->month, 1)->format('F'),
                            'year' => $indianSlip->run->year,
                            'gross_earnings' => (float)$indianSlip->gross_earnings,
                            'total_deductions' => (float)$indianSlip->total_statutory_deductions,
                            'net_pay' => (float)$indianSlip->net_pay,
                            'days_in_period' => $indianSlip->days_in_period,
                            'paid_days' => (float)$indianSlip->paid_days,
                            'loss_of_pay_days' => (float)$indianSlip->loss_of_pay_days,
                            'status' => $indianSlip->status,
                            'components' => $components
                        ]
                    ];
                }
            }

            $query = UserPaySlip::where('user_id', $user->id);
            
            if (isset($args['month'])) {
                $query->where('month_code', $args['month']);
            }
            if (isset($args['year'])) {
                $query->where('year', $args['year']);
            }

            $slip = $query->orderBy('year', 'desc')
                          ->orderBy('month_code', 'desc')
                          ->first();

            if (!$slip) {
                return [
                    'message' => 'No salary slip found for the requested period.',
                    'status' => 'No Data'
                ];
            }

            return [
                'payslip' => [
                    'id' => $slip->id,
                    'month' => $slip->month_code,
                    'year' => $slip->year,
                    'basic' => $slip->basic,
                    'net_salary' => $slip->total_net_salary,
                    'status' => $slip->status,
                    'generation_date' => $slip->slip_generation_date
                ]
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to fetch salary slip',
                'message' => $e->getMessage()
            ];
        }
    }
}
