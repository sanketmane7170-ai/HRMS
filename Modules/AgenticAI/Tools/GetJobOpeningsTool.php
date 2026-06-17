<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Recruitment\Entities\Job;

use Modules\AgenticAI\Tools\BaseTool;

class GetJobOpeningsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_job_openings';
    }

    public function description(): string
    {
        return 'Search for active job openings. Useful for finding open roles for candidates.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Optional keyword to search in title, description, or skills (e.g., "Python", "Manager").'
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['internal', 'external'],
                    'description' => 'Optional filter for hiring type. Default searches all.'
                ]
            ],
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $search = $args['search'] ?? null;
        $type = $args['type'] ?? null;

        $query = Job::query()->where('status', 'active')
            ->where('date_of_closing', '>=', now());

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('job_type', $type);
        }

        $jobs = $query->get()->map(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'position' => $job->position,
                'experience' => $job->experience,
                'location' => $job->workspace,
                'salary' => "{$job->start_salary} - {$job->end_salary}",
                'description' => $job->description,
            ];
        });

        return [
            'jobs' => $jobs,
            'count' => $jobs->count()
        ];
    }
}
