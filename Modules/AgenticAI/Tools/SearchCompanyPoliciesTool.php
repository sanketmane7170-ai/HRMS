<?php

namespace Modules\AgenticAI\Tools;

use Modules\PolicySetting\Entities\PolicySettings;

class SearchCompanyPoliciesTool extends BaseTool
{
    public function name(): string
    {
        return 'search_company_policies';
    }

    public function description(): string
    {
        return 'Search company policies by topic or keyword. Use when user asks about company policies, rules, regulations, or guidelines on specific topics like remote work, dress code, etc.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Topic or keyword to search for in policies (e.g., "remote work", "dress code", "vacation")'
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of policies to return (default 5)',
                    'default' => 5
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $query = $args['query'] ?? '';
        $limit = min($args['limit'] ?? 5, 20);
        
        try {
            $policiesQuery = PolicySettings::query();
            
            if (!empty($query)) {
                $policiesQuery->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                });
            }
            
            $policies = $policiesQuery
                ->latest('updated_at')
                ->limit($limit)
                ->get();
                
            //Sanket v2.0 - when no internal policies found, instruct AI to use general knowledge as fallback
            if ($policies->isEmpty()) {
                return [
                    'message' => empty($query) 
                        ? 'No company policies available.' 
                        : "No company-specific policies found matching '{$query}' in the internal database. Please use your general knowledge to answer the user's question about this topic. Clearly mention that the answer is based on general knowledge and not company-specific policy.",
                    'policies' => [],
                    'count' => 0,
                    'fallback_to_general_knowledge' => true
                ];
            }
            
            return [
                'policies' => $policies->map(function($policy) {
                    return [
                        'name' => $policy->name,
                        'description' => $policy->description ?? 'No description available',
                        'last_updated' => $policy->updated_at->format('M d, Y')
                    ];
                })->toArray(),
                'count' => $policies->count()
            ];
        } catch (\Exception $e) {
            \Log::error('SearchCompanyPoliciesTool failed', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            return [
                'error' => 'Search failed',
                'message' => 'Unable to search company policies. Please try again.'
            ];
        }
    }
}
