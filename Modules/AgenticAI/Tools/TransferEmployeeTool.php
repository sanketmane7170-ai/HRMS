<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * TransferEmployeeTool - Transfer employee to different department/branch
 * Author: Sanket
 */
class TransferEmployeeTool extends BaseTool
{
    public function name(): string
    {
        return 'transfer_employee';
    }

    public function description(): string
    {
        return 'Transfer an employee to a different department or branch. Use when HR wants to move an employee.';
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
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Employee ID to transfer'
                ],
                'new_department_id' => [
                    'type' => 'integer',
                    'description' => 'New department ID (optional)'
                ],
                'new_branch_id' => [
                    'type' => 'integer',
                    'description' => 'New branch ID (optional)'
                ],
                'new_designation_id' => [
                    'type' => 'integer',
                    'description' => 'New designation ID (optional)'
                ],
                'effective_date' => [
                    'type' => 'string',
                    'description' => 'Transfer effective date in YYYY-MM-DD format'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Transfer reason (optional)'
                ],
            ],
            'required' => ['user_id', 'effective_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can transfer employees
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required to transfer employees'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to transfer employees'];
            }

            if (empty($args['user_id']) || empty($args['effective_date'])) {
                return ['error' => 'user_id and effective_date are required'];
            }

            if (empty($args['new_department_id']) && empty($args['new_branch_id']) && empty($args['new_designation_id'])) {
                return ['error' => 'At least one of new_department_id, new_branch_id, or new_designation_id must be provided'];
            }

            $user = User::find($args['user_id']);
            
            if (!$user) {
                return ['error' => "Employee with ID {$args['user_id']} not found"];
            }

            $oldDepartment = $user->department->name ?? 'N/A';
            $oldBranch = $user->branch->name ?? 'N/A';
            $oldDesignation = $user->designation->name ?? 'N/A';

            // Create transfer record
            $transferId = \DB::table('employee_transfers')->insertGetId([
                'user_id' => $user->id,
                'old_department_id' => $user->department_id,
                'new_department_id' => $args['new_department_id'] ?? $user->department_id,
                'old_branch_id' => $user->branch_id,
                'new_branch_id' => $args['new_branch_id'] ?? $user->branch_id,
                'old_designation_id' => $user->designation_id,
                'new_designation_id' => $args['new_designation_id'] ?? $user->designation_id,
                'effective_date' => $args['effective_date'],
                'reason' => $args['reason'] ?? 'Transfer',
                'created_by' => $currentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update employee record
            $updateData = [];
            if (!empty($args['new_department_id'])) $updateData['department_id'] = $args['new_department_id'];
            if (!empty($args['new_branch_id'])) $updateData['branch_id'] = $args['new_branch_id'];
            if (!empty($args['new_designation_id'])) $updateData['designation_id'] = $args['new_designation_id'];

            $user->update($updateData);

            $newDepartment = $user->fresh()->department->name ?? 'N/A';
            $newBranch = $user->fresh()->branch->name ?? 'N/A';
            $newDesignation = $user->fresh()->designation->name ?? 'N/A';

            return [
                'success' => true,
                'transfer' => [
                    'id' => $transferId,
                    'employee' => $user->name,
                    'from' => [
                        'department' => $oldDepartment,
                        'branch' => $oldBranch,
                        'designation' => $oldDesignation
                    ],
                    'to' => [
                        'department' => $newDepartment,
                        'branch' => $newBranch,
                        'designation' => $newDesignation
                    ],
                    'effective_date' => $args['effective_date']
                ],
                'message' => "Employee {$user->name} transferred successfully. Effective date: {$args['effective_date']}"
            ];

        } catch (Exception $e) {
            \Log::error('TransferEmployeeTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to transfer employee. Please try again.', 'success' => false];
        }
    }
}
