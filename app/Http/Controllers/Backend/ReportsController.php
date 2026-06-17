<?php
namespace App\Http\Controllers\Backend;

use App\Exports\ExcelExport;
use App\Exports\VacationLeaveReportExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Modules\Payroll\Http\Controllers\UserPaySlipController;
use Yajra\DataTables\Facades\DataTables;

class ReportsController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'reports');
    }
    /**
     * Return resource for the admin dashboard
     */
    public function index()
    {
        // $view = 'employee.reports';
        $view = 'backend.reports';
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            $view = 'backend.reports';
        }
        return view($view);
    }
    public function leaves_report(Request $request)
    {
        // dd(request()->route()->parameters);
        $types = LeaveType::get(['id', 'name', 'days']);
        canPerform('Manage Leave');
        $filterEmployees = User::whereIn('id', $request->employee ?? [])->get();
        $users           = $dates           = [];
        $this->year      = $request->month_year ? date('Y', strtotime($request->month_year)) : date('Y');
        $this->month     = $request->month_year ? date('m', strtotime($request->month_year)) : date('m');

        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $month_year        = "";
        $year              = $this->year;
        $month             = $this->month;
        $departmentId      = '';
        $searchEmp         = '';
        $export            = '';
        $search            = true;
        $departmentId      = $request->department_id;
        $searchEmp         = $request->search_emp;
        $month_year        = $request->month_year;
        $button            = $request->button;
        $query             = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
        // if ($request->post()) {

        //     // echo"<pre>";print_r($request->post());die;
        //     $query = User::where('status', User::STATUS_ACTIVE);
        //     $query->whereDoesntHave('roles', function ($query) {
        //         $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
        //     });
        // }
        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }

        $users = $query->get();
        if ($button == "export") {
            foreach ($users as $i => $user) {
                $exportExcel[$i]['Employee Name'] = $user->name . ' (' . $user->department?->name ?? 'NA' . ')';
                if ($i == 0) {
                    $headers[] = 'Employee Name';
                }

                foreach ($types as $type) {
                    if ($i == 0) {
                        $headers[] = $type->name . '(' . $type->days . ')';
                    }
                    $exportExcel[$i][$type->name] = calculatePendingLeave($type, $user->id);
                }
            }
            $export = new ExcelExport($exportExcel, $headers);
            if ($departmentId != "all" && $departmentId > 0) {
                $department = Department::find($departmentId);
                return Excel::download($export, 'leave_report_' . $department->name . '.xlsx');
            } else {
                // $department = Department::find();
                return Excel::download($export, 'leave_report_.xlsx');
            }
        }
        // echo"<pre>";print_r($users);die;
        // return view('backend.reports.leaves_report', compact('types', 'filterEmployees', 'users', 'departmentId', 'searchEmp', 'search'));
        return view('backend.reports.leaves_report', compact('types', 'filterEmployees', 'users', 'year', 'month', 'departmentId', 'searchEmp', 'search', 'month_year'));
    }

    public function attendance_report(Request $request)
    {
        $this->request = $request;
        
        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        } elseif ($request->month_year) {
            // Fallback for older links or previous UI behavior if needed initially
            $startDate = Carbon::parse($request->month_year)->startOfMonth();
            $endDate = Carbon::parse($request->month_year)->endOfMonth();
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }

        $start_date = $startDate->format('Y-m-d');
        $end_date = $endDate->format('Y-m-d');
        
        // Ensure endDate is not before startDate
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy();
            $end_date = $start_date;
        }

        $this->daysInMonth = $startDate->diffInDays($endDate) + 1; // Store as total days in range

        $departmentId = '';
        $searchEmp = '';
        $export = '';
        $search = true;
        
        $departmentId = $request->department_id;
        $searchEmp = $request->search_emp;
        $button = $request->button;
        $query = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });

        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }

        $users = $query->get();
        // echo"<pre>";print_r($request->post());die;
        $attendances = [];

        foreach ($users as $key => $user) {
            $data = [
                $user->employee_id,
                $user->name,
            ];
            $absentCount    = 0;
            $presentCount   = 0;
            $leaveCount     = 0;
            $holidayCount   = 0;
            $weekendCount   = 0;
            $lateCount      = 0;
            $sickleaveCount = 0;
            $earlyoutCount = 0;
            $vacationleave = 0;
            $phleave = 0;
            $unpaidleave = 0;
            
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            
            foreach ($period as $dateObj) {
                $date = $dateObj->toDateString();
                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                $todayisleave = Leave::where([['user_id', $user->id],['status','approved']])
                                                        ->with('type')
                                                        ->whereDate('start_date', '<=', $date)
                                                        ->whereDate('end_date', '>=', $date)
                                                        ->first();
                $leaveType = $todayisleave ? $todayisleave->type?->type->value : '';
                if ($holiday) {
                    if($todayisleave && $leaveType == 'calendar'){
                        $leaveCount++;
                        $data[] = "LV";
                    } else {
                        $PresentCheckOut = Attendance::where('user_id', $user->id)->where('date', $date)->orderBy('id', 'desc')->first();
                        if($PresentCheckOut && $PresentCheckOut->status == \Modules\Attendance\Enums\AttendanceStatus::Present){
                            $presentCount++;
                            $data[] = "P";
                        } else {
                            $holidayCount++;
                            $data[] = "H";
                        }
                    }
                } else {
                    DB::connection()->enableQueryLog();
                    $attendance = Attendance::where('user_id', $user->id)->where('date', $date)->get();
                    $queries    = DB::getQueryLog();
                    $last_query = end($queries);

                    // dd($last_query);

                    if (!empty($attendance[0]->status->name)) {

                        if ($attendance[0]->status->name == 'Present') {
                            $presentCount++;
                            $data[] = "P";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Absent') {
                            if ($todayisleave) {
                                $vacationkeywords = ['Vacation', 'vacation', 'vacation leave', 'Vacation Leave' ];
                                $phkeywords = ['PH', 'ph', 'public holiday', 'Public Holiday' ];
                                $sickkeywords = ['Sick', 'sick', 'sick leave', 'Sick Leave' ];

                                if (in_array($todayisleave->type->name, $vacationkeywords)) {
                                    $vacationleave++;
                                    $data[] = "VL";
                                } elseif (in_array($todayisleave->type->name, $phkeywords)) {
                                    $phleave++;
                                    $data[] = "PH";

                                } elseif ($todayisleave->type->is_paid == 0) {
                                    $unpaidleave++;
                                    $data[] = "UL";
                                } elseif (in_array($todayisleave->type->name, $sickkeywords)) {
                                    $sickleaveCount++;
                                    $data[] = "SL";
                                } else {
                                    $leaveCount++;
                                    $data[] = "LV";
                                }
                            } else {
                                $absentCount++;
                                $data[] = "A";
                            }
                        } elseif ($attendance[0]->status->name == 'Leave') {
                            if($todayisleave && $leaveType == 'working'){
                                if($attendance[0]->status->name == 'Weekend') {
                                    $weekendCount++;
                                    $data[] = "W";
                                }
                            } else {
                                $vacationkeywords = ['Vacation', 'vacation', 'vacation leave', 'Vacation Leave' ];
                                $phkeywords = ['PH', 'ph', 'public holiday', 'Public Holiday' ];
                                $sickkeywords = ['Sick', 'sick', 'sick leave', 'Sick Leave' ];

                                if (in_array($todayisleave->type->name, $vacationkeywords)) {
                                    $vacationleave++;
                                    $data[] = "VL";
                                } elseif (in_array($todayisleave->type->name, $phkeywords)) {
                                    $phleave++;
                                    $data[] = "PH";

                                } elseif ($todayisleave->type->is_paid == 0) {
                                    $unpaidleave++;
                                    $data[] = "UL";
                                } elseif (in_array($todayisleave->type->name, $sickkeywords)) {
                                    $sickleaveCount++;
                                    $data[] = "SL";
                                } else {
                                    $leaveCount++;
                                    $data[] = "LV";
                                }
                            }
                        } elseif ($attendance[0]->status->name == 'Weekend') {
                            if($todayisleave && $leaveType == 'calendar'){
                                $leaveCount++;
                                $data[] = "LV";
                            } else {
                                $weekendCount++;
                                $data[] = "W";
                            }
                        } elseif ($attendance[0]->status->name == 'Holiday') {
                            $PresentCheckOut = Attendance::where('user_id', $user->id)->where('date', $date)->orderBy('id', 'desc')->first();
                            if($PresentCheckOut && $PresentCheckOut->status == \Modules\Attendance\Enums\AttendanceStatus::Present){
                                $presentCount++;
                                $data[] = "P";
                            } else {
                                $holidayCount++;
                                $data[] = "H";
                            }
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Late') {
                            $data[] = "P";
                            $presentCount++;
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'EarlyOut') {
                            $data[] = "P";
                            $presentCount++;
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } else {
                            $status = $attendance[0]?->status->name ?? 'NA';
                            $data[] = $status;
                        }
                    } else {
                        if ($todayisleave) {
                            $vacationkeywords = ['Vacation', 'vacation', 'vacation leave', 'Vacation Leave' ];
                            $phkeywords = ['PH', 'ph', 'public holiday', 'Public Holiday' ];
                            $sickkeywords = ['Sick', 'sick', 'sick leave', 'Sick Leave' ];

                            if (in_array($todayisleave->type->name, $vacationkeywords)) {
                                $vacationleave++;
                                $data[] = "VL";
                            } elseif (in_array($todayisleave->type->name, $phkeywords)) {
                                $phleave++;
                                $data[] = "PH";

                            } elseif ($todayisleave->type->is_paid == 0) {
                                $unpaidleave++;
                                $data[] = "UL";
                            } elseif (in_array($todayisleave->type->name, $sickkeywords)) {
                                $sickleaveCount++;
                                $data[] = "SL";
                            } else {
                                $leaveCount++;
                                $data[] = "LV";
                            }
                        } else {
                            $data[] = "";
                        }
                    }
                }
            }

            $data[] = (string) $absentCount;
            $data[] = (string) $presentCount;
            // $data[] = (string) $total_leaves;
            $data[] = (string) $leaveCount;
            $data[] = (string) $holidayCount;
            $data[] = (string) $weekendCount;
            // $data[] = (string) $lateCount;
            $data[] = (string) $sickleaveCount;
            // $data[] = (string) $earlyoutCount;
            $data[] = (string) $this->daysInMonth;
            $attendances[] = $data;
        }

        // echo"<pre>";print_r($attendances);die;

        $headers = [
            __trans('employee_id'),
            __trans('name'),
        ];

        foreach (\Carbon\CarbonPeriod::create($startDate, $endDate) as $dateObj) {
            $headers[] = $dateObj->format('d D');
        }

        $headers[] = __trans('total_absent');
        $headers[] = __trans('total_present');
        $headers[] = __trans('total_leave');
        $headers[] = __trans('total_holiday');
        $headers[] = __trans('total_weekend');
        // $headers[] = __trans('total_late');
        $headers[] = __trans('total_sickleave');
        // $headers[] = __trans('total_earlyout');
        $headers[] = __trans('total_days');

        if ($button == "export") {
            $exportExcel = [];

            foreach ($attendances as $i => $atte) {
                $exportExcel[$i][] = $atte;
            }

            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'attendance_report_' . $start_date . '_to_' . $end_date . '.xlsx');
        }

        return view('backend.reports.attendance_report', compact('attendances', 'headers', 'start_date', 'end_date', 'departmentId', 'searchEmp', 'search'));
    }

    public function late_comers_report(Request $request)
    {
        // view()->share('activeLink', 'attendance_report');
        $this->request     = $request;
        $this->year        = $request->month_year ? date('Y', strtotime($request->month_year)) : date('Y');
        $this->month       = $request->month_year ? date('m', strtotime($request->month_year)) : date('m');
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $month_year        = "";
        $year              = $this->year;
        $month             = $this->month;
        $departmentId      = '';
        $searchEmp         = '';
        $export            = '';
        $search            = true;
        $departmentId      = $request->department_id;
        $searchEmp         = $request->search_emp;
        $month_year        = $request->month_year;
        $button            = $request->button;

        $query = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }
        $users = $query->get();

        $attendances = [];
        foreach ($users as $key => $user) {
            $data = [
                $user->employee_id,
                $user->name,
            ];
            $absentCount    = 0;
            $presentCount   = 0;
            $leaveCount     = 0;
            $holidayCount   = 0;
            $weekendCount   = 0;
            $lateCount      = 0;
            $sickleaveCount = 0;
            $earlyoutCount  = 0;
            $late_days      = 0;
            for ($i = 1; $i <= $this->daysInMonth; $i++) {
                $date    = now()->parse("$this->year-$this->month-$i")->toDateString();
                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                if ($holiday) {
                    $data[] = "H";
                } else {
                    $attendance = Attendance::where('user_id', $user->id)->where('date', $date)->get();

                    if (! empty($attendance[0]->status->name)) {
                        // echo"<pre>";print_r($attendance[0]->clock_in);die;
                        // if($i==12){
                        //     dd($attendance);
                        // }
                        // if ($attendance[0]->status->name == 'Present') {
                        //     $presentCount++;
                        //     $data[] = "P";
                        //     // echo"<pre>";print_r($attendance[0]->status->name);
                        // } else
                        // if ($attendance[0]->status->name == 'Late') {
                        //     $lateCount++;
                        //     $data[] = "L";
                        //     // echo"<pre>";print_r($attendance[0]->status->name);
                        // } else
                        if ($attendance[0]->status->name == 'Absent') {
                            $absentCount++;
                            $data[] = "A";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Leave') {
                            $leaveCount++;
                            $data[] = "LV";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Weekend') {
                            $weekendCount++;
                            $data[] = "W";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Holiday') {
                            $holidayCount++;
                            $data[] = "H";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'SickLeave') {
                            $sickleaveCount++;
                            $data[] = "SL";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'EarlyOut') {
                            $earlyoutCount++;
                            $data[] = "EO";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } else {
                            $presentCount++;

                            $users_shifts = DB::table('users_shifts')
                                ->join('shift_schedules', 'users_shifts.schedule_id', '=', 'shift_schedules.id')
                                ->where('users_shifts.user_id', $user->id)
                                ->where('users_shifts.assigned_for_date', $date)
                                ->get();
                            if (! empty($users_shifts[0]->shift_start)) {
                                $shift_start = Carbon::parse($users_shifts[0]->shift_start)->format('H:i:00');
                                $clock_in    = Carbon::parse($attendance[0]->clock_in)->format('H:i:00');
                                // $visit_in =  Carbon::parse($attendance[0]->visit_in)->format('H:i:00') ;
                                $locationvisits = LocationVisits::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->orderBy('id', 'asc')->first();

                                if ($locationvisits) {
                                    $visit_in = Carbon::parse($locationvisits->visit_in)->format('H:i:00');
                                    // dd($visit_in);
                                    if ($shift_start < $visit_in) {
                                        // dd($locationvisits->visit_in);
                                        $late   = (new Carbon($visit_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $data[] = "LC-P(" . $late . ")";
                                        $late_days++;
                                    } else {
                                        $data[] = "P";
                                    }
                                } else {
                                    $checkins = Checkin::where('user_id', $user->id)
                                        ->where('date', $date)
                                        ->where('type', 'in')
                                        ->orderBy('id', 'asc')->first();
                                    if (isset($checkins) && $shift_start < Carbon::parse($checkins->time)->format('H:i:00')) {
                                        $clock_in = Carbon::parse($checkins->time)->format('H:i:00');
                                        $early    = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $data[]   = "LC-P(" . $early . ")";
                                        $late_days++;
                                    } else if ($shift_start < $clock_in) {
                                        $late   = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $data[] = "LC-P(" . $late . ")";
                                        $late_days++;
                                    } else {
                                        $data[] = "P";
                                    }
                                }
                                // echo"<pre>";print_r($users_shifts[0]->shift_start);die;

                            } elseif ($attendance[0]->status->name == 'Present') {
                                $data[] = "P";
                                // echo"<pre>";print_r($attendance[0]->status->name);
                            } elseif ($attendance[0]->status->name == 'Late') {
                                $data[] = "LC";
                                // echo"<pre>";print_r($attendance[0]->status->name);
                            } else {
                                $data[] = "NA";
                            }
                        }
                    } else {
                        $todayisleave = Leave::where([['user_id', $user->id], ['status', 'approved']])
                            ->whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date)
                            ->first();
                        if ($todayisleave) {
                            $leaveCount++;
                            $data[] = "LV";
                        } else {
                            $data[] = "";
                        }
                    }
                }
            }
            $data[]        = (string) $late_days;
            $data[]        = (string) $absentCount;
            $data[]        = (string) $presentCount;
            $data[]        = (string) $this->daysInMonth;
            $attendances[] = $data;
        }

        // echo"<pre>";print_r($attendances);die;

        $headers = [
            __trans('employee_id'),
            __trans('name'),
        ];

        for ($i = 1; $i <= $this->daysInMonth; $i++) {
            $headers[] = $i;
        }
        $headers[] = __trans('total_late_days');
        $headers[] = __trans('total_absent');
        $headers[] = __trans('total_present');
        $headers[] = __trans('total_days');
        if ($button == "export") {
            foreach ($attendances as $i => $atte) {
                $exportExcel[$i][] = $atte;
            }

            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'late_comers_attendance_report_' . $year . '_' . $month . '.xlsx');
        }
        // echo"<pre>";print_r($headers);die;
        return view('backend.reports.late_comers_report', compact('attendances', 'headers', 'year', 'month', 'departmentId', 'searchEmp', 'search', 'month_year'));
        // return view('backend.reports.late_comers_report', compact('attendances', 'headers', 'year', 'month'));
    }

    public function early_comers_report(Request $request)
    {
        // view()->share('activeLink', 'attendance_report');
        $this->request     = $request;
        $this->year        = $request->month_year ? date('Y', strtotime($request->month_year)) : date('Y');
        $this->month       = $request->month_year ? date('m', strtotime($request->month_year)) : date('m');
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $month_year        = "";
        $year              = $this->year;
        $month             = $this->month;
        $departmentId      = '';
        $searchEmp         = '';
        $export            = '';
        $search            = true;
        $departmentId      = $request->department_id;
        $searchEmp         = $request->search_emp;
        $month_year        = $request->month_year;
        $button            = $request->button;
        $query             = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }
        $users = $query->get();

        $attendances = [];
        foreach ($users as $key => $user) {

            $data = [
                $user->employee_id,
                $user->name,
            ];
            $absentCount    = 0;
            $presentCount   = 0;
            $leaveCount     = 0;
            $holidayCount   = 0;
            $weekendCount   = 0;
            $lateCount      = 0;
            $sickleaveCount = 0;
            $earlyoutCount  = 0;
            $earlyDays      = 0;
            for ($i = 1; $i <= $this->daysInMonth; $i++) {
                $date    = now()->parse("$this->year-$this->month-$i")->toDateString();
                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                if ($holiday) {
                    $data[] = "H";
                } else {

                    $attendance = Attendance::where('user_id', $user->id)->where('date', $date)->get();

                    if (! empty($attendance[0]->status->name)) {

                        // if ($attendance[0]->status->name == 'Present') {
                        //     $presentCount++;
                        //     $data[] = "P";
                        //     // echo"<pre>";print_r($attendance[0]->status->name);
                        // } else
                        if ($attendance[0]->status->name == 'Late') {
                            $lateCount++;
                            $data[] = "LT";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Absent') {
                            $absentCount++;
                            $data[] = "A";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Leave') {
                            $leaveCount++;
                            $data[] = "LV";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Weekend') {
                            $weekendCount++;
                            $data[] = "W";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Holiday') {
                            $holidayCount++;
                            $data[] = "H";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'SickLeave') {
                            $sickleaveCount++;
                            $data[] = "SL";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } else {
                            $presentCount++;

                            $users_shifts = DB::table('users_shifts')
                                ->join('shift_schedules', 'users_shifts.schedule_id', '=', 'shift_schedules.id')
                                ->where('users_shifts.user_id', $user->id)
                                ->where('users_shifts.assigned_for_date', $date)
                                ->get();

                            if (! empty($users_shifts[0]->shift_start)) {

                                // $shift_start = $users_shifts[0]->shift_start;
                                // $clock_in = $attendance[0]->clock_in;
                                // $visit_in = $attendance[0]->visit_in;
                                $shift_start = Carbon::parse($users_shifts[0]->shift_start)->format('H:i:00');
                                $clock_in    = Carbon::parse($attendance[0]->clock_in)->format('H:i:00');
                                // $visit_in =  Carbon::parse($attendance[0]->visit_in)->format('H:i:00') ;
                                $locationvisits = LocationVisits::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->orderBy('id', 'asc')->first();
                                if ($locationvisits) {
                                    $visit_in = Carbon::parse($locationvisits->visit_in)->format('H:i:00');

                                    if ($shift_start > $visit_in) {
                                        // dd($locationvisits);
                                        $early  = (new Carbon($visit_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $data[] = "EC-P(" . $early . ")";
                                        $earlyDays++;
                                    } else {
                                        $data[] = "P";
                                    }
                                } else {
                                    $checkins = Checkin::where('user_id', $user->id)
                                        ->where('date', $date)
                                        ->where('type', 'in')
                                        ->orderBy('id', 'asc')->first();
                                    if (isset($checkins) && $shift_start > Carbon::parse($checkins->time)->format('H:i:00')) {
                                        $clock_in = Carbon::parse($checkins->time)->format('H:i:00');
                                        $early    = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $data[]   = "EC-P(" . $early . ")";
                                        $earlyDays++;
                                    } else if ($shift_start > $clock_in) {
                                        $early  = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $data[] = "EC-P(" . $early . ")";
                                        $earlyDays++;
                                    } else {
                                        $data[] = "P";
                                    }
                                }
                            } elseif ($attendance[0]->status->name == 'Present') {
                                $data[] = "P";
                            } else {
                                $data[] = "NA";
                            }
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        }
                    } else {
                        $todayisleave = Leave::where([['user_id', $user->id], ['status', 'approved']])
                            ->whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date)
                            ->first();
                        if ($todayisleave) {
                            $leaveCount++;
                            $data[] = "LV";
                        } else {
                            $data[] = "";
                        }
                    }
                }
            }
            $data[]        = (string) $earlyDays;
            $data[]        = (string) $absentCount;
            $data[]        = (string) $presentCount;
            $data[]        = (string) $this->daysInMonth;
            $attendances[] = $data;
        }

        // echo"<pre>";print_r($attendances);die;

        $headers = [
            __trans('employee_id'),
            __trans('name'),
        ];

        for ($i = 1; $i <= $this->daysInMonth; $i++) {
            $headers[] = $i;
        }
        $headers[] = __trans('total_early_days');
        $headers[] = __trans('total_absent');
        $headers[] = __trans('total_present');
        $headers[] = __trans('total_days');

        // echo"<pre>";print_r($headers);die;
        if ($button == "export") {
            foreach ($attendances as $i => $atte) {
                $exportExcel[$i][] = $atte;
            }

            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'early_comers_attendance_report_' . $year . '_' . $month . '.xlsx');
        }

        // return view('backend.reports.early_comers_report', compact('attendances', 'headers', 'year', 'month'));
        return view('backend.reports.early_comers_report', compact('attendances', 'headers', 'year', 'month', 'departmentId', 'searchEmp', 'search', 'month_year'));
    }

    public function early_outs_report(Request $request)
    {
        // view()->share('activeLink', 'attendance_report');
        $this->request     = $request;
        $this->year        = $request->month_year ? date('Y', strtotime($request->month_year)) : date('Y');
        $this->month       = $request->month_year ? date('m', strtotime($request->month_year)) : date('m');
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $month_year        = "";
        $year              = $this->year;
        $month             = $this->month;
        $departmentId      = '';
        $searchEmp         = '';
        $export            = '';
        $search            = true;
        $departmentId      = $request->department_id;
        $searchEmp         = $request->search_emp;
        $month_year        = $request->month_year;
        $button            = $request->button;
        $query             = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }
        $users = $query->get();

        $attendances = [];
        foreach ($users as $key => $user) {

            $data = [
                $user->employee_id,
                $user->name,
            ];
            $absentCount    = 0;
            $presentCount   = 0;
            $leaveCount     = 0;
            $holidayCount   = 0;
            $weekendCount   = 0;
            $lateCount      = 0;
            $sickleaveCount = 0;
            $earlyoutCount  = 0;
            $earlyoutDays   = 0;
            for ($i = 1; $i <= $this->daysInMonth; $i++) {
                $date    = now()->parse("$this->year-$this->month-$i")->toDateString();
                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                if ($holiday) {
                    $data[] = "H";
                } else {

                    $attendance = Attendance::where('user_id', $user->id)->where('date', $date)->get();

                    if (! empty($attendance[0]->status->name)) {

                        // if ($attendance[0]->status->name == 'Present') {
                        //     $presentCount++;
                        //     $data[] = "P";
                        //     // echo"<pre>";print_r($attendance[0]->status->name);
                        // } else
                        if ($attendance[0]->status->name == 'Late') {
                            $lateCount++;
                            $data[] = "LT";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Absent') {
                            $absentCount++;
                            $data[] = "A";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Leave') {
                            $leaveCount++;
                            $data[] = "LV";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Weekend') {
                            $weekendCount++;
                            $data[] = "W";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'Holiday') {
                            $holidayCount++;
                            $data[] = "H";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } elseif ($attendance[0]->status->name == 'SickLeave') {
                            $sickleaveCount++;
                            $data[] = "SL";
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        } else {
                            $presentCount++;

                            $users_shifts = DB::table('users_shifts')
                                ->join('shift_schedules', 'users_shifts.schedule_id', '=', 'shift_schedules.id')
                                ->where('users_shifts.user_id', $user->id)
                                ->where('users_shifts.assigned_for_date', $date)
                                ->get();
                            // if ($i == 12)

                            if (! empty($users_shifts[0]->shift_end)) {
                                // dd($users_shifts);

                                // $shift_end = $users_shifts[0]->shift_end;
                                // $clock_in = $attendance[0]->clock_in;
                                // $visit_in = $attendance[0]->visit_in;
                                $shift_end = Carbon::parse($users_shifts[0]->shift_end)->format('H:i:00');
                                $clock_out = Carbon::parse($attendance[0]->clock_out)->format('H:i:00');
                                // $visit_in =  Carbon::parse($attendance[0]->visit_in)->format('H:i:00') ;
                                $locationvisits = LocationVisits::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->orderBy('id', 'asc')->first();
                                if ($locationvisits) {
                                    $visit_out = Carbon::parse($locationvisits->visit_out)->format('H:i:00');

                                    if ($shift_end > $visit_out) {
                                        // dd($locationvisits);
                                        $earlyout = (new Carbon($visit_out))->diff(new Carbon($shift_end))->format('%h:%I');
                                        $data[]   = "EO-P(" . $earlyout . ")";
                                        $earlyoutDays++;
                                    } else {
                                        $data[] = "P";
                                    }
                                } else {
                                    $checkins = Checkin::where('user_id', $user->id)
                                        ->where('date', $date)
                                        ->where('type', 'in')
                                        ->orderBy('id', 'asc')->first();
                                    if (isset($checkins) && $shift_end > Carbon::parse($checkins->time)->format('H:i:00')) {
                                        $clock_out = Carbon::parse($checkins->time)->format('H:i:00');
                                        $earlyout  = (new Carbon($clock_out))->diff(new Carbon($shift_end))->format('%h:%I');
                                        $data[]    = "EO-P(" . $earlyout . ")";
                                        $earlyoutDays++;
                                    } else if ($shift_end > $clock_out) {
                                        $earlyout = (new Carbon($clock_out))->diff(new Carbon($shift_end))->format('%h:%I');
                                        $data[]   = "EO-P(" . $earlyout . ")";
                                        $earlyoutDays++;
                                    } else {
                                        $data[] = "P";
                                    }
                                }
                            } elseif ($attendance[0]->status->name == 'Present') {
                                $data[] = "P";
                            } else {
                                $data[] = "NA";
                            }
                            // echo"<pre>";print_r($attendance[0]->status->name);
                        }
                    } else {
                        $todayisleave = Leave::where([['user_id', $user->id], ['status', 'approved']])
                            ->whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date)
                            ->first();
                        if ($todayisleave) {
                            $leaveCount++;
                            $data[] = "LV";
                        } else {
                            $data[] = "";
                        }
                    }
                }
            }
            $data[]        = (string) $earlyoutDays;
            $data[]        = (string) $absentCount;
            $data[]        = (string) $presentCount;
            $data[]        = (string) $this->daysInMonth;
            $attendances[] = $data;
        }

        // echo"<pre>";print_r($attendances);die;

        $headers = [
            __trans('employee_id'),
            __trans('name'),
        ];

        for ($i = 1; $i <= $this->daysInMonth; $i++) {
            $headers[] = $i;
        }
        $headers[] = __trans('total_earlyout_days');
        $headers[] = __trans('total_absent');
        $headers[] = __trans('total_present');
        $headers[] = __trans('total_days');

        // echo"<pre>";print_r($headers);die;
        if ($button == "export") {
            foreach ($attendances as $i => $atte) {
                $exportExcel[$i][] = $atte;
            }

            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'early_outs_attendance_report_' . $year . '_' . $month . '.xlsx');
        }

        // return view('backend.reports.early_comers_report', compact('attendances', 'headers', 'year', 'month'));
        return view('backend.reports.early_outs_report', compact('attendances', 'headers', 'year', 'month', 'departmentId', 'searchEmp', 'search', 'month_year'));
    }

    public function increment_report(Request $request)
    {
        // view()->share('activeLink', 'attendance_report');
        $this->request     = $request;
        $this->year        = $request->month_year ? date('Y', strtotime($request->month_year)) : date('Y');
        $this->month       = $request->month_year ? date('m', strtotime($request->month_year)) : date('m');
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $month_year        = "";
        $year              = $this->year;
        $month             = $this->month;
        $departmentId      = '';
        $searchEmp         = '';
        $export            = '';
        $search            = true;
        $departmentId      = $request->department_id;
        $searchEmp         = $request->search_emp;
        $month_year        = $request->month_year;
        $button            = $request->button;
        DB::connection()->enableQueryLog();

        $query = DB::table('user_salary_increments')
            ->select('user_salary_increments.*', 'departments.name as department_name', 'users.name as user_name', 'users.employee_id')
            ->join('users', 'user_salary_increments.user_id', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id');
        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('users.department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }
        if (! empty($request->month_year)) {
            $sdate = now()->parse("$this->year-$this->month-1")->toDateString();
            $edate = now()->parse("$this->year-$this->month-$this->daysInMonth")->toDateString();
            $query->where('increment_date', '>=', $sdate);
            $query->where('increment_date', '<=', $edate);
        }
        // dd($request->employee[0]);
        if (isset($request->employee[0]) && $request->employee[0] != "0") {
            $query->when($request->employee, function ($query) use ($request) {
                $query->whereIn('users.id', $request->employee);
            });
        }
        if (isset($request->department[0]) && $request->department[0] != "0") {
            $query->when($request->department, function ($query) use ($request) {
                $query->where('users.department_id', $request->department);
            });
        }

        $increments = $query->get();
        // dd($increments);

        // $queries = DB::getQueryLog();
        // $last_query = end($queries);
        // dd($last_query);

        $headers[] = __trans('employee_id');
        $headers[] = __trans('employee_name');
        $headers[] = __trans('department');
        $headers[] = __trans('before_increment');
        $headers[] = __trans('increment');
        $headers[] = __trans('after_increment');
        $headers[] = __trans('increment_date');

        if ($button == "export") {
            $exportExcel = [];

            foreach ($increments as $i => $data) {
                $exportExcel[$i]['employee_id']      = $data->employee_id;
                $exportExcel[$i]['user_name']        = $data->user_name;
                $exportExcel[$i]['department_name']  = $data->department_name;
                $exportExcel[$i]['before_increment'] = $data->before_increment;
                $exportExcel[$i]['increment']        = $data->increment;
                $exportExcel[$i]['after_increment']  = $data->after_increment;
                $exportExcel[$i]['increment_date']   = $data->increment_date;
            }
            // dd($exportExcel);
            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'increment_report_' . $year . '_' . $month . '.xlsx');
        }
        $filterEmployees = User::whereIn('id', $request->employee ?? [])->get();
        // dd($filterEmployees);
        // $filterEmployees->push(array(["id"=>"0","name"=>"All"]));

        $filterDepartment = Department::where('id', $request->department)->first();

        return view('backend.reports.increments_report', compact('increments', 'headers', 'year', 'month', 'departmentId', 'searchEmp', 'search', 'month_year', 'filterEmployees', 'filterDepartment'));
    }

    public function expense_report(Request $request)
    {
        // view()->share('activeLink', 'attendance_report');
        $this->request     = $request;
        $this->year        = $request->month_year ? date('Y', strtotime($request->month_year)) : date('Y');
        $this->month       = $request->month_year ? date('m', strtotime($request->month_year)) : date('m');
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $month_year        = "";
        $year              = $this->year;
        $month             = $this->month;
        $departmentId      = '';
        $searchEmp         = '';
        $export            = '';
        $search            = true;
        $departmentId      = $request->department_id;
        $searchEmp         = $request->search_emp;
        $month_year        = $request->month_year;
        $button            = $request->button;
        DB::connection()->enableQueryLog();

        $query = DB::table('expenses')
            ->select('expenses.*', 'expense_types.name as expense_types', 'users.name as user_name', 'users.employee_id', 'creator.name as creator_name')
            ->join('users', 'expenses.user_id', '=', 'users.id')
            ->join('users as creator', 'expenses.created_by', '=', 'creator.id')
            ->join('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id');
        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('users.department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }
        if (! empty($request->month_year)) {
            $sdate = now()->parse("$this->year-$this->month-1")->toDateString();
            $edate = now()->parse("$this->year-$this->month-$this->daysInMonth")->toDateString();
            $query->where('date', '>=', $sdate);
            $query->where('date', '<=', $edate);
        }
        // dd($request->employee[0]);
        if (isset($request->employee[0]) && $request->employee[0] != "0") {
            $query->when($request->employee, function ($query) use ($request) {
                $query->whereIn('users.id', $request->employee);
            });
        }
        if (isset($request->department[0]) && $request->department[0] != "0") {
            $query->when($request->department, function ($query) use ($request) {
                $query->where('users.department_id', $request->department);
            });
        }
        $query->orderBy('date', 'desc');

        $expenses = $query->get();

        // dd( $expenses);
        $headers[] = __trans('user_id');
        $headers[] = __trans('creator_name');
        $headers[] = __trans('employee_name');
        $headers[] = __trans('date');
        $headers[] = __trans('expense_types');
        $headers[] = __trans('name');
        $headers[] = __trans('amount');
        $headers[] = __trans('remark');
        $headers[] = __trans('status');

        if ($button == "export") {
            $exportExcel = [];

            foreach ($expenses as $i => $data) {
                $exportExcel[$i]['user_id']       = $data->employee_id;
                $exportExcel[$i]['creator_name']  = $data->creator_name;
                $exportExcel[$i]['employee_name'] = $data->user_name;
                $exportExcel[$i]['date']          = $data->date;
                $exportExcel[$i]['expense_types'] = $data->expense_types;
                $exportExcel[$i]['name']          = $data->name;
                $exportExcel[$i]['amount']        = $data->amount;
                $exportExcel[$i]['remark']        = $data->remark;
                $exportExcel[$i]['status']        = $data->status;
            }
            // dd($exportExcel);
            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'expense_report_' . $year . '_' . $month . '.xlsx');
        }
        $filterEmployees = User::whereIn('id', $request->employee ?? [])->get();
        // dd($filterEmployees);
        // $filterEmployees->push(array(["id"=>"0","name"=>"All"]));

        $filterDepartment = Department::where('id', $request->department)->first();

        return view('backend.reports.expense_report', compact('expenses', 'headers', 'year', 'month', 'departmentId', 'searchEmp', 'search', 'month_year', 'filterEmployees', 'filterDepartment'));
    }

    public function gratuity_report(Request $request)
    {
        // view()->share('activeLink', 'attendance_report');
        $this->request    = $request;
        $this->chosenDate = $request->chosenDate ? $request->chosenDate : date('Y-m-d');
        $chosenDate       = $this->chosenDate;
        $departmentId     = '';
        $searchEmp        = '';
        $export           = '';
        $search           = true;
        $departmentId     = $request->department_id;
        $searchEmp        = $request->search_emp;
        $month_year       = $request->month_year;
        $button           = $request->button;
        DB::connection()->enableQueryLog();

        $query = User::whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        })->where('status', 'active');
        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('users.department_id', $departmentId);
        }
        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }
        if (isset($request->employee[0]) && $request->employee[0] != "0") {
            $query->when($request->employee, function ($query) use ($request) {
                $query->whereIn('users.id', $request->employee);
            });
        }
        if (isset($request->department[0]) && $request->department[0] != "0") {
            $query->when($request->department, function ($query) use ($request) {
                $query->where('users.department_id', $request->department);
            });
        }
        // $query->orderBy('date', 'desc');
        $sl_no      = 0;
        $employees  = $query->get();
        $gratuities = $employees->map(function ($employee) use ($chosenDate, &$sl_no) {
            $gratuity = $employee->calculateGratuity($chosenDate);
            $sl_no++;
            return [
                'sl_no'         => $sl_no,
                'employee_id'   => $employee->employee_id,
                'employee_name' => $employee->name,
                // 'joining_date'  => $gratuity['joining_date']->format('Y-m-d'),
                'joining_date'  => ! empty($gratuity['joining_date'])
                    ? Carbon::parse($gratuity['joining_date'])->format('Y-m-d')
                    : '',

                'designation'   => $gratuity['designation'],
                'based_date'    => $chosenDate,
                'basic_salary'  => $gratuity['basic_salary'],
                // 'days' => $gratuity['day'],
                // 'months' => $gratuity['month'],
                // 'year' => $gratuity['year'],
                // 'below5year' => $gratuity['below5year'],
                // 'above5year' => $gratuity['above5year'],
                // 'below5grant' => $gratuity['below5yearsOfAmount'],
                // 'above5grant' => $gratuity['above5yearsOfAmount'],
                'totalgrant'    => $gratuity['totalamount'],
            ];
        });

        // Calculate the total gratuity
        $totalamount = $gratuities->sum('totalgrant');

        // Add a summary row for the total gratuity
        $gratuities->push([
            'sl_no'         => null,
            'employee_id'   => null,
            'employee_name' => "Total",
            'joining_date'  => null,
            'designation'   => null,
            'based_date'    => null,
            'basic_salary'  => null,
            // 'days' => null,
            // 'months' => null,
            // 'year' => null,
            // 'below5year' => null,
            // 'above5year' => null,
            // 'below5grant' => null,
            // 'above5grant' => null,
            'totalgrant'    => $totalamount,
        ]);

        if ($button == "export") {
            $exportExcel = [];
            $headers     = [
                __trans('Sl No'),
                __trans('Emp ID'),
                __trans('Full Name'),
                __trans('DOJ'),
                __trans('Designation'),
                __trans('LWD'),
                __trans('Basic'),
                // __trans('Days'),
                // __trans('Months'),
                // __trans('Year'),
                // __trans('Below 5 Year'),
                // __trans('Above 5 Year'),
                // __trans('Below 5 Grant'),
                // __trans('Above 5 Grant'),
                __trans('Total Grant'),
            ];

            $exportExcel = [];
            foreach ($gratuities as $i => $gratuity) {
                $exportExcel[$i][] = $gratuity;
            }
            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'gratuity_report.xlsx');
        }
        $filterEmployees  = User::whereIn('id', $request->employee ?? [])->get();
        $filterDepartment = Department::where('id', $request->department)->first();
        return view('backend.reports.gratuity_report', compact('gratuities', 'chosenDate', 'departmentId', 'searchEmp', 'search', 'month_year', 'filterEmployees', 'filterDepartment'));
    }
    public function accruals_report(Request $request)
    {
        // view()->share('activeLink', 'attendance_report');
        $this->request    = $request;
        $this->totalDays  = Carbon::now()->daysInMonth;
        $this->lastDate   = Carbon::now()->endOfMonth()->toDateString();
        $this->year       = $request->year ? date('Y', strtotime($request->year)) : date('Y');
        $this->month      = $request->month ? date('m', strtotime($request->month)) : date('m');
        $this->start_date = date('Y-m-01', strtotime("{$this->year}-{$this->month}-01"));

        // End date: last day of the month
        $this->end_date    = date('Y-m-t', strtotime("{$this->year}-{$this->month}-01"));
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $chosenDate        = $this->lastDate;
        $departmentId      = '';
        $searchEmp         = '';
        $export            = '';
        $search            = true;
        $departmentId      = $request->department_id;
        $searchEmp         = $request->search_emp;
        $month             = $this->month;
        $year              = $this->year;
        $start_date        = $this->start_date;
        $end_date          = $this->end_date;
        $button            = $request->button;
        DB::connection()->enableQueryLog();

        $query = User::whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        })->where('status', 'active');
        if ($departmentId != "all" && $departmentId > 0) {
            $query->where('users.department_id', $departmentId);
        }
        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }
        if (isset($request->employee[0]) && $request->employee[0] != "0") {
            $query->when($request->employee, function ($query) use ($request) {
                $query->whereIn('users.id', $request->employee);
            });
        }
        if (isset($request->department[0]) && $request->department[0] != "0") {
            $query->when($request->department, function ($query) use ($request) {
                $query->where('users.department_id', $request->department);
            });
        }
        // $query->orderBy('date', 'desc');
        $sl_no                  = 0;
        $employees              = $query->get();
        $accruals               = [];
        $total_baisc            = 0;
        $total_hra              = 0;
        $total_travel_allowance = 0;
        $total_other_allowance  = 0;
        // $total_food_allowance = 0;
        $total_gross             = 0;
        $total_gratuity          = 0;
        $total_leave_salary      = 0;
        $total_air_fair          = 0;
        $total_medical_insurance = 0;
        $total_visa              = 0;
        $total_bonus             = 0;
        $total_total_accruals    = 0;
        $total_month_accruals    = 0;
        foreach ($employees as $row => $employee) {
            try {

                $accruals[$row]["s_no"]             = $row + 1;
                $accruals[$row]["employee_id"]      = $employee->employee_id;
                $accruals[$row]["name"]             = $employee->name;
                $accruals[$row]["department_name"]  = optional($employee->department)->name ?? '';
                $accruals[$row]["designation_name"] = optional($employee->designation)->name ?? '';

                $basic            = 0;
                $hra              = 0;
                $travel_allowance = 0;
                $other_allowance  = 0;
                $food_allowance   = 0;
                if (isset($employee->salary)) {
                    $basic            = isset($employee->salary->basic) ? $employee->salary->basic : 0;
                    $hra              = isset($employee->salary->hra) ? $employee->salary->hra : 0;
                    $travel_allowance = isset($employee->salary->travel_allowance) ? $employee->salary->travel_allowance : 0;
                    $other_allowance  = isset($employee->salary->other_allowance) ? $employee->salary->other_allowance : 0;
                    $food_allowance   = isset($employee->salary->food_allowance) ? $employee->salary->food_allowance : 0;

                    $fixed_allowance  = isset($employee->salary) ? json_decode($employee->salary->fixed_allowances, true) : " ";
                    $hra              = isset($fixed_allowance['housing_allowance']) ? $fixed_allowance['housing_allowance'] : 0;
                    $hra              = intval($hra);
                    $travel_allowance = isset($fixed_allowance['transportation_allowance']) ? $fixed_allowance['transportation_allowance'] : 0;
                    $travel_allowance = intval($travel_allowance);

                    $other_allowance = isset($fixed_allowance['other_allowance']) ? $fixed_allowance['other_allowance'] : 0;
                    $other_allowance = intval($other_allowance);
                }

                $total_baisc            += $basic;
                $total_hra              += $hra;
                $total_travel_allowance += $travel_allowance;
                $total_other_allowance  += $other_allowance;
                // $total_food_allowance += $food_allowance;
                $accruals[$row]["baisc"]            = $basic;
                $accruals[$row]["hra"]              = $hra;
                $accruals[$row]["travel_allowance"] = $travel_allowance;
                $accruals[$row]["other_allowance"]  = $other_allowance;
                // $accruals[$row]["food_allowance"] = $food_allowance;
                // $gross = $basic + $hra + $travel_allowance + $travel_allowance + $other_allowance + $food_allowance;
                $userpayslipcontroller = new UserPaySlipController();
                $gross                 = $userpayslipcontroller->getGrossSalary($employee, $month, $year, $start_date, $end_date);
                // dd($gross);
                $total_gross                += $gross;
                $accruals[$row]["gross"]    = $gross;
                $gratuity_array             = $employee->calculateGratuity($chosenDate);
                $gratuity                   = isset($gratuity_array['totalamount']) ? $gratuity_array['totalamount'] : 0;
                $total_gratuity             += $gratuity;
                $accruals[$row]["gratuity"] = $gratuity;
                $leave_salary               = 0;
                if (getSetting('leave_salary') == 'yes') {
                    if (getSetting('salary_paid_on') === 'gross') {
                        $leave_salary = round(
                            ($basic +
                                $hra +
                                $food_allowance +
                                $travel_allowance +
                                $other_allowance)
                            / 12,
                            2
                        );
                    } elseif (getSetting('salary_paid_on') === 'basic') {
                        $leave_salary = round($basic / 12, 2);
                    } elseif (getSetting('salary_paid_on') === 'basic_housing') {
                        $leave_salary = round(($basic + $hra) / 12, 2);
                    }
                }
                $total_leave_salary             += $leave_salary;
                $accruals[$row]["leave_salary"] = $leave_salary;
                $air_fair                       = 0;
                if (isset($employee->workDetail) && $employee->workDetail->air_ticket_setting_id > 0) {
                    $airticketsetting = AirTicketSetting::find($employee->workDetail->air_ticket_setting_id);

                    if (isset($airticketsetting)) {
                        $request_after_months = ! empty($airticketsetting->request_after_months) ? $airticketsetting->request_after_months : 0;
                        $allowance_amount     = ! empty($airticketsetting->allowance_amount) ? $airticketsetting->allowance_amount : 0;
                        $air_fair             = number_format(($allowance_amount / $request_after_months), 2);
                    }
                }
                $total_air_fair             += $air_fair;
                $accruals[$row]['air_fair'] = $air_fair;
                $medical_insurance          = 0;
                if ($employee->workDetail) {
                    $medical_insurance = $employee->workDetail->annual_premium > 0 ? number_format($employee->workDetail->annual_premium / 12, 2) : 0;
                }
                $total_medical_insurance             += $medical_insurance;
                $accruals[$row]['medical_insurance'] = $medical_insurance;
                $bonus                               = 0;
                $visa                                = 0;
                foreach ($employee->all_allowance as $all_allowance) {
                    if (strtolower($all_allowance->title) == "bonus") {
                        if ($all_allowance->is_fixed_for_current_month == 0) {

                            if ($all_allowance->allowance_type == "fixed") {
                                $bonus = $all_allowance->amount;
                            } else {
                                $bonus = $all_allowance->amount % $all_allowance->percentage_amount;
                            }
                        } else if ($all_allowance->month_code = $month && $all_allowance->year == $year) {
                            if ($all_allowance->allowance_type == "fixed") {
                                $bonus = $all_allowance->amount;
                            } else {
                                $bonus = $all_allowance->amount % $all_allowance->percentage_amount;
                            }
                        }
                    }

                    if (strtolower($all_allowance->title) == "visa") {
                        if ($all_allowance->is_fixed_for_current_month == 0) {

                            if ($all_allowance->allowance_type == "fixed") {
                                $visa = $all_allowance->amount;
                            } else {
                                $visa = $all_allowance->amount % $all_allowance->percentage_amount;
                            }
                        } else if ($all_allowance->month_code = $month && $all_allowance->year == $year) {
                            if ($all_allowance->allowance_type == "fixed") {
                                $visa = $all_allowance->amount;
                            } else {
                                $visa = $all_allowance->amount % $all_allowance->percentage_amount;
                            }
                        }
                    }
                }

                $accruals[$row]['visa']           = $visa;
                $total_visa                       += $visa;
                $accruals[$row]['bonus']          = $bonus;
                $total_bonus                      += $bonus;
                $total_accruals                   = $gratuity + $leave_salary + $air_fair + $medical_insurance;
                $total_total_accruals             += $total_accruals;
                $accruals[$row]['total_accruals'] = $total_accruals;
                $total_month_accruals             += $total_accruals + $gross;
                $accruals[$row]['month_accruals'] = $total_accruals + $gross;
            } catch (Exception $e) {
                // dd($employee);
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }
        $accruals[] = [
            "s_no"              => "",
            "employee_id"       => "",
            "name"              => "Total",
            "department_name"   => "",
            "designation_name"  => "",
            "baisc"             => $total_baisc,
            "hra"               => $total_hra,
            "travel_allowance"  => $total_travel_allowance,
            "other_allowance"   => $total_other_allowance,
            // "food_allowance" => $total_food_allowance,
            "gross"             => $total_gross,
            "gratuity"          => $total_gratuity,
            "leave_salary"      => $total_leave_salary,
            "air_fair"          => $total_air_fair,
            "medical_insurance" => $total_medical_insurance,
            "visa"              => $total_visa,
            "bonus"             => $total_bonus,
            "total_accruals"    => $total_total_accruals,
            "month_accruals"    => $total_month_accruals,
        ];

        if ($button == "export") {
            $exportExcel = [];
            $headers     = [
                __trans('Sl No'),
                __trans('Emp ID'),
                __trans('Full Name'),
                __trans('Department'),
                __trans('Designation'),
                __trans('Basic'),
                __trans('HRA'),
                __trans('TA'),
                __trans('Other Allow'),
                // __trans('Food Allow'),
                __trans('Salary Gross'),
                __trans('Gratuity'),
                __trans('Leave Salary'),
                __trans('Air Fare'),
                __trans('Medical Insurance'),
                __trans('Visa'),
                __trans('Bonus'),
                __trans('Total Accruals'),
                __trans('Total' . Carbon::now()->format('F Y')),
            ];

            $exportExcel = [];
            foreach ($accruals as $i => $accrual) {
                $exportExcel[$i][] = $accrual;
            }
            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'accruals_report.xlsx');
        }
        $filterEmployees  = User::whereIn('id', $request->employee ?? [])->get();
        $filterDepartment = Department::where('id', $request->department)->first();
        return view('backend.reports.accruals_report', compact('accruals', 'chosenDate', 'departmentId', 'searchEmp', 'search', 'month', 'year', 'filterEmployees', 'filterDepartment'));
    }

    public function branch_budget_report(Request $request)
    {

        $departments = Department::get();
        $month       = $request->month ? $request->month : date('n', strtotime('-1 month'));

        return view('backend.reports.branch_budget_report', compact('departments', 'month'));
    }

    public function generateBranchBudgetReport(Request $request)
    {

        $month      = $request->month ? $request->month : date('n', strtotime('-1 month'));
        $year       = date('Y');
        $start_date = date('Y-m-01', strtotime("{$year}-{$month}-01"));

        // End date: last day of the month
        $end_date = date('Y-m-t', strtotime("{$year}-{$month}-01"));

        $headers = [
            __trans('branch_name'),
            __trans('monthly_budget'),
            __trans('active_employees'),
        ];

        $headers[] = __trans('total_spent');
        $headers[] = __trans('status');
        $headers[] = __trans('amount');
        $headers[] = __trans('month');
        if (str_contains(getSetting('currency'), 'AED')) {
            $AEDCurrency = '<img src="' . asset("assets/currency/aedb.png") . '" alt="AED" style="width:18px; height:18px; vertical-align:middle;">';
        } else {
            $AEDCurrency = getSetting('currency');
        }
        $departments = Department::get();
        $exportExcel = [];
        foreach ($departments as $department) {

            $row = [
                'branch_name'   => $department->name,
                'branch_budget' => $department->budget > 0 ? $department->budget . ' ' . $AEDCurrency : '0.00',
            ];
            $users = User::whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
                ->where('status', 'active')
                ->where('department_id', $department->id)
                ->get();
            $row['active_employees'] = $users->count();

            $total_user_salary = 0;

            foreach ($users as $user) {
                $year               = date('Y');
                $total_user_salary += getUserTotalNetSalary($user, $month, $year, $start_date, $end_date);
            }

            $is_over_budget = $total_user_salary > $department->budget ? 'Over Budget' : 'Within Budget';

            $row['total_spent'] = number_format($total_user_salary, 2) > 0 ? number_format($total_user_salary, 2) . ' ' . $AEDCurrency : '0.00';
            $row['status']      = $is_over_budget;
            $row['amount']      = abs($total_user_salary - $department->budget) > 0 ? abs($total_user_salary - $department->budget) . ' ' . $AEDCurrency : '0.00';
            $row['month']       = Carbon::create()->month($month)->format('F');

            $exportExcel[] = $row;
        }

        $export = new ExcelExport($exportExcel, $headers);
        return Excel::download($export, 'branch_budget_report.xlsx');
    }
    // public function air_ticket_report(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $today = Carbon::today();
    //         // $endOfMonth = Carbon::today()->endOfMonth();
    //         $endOfMonth = Carbon::today()->endOfMonth();

    //         $upcomingTickets = collect();

    //         $users = User::query()
    //             ->whereDoesntHave('roles', function ($query) {
    //                 $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
    //             })
    //             ->where('status', User::STATUS_ACTIVE)
    //             ->with('department') // eager load
    //             ->get();

    //         foreach ($users as $user) {

    //             $workdetails    = $user->workDetail()->first();
    //             $profiledetails = $user->profile()->first();

    //             if (!$workdetails || !$profiledetails) {
    //                 continue;
    //             }

    //             $joindate   = Carbon::parse($workdetails->joining_date);
    //             $country    = $profiledetails->country_id;
    //             $air_ticket_count = $workdetails->air_ticket_count;

    //             $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
    //             $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();

    //             $policymonth = 0;
    //             if ($workdetails->renewal_air_ticket === '1_year') {
    //                 $policymonth = 12;
    //             } elseif ($workdetails->renewal_air_ticket === '2_year') {
    //                 $policymonth = 24;
    //             } else {
    //                 $policymonth = $airtickeCountrySetting->request_after_months
    //                     ?? $airtickeSetting->request_after_months
    //                     ?? 0;
    //             }

    //             $quantity = $air_ticket_count > 0
    //                 ? $air_ticket_count
    //                 : ($airtickeCountrySetting?->request_limit_per_cycle ?? $airtickeSetting->request_limit_per_cycle ?? 0);

    //             if (!$airtickeSetting && !$airtickeCountrySetting) {
    //                 continue;
    //             }

    //             // $eligibleDate = $joindate->copy()->addMonths($policymonth);
    //             $eligibleDate = $joindate->copy();

    //             // Keep adding policy months until we reach the next eligible date
    //             while ($eligibleDate->lt($today)) {
    //                 $eligibleDate->addMonths($policymonth);
    //             }
    //             // if($user->id == 9)
    //             // dd($eligibleDate);

    //             // Only consider if upcoming date falls within this month
    //             if ($eligibleDate->between($today, $endOfMonth)) {
    //                 $upcomingTickets->push((object)[
    //                     "user"     => $user,
    //                     "date"     => $eligibleDate->toDateString(),
    //                     "amount"   => $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0,
    //                     "quantity" => $quantity,
    //                 ]);
    //             }
    //         }
    //         $upcomingTickets = $upcomingTickets->sortBy('date');

    //         return DataTables::of($upcomingTickets)
    //             ->addIndexColumn()
    //             ->addColumn('name', fn($row) => $row->user->name)
    //             ->addColumn('department', fn($row) => $row->user->department->name ?? '-')
    //             ->addColumn('date', fn($row) => formatDate($row->date, 'birth_date_format'))
    //             ->addColumn('amount', fn($row) => $row->amount)
    //             ->addColumn('quantity', fn($row) => $row->quantity)
    //             ->rawColumns(['name', 'department'])
    //             ->make(true);
    //     }

    //     return view('backend.reports.air_ticket_report');
    // }
    // public function air_ticket_report(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $month = $request->month ?? date('Y-m'); // format: YYYY-MM
    //         [$year, $mon] = explode('-', $month);

    //         $startOfMonth = Carbon::createFromDate($year, $mon, 1)->startOfMonth();
    //         $endOfMonth   = Carbon::createFromDate($year, $mon, 1)->endOfMonth();
    //         $today        = Carbon::today();
    //         // $today = Carbon::today();
    //         // $endOfMonth = Carbon::today()->endOfMonth();

    //         $upcomingTickets = collect();

    //         // eager load department, workDetail and profile to avoid extra queries
    //         $users = User::query()
    //             ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
    //             ->where('status', User::STATUS_ACTIVE)
    //             ->with(['department', 'workDetail', 'profile'])
    //             ->get();

    //         // preload all details for these users (avoid N+1)
    //         $userIds = $users->pluck('id')->toArray();
    //         $detailsGrouped = \App\Models\AirTicketDetail::whereIn('user_id', $userIds)
    //             ->orderBy('created_at', 'desc')
    //             ->get()
    //             ->groupBy('user_id');

    //         foreach ($users as $user) {
    //             $workdetails    = $user->workDetail;
    //             $profiledetails = $user->profile;

    //             if (!$workdetails || !$profiledetails) {
    //                 continue;
    //             }

    //             $joindate   = Carbon::parse($workdetails->joining_date);
    //             $country    = $profiledetails->country_id;
    //             $air_ticket_count = $workdetails->air_ticket_count;

    //             $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
    //             $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();

    //             if (!$airtickeSetting && !$airtickeCountrySetting) {
    //                 continue;
    //             }

    //             // determine policy months
    //             $policymonth = 0;
    //             if ($workdetails->renewal_air_ticket === '1_year') {
    //                 $policymonth = 12;
    //             } elseif ($workdetails->renewal_air_ticket === '2_year') {
    //                 $policymonth = 24;
    //             } else {
    //                 $policymonth = $airtickeCountrySetting->request_after_months
    //                     ?? $airtickeSetting->request_after_months
    //                     ?? 0;
    //             }

    //             // Prevent infinite loop: skip if policymonth not valid
    //             if ((int) $policymonth <= 0) {
    //                 continue;
    //             }

    //             $quantity = $air_ticket_count > 0
    //                 ? $air_ticket_count
    //                 : ($airtickeCountrySetting?->request_limit_per_cycle ?? $airtickeSetting->request_limit_per_cycle ?? 0);

    //             $eligibleDate = $joindate->copy();

    //             while ($eligibleDate->lt($startOfMonth)) {
    //                 $eligibleDate->addMonths($policymonth);
    //             }

    //             // Only consider if upcoming date falls within this month
    //             if ($eligibleDate->between($startOfMonth, $endOfMonth)) {

    //                 // fetch user's air ticket details
    //                 $airTicketDetails = \App\Models\AirTicketDetail::where('user_id', $user->id)
    //                     ->orderBy('created_at', 'desc')
    //                     ->get();

    //                 $allowanceAmount = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0;

    //                 // build details string

    //                 // $totalAmount = $allowanceAmount; // base amount
    //                 // $detailsStr = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
    //                 //     $calculatedAmount = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
    //                 //     $totalAmount += $calculatedAmount; // add details amount to total
    //                 //     return "{$d->title} (Qty-{$d->qty}, Per->{$d->percentage}%, Amount->$calculatedAmount)";
    //                 // })->implode(', ');
    //                 // build details string and calculate total
    //                 $totalAmount = $allowanceAmount; // base amount
    //                 $detailsStr = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
    //                     $calculatedAmount = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
    //                     $totalAmount += $calculatedAmount; // add details amount to total
    //                     return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
    //                 })->implode('<br>'); // use <br> instead of ','

    //                 $upcomingTickets->push((object)[
    //                     "user"        => $user,
    //                     "date"        => $eligibleDate->toDateString(),
    //                     "amount"      => $allowanceAmount,
    //                     "quantity"    => $quantity,
    //                     "details"     => $detailsStr ?: '-',
    //                     "total_amount" => round($totalAmount, 2),  // new total amount column
    //                 ]);
    //             }
    //         }

    //         $upcomingTickets = $upcomingTickets->sortBy('date');

    //         return DataTables::of($upcomingTickets)
    //             ->addIndexColumn()
    //             ->addColumn('name', fn($row) => $row->user->name)
    //             ->addColumn('department', fn($row) => $row->user->department->name ?? '-')
    //             ->addColumn('date', fn($row) => formatDate($row->date, 'birth_date_format'))
    //             ->addColumn('amount', fn($row) => $row->amount)
    //             ->addColumn('quantity', fn($row) => $row->quantity)
    //             ->addColumn('total_amount', fn($row) => $row->total_amount)
    //             ->addColumn('details', fn($row) => $row->details ?: '-') // last column
    //             ->rawColumns(['name', 'department', 'details'])
    //             ->make(true);
    //     }

    //     return view('backend.reports.air_ticket_report');
    // }
    public function air_ticket_report(Request $request)
    {
        if ($request->ajax()) {
            $month        = $request->month ?? date('Y-m'); // format: YYYY-MM
            [$year, $mon] = explode('-', $month);

            $startOfMonth = Carbon::createFromDate($year, $mon, 1)->startOfMonth();
            $endOfMonth   = Carbon::createFromDate($year, $mon, 1)->endOfMonth();
            $today        = Carbon::today();

            $upcomingTickets = collect();

            // eager load department, workDetail, and profile to reduce queries
            $users = User::query()
                 ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
                ->where('status', User::STATUS_ACTIVE)
                ->with(['department', 'workDetail', 'profile'])
                ->get();

            $userIds = $users->pluck('id')->toArray();

            // preload AirTicketDetails for all users
            $detailsGrouped = \App\Models\AirTicketDetail::whereIn('user_id', $userIds)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');

            // preload EMPAirTickets for status lookup
            $existingTickets = \App\Models\EMPAirTicket::whereIn('user_id', $userIds)
                ->get()
                ->groupBy(fn($t) => $t->user_id . '|' . $t->date);

            foreach ($users as $user) {
                $workdetails    = $user->workDetail;
                $profiledetails = $user->profile;

                if (! $workdetails || ! $profiledetails) {
                    continue;
                }

                $joindate         = Carbon::parse($workdetails->joining_date);
                $country          = $profiledetails->country_id;
                $air_ticket_count = $workdetails->air_ticket_count;

                $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
                $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();

                if (! $airtickeSetting && ! $airtickeCountrySetting) {
                    continue;
                }

                // determine policy months
                $policymonth = match ($workdetails->renewal_air_ticket) {
                    '1_year' => 12,
                    '2_year' => 24,
                    default  => $airtickeCountrySetting->request_after_months ?? $airtickeSetting->request_after_months ?? 0,
                };

                if ((int) $policymonth <= 0) {
                    continue;
                }

                $quantity = $air_ticket_count > 0
                    ? $air_ticket_count
                    : ($airtickeCountrySetting?->request_limit_per_cycle ?? $airtickeSetting->request_limit_per_cycle ?? 0);

                $eligibleDate = $joindate->copy();
                while ($eligibleDate->lt($startOfMonth)) {
                    $eligibleDate->addMonths($policymonth);
                }
                if ($joindate->format('Y-m') == $startOfMonth->format('Y-m')) {
                    continue;
                }

                // Only consider if upcoming date falls within this month
                if ($eligibleDate->between($startOfMonth, $endOfMonth)) {

                    // get status if record exists in e_m_p_air_tickets
                    $key            = $user->id . '|' . $eligibleDate->toDateString();
                    $existingTicket = $existingTickets->get($key)?->first();
                    $status         = $existingTicket->status ?? 'Pending';
                    $approval_date  = $existingTicket->approve_date ?? null;
                    $approveDateStr = (! empty($approval_date))
                        ? formatDate($approval_date, 'birth_date_format')
                        : '-';

                    // get details and calculate total
                    $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
                    $allowanceAmount  = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0;

                    $totalAmount = $allowanceAmount; // base
                    $detailsStr  = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
                        $calculatedAmount  = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
                        $totalAmount      += $calculatedAmount;
                        return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
                    })->implode('<br>');

                    $upcomingTickets->push((object) [
                        "user"         => $user,
                        "date"         => $eligibleDate->toDateString(),
                        "amount"       => $allowanceAmount,
                        "quantity"     => $quantity,
                        "details"      => $detailsStr ?: '-',
                        "total_amount" => round($totalAmount, 2),
                        "status"       => $status,
                        "approve_date" => $approveDateStr,
                    ]);
                }
            }

            $upcomingTickets = $upcomingTickets->sortBy('date');

            return DataTables::of($upcomingTickets)
                ->addIndexColumn()
                ->addColumn('name', fn($row) => $row->user->name)
                ->addColumn('department', fn($row) => $row->user->department->name ?? '-')
                ->addColumn('date', fn($row) => formatDate($row->date, 'birth_date_format'))
                ->addColumn('amount', fn($row) => $row->amount)
                ->addColumn('quantity', fn($row) => $row->quantity)
                ->addColumn('total_amount', fn($row) => $row->total_amount)
                ->addColumn('details', fn($row) => $row->details ?: '-')
                ->addColumn('status', function ($row) {
                    $color = match (strtolower($row->status)) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'reject', 'rejected' => 'danger',
                        default    => 'secondary',
                    };
                    return "<span class='badge bg-{$color} text-uppercase'>{$row->status}</span>";
                })
                ->addColumn('approve_date', fn($row) => $row->approve_date)
                ->rawColumns(['name', 'department', 'details', 'status'])
                ->make(true);
        }

        return view('backend.reports.air_ticket_report');
    }

    public function air_ticket_report_export(Request $request, $type)
    {
        $month        = $request->month ?? date('Y-m'); // format: YYYY-MM
        [$year, $mon] = explode('-', $month);

        $startOfMonth = Carbon::createFromDate($year, $mon, 1)->startOfMonth();
        $endOfMonth   = Carbon::createFromDate($year, $mon, 1)->endOfMonth();
        $today        = Carbon::today();

        $upcomingTickets = collect();

        // eager load department, workDetail, and profile to reduce queries
        $users = User::query()
             ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('status', User::STATUS_ACTIVE)
            ->with(['department', 'workDetail', 'profile'])
            ->get();

        $userIds = $users->pluck('id')->toArray();

        // preload AirTicketDetails for all users
        $detailsGrouped = \App\Models\AirTicketDetail::whereIn('user_id', $userIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');

        // preload EMPAirTickets for status lookup
        $existingTickets = \App\Models\EMPAirTicket::whereIn('user_id', $userIds)
            ->get()
            ->groupBy(fn($t) => $t->user_id . '|' . $t->date);

        foreach ($users as $user) {
            $workdetails    = $user->workDetail;
            $profiledetails = $user->profile;

            if (! $workdetails || ! $profiledetails) {
                continue;
            }

            $joindate         = Carbon::parse($workdetails->joining_date);
            $country          = $profiledetails->country_id;
            $air_ticket_count = $workdetails->air_ticket_count;

            $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
            $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();

            if (! $airtickeSetting && ! $airtickeCountrySetting) {
                continue;
            }

            // determine policy months
            $policymonth = match ($workdetails->renewal_air_ticket) {
                '1_year' => 12,
                '2_year' => 24,
                default  => $airtickeCountrySetting->request_after_months ?? $airtickeSetting->request_after_months ?? 0,
            };

            if ((int) $policymonth <= 0) {
                continue;
            }

            $quantity = $air_ticket_count > 0
                ? $air_ticket_count
                : ($airtickeCountrySetting?->request_limit_per_cycle ?? $airtickeSetting->request_limit_per_cycle ?? 0);

            $eligibleDate = $joindate->copy();
            while ($eligibleDate->lt($startOfMonth)) {
                $eligibleDate->addMonths($policymonth);
            }
            if ($joindate->format('Y-m') == $startOfMonth->format('Y-m')) {
                continue;
            }

            // Only consider if upcoming date falls within this month
            if ($eligibleDate->between($startOfMonth, $endOfMonth)) {

                // get status if record exists in e_m_p_air_tickets
                $key            = $user->id . '|' . $eligibleDate->toDateString();
                $existingTicket = $existingTickets->get($key)?->first();
                $status         = $existingTicket->status ?? 'Pending';
                $approval_date  = $existingTicket->approve_date ?? null;
                $approveDateStr = (! empty($approval_date))
                    ? formatDate($approval_date, 'birth_date_format')
                    : '-';

                // get details and calculate total
                $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
                $allowanceAmount  = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0;

                $totalAmount = $allowanceAmount; // base
                $detailsStr  = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
                    $calculatedAmount  = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
                    $totalAmount      += $calculatedAmount;
                    return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
                })->implode('<br>');

                $upcomingTickets->push([
                    "Emp ID"        => $user->employee_id,
                    "Full Name"     => $user->name,
                    "Department"    => $user->department?->name ?? 'NA' ?? '-',
                    "Eligible Date" => $eligibleDate->toDateString(),
                    "Quantity"      => $quantity,
                    "Allowance"     => $allowanceAmount,
                    "Total Amount"  => round($totalAmount, 2),
                    "Details"       => $detailsStr ?: '-',
                    "Status"        => $status,
                    "Approval Date" => $approveDateStr,
                ]);

            }
        }

        // $upcomingTickets = $upcomingTickets->sortBy('date');

        // ✅ EXPORT HANDLING
        if ($type === 'excel') {
            $headers     = array_keys($upcomingTickets->first() ?? []);
            $exportExcel = $upcomingTickets->toArray();
            $export      = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'air_ticket_report.xlsx');
        } else {
            $pdf = Pdf::loadView('backend.reports.air_ticket_report_pdf', [
                'tickets' => $upcomingTickets,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('air_ticket_report.pdf');
        }
    }
    public function air_ticket_report_export_bkp(Request $request, $type)
    {
        dd(1);
        $month        = $request->month ?? date('Y-m');
        [$year, $mon] = explode('-', $month);

        $startOfMonth = Carbon::createFromDate($year, $mon, 1)->startOfMonth();
        $endOfMonth   = Carbon::createFromDate($year, $mon, 1)->endOfMonth();

        $users = User::query()
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
            ->where('status', User::STATUS_ACTIVE)
            ->with(['department', 'workDetail', 'profile'])
            ->get();

        $userIds        = $users->pluck('id')->toArray();
        $detailsGrouped = \App\Models\AirTicketDetail::whereIn('user_id', $userIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');

        $upcomingTickets = collect();

        foreach ($users as $user) {
            $workdetails    = $user->workDetail;
            $profiledetails = $user->profile;
            if (! $workdetails || ! $profiledetails) {
                continue;
            }

            $joindate         = Carbon::parse($workdetails->joining_date);
            $country          = $profiledetails->country_id;
            $air_ticket_count = $workdetails->air_ticket_count;

            $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
            $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();
            if (! $airtickeSetting && ! $airtickeCountrySetting) {
                continue;
            }

            $policymonth = match ($workdetails->renewal_air_ticket) {
                '1_year' => 12,
                '2_year' => 24,
                default  => $airtickeCountrySetting?->request_after_months ?? $airtickeSetting->request_after_months ?? 0
            };

            if ((int) $policymonth <= 0) {
                continue;
            }

            $quantity = $air_ticket_count > 0
                ? $air_ticket_count
                : ($airtickeCountrySetting?->request_limit_per_cycle ?? $airtickeSetting->request_limit_per_cycle ?? 0);

            $eligibleDate = $joindate->copy();
            while ($eligibleDate->lt($startOfMonth));
            $eligibleDate->addMonths($policymonth);

            if ($joindate->format('Y-m') == $startOfMonth->format('Y-m')) {
                continue;
            }

            if ($eligibleDate->between($startOfMonth, $endOfMonth)) {
                $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
                $allowanceAmount  = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0;

                $totalAmount = $allowanceAmount;
                $detailsStr  = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
                    $calculatedAmount  = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
                    $totalAmount      += $calculatedAmount;
                    return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
                })->implode(', ');

                // ✅ Determine status logic (example)
                // If user has ticket record approved in current month, mark Approved; else Pending.
                $latestTicket = \App\Models\EMPAirTicket::where('user_id', $user->id)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->latest()
                    ->first();

                $status         = $latestTicket->status ?? 'Pending'; // default Pending
                $approval_date  = $latestTicket->approve_date ?? null;
                $approveDateStr = (! empty($approval_date))
                    ? formatDate($approval_date, 'birth_date_format')
                    : '-';

                $upcomingTickets->push([
                    "Emp ID"        => $user->employee_id,
                    "Full Name"     => $user->name,
                    "Department"    => $user->department?->name ?? 'NA' ?? '-',
                    "Eligible Date" => $eligibleDate->toDateString(),
                    "Quantity"      => $quantity,
                    "Allowance"     => $allowanceAmount,
                    "Total Amount"  => round($totalAmount, 2),
                    "Details"       => $detailsStr ?: '-',
                    "Status"        => $status,
                    "Approval Date" => $approveDateStr,
                ]);
            }
        }

        // ✅ EXPORT HANDLING
        if ($type === 'excel') {
            $headers     = array_keys($upcomingTickets->first() ?? []);
            $exportExcel = $upcomingTickets->toArray();
            $export      = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'air_ticket_report.xlsx');
        } else {
            $pdf = Pdf::loadView('backend.reports.air_ticket_report_pdf', [
                'tickets' => $upcomingTickets,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('air_ticket_report.pdf');
        }
    }

    public function calculateVacationBalance(User $user, LeaveType $leaveType)
    {
        Log::info("---- Start calculateVacationBalance for User ID: {$user->id} ----");

        $today           = Carbon::now();
        $joiningDate     = Carbon::parse(optional($user->workDetail)->joining_date ?? $today);
        $yearlyAllowance = $leaveType->days ?? 12;
        $isMonthWise     = Setting::where('key', 'is_month_wise_show_leave')->value('value') == 1;

        Log::info("Initial data", [
            'today'            => $today->toDateString(),
            'joining_date'     => $joiningDate->toDateString(),
            'yearly_allowance' => $yearlyAllowance,
            'is_month_wise'    => $isMonthWise,
        ]);

        // Fetch or create leave balance
        $leaveBalance = LeaveBalance::firstOrNew([
            'user_id'       => $user->id,
            'leave_type_id' => $leaveType->id,
            'year'          => $today->year,
        ]);

        if (empty($leaveBalance->initial_balance) || empty($leaveBalance->initial_balance_date)) {
            $leaveBalance->initial_balance      = $leaveBalance->available ?? 0;
            $leaveBalance->initial_balance_date = $today->toDateString();
            $leaveBalance->save();
            Log::info("Initial balance initialized", [
                'initial_balance'      => $leaveBalance->initial_balance,
                'initial_balance_date' => $leaveBalance->initial_balance_date,
            ]);
        }

        $initialBalance = $leaveBalance->initial_balance;
        $initialDate    = Carbon::parse($leaveBalance->initial_balance_date);

        $addedBalance = 0;
        $monthsPassed = 0;
        $yearsPassed  = 0;
        $monthWiseMap = [];

        if ($isMonthWise) {
            Log::info("Calculating month-wise leave credit...");

            $monthlyLeave = round($yearlyAllowance / 12, 2);
            $monthPointer = $initialDate->copy()->startOfMonth();

            while ($monthPointer->lte($today)) {
                $key           = $monthPointer->format('M Y');
                $leaveForMonth = $monthlyLeave;
                $oneYearDate   = $joiningDate->copy()->addYear();

                Log::info("Processing month", [
                    'month'         => $key,
                    'monthly_leave' => $monthlyLeave,
                    'one_year_date' => $oneYearDate->toDateString(),
                ]);
                $leaveForMonth = $monthlyLeave; // default full month leave

                $oneYearDate  = $joiningDate->copy()->addYear();
                $sixMonthDate = $joiningDate->copy()->addMonths(6);

                $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
                $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');

                // ✅ If user has not completed 6 months or 1 year from joining date (based on monthPointer)
                // if (
                //     ($monthPointer->lt($oneYearDate) || $monthPointer->lt($sixMonthDate)) &&
                //     ($yearGiven2Leave == 1 || $monthwise2leave == 1)
                // ) {
                //     $leaveForMonth = 2;
                // }
                $sixMonthDate = $joiningDate->copy()->addMonths(6);
                $oneYearDate  = $joiningDate->copy()->addYear();

                // Check if current month is BEFORE 6-month or 1-year completion
                $isBeforeSixMonth = $monthPointer->lt($sixMonthDate->copy()->startOfMonth());
                $isBeforeOneYear  = $monthPointer->lt($oneYearDate->copy()->startOfMonth());

                if ($monthPointer->lt($oneYearDate) && $yearGiven2Leave == 1 && $isBeforeOneYear) {

                    $leaveForMonth = 2;
                } elseif ($monthPointer->lt($oneYearDate) && $yearGiven2Leave == 1) {
                    $leaveForMonth = 2;
                } elseif ($monthPointer->lt($sixMonthDate) && $monthwise2leave == 1 && $isBeforeSixMonth) {

                    $leaveForMonth = 2;
                } else {
                    $leaveForMonth = $monthlyLeave;
                }

                // $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
                // if ($yearGiven2Leave == 1) {
                //     $leaveForMonth = 2;
                // }
                // $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');
                // if ($monthwise2leave == 1) {
                //     $leaveForMonth = 2;
                // }

                // prorate if user about to complete 1 year

                if ($monthPointer->isSameMonth($oneYearDate->copy())) {

                    $startOfMonth = $monthPointer->copy()->startOfMonth();
                    $endOfMonth   = $monthPointer->copy()->endOfMonth();
                    $startProrate = $monthPointer->copy()->day($joiningDate->day);

                    if ($startProrate->lt($startOfMonth)) {
                        $startProrate = $startOfMonth;
                    }

                    $totalDays  = $endOfMonth->diffInDays($startOfMonth) + 1;
                    $activeDays = $endOfMonth->diffInDays($startProrate) + 1;

                    // $leaveForMonth = round((($activeDays - 1) / 30) * $leaveForMonth, 2);
                    $proleaveForMonth = $leaveForMonth;
                    $leaveForMonth    = round((($activeDays - 1) / 30) * $leaveForMonth + $proleaveForMonth, 2);

                    // dd($monthlyLeave);

                    Log::info("Prorated month", [
                        'start_prorate'   => $startProrate->toDateString(),
                        'end_of_month'    => $endOfMonth->toDateString(),
                        'active_days'     => $activeDays,
                        'leave_for_month' => $leaveForMonth,
                    ]);
                }
                if ($monthPointer->isSameMonth($initialDate) && $initialDate->day > $joiningDate->day) {
                    $leaveForMonth = 0;

                    Log::info("Skipped month as already credited earlier", [
                        'month'        => $key,
                        'initial_date' => $initialDate->toDateString(),
                    ]);
                }

                if (! $monthPointer->isSameMonth($oneYearDate) && $monthPointer->isSameMonth($initialDate) && $initialDate->day < $joiningDate->day && $joiningDate->day > $today->day && $today->month == $joiningDate->month) {
                    $leaveForMonth = 0;

                    Log::info("Skipped month as because credited date is upcoming", [
                        'month'        => $key,
                        'initial_date' => $initialDate->toDateString(),
                    ]);
                }
                $oneMonthCompletion = $joiningDate->copy()->addMonth();

                if ($today->lt($oneMonthCompletion)) {
                    $leaveForMonth = 0;

                    Log::info("Skipped month because 1 month is not completed yet", [
                        'month'           => $key,
                        'joining_date'    => $joiningDate->toDateString(),
                        'completion_date' => $oneMonthCompletion->toDateString(),
                        'today'           => $today->toDateString(),
                    ]);
                }

                // if ($monthPointer->lt($oneMonthCompletion)) {
                //     $leaveForMonth = 0;

                //     Log::info("Skipped month because 1 month is not completed yet", [
                //         'month'            => $key,
                //         'joining_date'     => $joiningDate->toDateString(),
                //         'completion_date'  => $oneMonthCompletion->toDateString(),
                //         'today'            => $today->toDateString(),
                //     ]);
                // }
                $monthPointerEnd = $monthPointer->copy()->endOfMonth();

                if ($joiningDate->isSameMonth($monthPointer) && $joiningDate->copy()->addDays(30)->gt($monthPointerEnd)) {
                    $leaveForMonth = 0;

                    Log::info("Skipped month because joining month has not completed 30 days", [
                        'month'        => $key,
                        'joining_date' => $joiningDate->toDateString(),
                        'month_end'    => $monthPointerEnd->toDateString(),
                        '30_days_date' => $joiningDate->copy()->addDays(30)->toDateString(),
                    ]);
                }
                // NEW CONDITION: Credit date of this month is upcoming (joining day not reached yet)
                if (
                    $today->isSameMonth($monthPointer) && // Same month
                    $joiningDate->day > $today->day       // Joining day is still coming
                ) {
                    $leaveForMonth = 0;

                    Log::info("Skipped month because credited date is upcoming this month", [
                        'month'       => $key,
                        'joining_day' => $joiningDate->day,
                        'today_day'   => $today->day,
                        'today'       => $today->toDateString(),
                    ]);
                }

                if ($monthPointer->lt($joiningDate->copy()->startOfMonth())) {
                    // Entire month is before joining date → leave = 0
                    $leaveForMonth = 0;

                    Log::info("Skipped month before joining date", [
                        'month'        => $monthPointer->format('M Y'),
                        'joining_date' => $joiningDate->toDateString(),
                    ]);
                }

                $monthWiseMap[$key] = $leaveForMonth;

                if ($leaveForMonth > 0) {
                    $addedBalance += $leaveForMonth;
                    $monthsPassed++;
                    Log::info("Leave added for month", [
                        'month'         => $key,
                        'leave_added'   => $leaveForMonth,
                        'months_passed' => $monthsPassed,
                        'added_balance' => $addedBalance,
                    ]);
                }

                $monthPointer->addMonth();
            }

            Log::info("Month-wise calculation complete", [
                'months_passed' => $monthsPassed,
                'added_balance' => $addedBalance,
            ]);
        } else {
            Log::info("Calculating yearly leave credit...");

            $yearsPassed  = $initialDate->diffInYears($today);
            $addedBalance = 0;
            $monthWiseMap = [];

            $monthlyLeave = round($yearlyAllowance / 12, 2);
            $monthPointer = $initialDate->copy()->startOfMonth();
            $oneYearDate  = $joiningDate->copy()->addYear();
            $sixMonthDate = $joiningDate->copy()->addMonths(6);

            $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
            $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');

            Log::info("Yearly mode setup", [
                'joining_date'     => $joiningDate->toDateString(),
                'one_year_date'    => $oneYearDate->toDateString(),
                'monthly_leave'    => $monthlyLeave,
                'yearly_allowance' => $yearlyAllowance,
            ]);
            while ($monthPointer->lte($today)) {
                $key           = $monthPointer->format('M Y');
                $leaveForMonth = 0;

                Log::info("Processing (yearly mode) month", ['month' => $key]);

                // 🟢 CASE 1: Apply same 6-month / 1-year leave rules as in month-wise mode
                if ($monthPointer->isSameMonth($oneYearDate->copy()->subMonth()) && $monthPointer->lt($oneYearDate) && $yearGiven2Leave == 1) {
                    $leaveForMonth = 2;
                } elseif ($monthPointer->lt($oneYearDate) && $yearGiven2Leave == 1) {
                    $leaveForMonth = 2;
                } elseif ($monthPointer->lt($sixMonthDate) && $monthwise2leave == 1) {
                    $leaveForMonth = 2;
                } else {
                    $leaveForMonth = $monthlyLeave; // default unless other conditions below override
                }
                // 🟢 CASE 2: Before completing 1 year → assign monthly leave
                // if ($monthPointer->lt($oneYearDate)) {
                //     $leaveForMonth = max($leaveForMonth, $monthlyLeave);
                //     Log::info("User not completed 1 year — monthly prorated leave applied", [
                //         'month' => $key,
                //         'leave_for_month' => $leaveForMonth,
                //     ]);
                // }

                // 🟢 CASE 3: Prorate final month before 1st anniversary

                $yearsSinceJoining = $monthPointer->copy()->startOfMonth()
                    ->diffInYears($joiningDate->copy()->startOfMonth());

                // anniversary date for that many years
                $anniversary = $joiningDate->copy()->addYears($yearsSinceJoining);

                // credited date = 1st of the month AFTER the anniversary
                $creditedDate = $anniversary->copy()->addMonth()->startOfMonth();

                $anniversaryThisYear = $joiningDate->copy()->year($monthPointer->year);

                if ($monthPointer->isSameMonth($oneYearDate->copy())) {
                    $startOfMonth = $monthPointer->copy()->startOfMonth();
                    $endOfMonth   = $monthPointer->copy()->endOfMonth();
                    $startProrate = $monthPointer->copy()->day($joiningDate->day);

                    if ($startProrate->lt($startOfMonth)) {
                        $startProrate = $startOfMonth;
                    }

                    $totalDays  = $endOfMonth->diffInDays($startOfMonth) + 1;
                    $activeDays = $endOfMonth->diffInDays($startProrate) + 1;

                    $proleaveForMonth = $leaveForMonth;
                    $leaveForMonth    = round((($activeDays - 1) / 30) * $leaveForMonth + $proleaveForMonth, 2);

                    Log::info("Yearly prorated final month before anniversary", [
                        'month'           => $key,
                        'active_days'     => $activeDays,
                        'leave_for_month' => $leaveForMonth,
                    ]);
                }
                // if ($monthPointer->isSameMonth($oneYearDate))
                //     dd($monthPointer->isSameMonth($oneYearDate->copy()->startOfMonth()));

                if ($monthPointer->isSameMonth($creditedDate) && $monthPointer->year === $creditedDate->year && $oneYearDate < $monthPointer) {
                    $leaveForMonth = $yearlyAllowance;
                    Log::info("Anniversary month reached — full yearly leave credited", [
                        'month'           => $key,
                        'leave_for_month' => $leaveForMonth,
                    ]);
                }

                // If credited date is in future → leave not yet credited

                // 🟡 CASE 5: After anniversary month → no further credit until next cycle

                if (! $monthPointer->isSameMonth($creditedDate) && $monthPointer->gt($oneYearDate->copy()->endOfMonth())) {
                    $leaveForMonth = 0;
                    Log::info("After anniversary month — skipping further leave credit", [
                        'month' => $key,
                    ]);
                }

                if ($monthPointer->isSameMonth($initialDate) && $initialDate->day > $joiningDate->day) {
                    $leaveForMonth = 0;
                    Log::info("Skipped month as already credited earlier", [
                        'month'        => $key,
                        'initial_date' => $initialDate->toDateString(),
                    ]);
                }

                if (! $monthPointer->isSameMonth($oneYearDate) && $monthPointer->isSameMonth($initialDate) && $initialDate->day < $joiningDate->day && $joiningDate->day > $today->day && $today->month == $joiningDate->month) {
                    $leaveForMonth = 0;
                    Log::info("Skipped month as because credited date is upcoming", [
                        'month'        => $key,
                        'initial_date' => $initialDate->toDateString(),
                    ]);
                }

                if ($monthPointer->isSameMonth($oneYearDate) && $monthPointer->isSameMonth($initialDate) && $initialDate->day > 1) {
                    $leaveForMonth = 0;
                    Log::info("Skipped Yearly as because credited date is passed", [
                        'month'        => $key,
                        'initial_date' => $initialDate->toDateString(),
                    ]);
                }
                // NEW CONDITION: If current date has not completed 1 full month from joining date
                $oneMonthCompletion = $joiningDate->copy()->addMonth();

                if ($today->lt($oneMonthCompletion)) {
                    $leaveForMonth = 0;

                    Log::info("Skipped month because 1 month is not completed yet", [
                        'month'           => $key,
                        'joining_date'    => $joiningDate->toDateString(),
                        'completion_date' => $oneMonthCompletion->toDateString(),
                        'today'           => $today->toDateString(),
                    ]);
                }
                // dd($monthPointer->toDateString(), $oneMonthCompletion->toDateString());
                // if ($monthPointer->lt($oneMonthCompletion)) {
                //     $leaveForMonth = 0;

                //     Log::info("Skipped month because 1 month is not completed yet", [
                //         'month'            => $key,
                //         'joining_date'     => $joiningDate->toDateString(),
                //         'completion_date'  => $oneMonthCompletion->toDateString(),
                //         'today'            => $today->toDateString(),
                //     ]);
                // }
                $monthPointerEnd = $monthPointer->copy()->endOfMonth();

                if ($joiningDate->isSameMonth($monthPointer) && $joiningDate->copy()->addDays(30)->gt($monthPointerEnd)) {
                    $leaveForMonth = 0;

                    Log::info("Skipped month because joining month has not completed 30 days", [
                        'month'        => $key,
                        'joining_date' => $joiningDate->toDateString(),
                        'month_end'    => $monthPointerEnd->toDateString(),
                        '30_days_date' => $joiningDate->copy()->addDays(30)->toDateString(),
                    ]);
                }

                // NEW CONDITION: Credit date of this month is upcoming (joining day not reached yet)
                if (
                    $today->isSameMonth($monthPointer) && // Same month
                    $joiningDate->day > $today->day       // Joining day is still coming
                ) {
                    $leaveForMonth = 0;

                    Log::info("Skipped month because credited date is upcoming this month", [
                        'month'       => $key,
                        'joining_day' => $joiningDate->day,
                        'today_day'   => $today->day,
                        'today'       => $today->toDateString(),
                    ]);
                }

                if ($monthPointer->lt($joiningDate->copy()->startOfMonth())) {
                    // Entire month is before joining date → leave = 0
                    $leaveForMonth = 0;

                    Log::info("Skipped month before joining date", [
                        'month'        => $monthPointer->format('M Y'),
                        'joining_date' => $joiningDate->toDateString(),
                    ]);
                }

                if ($leaveForMonth > 0) {
                    $addedBalance += $leaveForMonth;
                    $monthsPassed++;
                    Log::info("Leave added for month", [
                        'month'         => $key,
                        'leave_added'   => $leaveForMonth,
                        'months_passed' => $monthsPassed,
                        'added_balance' => $addedBalance,
                    ]);
                }
                $monthWiseMap[$key] = $leaveForMonth;

                $monthPointer->addMonth();
                // if ($key == 'Sep 2025') {
                //     dd($joiningDate->day, $initialDate->toDateString(), $leaveForMonth, $addedBalance);
                // }
            }

            Log::info("Yearly mode calculation complete", [
                'added_balance'    => $addedBalance,
                'yearly_allowance' => $yearlyAllowance,
                'years_passed'     => $yearsPassed,
            ]);
        }

        $usedLeaves = Leave::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '>=', $initialDate->toDateString())
            ->sum('total_leave_days');

        Log::info("Used leaves calculated", [
            'used_leaves'     => $usedLeaves,
            'initial_balance' => $initialBalance,
            'added_balance'   => $addedBalance,
        ]);

        $currentBalance = max(($initialBalance + $addedBalance) - $usedLeaves, 0);

        $leaveBalance->available = $currentBalance;
        $leaveBalance->save();

        Log::info("Final leave balance saved", [
            'current_balance' => $currentBalance,
            'available'       => $leaveBalance->available,
        ]);

        Log::info("---- End calculateVacationBalance for User ID: {$user->id} ----");
        return [
            'employee_name'            => $user->name,
            'department_name'          => optional($user->department)->name ?? '-',
            'initial_balance'          => $initialBalance,
            'initial_balance_date'     => $initialDate->format('d M Y'),
            'initial_balance_date_raw' => $initialDate->toDateString(),
            'added_balance'            => $addedBalance,
            'current_balance'          => $currentBalance,
            'policy_type'              => $isMonthWise ? 'Monthly' : 'Yearly',
            'months_passed'            => $monthsPassed,
            'years_passed'             => $yearsPassed,
            'used_leave'               => $usedLeaves,
            'month_wise_map'           => $monthWiseMap,
        ];
    }

    public function vacation_leave_report(Request $request)
    {
        Log::info("==== Start vacation_leave_report ====");

        if ($request->ajax()) {
            $leaveType = LeaveType::where('name', 'like', '%Vacation%')->first();
            Log::info("Vacation leave type fetched", ['leave_type' => $leaveType?->name]);

            $users = User::query()
                 ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
                ->where('status', User::STATUS_ACTIVE)
                ->with(['department', 'designation', 'workDetail'])
            // ->wherein("id", [11])
                ->get();

            Log::info("Fetched active users", ['count' => $users->count()]);

            $data = [];

            $allMonths = collect();

            foreach ($users as $index => $user) {
                Log::info("Processing user", ['user_id' => $user->id, 'name' => $user->name]);
                $calc      = $this->calculateVacationBalance($user, $leaveType);
                $monthMap  = $calc['month_wise_map'];
                $allMonths = $allMonths->merge(array_keys($monthMap));

                $row = [
                    'DT_RowIndex'          => $index + 1,
                    'employee_id'          => $user->employee_id ?? '-',
                    'employee_name'        => $user->name,
                    'department_name'      => optional($user->department)->name ?? '-',
                    'location'             => optional($user->workDetail)->location ?? '-',
                    'designation'          => optional($user->designation)->name ?? '-',
                    'join_date'            => optional($user->workDetail)->joining_date
                        ? Carbon::parse($user->workDetail?->joining_date)->format('d M Y')
                        : '-',
                    'policy_type'          => $calc['policy_type'],
                    'annual_leave'         => $leaveType->days ?? 0,
                    'initial_balance'      => $calc['initial_balance'],
                    'initial_balance_date' => $calc['initial_balance_date'],
                    'total_leave'          => $calc['added_balance'],
                    'total_month'          => $calc['months_passed'],
                    'used_leave'           => $calc['used_leave'],
                    'balance_leave'        => $calc['current_balance'],
                ];

                foreach ($monthMap as $month => $leaveAdded) {
                    $row[$month] = $leaveAdded;
                }

                $data[] = $row;
            }

            $allMonths = $allMonths->unique()->sort(function ($a, $b) {
                return Carbon::parse("01 $a")->timestamp <=> Carbon::parse("01 $b")->timestamp;
            })->values();
            foreach ($data as &$row) {
                foreach ($allMonths as $month) {
                    if (! isset($row[$month])) {
                        $row[$month] = 0;
                    }
                }
            }

            Log::info("Report generation completed", [
                'users_processed' => count($data),
                'months_found'    => $allMonths->count(),
            ]);

            return response()->json([
                'data'   => $data,
                'months' => $allMonths,
            ]);
        }

        Log::info("Vacation leave report view loaded (non-AJAX request)");
        Log::info("==== End vacation_leave_report ====");

        return view('backend.reports.vacation_leave_report');
    }

    public function vacation_leave_report_export(Request $request, $type)
    {
        Log::info("==== Start vacation_leave_report_export ($type) ====");

        $leaveType = LeaveType::where('name', 'like', '%Vacation%')->first();
        $users     = User::query()
             ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('status', User::STATUS_ACTIVE)
            ->with(['department', 'designation', 'workDetail'])
        // ->where("id", 9)
            ->get();

        $data      = [];
        $allMonths = collect();

        foreach ($users as $index => $user) {
            $calc      = $this->calculateVacationBalance($user, $leaveType);
            $monthMap  = $calc['month_wise_map'];
            $allMonths = $allMonths->merge(array_keys($monthMap));

            $row = [
                'DT_RowIndex'          => $index + 1,
                'employee_id'          => $user->employee_id ?? '-',
                'employee_name'        => $user->name,
                'department_name'      => optional($user->department)->name ?? '-',
                'location'             => optional($user->workDetail)->location ?? '-',
                'designation'          => optional($user->designation)->name ?? '-',
                'join_date'            => optional($user->workDetail)->joining_date
                    ? Carbon::parse($user->workDetail?->joining_date)->format('d M Y')
                    : '-',
                'policy_type'          => $calc['policy_type'],
                'annual_leave'         => $leaveType->days ?? 0,
                'initial_balance'      => $calc['initial_balance'],
                'initial_balance_date' => $calc['initial_balance_date'],
                'total_leave'          => $calc['added_balance'],
                'total_month'          => $calc['months_passed'],
                'used_leave'           => $calc['used_leave'],
                'balance_leave'        => $calc['current_balance'],
            ];

            foreach ($monthMap as $month => $leaveAdded) {
                $row[$month] = $leaveAdded;
            }

            $data[] = $row;
        }

        $allMonths = $allMonths->unique()->sort(function ($a, $b) {
            return Carbon::parse("01 $a")->timestamp <=> Carbon::parse("01 $b")->timestamp;
        })->values();
        foreach ($data as &$row) {
            foreach ($allMonths as $month) {
                if (! isset($row[$month])) {
                    $row[$month] = 0;
                }
            }
        }

        if ($type === 'excel') {
            return Excel::download(new VacationLeaveReportExport($data, $allMonths), 'vacation_leave_report.xlsx');
        }

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('backend.reports.vacation_leave_excel', [
                'data'   => $data,
                'months' => $allMonths,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('vacation_leave_report.pdf');
        }

        abort(404, 'Invalid export type');
    }
    // public function initialBalanceReport(Request $request)
    // {
    //     $users = User::with(['department', 'designation', 'workDetail'])->get();
    //     $leaveTypes = LeaveType::all();
    //     $data = [];
    //     if ($request->ajax()) {

    //         foreach ($users as $index => $user) {
    //             $row = [
    //                 'DT_RowIndex' => $index + 1,
    //                 'employee_id' => $user->employee_id ?? '',
    //                 'employee_name' => $user->name ?? '',
    //                 'department_name' => optional($user->department)->name ?? '',
    //                 'designation' => optional($user->designation)->title ?? '',
    //                 'join_date' => optional($user->workDetail)->joining_date ? \Carbon\Carbon::parse($user->workDetail?->joining_date)->format('d M Y') : '-',
    //                 'initial_balance_date' => optional($user->leaveBalances()->first())->initial_balance_date
    //                     ? \Carbon\Carbon::parse($user->leaveBalances()->first()->initial_balance_date)->format('d M Y')
    //                     : '-',
    //             ];

    //             // Add each leave type as a column
    //             foreach ($leaveTypes as $type) {
    //                 $balance = $user->leaveBalances()
    //                     ->where('leave_type_id', $type->id)
    //                     ->value('initial_balance') ?? 0;

    //                 $row[$type->name] = round($balance, 2);
    //             }

    //             $data[] = $row;
    //         }
    //     }

    //     // return response()->json([
    //     //     'data' => $data,
    //     //     'leave_types' => $leaveTypes->pluck('name')->toArray()
    //     // ]);
    //     return view('backend.reports.initial_balance_report', compact('leaveTypes', 'data'));
    // }
    public function initialBalanceReport()
    {
        // ✅ This just loads the Blade view (no data yet)
        $leaveTypes = LeaveType::pluck('name')->toArray();
        return view('backend.reports.initial_balance_report', compact('leaveTypes'));
    }

    public function getInitialBalanceReportData(Request $request)
    {
        $users = User::with(['department', 'designation', 'workDetail', 'leaveBalances'])
            ->notAdmin()
        // ->where("id", 11)
            ->get();
        $leaveTypes = LeaveType::all();
        $data       = [];

        foreach ($users as $index => $user) {
            $row = [
                'DT_RowIndex'          => $index + 1,
                'employee_id'          => $user->employee_id ?? '',
                'employee_name'        => $user->name ?? '',
                'department_name'      => optional($user->department)->name ?? '',
                'designation'          => optional($user->designation)->title ?? '',
                'join_date'            => optional($user->workDetail)->joining_date
                    ? Carbon::parse($user->workDetail?->joining_date)->format('d M Y')
                    : '-',
                'initial_balance_date' => optional($user->leaveBalances->first())->initial_balance_date
                    ? Carbon::parse($user->leaveBalances->first()->initial_balance_date)->format('d M Y')
                    : '-',
            ];

            // Add each leave type as a column
            foreach ($leaveTypes as $type) {
                $balance = $user->leaveBalances
                    ->where('leave_type_id', $type->id)
                    ->pluck('initial_balance')
                    ->first() ?? 0;
                $row[$type->name] = round($balance, 2);
            }

            $data[] = $row;
        }

        return response()->json([
            'data'        => $data,
            'leave_types' => $leaveTypes->pluck('name')->toArray(),
        ]);
    }
    public function exportInitialBalance()
    {
        $users = User::with(['department', 'designation', 'workDetail', 'leaveBalances'])
        // ->where("id", 11)
            ->notAdmin()->get();
        $leaveTypes = LeaveType::all();

        // Header
        $headers = ['Employee ID', 'Employee Name', 'Department', 'Designation', 'Join Date', 'Initial Balance Date'];
        foreach ($leaveTypes as $type) {
            $headers[] = $type->name;
        }

        // Build rows
        $rows = [];
        foreach ($users as $user) {
            $row = [
                $user->employee_id,
                $user->name,
                optional($user->department)->name,
                optional($user->designation)->title,
                optional($user->workDetail)->joining_date ? Carbon::parse($user->workDetail?->joining_date)->format('d M Y') : '-',
                optional($user->leaveBalances()->first())->initial_balance_date
                    ? Carbon::parse($user->leaveBalances()->first()->initial_balance_date)->format('d M Y')
                    : '-',
            ];

            foreach ($leaveTypes as $type) {
                $row[] = $user->leaveBalances()->where('leave_type_id', $type->id)->value('initial_balance') ?? 0;
            }

            $rows[] = $row;
        }

        // Create CSV file
        $filename = 'initial_leave_balance_' . now()->format('Y_m_d_His') . '.csv';
        $filepath = storage_path('app/public/' . $filename);

        $file = fopen($filepath, 'w');
        fputcsv($file, $headers);
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }
    public function importInitialBalance(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $response = getErrorResponse();

        try {
            $file   = fopen($request->file('file')->getRealPath(), 'r');
            $header = fgetcsv($file);

            $leaveTypes = LeaveType::pluck('id', 'name')->toArray();
            $failedRows = [];

            while (($row = fgetcsv($file)) !== false) {
                $data = @array_combine($header, $row);
                if (! $data) {
                    continue;
                }

                $employeeId = trim($data['Employee ID'] ?? '');
                if (! $employeeId) {
                    continue;
                }

                $user = User::where('employee_id', $employeeId)->first();
                if (! $user) {
                    $failedRows[] = array_merge($data, ['Error' => 'Employee not found']);
                    continue;
                }

                // ✅ Read date from CSV or use current date
                $initialBalanceDate = null;
                if (! empty($data['Initial Balance Date'])) {
                    try {
                        $initialBalanceDate = \Carbon\Carbon::parse($data['Initial Balance Date'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $initialBalanceDate = now()->format('Y-m-d');
                        $failedRows[]       = array_merge($data, ['Error' => 'Invalid date format, used current date']);
                    }
                } else {
                    $initialBalanceDate = now()->format('Y-m-d');
                }

                foreach ($leaveTypes as $typeName => $typeId) {
                    if (! isset($data[$typeName])) {
                        continue;
                    }

                    try {
                        LeaveBalance::updateOrCreate(
                            [
                                'user_id'       => $user->id,
                                'leave_type_id' => $typeId,
                            ],
                            [
                                'initial_balance'      => (float) $data[$typeName],
                                'initial_balance_date' => $initialBalanceDate, // ✅ from CSV
                            ]
                        );
                    } catch (\Exception $e) {
                        $failedRows[] = array_merge($data, ['Error' => $e->getMessage()]);
                    }
                }
            }

            fclose($file);

            // ✅ If failed rows exist — create downloadable report
            if (! empty($failedRows)) {
                $filePath = 'uploads/failedexport/initial_balance_import_failed.csv';
                if (file_exists(public_path($filePath))) {
                    unlink(public_path($filePath));
                }

                $handle = fopen(public_path($filePath), 'w');
                fputcsv($handle, array_keys($failedRows[0]));
                foreach ($failedRows as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);

                $response                 = getSuccessResponse(createFlashMessage('File', 'imported partially'));
                $response['download_url'] = asset($filePath);
            } else {
                $response = getSuccessResponse(createFlashMessage('File', 'imported successfully'));
            }
        } catch (\Exception $e) {
            \Log::error('Initial Balance Import Error: ' . $e->getMessage());
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
