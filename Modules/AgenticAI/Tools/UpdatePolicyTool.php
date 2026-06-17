<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * UpdatePolicyTool - Update company policy
 * Author: Sanket
 */
class UpdatePolicyTool extends BaseTool
{
    public function name(): string
    {
        return 'update_policy';
    }

    public function description(): string
    {
        return 'Update an existing company policy. Use when HR wants to modify a policy.';
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
                'policy_id' => [
                    'type' => 'integer',
                    'description' => 'Policy ID to update'
                ],
                'title' => [
                    'type' => 'string',
                    'description' => 'New title (optional)'
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'New content (optional)'
                ],
            ],
            'required' => ['policy_id']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $policy = \DB::table('company_policies')->find($args['policy_id']);
            
            if (!$policy) {
                return ['error' => "Policy with ID {$args['policy_id']} not found"];
            }

            $updateData = ['updated_at' => now()];
            if (!empty($args['title'])) $updateData['title'] = $args['title'];
            if (!empty($args['content'])) {
                $updateData['content'] = $args['content'];
                $updateData['version'] = $policy->version + 1;
            }

            \DB::table('company_policies')
                ->where('id', $args['policy_id'])
                ->update($updateData);

            return [
                'success' => true,
                'policy_id' => $args['policy_id'],
                'message' => "Policy updated successfully"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
