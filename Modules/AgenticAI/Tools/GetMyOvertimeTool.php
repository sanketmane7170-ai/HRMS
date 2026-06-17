<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetMyOvertimeTool - View overtime history
 * Author: Sanket
 */
class GetMyOvertimeTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_overtime';
    }

    public function description(): string
    {
        return 'View overtime history and pending requests. Use when employee wants to check their overtime.';
    }

    public function isSensitive(): bool
    {
        return false;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return ['error' => 'User not authenticated'];
            }

            $overtime = \DB::table('overtime_requests')
                ->where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->get();

            if ($overtime->isEmpty()) {
                return [
                    'has_overtime' => false,
                    'message' => 'No overtime records found'
                ];
            }

            $totalHours = $overtime->where('status', 'approved')->sum('hours');

            return [
                'has_overtime' => true,
                'total_approved_hours' => $totalHours,
                'overtime_requests' => $overtime->map(function($ot) {
                    return [
                        'id' => $ot->id,
                        'date' => $ot->date,
                        'hours' => $ot->hours,
                        'reason' => $ot->reason,
                        'status' => $ot->status
                    ];
                })->toArray(),
                'message' => "Found {$overtime->count()} overtime request(s). Total approved: {$totalHours} hours"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
