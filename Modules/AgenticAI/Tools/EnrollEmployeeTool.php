<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Exception;

class EnrollEmployeeTool extends BaseTool
{
    public function name(): string
    {
        return 'enroll_employee';
    }

    public function description(): string
    {
        return 'Enroll a new employee into the system. This creates the user account, profile, work details, and initial payroll setup. Required fields: first_name, last_name, email, phone, branch_id, designation_id, role_id, date_of_birth, gender, marital_status, date_of_joining.';
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
                'first_name' => ['type' => 'string'],
                'last_name' => ['type' => 'string'],
                'email' => ['type' => 'string', 'description' => 'Work email.'],
                'phone' => ['type' => 'string', 'description' => 'Work/Main phone.'],
                'personal_email' => ['type' => 'string'],
                'personal_phone' => ['type' => 'string'],
                'gender' => ['type' => 'string', 'enum' => ['Male', 'Female']],
                'marital_status' => ['type' => 'string', 'enum' => ['single', 'married', 'divorced', 'widow']],
                'date_of_birth' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD'],
                'date_of_joining' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD'],
                'address' => ['type' => 'string'],
                'branch_id' => ['type' => 'integer', 'description' => 'The ID of the Branch (Department).'],
                'designation_id' => ['type' => 'integer', 'description' => 'The ID of the Designation.'],
                'division_id' => ['type' => 'integer', 'description' => 'Optional: The ID of the Division.'],
                'role_id' => ['type' => 'integer', 'description' => 'Role ID (e.g., 2 for employee, 1 for admin). Default: 2'],
                'report_to_id' => ['type' => 'integer', 'description' => 'Manager/Supervisor User ID.'],
                'company_name' => ['type' => 'string', 'description' => 'Current company name. Default: MOM Digital'],
                'location' => ['type' => 'string', 'description' => 'Work location/city.'],
                'work_week' => ['type' => 'integer', 'description' => 'Working days per week. Default: 5'],
                'country_id' => ['type' => 'integer', 'description' => 'Country ID (Default: 1 for UAE/Local).']
            ],
            'required' => [
                'first_name', 'last_name', 'email', 'phone', 
                'branch_id', 'designation_id', 'role_id',
                'date_of_birth', 'gender', 'marital_status', 'date_of_joining'
            ]
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            // Map branch_id to department_id as expected by UserService
            $args['department_id'] = $args['branch_id'];
            
            // Fill defaults based on UserService requirements
            $args['company_name'] = $args['company_name'] ?? 'MOM Digital';
            $args['work_week'] = $args['work_week'] ?? 5;
            $args['country_id'] = $args['country_id'] ?? 1;
            $args['role_id'] = $args['role_id'] ?? 2;
            $args['location'] = $args['location'] ?? 'Dubai';
            $args['personal_email'] = $args['personal_email'] ?? $args['email'];
            $args['personal_phone'] = $args['personal_phone'] ?? $args['phone'];
            $args['address'] = $args['address'] ?? 'Not Provided';
            
            // Map marital_status (AI-friendly) to martial_status (DB requirement)
            $args['martial_status'] = $args['marital_status'] ?? ($args['martial_status'] ?? 'single');

            // Additional fields required by UserService/Database
            $args['attendance_base'] = $args['attendance_base'] ?? 'office';
            $args['salary_mode'] = $args['salary_mode'] ?? 'monthly';
            $args['medical_insurance_provided'] = $args['medical_insurance_provided'] ?? 0;
            $args['company_accommodation'] = $args['company_accommodation'] ?? 0;
            $args['shift_start'] = $args['shift_start'] ?? '09:00:00';
            $args['shift_end'] = $args['shift_end'] ?? '18:00:00';
            $args['shifts'] = $args['shifts'] ?? [['shift_start' => '09:00:00', 'shift_end' => '18:00:00']];
            $args['tickets'] = $args['tickets'] ?? [];

            // Create a Request object to match UserService expectations
            $request = new Request($args);
            
            $userService = new UserService();
            $user = $userService->add($request);

            if ($user instanceof User) {
                return [
                    'success' => true,
                    'message' => "Employee '{$user->name}' enrolled successfully with ID #{$user->employee_id}.",
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create employee. Service returned unexpected result.'
            ];

        } catch (Exception $e) {
            // Check for specific database constraint errors
            $message = $e->getMessage();
            if (str_contains(strtolower($message), 'a foreign key constraint fails')) {
                if (str_contains($message, 'designation_id')) {
                    $message = "Enrollment failed: The provided designation_id (#{$args['designation_id']}) does not exist in the system.";
                } elseif (str_contains($message, 'department_id') || str_contains($message, 'branch_id')) {
                    $message = "Enrollment failed: The provided branch_id (#{$args['branch_id']}) does not exist in the system.";
                }
            }

            return [
                'success' => false,
                'error' => 'Enrollment failed',
                'message' => $message
            ];
        }
    }
}
