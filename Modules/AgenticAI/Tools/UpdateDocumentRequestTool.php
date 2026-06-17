<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Entities\DocumentRequest;

class UpdateDocumentRequestTool extends BaseTool
{
    public function name(): string { return 'update_document_request'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Update document request. Use when user wants to modify their document request.'; }
    public function schema(): array { return ['type' => 'object', 'properties' => ['request_id' => ['type' => 'integer'], 'purpose' => ['type' => 'string']], 'required' => ['request_id']]; }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['request_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide request ID.'];
        
        try {
            $request = DocumentRequest::where('id', $id)->where('user_id', $user->id)->where('status', 'pending')->first();
            if (!$request) return ['error' => 'Not found', 'message' => 'Request not found or already processed.'];
            if (isset($args['purpose'])) $request->update(['purpose' => $args['purpose']]);
            return ['success' => true, 'message' => 'Document request updated.'];
        } catch (\Exception $e) {
            \Log::error('UpdateDocumentRequestTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
