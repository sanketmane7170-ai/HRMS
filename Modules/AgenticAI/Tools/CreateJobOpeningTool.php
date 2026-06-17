<?php

namespace Modules\AgenticAI\Tools;

use Modules\Recruitment\Entities\JobOpening;
use Exception;

/**
 * CreateJobOpeningTool - Create new job opening
 * Author: Sanket
 */
class CreateJobOpeningTool extends BaseTool
{
    public function name(): string
    {
        return 'create_job_opening';
    }

    public function description(): string
    {
        return 'Create a new job opening/posting. Use when HR wants to post a new job.';
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
                'title' => [
                    'type' => 'string',
                    'description' => 'Job title'
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Department ID'
                ],
                'designation_id' => [
                    'type' => 'integer',
                    'description' => 'Designation ID'
                ],
                'vacancies' => [
                    'type' => 'integer',
                    'description' => 'Number of vacancies'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Job description (optional)'
                ],
                'requirements' => [
                    'type' => 'string',
                    'description' => 'Job requirements (optional)'
                ],
            ],
            'required' => ['title', 'department_id', 'designation_id', 'vacancies']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can create job openings
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to create job openings'];
            }

            if (empty($args['title']) || empty($args['department_id']) || empty($args['designation_id']) || empty($args['vacancies'])) {
                return ['error' => 'title, department_id, designation_id, and vacancies are required'];
            }

            $jobOpening = JobOpening::create([
                'title' => $args['title'],
                'department_id' => $args['department_id'],
                'designation_id' => $args['designation_id'],
                'vacancies' => $args['vacancies'],
                'description' => $args['description'] ?? null,
                'requirements' => $args['requirements'] ?? null,
                'status' => 'open',
                'created_by' => $currentUser->id
            ]);

            return [
                'success' => true,
                'job_opening' => [
                    'id' => $jobOpening->id,
                    'title' => $jobOpening->title,
                    'vacancies' => $jobOpening->vacancies,
                    'status' => $jobOpening->status
                ],
                'message' => "Job opening '{$jobOpening->title}' created with {$jobOpening->vacancies} vacancies"
            ];

        } catch (Exception $e) {
            \Log::error('CreateJobOpeningTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to create job opening. Please try again.', 'success' => false];
        }
    }
}
