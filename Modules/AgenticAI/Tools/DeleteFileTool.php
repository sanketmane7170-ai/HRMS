<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteFileTool extends BaseTool
{
    public function name(): string
    {
        return 'delete_file';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Delete a file/document. Use when user wants to remove an uploaded file.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'file_id' => ['type' => 'integer', 'description' => 'ID of file to delete']
            ],
            'required' => ['file_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['file_id'] ?? null;
        
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide file ID.'];
        
        try {
            $deleted = DB::table('user_documents')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->delete();
            
            if (!$deleted) return ['error' => 'Not found', 'message' => 'File not found or no permission.'];
            
            return ['success' => true, 'message' => 'File deleted.'];
        } catch (\Exception $e) {
            \Log::error('DeleteFileTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
