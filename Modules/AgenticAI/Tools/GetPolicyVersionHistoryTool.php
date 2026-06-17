<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetPolicyVersionHistoryTool - View policy version history
 * Author: Sanket
 */
class GetPolicyVersionHistoryTool extends BaseTool
{
    public function name(): string
    {
        return 'get_policy_version_history';
    }

    public function description(): string
    {
        return 'View version history of a company policy. Use when user wants to see policy changes over time.';
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
                'policy_id' => [
                    'type' => 'integer',
                    'description' => 'Policy ID'
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

            $versions = \DB::table('policy_versions')
                ->where('policy_id', $args['policy_id'])
                ->orderBy('version', 'desc')
                ->get();

            if ($versions->isEmpty()) {
                return [
                    'has_versions' => false,
                    'current_version' => $policy->version,
                    'policy_title' => $policy->title,
                    'message' => 'No version history found'
                ];
            }

            return [
                'has_versions' => true,
                'policy_title' => $policy->title,
                'current_version' => $policy->version,
                'total_versions' => $versions->count(),
                'versions' => $versions->map(function($v) {
                    return [
                        'version' => $v->version,
                        'updated_by' => $v->updated_by_name ?? 'Unknown',
                        'updated_at' => $v->updated_at,
                        'changes' => $v->change_summary ?? 'N/A'
                    ];
                })->toArray(),
                'message' => "Found {$versions->count()} version(s) for '{$policy->title}'"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
