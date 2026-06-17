<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetMyDocumentsTool - View uploaded documents
 * Author: Sanket
 */
class GetMyDocumentsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_documents';
    }

    public function description(): string
    {
        return 'View all uploaded personal documents. Use when employee wants to see their documents.';
    }

    public function isSensitive(): bool
    {
        return false;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return ['error' => 'User not authenticated'];
            }

            $documents = \DB::table('user_documents')
                ->where('user_id', $user->id)
                ->orderBy('uploaded_at', 'desc')
                ->get();

            if ($documents->isEmpty()) {
                return [
                    'has_documents' => false,
                    'message' => 'No documents found'
                ];
            }

            return [
                'has_documents' => true,
                'total_documents' => $documents->count(),
                'documents' => $documents->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'type' => $doc->document_type,
                        'description' => $doc->description,
                        'uploaded_at' => $doc->uploaded_at,
                        'file_path' => $doc->file_path
                    ];
                })->toArray(),
                'message' => "Found {$documents->count()} document(s)"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
