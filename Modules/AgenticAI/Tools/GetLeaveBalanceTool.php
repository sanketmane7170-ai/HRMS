<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use App\Services\Leave\LeaveCalculationService;
use Modules\Leave\Entities\LeaveType;
use Illuminate\Support\Facades\Auth;
use Modules\AgenticAI\Tools\BaseTool;

class GetLeaveBalanceTool extends BaseTool
{
    protected $leaveCalculationService;

    public function __construct()
    {
        $this->leaveCalculationService = app(LeaveCalculationService::class);
    }

    public function name(): string
    {
        return 'get_my_leave_balance';
    }

    public function description(): string
    {
        return 'Get the current leave balance (vacation days, sick leave, etc.) for the logged-in user.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[], // No arguments needed, uses Auth::user()
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        if (!$user) {
            return ['error' => 'User not logged in.'];
        }

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $balances = [];

        foreach ($leaveTypes as $type) {
            $result = $this->leaveCalculationService->calculateCurrentBalance($user->id, $type->id);
            if ($result['success']) {
                $balances[] = [
                    'leave_type' => $type->name,
                    'remaining_balance' => $result['components']['remaining_balance'] ?? $result['balance']->remaining_balance,
                    'used_balance' => $result['components']['used_balance'] ?? $result['balance']->used_balance,
                    'earned_balance' => $result['components']['earned_balance'] ?? $result['balance']->earned_balance,
                ];
            }
        }

        if (empty($balances)) {
             return ['message' => 'No active leave policies found for this user.'];
        }

        return [
            'balances' => $balances
        ];
    }
}
