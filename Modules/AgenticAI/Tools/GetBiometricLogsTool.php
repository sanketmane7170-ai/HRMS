<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetBiometricLogsTool - View biometric attendance logs
 * Author: Sanket
 */
class GetBiometricLogsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_biometric_logs';
    }

    public function description(): string
    {
        return 'View biometric device attendance logs. Use when user wants to see raw biometric data.';
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
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Employee ID (optional, defaults to current user)'
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Date in YYYY-MM-DD format (optional, defaults to today)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $userId = $args['user_id'] ?? auth()->id();
            $date = $args['date'] ?? now()->format('Y-m-d');

            $logs = \DB::table('biometric_logs')
                ->where('user_id', $userId)
                ->whereDate('timestamp', $date)
                ->orderBy('timestamp', 'asc')
                ->get();

            if ($logs->isEmpty()) {
                return [
                    'has_logs' => false,
                    'date' => $date,
                    'message' => 'No biometric logs found'
                ];
            }

            $user = \App\Models\User::find($userId);

            return [
                'has_logs' => true,
                'employee' => $user->name ?? 'Unknown',
                'date' => $date,
                'total_logs' => $logs->count(),
                'logs' => $logs->map(function($log) {
                    return [
                        'timestamp' => $log->timestamp,
                        'type' => $log->type, // check-in/check-out
                        'device_id' => $log->device_id ?? 'N/A'
                    ];
                })->toArray(),
                'message' => "Found {$logs->count()} biometric log(s) for {$date}"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
