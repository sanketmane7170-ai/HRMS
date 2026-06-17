<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use App\Services\Leave\LeaveValidationService;
use App\Services\Leave\LeaveCalculationService;
use App\Services\Leave\LeaveWorkflowService;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\AgenticAI\Tools\BaseTool;

class ApplyLeaveTool extends BaseTool
{
    protected $validationService;
    protected $calculationService;
    protected $workflowService;

    public function __construct()
    {
        $this->validationService = app(LeaveValidationService::class);
        $this->calculationService = app(LeaveCalculationService::class);
        $this->workflowService = app(LeaveWorkflowService::class);
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'apply_leave';
    }

    public function description(): string
    {
        return 'Apply for a leave (vacation, sick, etc.). Requires leave_type_id (use get_my_leave_balance to find IDs), start_date, end_date, and reason.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'leave_type_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the leave type (e.g., 1 for Casual, 2 for Sick). Use get_my_leave_balance to see available types and IDs.'
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date in YYYY-MM-DD format.'
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date in YYYY-MM-DD format.'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for the leave.'
                ],
                'is_half_day' => [
                    'type' => 'boolean',
                    'description' => 'Is this a half-day leave? Default is false.'
                ]
            ],
            'required' => ['leave_type_id', 'start_date', 'end_date', 'reason'],
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        if (!$user) {
            return ['error' => 'User not logged in.'];
        }

        $userId = $user->id;
        $leaveTypeId = $args['leave_type_id'];
        $startDate = Carbon::parse($args['start_date']);
        $endDate = Carbon::parse($args['end_date']);
        $reason = $args['reason'];
        $isHalfDay = $args['is_half_day'] ?? false;

        // 1. Validate the request
        $validation = $this->validationService->validateLeaveRequest(
            $userId,
            $leaveTypeId,
            $startDate,
            $endDate,
            ['is_half_day' => $isHalfDay]
        );

        if (!$validation['valid']) {
            return ['error' => 'Validation failed: ' . implode(', ', $validation['violations'])];
        }

        try {
            DB::beginTransaction();

            $leaveType = LeaveType::find($leaveTypeId);

            // 2. Create the leave record
            $leave = Leave::create([
                'user_id' => $userId,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_leave_days' => $validation['leave_days'],
                'reason' => $reason,
                'status' => 'pending',
                'is_half_day' => $isHalfDay
            ]);

            // 3. Deduct balance
            $deductionSuccess = $this->calculationService->deductBalance(
                $userId,
                $leaveTypeId,
                $validation['leave_days'],
                $leave->id,
                $reason
            );

            if (!$deductionSuccess) {
                DB::rollBack();
                return ['error' => 'Failed to deduct balance.'];
            }

            // 4. Initialize workflow
            $workflow = $this->workflowService->initializeWorkflow($leave);
            
            DB::commit();

            return [
                'success' => true,
                'message' => "Leave application submitted successfully for {$validation['leave_days']} days.",
                'leave_id' => $leave->id,
                'status' => 'pending'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['error' => 'System error: ' . $e->getMessage()];
        }
    }
}
