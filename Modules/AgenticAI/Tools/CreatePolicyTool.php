<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * CreatePolicyTool - Create company policy
 * Author: Sanket
 */
class CreatePolicyTool extends BaseTool
{
    public function name(): string
    {
        return 'create_policy';
    }

    public function description(): string
    {
        return 'Create a new company policy. Use when HR wants to create a policy.';
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
                    'description' => 'Policy title'
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'Policy content'
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Policy category (optional)'
                ],
            ],
            'required' => ['title', 'content']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR can create policies
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'super-admin'])) {
                return ['error' => 'You do not have permission to create policies'];
            }

            $policyId = \DB::table('company_policies')->insertGetId([
                'title' => $args['title'],
                'content' => $args['content'],
                'category' => $args['category'] ?? 'General',
                'version' => 1,
                'status' => 'active',
                'created_by' => $currentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'policy_id' => $policyId,
                'title' => $args['title'],
                'message' => "Policy '{$args['title']}' created successfully"
            ];

        } catch (Exception $e) {
            \Log::error('CreatePolicyTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to create policy. Please try again.', 'success' => false];
        }
    }
}
