<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\GeneralRequest\Entities\GeneralRequest;

class DeleteGeneralRequestTool extends BaseTool
{
    public function name(): string
    {
        return 'delete_general_request';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Delete or cancel a general request. Use when user wants to delete, cancel, or remove a request they submitted.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'request_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the request to delete'
                ]
            ],
            'required' => ['request_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $requestId = $args['request_id'] ?? null;
        
        if (!$requestId) {
            return [
                'error' => 'Missing request ID',
                'message' => 'Please provide the request ID to delete.'
            ];
        }
        
        try {
            $request = GeneralRequest::query()
                ->where('id', $requestId)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$request) {
                return [
                    'error' => 'Request not found',
                    'message' => "Request ID {$requestId} not found or you don't have permission to delete it."
                ];
            }
            
            // Only allow deletion of pending requests
            if ($request->status != 0) {
                return [
                    'error' => 'Cannot delete',
                    'message' => 'Only pending requests can be deleted. This request has already been processed.'
                ];
            }
            
            $request->delete();
            
            return [
                'success' => true,
                'message' => "Request ID {$requestId} has been successfully deleted."
            ];
        } catch (\Exception $e) {
            \Log::error('DeleteGeneralRequestTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'request_id' => $requestId
            ]);
            
            return [
                'error' => 'Failed to delete request',
                'message' => 'Unable to delete the request. Error: ' . $e->getMessage()
            ];
        }
    }
}
