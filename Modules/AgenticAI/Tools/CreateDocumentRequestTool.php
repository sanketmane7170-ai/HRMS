<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Document\Entities\DocumentRequest;

class CreateDocumentRequestTool extends BaseTool
{
    public function name(): string
    {
        return 'create_document_request';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Request an official document from HR (e.g., employment letter, salary certificate). Use when user wants to request official documents.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'document_type' => [
                    'type' => 'string',
                    'description' => 'Type of document requested (e.g., "employment letter", "salary certificate", "experience letter")'
                ],
                'purpose' => [
                    'type' => 'string',
                    'description' => 'Purpose or reason for requesting the document'
                ]
            ],
            'required' => ['document_type', 'purpose']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $documentType = $args['document_type'] ?? '';
        $purpose = $args['purpose'] ?? '';
        
        if (empty($documentType) || empty($purpose)) {
            return [
                'error' => 'Missing required fields',
                'message' => 'Please provide both document type and purpose.'
            ];
        }
        
        try {
            // Lookup document_type_id from document_types table
            $docType = \DB::table('document_types')
                ->where('name', 'LIKE', "%{$documentType}%")
                ->orWhere('name', 'LIKE', str_replace(' ', '%', $documentType))
                ->first();
            
            if (!$docType) {
                // Try to find by common keywords
                $keywords = ['employment' => 'Employment Letter', 'salary' => 'Salary Certificate', 'experience' => 'Experience Letter'];
                foreach ($keywords as $key => $value) {
                    if (stripos($documentType, $key) !== false) {
                        $docType = \DB::table('document_types')->where('name', $value)->first();
                        break;
                    }
                }
            }
            
            if (!$docType) {
                return [
                    'error' => 'Document type not found',
                    'message' => "Document type '{$documentType}' not found. Available types: Employment Letter, Salary Certificate, Experience Letter, etc."
                ];
            }
            
            $request = DocumentRequest::create([
                'user_id' => $user->id,
                'document_type_id' => $docType->id,
                'purpose' => $purpose,
                'status' => 'pending',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);
            
            return [
                'success' => true,
                'message' => "Document request submitted successfully. Request ID: {$request->id}",
                'request' => [
                    'id' => $request->id,
                    'document_type' => $docType->name,
                    'purpose' => $request->purpose,
                    'status' => 'pending',
                    'requested' => $request->created_at->format('M d, Y H:i')
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('CreateDocumentRequestTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'document_type' => $documentType
            ]);
            
            return [
                'error' => 'Failed to create request',
                'message' => 'Unable to submit document request. Error: ' . $e->getMessage()
            ];
        }
    }
}
