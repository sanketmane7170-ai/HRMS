<?php

namespace App\Services\Leave;

use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveBalanceUpdateLog;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Illuminate\Support\Facades\Auth;

class LeaveCalculationService
{
    //Sanket v2.0 - calculates current leave balance for a user+type, returns structured result expected by GetLeaveBalanceTool
    public function calculateCurrentBalance(int $userId, int $leaveTypeId): array
    {
        $balance = LeaveBalance::where([
            'user_id'       => $userId,
            'leave_type_id' => $leaveTypeId,
            'year'          => date('Y'),
        ])->first();

        $leaveType = LeaveType::find($leaveTypeId);

        if (!$balance || !$leaveType) {
            return ['success' => false, 'message' => 'No balance record found.'];
        }

        $usedBalance = Leave::where([
            'user_id'       => $userId,
            'leave_type_id' => $leaveTypeId,
            'status'        => LeaveStatus::Approved,
            'year'          => date('Y'),
        ])->sum('total_leave_days');

        $earnedBalance = $leaveType->days ?? 0;
        $remainingBalance = max(0, (float) $balance->available);

        return [
            'success'  => true,
            'balance'  => $balance,
            'components' => [
                'earned_balance'    => (float) $earnedBalance,
                'used_balance'      => (float) $usedBalance,
                'remaining_balance' => $remainingBalance,
            ],
        ];
    }

    //Sanket v2.0 - deducts leave days from the balance record and logs the change; called by ApplyLeaveTool after leave is created
    public function deductBalance(int $userId, int $leaveTypeId, float $days, int $leaveId, string $reason): bool
    {
        $balance = LeaveBalance::where([
            'user_id'       => $userId,
            'leave_type_id' => $leaveTypeId,
            'year'          => date('Y'),
        ])->first();

        if (!$balance) {
            return false;
        }

        $previousBalance = (float) $balance->available;
        $newBalance = max(0, $previousBalance - $days);

        $balance->available = $newBalance;
        $balance->save();

        LeaveBalanceUpdateLog::create([
            'user_id'          => $userId,
            'leave_type_id'    => $leaveTypeId,
            'previous_balance' => $previousBalance,
            'new_balance'      => $newBalance,
            'updated_by'       => Auth::id() ?? $userId,
            'diff_value'       => $days,
            'is_less'          => 1,
            'description'      => "Leave applied via AI agent. Leave ID: {$leaveId}. Reason: {$reason}",
        ]);

        return true;
    }
}
