<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\GeneralRequest\Entities\GeneralRequest;

class CreateGeneralRequestTool extends BaseTool
{
    public function name(): string
    {
        return 'create_general_request';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Submit a general request to HR or management. Use when user wants to request something that doesn\'t fit other categories (not leave, expense, or asset related).';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'subject' => [
                    'type' => 'string',
                    'description' => 'Subject or title of the request'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Detailed description of what is being requested'
                ]
            ],
            'required' => ['subject', 'description']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $subject = $args['subject'] ?? '';
        $description = $args['description'] ?? '';
        
        if (empty($subject) || empty($description)) {
            return [
                'error' => 'Missing required fields',
                'message' => 'Please provide both subject and description for your request.'
            ];
        }
        
        try {
            // Get or create a default request type
            $requestType = \Modules\GeneralRequest\Entities\GeneralRequestType::first();
            if (!$requestType) {
                $requestType = \Modules\GeneralRequest\Entities\GeneralRequestType::create([
                    'name' => 'General Request'
                ]);
            }
            
            $request = GeneralRequest::create([
                'user_id' => $user->id,
                'type_id' => $requestType->id,
                'note' => $subject . "\n\n" . $description,
                'status' => 0, // Pending
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);
            
            return [
                'success' => true,
                'message' => "Your request has been submitted successfully. Request ID: {$request->id}",
                'request' => [
                    'id' => $request->id,
                    'note' => $request->note,
                    'status' => 'pending',
                    'submitted' => $request->created_at->format('M d, Y H:i')
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('CreateGeneralRequestTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'subject' => $subject
            ]);
            
            return [
                'error' => 'Failed to create request',
                'message' => 'Unable to submit your request. Error: ' . $e->getMessage()
            ];
        }
    }
}
