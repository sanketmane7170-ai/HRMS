<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Recruitment\Entities\Application;

use Modules\AgenticAI\Tools\BaseTool;

class GetApplicationsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_job_applications';
    }

    public function description(): string
    {
        return 'Get a list of job applications. Can filter by Job ID or Status.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'job_id' => [
                    'type' => 'integer',
                    'description' => 'Optional Job ID to filter applications for a specific job.'
                ],
                'stage' => [
                    'type' => 'string',
                    'enum' => ['applied', 'screening', 'shortlisted', 'interview', 'offer', 'hired', 'rejected'],
                    'description' => 'Optional stage to filter by.'
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Search by candidate name or email.'
                ]
            ],
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $jobId = $args['job_id'] ?? null;
        $stage = $args['stage'] ?? null;
        $search = $args['search'] ?? null;

        $query = Application::query();

        if ($jobId) {
            $query->where('job_id', $jobId);
        }

        if ($stage) {
            $query->where('status', $stage);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        // Limit to 20 for safety
        $applications = $query->limit(20)->get()->map(function ($app) {
            return [
                'id' => $app->id,
                'candidate' => $app->name,
                'email' => $app->email,
                'job_id' => $app->job_id,
                'status' => $app->status,
                'applied_at' => $app->created_at->format('Y-m-d')
            ];
        });

        return [
            'applications' => $applications,
            'count' => $applications->count()
        ];
    }
}
