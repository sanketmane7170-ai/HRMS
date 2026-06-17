<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;

use Modules\AgenticAI\Tools\BaseTool;

class GetSystemTimeTool extends BaseTool
{
    public function name(): string
    {
        return 'get_current_time';
    }

    public function description(): string
    {
        return 'Get the current server time and date. Use this when the user asks specifically for the time.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'timezone' => [
                    'type' => 'string',
                    'description' => 'The timezone to get the time for (e.g., UTC, Asia/Kolkata)',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $timezone = $args['timezone'] ?? 'UTC';
        $now = now();
        
        return [
            'current_time' => $now->format('H:i:s'),
            'date' => $now->format('Y-m-d'),
            'day' => $now->format('l'),
            'time_zone' => config('app.timezone'),
            'full_timestamp' => $now->toIso8601String()
        ];
    }
}
