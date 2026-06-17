<?php

namespace Modules\AgenticAI\Tools;

use Modules\Payroll\Entities\UserOvertime;
use App\Models\User;
use Exception;

class ProcessOvertimeTool extends BaseTool
{
    public function name(): string
    {
        return 'process_overtime';
    }

    public function description(): string
    {
        return 'Record or list overtime for employees. Required: user_id, hours, date, and overtime_type.';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['record', 'list'],
                    'description' => 'Action to perform.'
                ],
                'user_id' => ['type' => 'integer', 'description' => 'ID of the employee.'],
                'hours' => ['type' => 'number', 'description' => 'Number of overtime hours.'],
                'date' => ['type' => 'string', 'description' => 'Date of overtime. Format: YYYY-MM-DD'],
                'overtime_type' => [
                    'type' => 'string', 
                    'enum' => ['normal', 'holiday', 'weekend'],
                    'description' => 'Type of overtime.'
                ],
                'rate_per_hour' => ['type' => 'number', 'description' => 'Optional custom rate per hour.'],
            ],
            'required' => ['action']
        ];
    }

    public function execute(array $args): mixed
    {
        $action = $args['action'];

        try {
            switch ($action) {
                case 'record':
                    if (empty($args['user_id']) || empty($args['hours']) || empty($args['date'])) {
                        throw new Exception('user_id, hours, and date are required for record.');
                    }
                    
                    $date = \Carbon\Carbon::parse($args['date']);
                    
                    $overtime = UserOvertime::create([
                        'user_id' => $args['user_id'],
                        'date' => $args['date'],
                        'hours' => $args['hours'],
                        'overtime_type' => $args['overtime_type'] ?? 'normal',
                        'rate_per_hour' => $args['rate_per_hour'] ?? 0, // Should ideally be fetched from policy
                        'month_code' => $date->format('m'),
                        'year' => $date->format('Y'),
                        'is_system_add' => 0,
                    ]);
                    
                    return [
                        'success' => true, 
                        'message' => "Overtime of {$args['hours']} hours recorded for user ID {$args['user_id']} on {$args['date']}.",
                        'overtime_id' => $overtime->id
                    ];

                case 'list':
                    if (empty($args['user_id'])) {
                        throw new Exception('user_id is required for list.');
                    }
                    $overtimes = UserOvertime::where('user_id', $args['user_id'])
                        ->orderBy('date', 'desc')
                        ->limit(10)
                        ->get();
                        
                    return [
                        'overtimes' => $overtimes->map(function($o) {
                            return [
                                'id' => $o->id,
                                'date' => $o->date,
                                'hours' => $o->hours,
                                'type' => $o->overtime_type,
                                'amount' => $o->calculated_amount
                            ];
                        })->toArray()
                    ];

                default:
                    return ['error' => 'Invalid action.'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
