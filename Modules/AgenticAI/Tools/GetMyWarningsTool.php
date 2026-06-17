<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Warning\Entities\UserWarning;

class GetMyWarningsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_warnings';
    }

    public function description(): string
    {
        return 'Get disciplinary warnings or notices issued to the user. Use when user asks about warnings, disciplinary actions, or notices they have received.';
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
            $warnings = UserWarning::query()
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->get();
                
            if ($warnings->isEmpty()) {
                return [
                    'message' => 'You have no warnings or disciplinary notices.',
                    'warnings' => []
                ];
            }
            
            return [
                'warnings' => $warnings->map(function($warning) {
                    return [
                        'type' => ucfirst(str_replace('_', ' ', $warning->type)),
                        'detail' => $warning->detail,
                        'date' => date('M d, Y', strtotime($warning->date)),
                        'issued_ago' => $warning->created_at->diffForHumans(),
                        'acknowledged' => $warning->ack_status ? 'Yes' : 'No'
                    ];
                })->toArray(),
                'count' => $warnings->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyWarningsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch warnings',
                'message' => 'Unable to retrieve warnings at this time.'
            ];
        }
    }
}
