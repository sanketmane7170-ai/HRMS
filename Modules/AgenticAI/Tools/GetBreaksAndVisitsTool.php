<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Attendance\Entities\Visitin;
use Modules\Payroll\Entities\UserOvertime;
use App\Models\User;

class GetBreaksAndVisitsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_breaks_and_visits';
    }

    public function description(): string
    {
        return 'Get attendance breaks, site visits, and overtime requests. Employees can view their own data, admins/managers can view any employee\'s data.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => ['breaks', 'visits', 'overtime', 'all'],
                    'description' => 'Type of data to fetch: breaks, visits, overtime, or all (default)'
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Optional: Specific date in Y-m-d format (defaults to today)'
                ],
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Specific employee ID (admin/manager only)'
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Optional: Start date for date range in Y-m-d format'
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'Optional: End date for date range in Y-m-d format'
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $currentUser = Auth::user();
        $type = $args['type'] ?? 'all';
        $date = $args['date'] ?? now()->format('Y-m-d');
        $targetUserId = $args['user_id'] ?? $currentUser->id;
        $startDate = $args['start_date'] ?? null;
        $endDate = $args['end_date'] ?? null;

        try {
            // Permission check
            $roles = $currentUser->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);
            $isManager = in_array('manager', $roles);

            // Only admin/manager can view other users' data
            if ($targetUserId != $currentUser->id && !$isAdmin && !$isManager) {
                return [
                    'error' => 'Access denied',
                    'message' => 'You can only view your own attendance data.'
                ];
            }

            $targetUser = User::find($targetUserId);
            if (!$targetUser) {
                return [
                    'error' => 'User not found',
                    'message' => "Employee ID {$targetUserId} not found."
                ];
            }

            $result = [
                'employee' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'department' => $targetUser->department->name ?? 'N/A'
                ]
            ];

            // Breaks
            if ($type === 'breaks' || $type === 'all') {
                $breaksQuery = Breakin::where('user_id', $targetUserId);

                if ($startDate && $endDate) {
                    $breaksQuery->whereBetween('date', [$startDate, $endDate]);
                } else {
                    $breaksQuery->whereDate('date', $date);
                }

                $breaks = $breaksQuery->orderBy('time', 'desc')->get();

                // Group breaks by date and pair IN/OUT
                $breakPairs = [];
                $breaksGrouped = $breaks->groupBy('date');
                
                foreach ($breaksGrouped as $breakDate => $dayBreaks) {
                    $breakIn = $dayBreaks->where('type', 'in')->first();
                    $breakOut = $dayBreaks->where('type', 'out')->first();
                    
                    if ($breakIn || $breakOut) {
                        $duration = null;
                        if ($breakIn && $breakOut) {
                            $breakInTime = \Carbon\Carbon::parse($breakDate . ' ' . $breakIn->time);
                            $breakOutTime = \Carbon\Carbon::parse($breakDate . ' ' . $breakOut->time);
                            $duration = $breakInTime->diffInMinutes($breakOutTime);
                        }

                        $breakPairs[] = [
                            'date' => $breakDate,
                            'break_in' => $breakIn ? $breakIn->time : null,
                            'break_out' => $breakOut ? $breakOut->time : null,
                            'duration_minutes' => $duration,
                            'status' => ($breakIn && $breakOut) ? 'completed' : 'ongoing'
                        ];
                    }
                }

                $result['breaks'] = $breakPairs;
                $result['total_breaks'] = count($breakPairs);
                $result['total_break_time_minutes'] = collect($breakPairs)->sum('duration_minutes');
            }

            // Visits (Location Visits)
            if ($type === 'visits' || $type === 'all') {
                $visitsQuery = LocationVisits::where('user_id', $targetUserId);

                if ($startDate && $endDate) {
                    $visitsQuery->whereBetween('date', [$startDate, $endDate]);
                } else {
                    $visitsQuery->whereDate('date', $date);
                }

                $visits = $visitsQuery->orderBy('visit_in', 'desc')->get();

                $result['visits'] = $visits->map(function ($visit) {
                    $duration = null;
                    if ($visit->visit_in && $visit->visit_out) {
                        $visitIn = \Carbon\Carbon::parse($visit->visit_in);
                        $visitOut = \Carbon\Carbon::parse($visit->visit_out);
                        $duration = $visitIn->diffInMinutes($visitOut);
                    }

                    return [
                        'id' => $visit->id,
                        'date' => $visit->date,
                        'visit_in' => $visit->visit_in,
                        'visit_out' => $visit->visit_out,
                        'location' => $visit->location ?? 'N/A',
                        'purpose' => $visit->purpose ?? 'N/A',
                        'duration_minutes' => $duration,
                        'status' => $visit->visit_out ? 'completed' : 'ongoing'
                    ];
                })->toArray();

                $result['total_visits'] = $visits->count();
            }

            // Overtime Requests
            if ($type === 'overtime' || $type === 'all') {
                $overtimeQuery = UserOvertime::where('user_id', $targetUserId);

                if ($startDate && $endDate) {
                    $overtimeQuery->whereBetween('date', [$startDate, $endDate]);
                } else if (!$startDate && !$endDate) {
                    // If no date range, get recent overtime requests
                    $overtimeQuery->whereDate('date', '>=', now()->subDays(30));
                }

                $overtime = $overtimeQuery->orderBy('date', 'desc')->get();

                $result['overtime_requests'] = $overtime->map(function ($ot) {
                    return [
                        'id' => $ot->id,
                        'date' => $ot->date,
                        'hours' => $ot->hours ?? 0,
                        'reason' => $ot->reason ?? 'N/A',
                        'status' => $ot->status ?? 'pending',
                        'approved_by' => $ot->approved_by_name ?? null,
                        'approval_date' => $ot->approval_date ? \Carbon\Carbon::parse($ot->approval_date)->format('Y-m-d') : null
                    ];
                })->toArray();

                $result['total_overtime_requests'] = $overtime->count();
                $result['total_overtime_hours'] = $overtime->sum('hours');
            }

            // Add query period info
            if ($startDate && $endDate) {
                $result['period'] = "From {$startDate} to {$endDate}";
            } else {
                $result['period'] = "Date: {$date}";
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error('GetBreaksAndVisitsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser->id,
                'target_user_id' => $targetUserId,
                'type' => $type
            ]);

            return [
                'error' => 'Failed to fetch attendance data',
                'message' => 'Unable to retrieve breaks and visits information. Error: ' . $e->getMessage()
            ];
        }
    }
}
