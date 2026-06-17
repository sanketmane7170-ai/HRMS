<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Exception;

class ManageAccessTool extends BaseTool
{
    public function name(): string
    {
        return 'manage_access';
    }

    public function description(): string
    {
        return 'Assign or revoke security roles (e.g., admin, hr, employee) for a user. Use this to promote employees or manage administrative access.';
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
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the user whose access is being modified.'
                ],
                'role_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the role to assign or revoke.'
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['assign', 'revoke', 'sync'],
                    'description' => 'Action: assign (add role), revoke (remove role), or sync (replace all roles with this one).'
                ]
            ],
            'required' => ['user_id', 'role_id', 'action']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - only admin/HR users can manage access roles (prevent privilege escalation)
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required', 'message' => 'You must be logged in to manage access.'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'super-admin'])) {
                return ['error' => 'Permission denied', 'message' => 'Only Admin or HR can manage user roles.'];
            }

            $user = User::findOrFail($args['user_id']);
            $role = Role::findById($args['role_id']);

            switch ($args['action']) {
                case 'assign':
                    $user->assignRole($role);
                    $msg = "Role '{$role->name}' assigned to '{$user->name}'.";
                    break;
                case 'revoke':
                    $user->removeRole($role);
                    $msg = "Role '{$role->name}' revoked from '{$user->name}'.";
                    break;
                case 'sync':
                    $user->syncRoles($role);
                    $msg = "Roles for '{$user->name}' synced to '{$role->name}' only.";
                    break;
            }

            return [
                'success' => true,
                'message' => $msg,
                'current_roles' => $user->getRoleNames()->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Access management failed',
                'message' => 'Unable to manage access. Please try again.'
            ];
        }
    }
}
