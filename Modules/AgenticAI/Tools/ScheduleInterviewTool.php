<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Recruitment\Entities\Interview;
use Modules\Recruitment\Entities\Application;

use Modules\AgenticAI\Tools\BaseTool;

class ScheduleInterviewTool extends BaseTool
{
    public function name(): string
    {
        return 'schedule_interview';
    }

    public function description(): string
    {
        return 'Schedule an interview for a candidate. Automatically updates application stage to "interview".';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'application_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the application.'
                ],
                'scheduled_at' => [
                    'type' => 'string',
                    'description' => 'Date and time of interview (YYYY-MM-DD HH:MM).'
                ],
                'interviewer_id' => [
                    'type' => 'integer',
                    'description' => 'User ID of the interviewer. Uses current user if not specified, but usually should be specified.'
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['video', 'phone', 'in-person', 'panel'],
                    'default' => 'video',
                    'description' => 'Type of interview. Default is video.'
                ],
                'duration_minutes' => [
                    'type' => 'integer',
                    'default' => 30,
                    'description' => 'Duration in minutes. Default 30.'
                ]
            ],
            'required' => ['application_id', 'scheduled_at', 'interviewer_id'],
        ];
    }

    public function execute(array $args): mixed
    {
        $appId = $args['application_id'];
        $when = $args['scheduled_at'];
        $who = $args['interviewer_id'];
        $type = $args['type'] ?? 'video';
        $duration = $args['duration_minutes'] ?? 30;

        // 1. Create Interview
        $interview = Interview::create([
            'application_id' => $appId,
            'interviewer_id' => $who,
            'scheduled_at' => $when,
            'type' => $type,
            'duration' => $duration,
            'status' => 'scheduled'
        ]);

        // 2. Update Application Status
        Application::where('id', $appId)->update(['status' => 'interview']);

        return [
            'status' => 'success',
            'message' => "Interview scheduled for {$when} ({$type})",
            'interview_id' => $interview->id
        ];
    }
}
