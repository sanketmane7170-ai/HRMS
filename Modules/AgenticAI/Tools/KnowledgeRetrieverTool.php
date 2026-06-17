<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\AgenticAI\Services\VectorStore\DatabaseVectorStore;
use Modules\AgenticAI\Tools\BaseTool;

class KnowledgeRetrieverTool extends BaseTool
{
    protected $vectorStore;

    public function __construct()
    {
        // Ideally dependency injected, but for tool registry usage:
        $this->vectorStore = new DatabaseVectorStore();
    }

    public function name(): string
    {
        return 'search_knowledge_base';
    }

    public function description(): string
    {
        return 'Search company policies, handbooks, and documents. Use this when the user asks "How do I...", "What is the policy for...", or general questions about company rules. DO NOT use for finding specific employee data.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'The search query. Convert the user question into a search term (e.g., "Casual Leave Policy" or "Travel reimbursement limit").'
                ]
            ],
            'required' => ['query'],
        ];
    }

    public function execute(array $args): mixed
    {
        $query = $args['query'] ?? '';
        if (empty($query)) return ['error' => 'Query cannot be empty.'];

        $results = $this->vectorStore->search($query, 3); // Get top 3 chunks

        //Sanket v2.0 - when no internal docs found, instruct AI to use general knowledge instead of just saying "not found"
        if (empty($results)) {
            return [
                'message' => 'No company-specific documents found in the internal knowledge base for this query. Please use your general knowledge to answer the user\'s question about this topic. Clearly mention that the answer is based on general knowledge and not company-specific policy.',
                'results' => [],
                'fallback_to_general_knowledge' => true
            ];
        }

        // Format for AI consumption
        $formatted = [];
        foreach ($results as $res) {
            $formatted[] = [
                'content' => $res['content'],
                'source' => $res['metadata']['title'] ?? 'Unknown',
                'relevance' => round($res['score'], 2)
            ];
        }

        return [
            'relevant_documents' => $formatted,
            'count' => count($formatted)
        ];
    }
}
