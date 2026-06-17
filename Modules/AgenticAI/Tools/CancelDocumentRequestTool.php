<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Entities\DocumentRequest;

class CancelDocumentRequestTool extends BaseTool
{
    public function name(): string { return 'cancel_document_request'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Cancel document request. Use when user wants to cancel their request.'; }
    public function schema(): array { return ['type' => 'object', 'properties' => ['request_id' => ['type' => 'integer']], 'required' => ['request_id']]; }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['request_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide request ID.'];
        
        try {
            $request = DocumentRequest::where('id', $id)->where('user_id', $user->id)->where('status', 'pending')->first();
            if (!$request) return ['error' => 'Not found', 'message' => 'Request not found or already processed.'];
            $request->update(['status' => 'cancelled']);
            return ['success' => true, 'message' => 'Document request cancelled.'];
        } catch (\Exception $e) {
            \Log::error('CancelDocumentRequestTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
