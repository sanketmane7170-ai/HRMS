<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\ApplicationLog;

use Modules\AgenticAI\Tools\BaseTool;

class ScreenCandidateTool extends BaseTool
{
    public function name(): string
    {
        return 'screen_candidate';
    }

    public function description(): string
    {
        return 'Update the stage/status of a candidate application. Used for screening, shortlisting, or rejecting.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'application_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the application to update.'
                ],
                'stage' => [
                    'type' => 'string',
                    'enum' => ['screening', 'shortlisted', 'interview', 'offer', 'rejected'],
                    'description' => 'The new stage to move candidate to.'
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Optional notes or feedback for this stage change.'
                ]
            ],
            'required' => ['application_id', 'stage'],
        ];
    }

    public function execute(array $args): mixed
    {
        $appId = $args['application_id'];
        $stage = $args['stage'];
        $notes = $args['notes'] ?? null;

        $application = Application::findOrFail($appId);
        $oldStage = $application->status;
        
        $application->update(['status' => $stage]);

        // Log the change
        if ($notes) {
            ApplicationLog::create([
                'application_id' => $appId,
                'action' => "Stage changed from $oldStage to $stage",
                'notes' => $notes,
                'user_id' => auth()->id()
            ]);
        }

        return [
            'status' => 'success',
            'message' => "Candidate moved to {$stage}",
            'application_id' => $appId
        ];
    }
}
