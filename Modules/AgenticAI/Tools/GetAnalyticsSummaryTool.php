<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetAnalyticsSummaryTool extends BaseTool
{
    public function name(): string
    {
        return 'get_analytics_summary';
    }

    public function description(): string
    {
        return 'Get analytics and statistics summary for the user. Use when user asks about their stats, metrics, or dashboard summary.';
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
        $user = Auth::user();
        
        try {
            // Gather various statistics
            $stats = [
                'attendance' => [
                    'present_days' => DB::table('attendances')
                        ->where('user_id', $user->id)
                        ->whereMonth('date', now()->month)
                        ->where('status', 'present')
                        ->count(),
                    'late_days' => DB::table('attendances')
                        ->where('user_id', $user->id)
                        ->whereMonth('date', now()->month)
                        ->where('late', true)
                        ->count()
                ],
                'leave' => [
                    'total_balance' => DB::table('leave_balances')
                        ->where('user_id', $user->id)
                        ->sum('balance'),
                    'used_this_year' => DB::table('leave_requests')
                        ->where('user_id', $user->id)
                        ->whereYear('from_date', now()->year)
                        ->where('status', 'approved')
                        ->sum('days')
                ],
                'tasks' => [
                    'pending' => DB::table('tasks')
                        ->where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->count(),
                    'completed_this_month' => DB::table('tasks')
                        ->where('user_id', $user->id)
                        ->where('status', 'completed')
                        ->whereMonth('updated_at', now()->month)
                        ->count()
                ],
                'expenses' => [
                    'pending_amount' => DB::table('expenses')
                        ->where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->sum('amount'),
                    'approved_this_month' => DB::table('expenses')
                        ->where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->whereMonth('created_at', now()->month)
                        ->sum('amount')
                ]
            ];
            
            return [
                'summary' => $stats,
                'month' => now()->format('F Y')
            ];
        } catch (\Exception $e) {
            \Log::error('GetAnalyticsSummaryTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch analytics',
                'message' => 'Unable to retrieve analytics summary.'
            ];
        }
    }
}
