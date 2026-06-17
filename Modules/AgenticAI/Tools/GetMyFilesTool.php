<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetMyFilesTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_files';
    }

    public function description(): string
    {
        return 'Get user\'s uploaded files and documents. Use when user asks about their files, uploads, or documents.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of files (default 10)',
                    'default' => 10
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $limit = min($args['limit'] ?? 10, 50);
        
        try {
            $files = DB::table('user_documents')
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->limit($limit)
                ->get();
                
            if ($files->isEmpty()) {
                return [
                    'message' => 'You have no uploaded files.',
                    'files' => []
                ];
            }
            
            return [
                'files' => $files->map(function($file) {
                    return [
                        'name' => $file->name ?? $file->file_name ?? 'Unnamed',
                        'type' => $file->type ?? 'Unknown',
                        'size' => $file->size ? round($file->size / 1024, 2) . ' KB' : 'N/A',
                        'uploaded' => date('M d, Y', strtotime($file->created_at)),
                        'url' => $file->file_path ? url($file->file_path) : null
                    ];
                })->toArray(),
                'count' => $files->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyFilesTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch files',
                'message' => 'Unable to retrieve your files.'
            ];
        }
    }
}
