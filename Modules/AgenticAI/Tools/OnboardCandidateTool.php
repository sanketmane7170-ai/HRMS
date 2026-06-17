<?php

namespace Modules\AgenticAI\Tools;

use Modules\Recruitment\Entities\Application;
use App\Models\User;
use Exception;

/**
 * OnboardCandidateTool - Onboard accepted candidate as employee
 * Author: Sanket
 */
class OnboardCandidateTool extends BaseTool
{
    public function name(): string
    {
        return 'onboard_candidate';
    }

    public function description(): string
    {
        return 'Onboard an accepted candidate as an employee. Use when converting offer acceptance to employee record.';
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
                'application_id' => [
                    'type' => 'integer',
                    'description' => 'Application ID to onboard'
                ],
                'joining_date' => [
                    'type' => 'string',
                    'description' => 'Joining date in YYYY-MM-DD format'
                ],
                'salary' => [
                    'type' => 'number',
                    'description' => 'Offered salary'
                ],
            ],
            'required' => ['application_id', 'joining_date', 'salary']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR can onboard candidates
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'super-admin'])) {
                return ['error' => 'You do not have permission to onboard candidates'];
            }

            if (empty($args['application_id']) || empty($args['joining_date']) || empty($args['salary'])) {
                return ['error' => 'application_id, joining_date, and salary are required'];
            }

            $application = Application::find($args['application_id']);
            
            if (!$application) {
                return ['error' => "Application with ID {$args['application_id']} not found"];
            }

            if ($application->status !== 'offer_accepted') {
                return [
                    'error' => "Cannot onboard candidate - application status is '{$application->status}', must be 'offer_accepted'",
                    'current_status' => $application->status
                ];
            }

            // Create employee record
            $employee = User::create([
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'department_id' => $application->job_opening->department_id ?? null,
                'designation_id' => $application->job_opening->designation_id ?? null,
                'joining_date' => $args['joining_date'],
                'salary' => $args['salary'],
                'status' => 'active',
                'password' => bcrypt('password123'), // Default password
                'created_by' => $currentUser->id
            ]);

            // Update application status
            $application->update([
                'status' => 'onboarded',
                'employee_id' => $employee->id,
                'onboarded_at' => now()
            ]);

            return [
                'success' => true,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'joining_date' => $args['joining_date'],
                    'salary' => $args['salary']
                ],
                'message' => "Candidate {$employee->name} onboarded successfully. Employee ID: {$employee->id}"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
