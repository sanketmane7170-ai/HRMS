<?php

namespace App\Http\Controllers\Backend;

use App\Models\Feature;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Modules\Attendance\Entities\Attendance;
use Carbon\Carbon;
use Modules\Performance\Entities\PerformanceAppraisal;
use Modules\Performance\Entities\AppraisalCriterion;
use Modules\Leave\Entities\Leave;

class DashboardController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'dashboard');
    }

    /**
     * Return resource for the admin dashboard
     */
    public function index()
    {
        $features = Feature::where('status', 1)->orderBy('id', 'DESC')->get();;
        $data['features'] = $features;

        $view = 'employee.dashboard';
        
        // Check if user has Admin role - always gets backend dashboard
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            $view = 'backend.dashboard';
        }
        // Check if user has HR role - needs View Dashboard permission
        elseif (auth()->user()->hasRole(User::ROLE_HR)) {
            if (hasPermission('View Dashboard')) {
                $view = 'backend.dashboard';
            } else {
                return view($view);
            }
        }
        // Check if user has View Dashboard permission (for any other role)
        elseif (hasPermission('View Dashboard')) {
            $view = 'backend.dashboard';
        }
        $startDateStr = Carbon::now()->subDays(7)->toDateString();
        $endDateStr = Carbon::now()->toDateString();

        $attendances = Attendance::select('date', 'status', \DB::raw('COUNT(DISTINCT user_id) as total'))
            ->whereBetween('date', [$startDateStr, $endDateStr])
            ->groupBy('date', 'status')
            ->get();

        $leaves = Leave::where('status', 'approved')
            ->whereDate('start_date', '<=', $endDateStr)
            ->whereDate('end_date', '>=', $startDateStr)
            ->get();

        $data['userloginCount'] = [];
        $data['leaveUsersCount'] = [];
        $data['absentUsersCount'] = [];
        $data['weekendUsersCount'] = [];
        $data['userLoginDates'] = [];

        for ($i = 0; $i <= 7; $i++) {
            $dateObj = Carbon::parse(Carbon::now()->subDays(7))->addDays($i);
            $dateStr = $dateObj->toDateString();

            // Login Count
            $data['userloginCount'][] = $attendances->filter(function($att) use ($dateStr) {
                return $att->date === $dateStr && in_array($att->status, [
                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut
                ]);
            })->sum('total');

            // Absent Count
            $data['absentUsersCount'][] = $attendances->filter(function($att) use ($dateStr) {
                return $att->date === $dateStr && $att->status === \Modules\Attendance\Enums\AttendanceStatus::Absent;
            })->sum('total');

            // Weekend Count
            $data['weekendUsersCount'][] = $attendances->filter(function($att) use ($dateStr) {
                return $att->date === $dateStr && $att->status === \Modules\Attendance\Enums\AttendanceStatus::Weekend;
            })->sum('total');

            // Leave Count
            $data['leaveUsersCount'][] = $leaves->filter(function($leave) use ($dateStr) {
                return $leave->start_date <= $dateStr && $leave->end_date >= $dateStr;
            })->count();

            $data['userLoginDates'][] = $dateObj->dayName . "\n" . $dateObj->format('d-m-y');
        }

        $departmentData = User::join('departments', 'users.department_id', '=', 'departments.id')
            ->select('departments.name as department', \DB::raw('COUNT(users.id) as count'))
            ->where('users.status', 'active')
            ->groupBy('departments.name')
            ->get();

        $data['departmentChartData'] = $departmentData->map(function ($item) {
            return [
                'department' => $item->department, // Replace with the department name if available
                'count' => $item->count,
            ];
        });

        $divisionsChartData = User::join('divisions', 'users.division_id', '=', 'divisions.id')
            ->select('divisions.name as division', \DB::raw('COUNT(users.id) as count'))
            ->where('users.status', 'active')
            ->groupBy('divisions.name')
            ->get();

        $data['divisionsChartData'] = $divisionsChartData->map(function ($item) {
            return [
                'divisionNames' => $item->division,
                'employeeCounts' => $item->count,
            ];
        });

        $designationData = User::join('designations', 'users.designation_id', '=', 'designations.id')
            ->select('designations.name as designation', \DB::raw('COUNT(users.id) as count'))
            ->where('users.status', 'active')
            ->groupBy('designations.name')
            ->get();
        $data['designationChartData'] = $designationData->map(function ($item) {
            return [
                'designation' => $item->designation, // Replace with the department name if available
                'count' => $item->count,
            ];
        });

        $data['roleChartData'] = \DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->join('users', 'model_has_roles.model_id', '=', 'users.id')
            ->where('model_has_roles.model_type', '=', 'App\\Models\\User')
            ->where('users.status', 'active')
            ->select('roles.name as role', \DB::raw('COUNT(users.id) as count'))
            ->groupBy('roles.name')
            ->get();

        $data['workDetailsData'] = \DB::table('user_work_details')
            ->select(
                \DB::raw('YEAR(joining_date) as year'), // Extract the year from joining_date
                \DB::raw('COUNT(id) as joining_count'), // Count users who joined in that year
                \DB::raw('SUM(CASE WHEN probation_end_date IS NULL OR probation_end_date > NOW() THEN 1 ELSE 0 END) as probation_count') // Count users still on probation
            )
            ->groupBy('year') // Group data by year
            ->orderBy('year', 'ASC') // Sort by year (optional)
            ->get();
        // dd($workDetailsData);


        // 1. Performance Heatmap (Employee vs. average score)
        // $data['heatmapData'] = PerformanceAppraisal::with('employee', 'criteria')
        //     ->get()
        //     ->map(function ($appraisal) {
        //         $totalScore = 0;
        //         $totalWeight = 0;
        //         foreach ($appraisal->criteria as $c) {
        //             if ($c->score !== null) {
        //                 $totalScore += $c->score * $c->weight;
        //                 $totalWeight += $c->weight;
        //             }
        //         }
        //         $averageScore = $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
        //         return [
        //             'employee' => $appraisal->employee->name,
        //             'average_score' => $averageScore
        //         ];
        //     });
        // $data['heatmapData'] = User::select('users.id', 'users.name')
        //     ->join('performance_appraisals', 'users.id', '=', 'performance_appraisals.employee_id')
        //     ->join('appraisal_criteria', 'performance_appraisals.id', '=', 'appraisal_criteria.appraisal_id')
        //     ->where('users.status', 'active') // Optional filter if needed
        //     ->groupBy('users.id', 'users.name')
        //     ->get()
        //     ->map(function ($user) {
        //         // Fetch all criteria linked to this employee
        //         $criteria = AppraisalCriterion::whereHas('appraisal', function ($q) use ($user) {
        //             $q->where('employee_id', $user->id);
        //         })->get();

        //         // Calculate weighted average
        //         $totalWeighted = 0;
        //         $totalWeight = 0;
        //         foreach ($criteria as $c) {
        //             if (!is_null($c->score)) {
        //                 $totalWeighted += $c->score * $c->weight;
        //                 $totalWeight += $c->weight;
        //             }
        //         }

        //         $averageScore = $totalWeight > 0 ? round($totalWeighted / $totalWeight, 2) : 0;

        //         return [
        //             'employee' => $user->name,
        //             'average_score' => $averageScore
        //         ];
        //     })
        //     ->sortByDesc('average_score')
        //     ->values(); // Re-index the collection

        $data['heatmapData'] = User::where('status', 'active')
            ->with(['performanceAppraisals.criteria'])
            ->get()
            ->map(function ($user) {
                $criteria = $user->performanceAppraisals->flatMap->criteria;

                $totalWeighted = 0;
                $totalWeight = 0;
                foreach ($criteria as $criterion) {
                    if (!is_null($criterion->score)) {
                        $totalWeighted += $criterion->score * $criterion->weight;
                        $totalWeight += $criterion->weight;
                    }
                }

                $averageScore = $totalWeight > 0 ? round($totalWeighted / $totalWeight, 2) : 0;

                return [
                    'employee' => $user->name,
                    'average_score' => $averageScore
                ];
            })
            ->sortByDesc('average_score')
            ->values(); // re-index the collection
        // 2. Active Goals & OKRs (stubbed; can be dynamic later)
        $data['activeGoals'] = [
            ['title' => 'Improve response-time by 20%', 'progress' => 60],
            ['title' => 'Launch Q3 sales campaign', 'progress' => 45]
        ];

        // 3. PIP Alerts
        $data['pipAlerts'] = PerformanceAppraisal::where('status', 'PIP')->with('employee')->get();

        // 4. Review Completion
        $data['reviewCompletion'] = [
            'Q1' => 90,
            'Q2' => 70,
            'Q3' => 50,
            'Q4' => 30
        ];

        // 5. Review Type Status
        $data['reviewStatus'] = [
            'self' => AppraisalCriterion::whereNotNull('self_score')->count(),
            'peer' => AppraisalCriterion::whereNotNull('score')->count(),
            'manager' => PerformanceAppraisal::whereNotNull('reviewer_comments')->count()
        ];

        // 6. Engagement Score
        $data['engagementScore'] = 88;

        // 7. Top Performers
        $data['topPerformers'] = PerformanceAppraisal::with('employee')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        // 8. Performance Tier Distribution
        $data['tierDistribution'] = [
            'exceeds' => 18,
            'meets' => 61,
            'needs_improvement' => 21
        ];


        return view($view, $data, [
            'userloginCount' => $data['userloginCount'],
            'leaveUsersCount' => $data['leaveUsersCount'],
            'absentUsersCount' => $data['absentUsersCount'],
            'weekendUsersCount' => $data['weekendUsersCount'],
            'userLoginDates' => $data['userLoginDates'],
        ]);
    }

    public function user_checkin(Request $request)
    {

        if ($request->date) {

            $date = explode('-', $request->date);
            $year = isset($date[0]) ? $date[0] : date('Y');
            $month = isset($date[1]) ? $date[1] : date('m');
            $day = isset($date[2]) ? $date[2] : date('d');
            $weekStartDate = now()->parse("$year-$month-$day")->toDateString();
        } else {
            $weekStartDate = Carbon::now();
            // $weekStartDate = $now->startOfWeek()->format('Y-m-d');
        }


        for ($i = 0; $i <= 7; $i++) {
            $start_date = Carbon::parse($weekStartDate)->addDays($i);

            $attendance = Attendance::whereDate('date', $start_date->toDateString())
                ->whereIn('status', [
                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                ])
                // ->where('status', 'present')
                ->distinct('user_id')
                ->count('user_id');
            if ($attendance) {
                $data['userloginCount'][$i] = $attendance;
                $data['userLoginDates'][$i] = Carbon::parse($start_date)->dayName . "\n" . $start_date->format('d-m-y');
            } else {
                $data['userloginCount'][$i] = 0;
                $data['userLoginDates'][$i] = Carbon::parse($start_date)->dayName . "\n" . $start_date->format('d-m-y');
            }
        }

        return $data;
    }
}
