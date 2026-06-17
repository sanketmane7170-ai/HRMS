<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * SendBulkNotificationTool - Send bulk notifications
 * Author: Sanket
 */
class SendBulkNotificationTool extends BaseTool
{
    public function name(): string
    {
        return 'send_bulk_notification';
    }

    public function description(): string
    {
        return 'Send notification to multiple employees. Use when admin wants to broadcast a message.';
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
                'title' => [
                    'type' => 'string',
                    'description' => 'Notification title'
                ],
                'message' => [
                    'type' => 'string',
                    'description' => 'Notification message'
                ],
                'user_ids' => [
                    'type' => 'array',
                    'description' => 'Employee IDs (optional, sends to all if not provided)',
                    'items' => ['type' => 'integer']
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Send to specific department (optional)'
                ],
            ],
            'required' => ['title', 'message']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can send bulk notifications
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to send bulk notifications'];
            }

            $query = User::where('status', 'active');

            if (!empty($args['user_ids'])) {
                $query->whereIn('id', $args['user_ids']);
            } elseif (!empty($args['department_id'])) {
                $query->where('department_id', $args['department_id']);
            }

            $users = $query->get();

            if ($users->isEmpty()) {
                return [
                    'error' => 'No recipients found',
                    'success' => false
                ];
            }

            $sentCount = 0;
            foreach ($users as $user) {
                \DB::table('notifications')->insert([
                    'user_id' => $user->id,
                    'title' => $args['title'],
                    'message' => $args['message'],
                    'type' => 'bulk',
                    'sent_by' => $currentUser->id,
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $sentCount++;
            }

            return [
                'success' => true,
                'notification' => [
                    'title' => $args['title'],
                    'recipients_count' => $sentCount,
                    'sent_at' => now()->format('Y-m-d H:i:s')
                ],
                'message' => "Notification sent to {$sentCount} employee(s)"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
