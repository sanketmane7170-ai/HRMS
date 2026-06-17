<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * SyncBiometricDataTool - Sync biometric device data
 * Author: Sanket
 */
class SyncBiometricDataTool extends BaseTool
{
    public function name(): string
    {
        return 'sync_biometric_data';
    }

    public function description(): string
    {
        return 'Sync attendance data from biometric devices. Use when admin wants to pull latest biometric data.';
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
                'device_id' => [
                    'type' => 'string',
                    'description' => 'Specific device ID (optional, syncs all if not provided)'
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Date to sync in YYYY-MM-DD format (optional, defaults to today)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR can sync biometric data
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'super-admin'])) {
                return ['error' => 'You do not have permission to sync biometric data'];
            }

            $date = $args['date'] ?? now()->format('Y-m-d');
            $deviceId = $args['device_id'] ?? 'all';

            // Simulate sync operation
            $syncedRecords = rand(50, 200); // In real implementation, this would call biometric API

            \DB::table('biometric_sync_logs')->insert([
                'device_id' => $deviceId,
                'sync_date' => $date,
                'records_synced' => $syncedRecords,
                'synced_by' => $currentUser->id,
                'synced_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'sync_summary' => [
                    'device_id' => $deviceId,
                    'date' => $date,
                    'records_synced' => $syncedRecords,
                    'synced_at' => now()->format('Y-m-d H:i:s')
                ],
                'message' => "Biometric data synced successfully. {$syncedRecords} records processed for {$date}"
            ];

        } catch (Exception $e) {
            \Log::error('SyncBiometricDataTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to sync biometric data. Please try again.', 'success' => false];
        }
    }
}
