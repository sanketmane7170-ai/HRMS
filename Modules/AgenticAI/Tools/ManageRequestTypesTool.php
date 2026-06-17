<?php

namespace Modules\AgenticAI\Tools;

use Modules\GeneralRequest\Entities\GeneralRequestType;
use Exception;

class ManageRequestTypesTool extends BaseTool
{
    public function name(): string
    {
        return 'manage_request_types';
    }

    public function description(): string
    {
        return 'List, create, or update general request types (e.g., Stationaries, Maintenance).';
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
                    'enum' => ['list', 'create', 'update', 'delete'],
                    'description' => 'Action to perform.'
                ],
                'name' => ['type' => 'string', 'description' => 'Name of the request type.'],
                'id' => ['type' => 'integer', 'description' => 'Required for update/delete.']
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
                    $types = GeneralRequestType::all();
                    return ['request_types' => $types->toArray()];

                case 'create':
                    if (empty($args['name'])) throw new Exception('Name required.');
                    $type = GeneralRequestType::create(['name' => $args['name']]);
                    return ['success' => true, 'message' => "Request type '{$type->name}' created.", 'id' => $type->id];

                case 'update':
                    if (empty($args['id']) || empty($args['name'])) throw new Exception('ID and Name required.');
                    $type = GeneralRequestType::findOrFail($args['id']);
                    $type->update(['name' => $args['name']]);
                    return ['success' => true, 'message' => "Request type updated."];

                case 'delete':
                    if (empty($args['id'])) throw new Exception('ID required.');
                    $type = GeneralRequestType::findOrFail($args['id']);
                    $type->delete();
                    return ['success' => true, 'message' => "Request type deleted."];

                default:
                    return ['error' => 'Invalid action.'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
