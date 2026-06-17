<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetMyAppreciationsTool - View appreciation letters
 * Author: Sanket
 */
class GetMyAppreciationsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_appreciations';
    }

    public function description(): string
    {
        return 'View all appreciation letters received. Use when employee wants to see their appreciations.';
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

            $appreciations = \DB::table('appreciations')
                ->where('user_id', $user->id)
                ->orderBy('issued_at', 'desc')
                ->get();

            if ($appreciations->isEmpty()) {
                return [
                    'has_appreciations' => false,
                    'message' => 'No appreciations found'
                ];
            }

            return [
                'has_appreciations' => true,
                'total_appreciations' => $appreciations->count(),
                'appreciations' => $appreciations->map(function($app) {
                    return [
                        'id' => $app->id,
                        'title' => $app->title,
                        'reason' => $app->reason,
                        'issued_by' => $app->issued_by_name ?? 'Unknown',
                        'issued_at' => $app->issued_at
                    ];
                })->toArray(),
                'message' => "Found {$appreciations->count()} appreciation(s)"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
