<?php

namespace Modules\AgenticAI\Tools;

use Modules\NotificationManager\Entities\AlertRecipient;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Exception;

class ConfigureAlertRecipientsTool extends BaseTool
{
    public function name(): string
    {
        return 'configure_alert_recipients';
    }

    public function description(): string
    {
        return 'Configure who receives system alerts. Can add/remove users or roles from the recipient list.';
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
                    'enum' => ['list', 'add', 'remove'],
                    'description' => 'Action to perform.'
                ],
                'user_id' => ['type' => 'integer', 'description' => 'ID of the user.'],
                'role_id' => ['type' => 'integer', 'description' => 'ID of the role.'],
                'alert_status' => ['type' => 'integer', 'default' => 1, 'description' => '1 for active, 0 for inactive.'],
                'id' => ['type' => 'integer', 'description' => 'Required for remove action.']
            ],
            'required' => ['action']
        ];
    }

    public function execute(array $args): mixed
    {
        $action = $args['action'];

        try {
            switch ($action) {
                case 'list':
                    $recipients = AlertRecipient::with(['user', 'role'])->get();
                    return [
                        'recipients' => $recipients->map(function($r) {
                            return [
                                'id' => $r->id,
                                'user' => $r->user->name ?? 'N/A',
                                'role' => $r->role->name ?? 'N/A',
                                'status' => $r->alert_status ? 'Active' : 'Inactive'
                            ];
                        })->toArray()
                    ];

                case 'add':
                    if (empty($args['user_id']) && empty($args['role_id'])) {
                        throw new Exception('Either user_id or role_id is required.');
                    }
                    $recipient = AlertRecipient::create([
                        'user_id' => $args['user_id'] ?? null,
                        'role_id' => $args['role_id'] ?? null,
                        'alert_status' => $args['alert_status'] ?? 1,
                    ]);
                    return ['success' => true, 'message' => "Alert recipient added successfully.", 'id' => $recipient->id];

                case 'remove':
                    if (empty($args['id'])) throw new Exception('ID required for remove.');
                    $recipient = AlertRecipient::findOrFail($args['id']);
                    $recipient->delete();
                    return ['success' => true, 'message' => "Alert recipient removed."];

                default:
                    return ['error' => 'Invalid action.'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
