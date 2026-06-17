<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\CompanyDocument\Entities\CompanyDocument;

class GetCompanyDocumentsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_company_documents';
    }

    public function description(): string
    {
        return 'Search and retrieve company documents like handbooks, policies, forms, and other official documents. Use when user asks about company documents, handbooks, forms, or official files.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Search term for document name. Leave empty to list all documents.'
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of documents to return (default 10)',
                    'default' => 10
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $search = $args['search'] ?? '';
        $limit = min($args['limit'] ?? 10, 50);
        
        try {
            $query = CompanyDocument::query()->where('status', 1); // Active only
            
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('legal_trade_name', 'like', "%{$search}%")
                      ->orWhere('short_name', 'like', "%{$search}%");
                });
            }
            
            $documents = $query
                ->latest('created_at')
                ->limit($limit)
                ->get();
                
            if ($documents->isEmpty()) {
                return [
                    'message' => empty($search) 
                        ? 'No company documents available.' 
                        : "No documents found matching '{$search}'.",
                    'documents' => []
                ];
            }
            
            return [
                'documents' => $documents->map(function($doc) {
                    return [
                        'name' => $doc->legal_trade_name,
                        'short_name' => $doc->short_name ?? 'N/A',
                        'license_number' => $doc->license_number,
                        'license_expiry' => $doc->license_expiry,
                        'document_url' => $doc->document ? url($doc->document) : null,
                        'added_date' => date('M d, Y', strtotime($doc->added_date))
                    ];
                })->toArray(),
                'count' => $documents->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetCompanyDocumentsTool failed', [
                'error' => $e->getMessage(),
                'search' => $search
            ]);
            
            return [
                'error' => 'Failed to fetch documents',
                'message' => 'Unable to retrieve company documents. Error: ' . $e->getMessage()
            ];
        }
    }
}
