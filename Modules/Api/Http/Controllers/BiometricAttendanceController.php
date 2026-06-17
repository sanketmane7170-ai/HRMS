<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BiometricAttendanceController extends Controller
{
    /**
     * Store a new check-in/out punch from the biometric machine.
     * Idempotent: If the same punch exists, it returns success without duplicating.
     * 
     * Author: Sanket - Handles biometric device check-ins with duplicate prevention
     */
    public function checkin(Request $request)
    {
        // 1. Validation
        $request->validate([
            'employee_id' => 'nullable|string',
            'user_id'     => 'nullable|integer',
            'biometric_user_id' => 'required|integer',
            'date'        => 'required|date_format:Y-m-d',
            'time'        => 'required|date_format:H:i:s',
            'type'        => 'required|in:in,out,late',
            'device_id'   => 'nullable|string'
        ]);

        try {
            // 2. Resolve User
            $user = null;
            if ($request->user_id) {
                $user = User::find($request->user_id);
            } elseif ($request->employee_id) {
                // Find user by custom employee_id field
                $user = User::where('employee_id', $request->employee_id)->first();
            }
            elseif ($request->biometric_user_id) {
                // Find user by biometric_user_id field
                $user = User::where('biometric_user_id', $request->biometric_user_id)->first();
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.'
                ], 404);
            }

            // 3. Idempotency Check
            // Check if this punch was already recorded
            $existingCheckin = Checkin::where([
                'user_id' => $user->id,
                'date'    => $request->date,
                'time'    => $request->time,
                'type'    => $request->type
            ])->first();

            if ($existingCheckin) {
                return response()->json([
                    'success' => true,
                    'message' => 'Punch already recorded (Idempotent).',
                    'data'    => $existingCheckin
                ], 200);
            }

            // 4. Create Checkin
            // We do NOT need to manually update 'attendances' table here.
            // The Checkin model's Observer (Checkin::boot) handles that logic automatically.
           
            // Determine Branch: Use passed branch_id or user's assigned branch
            $branchId = $request->branch_id ?? $user->assigned_branch_id ?? $user->department_id; // Fallback to dept if no branch

            $checkin = Checkin::create([
                'user_id'         => $user->id,
                'date'            => $request->date,
                'time'            => $request->time,
                'type'            => $request->type,
                'latecomment'     => $request->latecomment ?? 'Biometric Punch',
                'location'        => $request->location ?? 'Office (Biometric)',
                'branch_id'       => $branchId,
                'face_attendance' => $request->face_attendance ?? 0,
                'is_auto_update'  => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in recorded successfully.',
                'data'    => $checkin
            ], 201);

        } catch (\Exception $e) {
            Log::error("Biometric Checkin Failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance report for a specific user ID
     */
    public function userReport($user_id)
    {
        $attendances = Attendance::where('user_id', $user_id)
            ->orderBy('date', 'desc')
            ->limit(31) // Last 31 records by default
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $attendances
        ]);
    }

    /**
     * Get attendance report by Employee ID
     */
    public function employeeReport($biometric_user_id)
    {
        $user = User::where('biometric_user_id', $biometric_user_id)->first();
        

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        return $this->userReport($user->id);
    }

    /**
     * Get detailed attendance for a specific date
     */
    public function dateReport($date)
    {
        $attendances = Attendance::with('user:id,name,employee_id,biometric_user_id')
            ->where('date', $date)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $attendances
        ]);
    }
}
