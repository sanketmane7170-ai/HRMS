<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;

class CheckExpiredItemsTool extends BaseTool
{
    public function name(): string
    {
        return 'check_expired_items';
    }

    public function description(): string
    {
        return 'Check for expired employee documents and file manager items. Use when user asks about expired documents, expiring items, or document compliance. Admin/Manager only.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'item_type' => [
                    'type' => 'string',
                    'enum' => ['employee_documents', 'file_manager', 'all'],
                    'description' => 'Type of items to check. Options: employee_documents, file_manager, or all (default)'
                ],
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Filter by specific employee ID (admin only)'
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $itemType = $args['item_type'] ?? 'all';
        $userId = $args['user_id'] ?? null;

        try {
            // Permission check - only admin and managers can access this tool
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);
            $isManager = in_array('manager', $roles);

            if (!$isAdmin && !$isManager) {
                return [
                    'error' => 'Access denied',
                    'message' => 'Only administrators and managers can view expired items.'
                ];
            }

            $result = [];

            // Employee Documents
            if ($itemType === 'employee_documents' || $itemType === 'all') {
                $documentsQuery = getUserDocumentExpiredQuery();
                
                if ($userId) {
                    $documentsQuery->where('user_id', $userId);
                }

                $expiredDocuments = $documentsQuery
                    ->with(['user' => ['department'], 'type'])
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                $result['employee_documents'] = $expiredDocuments->map(function ($doc) {
                    return [
                        'document_id' => $doc->id,
                        'employee_id' => $doc->user_id,
                        'employee_name' => $doc->user->name ?? 'Unknown',
                        'department' => $doc->user->department->name ?? 'N/A',
                        'document_type' => $doc->type->name ?? 'Unknown',
                        'document_name' => $doc->name ?? 'N/A',
                        'expiry_date' => $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : null,
                        'days_expired' => $doc->expiry_date ? now()->diffInDays(\Carbon\Carbon::parse($doc->expiry_date), false) : null,
                        'status' => 'expired'
                    ];
                })->values()->toArray();
            }

            // File Manager Documents
            if ($itemType === 'file_manager' || $itemType === 'all') {
                $fileManagerQuery = getFilemanagerDocumentExpiredQuery();
                
                $expiredFiles = $fileManagerQuery
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                $result['file_manager_items'] = $expiredFiles->map(function ($file) {
                    return [
                        'file_id' => $file->id,
                        'file_name' => $file->name ?? 'Unknown',
                        'file_type' => $file->file_type ?? 'N/A',
                        'department_id' => $file->department_id ?? null,
                        'expiry_date' => $file->expiry_date ? \Carbon\Carbon::parse($file->expiry_date)->format('Y-m-d') : null,
                        'days_expired' => $file->expiry_date ? now()->diffInDays(\Carbon\Carbon::parse($file->expiry_date), false) : null,
                        'status' => 'expired'
                    ];
                })->values()->toArray();
            }

            // Add summary
            $result['summary'] = [
                'total_expired_employee_documents' => count($result['employee_documents'] ?? []),
                'total_expired_file_manager_items' => count($result['file_manager_items'] ?? []),
                'total_expired_items' => count($result['employee_documents'] ?? []) + count($result['file_manager_items'] ?? []),
                'checked_at' => now()->format('Y-m-d H:i:s')
            ];

            return $result;

        } catch (\Exception $e) {
            \Log::error('CheckExpiredItemsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'item_type' => $itemType
            ]);

            return [
                'error' => 'Failed to check expired items',
                'message' => 'Unable to retrieve expired items information. Error: ' . $e->getMessage()
            ];
        }
    }
}
