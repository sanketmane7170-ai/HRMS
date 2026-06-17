<?php

namespace Modules\AgenticAI\Tools;

use Modules\Recruitment\Entities\InterviewFeedback;
use Modules\Recruitment\Entities\Application;
use Exception;

class ProcessInterviewFeedbackTool extends BaseTool
{
    public function name(): string
    {
        return 'process_interview_feedback';
    }

    public function description(): string
    {
        return 'Record feedback and recommendations for an interview. Use this to move candidates through the hiring pipeline (Hire, Reject, Next Round).';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'application_id' => ['type' => 'integer', 'description' => 'ID of the application.'],
                'interview_id' => ['type' => 'integer', 'description' => 'ID of the scheduled interview.'],
                'recommendation' => [
                    'type' => 'string', 
                    'enum' => ['hire', 'reject', 'next_round', 'hold'],
                    'description' => 'Interviewer recommendation.'
                ],
                'overall_rating' => ['type' => 'integer', 'description' => 'Rating from 1 to 5.'],
                'interviewer_observations' => ['type' => 'string', 'description' => 'Detailed notes about the candidate.'],
                'candidate_showed_up' => ['type' => 'boolean', 'default' => true],
                'candidate_on_time' => ['type' => 'boolean', 'default' => true]
            ],
            'required' => ['application_id', 'recommendation', 'overall_rating']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $application = Application::findOrFail($args['application_id']);
            
            $feedback = InterviewFeedback::create([
                'application_id' => $args['application_id'],
                'interview_id' => $args['interview_id'] ?? null,
                'interviewer_id' => auth()->id(),
                'recommendation' => $args['recommendation'],
                'overall_rating' => $args['overall_rating'],
                'interviewer_observations' => $args['interviewer_observations'] ?? '',
                'candidate_showed_up' => $args['candidate_showed_up'] ?? true,
                'candidate_on_time' => $args['candidate_on_time'] ?? true,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Optionally update application status based on recommendation
            if ($args['recommendation'] === 'reject') {
                $application->update(['status' => 'rejected']);
            }

            return [
                'success' => true,
                'message' => "Feedback recorded for candidate '{$application->name}'. Recommendation: {$args['recommendation']}.",
                'feedback_id' => $feedback->id
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
