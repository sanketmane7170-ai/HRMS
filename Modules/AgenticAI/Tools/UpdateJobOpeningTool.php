<?php

namespace Modules\AgenticAI\Tools;

use Modules\Recruitment\Entities\JobOpening;
use Exception;

/**
 * UpdateJobOpeningTool - Update job opening details
 * Author: Sanket
 */
class UpdateJobOpeningTool extends BaseTool
{
    public function name(): string
    {
        return 'update_job_opening';
    }

    public function description(): string
    {
        return 'Update an existing job opening. Use when HR wants to modify job posting details.';
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
                'job_id' => [
                    'type' => 'integer',
                    'description' => 'Job opening ID'
                ],
                'title' => [
                    'type' => 'string',
                    'description' => 'New job title (optional)'
                ],
                'vacancies' => [
                    'type' => 'integer',
                    'description' => 'New number of vacancies (optional)'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'New description (optional)'
                ],
            ],
            'required' => ['job_id']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $job = JobOpening::find($args['job_id']);
            
            if (!$job) {
                return ['error' => "Job opening with ID {$args['job_id']} not found"];
            }

            $updateData = [];
            if (!empty($args['title'])) $updateData['title'] = $args['title'];
            if (isset($args['vacancies'])) $updateData['vacancies'] = $args['vacancies'];
            if (!empty($args['description'])) $updateData['description'] = $args['description'];

            if (empty($updateData)) {
                return ['error' => 'No fields to update provided'];
            }

            $job->update($updateData);

            return [
                'success' => true,
                'job_id' => $job->id,
                'title' => $job->title,
                'vacancies' => $job->vacancies,
                'message' => "Job opening '{$job->title}' updated successfully"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
