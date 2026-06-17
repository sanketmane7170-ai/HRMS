<?php

namespace App\Services\Leave;

use Modules\Leave\Entities\Leave;
use Illuminate\Support\Facades\Log;

class LeaveWorkflowService
{
    //Sanket v2.0 - initializes post-creation leave workflow; currently logs the event; extend here for notifications/approvals
    public function initializeWorkflow(Leave $leave): array
    {
        // Log the workflow initiation for audit trail
        Log::info('LeaveWorkflow: Leave application submitted via AI agent', [
            'leave_id'      => $leave->id,
            'user_id'       => $leave->user_id,
            'leave_type_id' => $leave->leave_type_id,
            'start_date'    => $leave->start_date,
            'end_date'      => $leave->end_date,
            'days'          => $leave->total_leave_days,
            'status'        => $leave->status,
        ]);

        return [
            'initiated' => true,
            'leave_id'  => $leave->id,
        ];
    }
}
