<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadFileTool extends BaseTool
{
    public function name(): string
    {
        return 'upload_file';
    }

    public function description(): string
    {
        return 'Upload a file/document. Use when user wants to upload a file. Note: This creates a file record, actual file upload requires frontend.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'file_name' => ['type' => 'string', 'description' => 'Name of the file'],
                'file_type' => ['type' => 'string', 'description' => 'Type/category of file'],
                'file_path' => ['type' => 'string', 'description' => 'Path where file will be stored']
            ],
            'required' => ['file_name']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        if (empty($args['file_name'])) {
            return ['error' => 'Missing file name', 'message' => 'Provide file name.'];
        }
        
        try {
            //Sanket v2.0 - sanitize file_path to prevent path traversal attacks
            $fileName = basename($args['file_name']); // strip any directory components
            $filePath = $args['file_path'] ?? '/uploads/' . $fileName;
            // Remove path traversal sequences
            $filePath = str_replace(['../', '..\\', '..'], '', $filePath);
            $filePath = preg_replace('#/+#', '/', $filePath); // collapse multiple slashes

            $fileId = DB::table('user_documents')->insertGetId([
                'user_id' => $user->id,
                'name' => $fileName,
                'type' => $args['file_type'] ?? 'document',
                'file_path' => $filePath,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => 'File record created. Please upload the actual file through the UI.',
                'file' => ['id' => $fileId, 'name' => $args['file_name']]
            ];
        } catch (\Exception $e) {
            \Log::error('UploadFileTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => 'Unable to create file record. Please try again.'];
        }
    }
}
