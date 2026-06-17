<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * RequestOvertimeTool - Request overtime
 * Author: Sanket
 */
class RequestOvertimeTool extends BaseTool
{
    public function name(): string
    {
        return 'request_overtime';
    }

    public function description(): string
    {
        return 'Submit overtime request. Use when employee wants to request overtime.';
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
                'date' => [
                    'type' => 'string',
                    'description' => 'Overtime date in YYYY-MM-DD format'
                ],
                'hours' => [
                    'type' => 'number',
                    'description' => 'Number of overtime hours'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for overtime'
                ],
            ],
            'required' => ['date', 'hours', 'reason']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return ['error' => 'User not authenticated'];
            }

            $overtimeId = \DB::table('overtime_requests')->insertGetId([
                'user_id' => $user->id,
                'date' => $args['date'],
                'hours' => $args['hours'],
                'reason' => $args['reason'],
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'overtime_id' => $overtimeId,
                'date' => $args['date'],
                'hours' => $args['hours'],
                'status' => 'pending',
                'message' => "Overtime request submitted for {$args['date']}: {$args['hours']} hours"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
