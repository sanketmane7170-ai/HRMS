<?php

namespace Modules\AgenticAI\Tools;

use Modules\Document\Entities\DocumentRequest;
use Exception;

/**
 * ApproveDocumentRequestTool - Approve document requests
 * Author: Sanket
 */
class ApproveDocumentRequestTool extends BaseTool
{
    public function name(): string
    {
        return 'approve_document_request';
    }

    public function description(): string
    {
        return 'Approve or reject employee document requests. Use when HR needs to process document requests.';
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
                'request_id' => [
                    'type' => 'integer',
                    'description' => 'Document request ID'
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['approve', 'reject'],
                    'description' => 'Action to take'
                ],
                'comments' => [
                    'type' => 'string',
                    'description' => 'Comments (optional)'
                ],
            ],
            'required' => ['request_id', 'action']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR can approve document requests
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to process document requests'];
            }

            $request = DocumentRequest::find($args['request_id']);
            
            if (!$request) {
                return ['error' => "Document request with ID {$args['request_id']} not found"];
            }

            $status = $args['action'] === 'approve' ? 'approved' : 'rejected';
            
            $request->update([
                'status' => $status,
                'admin_comments' => $args['comments'] ?? null,
                'processed_by' => $currentUser->id,
                'processed_at' => now()
            ]);

            return [
                'success' => true,
                'request_id' => $request->id,
                'employee' => $request->user->name ?? 'Unknown',
                'document_type' => $request->document_type,
                'status' => $status,
                'message' => "Document request {$status} for {$request->user->name}"
            ];

        } catch (Exception $e) {
            \Log::error('ApproveDocumentRequestTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to process document request. Please try again.', 'success' => false];
        }
    }
}
