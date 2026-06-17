<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Attendance\Enums\WorkStatus;
use Modules\Attendance\Services\WorkStatusService;
use Illuminate\Support\Facades\Auth;

class WorkStatusController extends Controller
{
    protected $statusService;

    public function __construct(WorkStatusService $statusService)
    {
        $this->statusService = $statusService;
        view()->share('activeLink', 'live-board');
    }

    /**
     * Update the current user's work status.
     */
    public function update(Request $request)
    {
        $request->validate([
            'status' => 'required|string',
            'remarks' => 'nullable|string|max:255',
        ]);

        try {
            $newStatus = WorkStatus::from($request->status);
            $this->statusService->transitionTo(Auth::user(), $newStatus, $request->remarks);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully to ' . $newStatus->label(),
                'status' => [
                    'value' => $newStatus->value,
                    'label' => $newStatus->label(),
                    'color' => $newStatus->color(),
                    'icon' => $newStatus->icon(),
                ]
            ]);
        } catch (\ValueError $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating status.'
            ], 500);
        }
    }

    public function liveBoard()
    {
        // Only show users who have actively updated their status or logged in today
        $users = \App\Models\User::with('department')
            ->where('status', 'active')
            ->whereNotNull('status_updated_at')
            ->whereDate('status_updated_at', \Carbon\Carbon::today())
            ->orderBy('work_status', 'asc') // Group by status
            ->get();

        return view('attendance::admin.live_status', compact('users'));
    }
}
