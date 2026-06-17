<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * UploadEmployeeDocumentTool - Upload employee documents
 * Author: Sanket
 */
class UploadEmployeeDocumentTool extends BaseTool
{
    public function name(): string
    {
        return 'upload_employee_document';
    }

    public function description(): string
    {
        return 'Upload personal documents (passport, certificates, etc.). Use when employee wants to upload documents.';
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
                'document_type' => [
                    'type' => 'string',
                    'description' => 'Document type (e.g., passport, certificate, id_proof)'
                ],
                'file_path' => [
                    'type' => 'string',
                    'description' => 'File path or URL'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Document description (optional)'
                ],
            ],
            'required' => ['document_type', 'file_path']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return ['error' => 'User not authenticated'];
            }

            $documentId = \DB::table('user_documents')->insertGetId([
                'user_id' => $user->id,
                'document_type' => $args['document_type'],
                'file_path' => $args['file_path'],
                'description' => $args['description'] ?? null,
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'document_id' => $documentId,
                'document_type' => $args['document_type'],
                'message' => "Document uploaded successfully: {$args['document_type']}"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
