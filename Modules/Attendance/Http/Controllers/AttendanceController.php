<?php
namespace Modules\Attendance\Http\Controllers;

use App\Exports\AttendanceMonthSampleExport;
use App\Exports\ExcelExport;
use App\Exports\UserAttendanceSampleExport;
use App\Exports\VisitReportExport;
use App\Imports\AttendanceImport;
use App\Imports\UserAttendanceImport;
use App\Models\Department;
use App\Models\extraWork;
use App\Models\extraWorkRequest;
use App\Models\lateCome;
use App\Models\PHLeaveReport;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeaveBalanceTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Exports\Attendance as AttendanceExport;
use Modules\Attendance\Exports\ExtraWorkHoursExport;
use Modules\Attendance\Http\Requests\StoreUpdateAttendanceRequest;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Payroll\Entities\UserDeduction;
// use Mpdf\Mpdf;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserPaySlip;
use Modules\Payroll\Traits\SalaryCalculation;
use Modules\Shift\Entities\UsersShift;
use Yajra\DataTables\DataTables;

class AttendanceController extends Controller
{
    use SalaryCalculation;

    /**
     * Display a listing of the attendance of logged in user.
     */
    public function index(Request $request)
    {
        canPerform('Manage Attendance');
        view()->share('activeLink', 'marked-attendances');
        $year  = $request->year ? $request->year : date('Y');
        $month = $request->month ? $request->month : date('m');

        $perPage = $request->input('per_page', 10);
        $users   = User::query()->where('status', User::STATUS_ACTIVE)->notAdmin()
            ->with('attendances', function ($query) use ($month, $year, $request) {
                // if($request->year || $request->month){
                $query->whereMonth('date', $month)->whereYear('date', $year);
                // }
                // if($request->start_date || $request->end_date){
                //     $query->whereBetween('date', [$request->start_date, $request->end_date]);
                // }
            })
            ->when($request->employee, function ($query) use ($request) {
                $query->whereIn('id', $request->employee);
            })
            ->when($request->department, function ($query) use ($request) {
                $query->where('department_id', $request->department);
            })
            ->paginate($perPage);
        $filterEmployees  = User::whereIn('id', $request->employee ?? [])->get();
        $filterDepartment = Department::where('id', $request->department)->first();

        return view('attendance::attendance.index', compact('users', 'month', 'year', 'filterEmployees', 'filterDepartment'));
    }

    /**
     * Show edit form for the user attendance for particular date
     */
    public function getUserDayAttendance(User $user, $date): JsonResponse
    {
        $attendance = Attendance::firstOrNew(
            [
                'user_id' => $user->id,
                'date'    => $date,
            ]
        );

        $first_clock_in = DB::table('attendances')
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->orderBy('clock_in', 'asc')
            ->first();

        // Fetch the last row (latest clock_out, even if it's NULL)
        $last_clock_out = DB::table('attendances')
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->orderBy('clock_in', 'desc')
            ->first();

        $attendance->load('user');
        $attendanceStatuses = AttendanceStatus::cases();
        $html               = view('attendance::attendance.edit', compact('attendance', 'attendanceStatuses', 'first_clock_in', 'last_clock_out'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    /**
     * update the user attendance record for particular date
     */
    public function updateUserDayAttendance(User $user, $date, StoreUpdateAttendanceRequest $request): JsonResponse
    {
        $attendance = Attendance::firstOrNew(
            [
                'user_id' => $user->id,
                'date'    => $date,
            ]
        );

        if ($attendance->clock_in && $request->clock_in && ($request->clock_in != $attendance->clock_in)) {
            $checkin = \Modules\Attendance\Entities\Checkin::where([
                'user_id' => $user->id,
                'date'    => $attendance->date,
                'time'    => $attendance->clock_in,
                'type'    => 'in',
            ])->first();

            if ($checkin) {
                $checkin->update([
                    'time' => $request->clock_in,
                ]);
            }
        }

        // Update or create LAST clock-out entry
        if ($attendance->clock_out && $request->clock_out && ($request->clock_out != $attendance->clock_out)) {
            $checkout = \Modules\Attendance\Entities\Checkin::where([
                'user_id' => $user->id,
                'date'    => $attendance->clockout_date,
                'time'    => $attendance->clock_out,
                'type'    => 'out',
            ])->first();

            if ($checkout) {
                $checkout->update([
                    'time' => $request->clock_out,
                ]);
            }
        }
        $from = Carbon::parse(($date . " " . $request->clock_in));
        $to   = Carbon::parse(($request->clockout_date . " " . $request->clock_out));

        $attendance->clock_in      = $request->clock_in;
        $attendance->clock_out     = $request->clock_out;
        $attendance->status        = $request->status;
        $attendance->remark        = $request->remark;
        $attendance->created_by_id = auth()->id();
        $attendance->clockout_date = $request->clockout_date;
        $attendance->total_worked  = $to->diffInMinutes($from);
        $attendance->save();

        // extra hours calculate
        $attendDate   = Carbon::parse($date);
        $clockinTime  = Carbon::parse($attendDate->format('Y-m-d') . ' ' . $request->clock_in);
        $clockoutTime = Carbon::parse($attendDate->format('Y-m-d') . ' ' . $request->clock_out);
        if ($clockoutTime->lessThan($clockinTime)) {
            $clockoutTime->addDay();
        }
        $totalMinutes       = $clockinTime->diffInMinutes($clockoutTime);
        $shift_time         = [];
        $user_shifts        = [];
        $totalShiftMinuts   = 0;
        $total_worked_hours = 0;
        $extra_hours        = 0;
        if ($attendance) {
            $hours              = intdiv($totalMinutes, 60);
            $minutes            = $totalMinutes % 60;
            $total_worked_hours = $hours . '.' . $minutes;

            $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
            $user_shifts  = User::find($user->id)
                ->assigned_shifts()
                ->with('shift_schedule_information')
                ->where('assigned_for_date', $date)
                ->get();

            foreach ($user_shifts as $index => $shiftData) {
                $shift = $shiftData->shift_schedule_information;
                // Convert shift start and end times to Carbon instances
                // $shiftStart = Carbon::parse($shift->shift_start);
                // $shiftEnd = Carbon::parse($shift->shift_end);
                $shiftDate  = Carbon::parse($shiftData->assigned_for_date);
                $shiftStart = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $shift->shift_start);
                $shiftEnd   = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $shift->shift_end);
                // Calculate the hours between shift start and end
                if ($shiftEnd->lessThan($shiftStart)) {
                    $shiftEnd->addDay();
                }
                $hoursDifference   = $shiftStart->diffInMinutes($shiftEnd);
                $totalShiftMinuts += $hoursDifference;
            }
            if (count($user_shifts) != 0 && $totalShiftMinuts != 0) {
                $sshours         = intdiv($totalShiftMinuts, 60);
                $ssminutes       = $totalShiftMinuts % 60;
                $totalShiftHours = $sshours . '.' . $ssminutes;
                if ($clockoutTime->greaterThan($shiftEnd)) {
                    if ($total_worked_hours > $totalShiftHours) {
                        $totalShiftHours     = sprintf('%02d:%02d', $sshours, $ssminutes);
                        $total_worked_hours  = sprintf('%02d:%02d', $hours, $minutes);
                        $extrahours          = $shiftEnd->diffInMinutes($clockoutTime); //Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon::createFromTimeString($totalShiftHours));
                        $extra_hours        += $extrahours;
                    }
                }
            } else {
                if ($company_hour > 0) {
                    if ($total_worked_hours > $company_hour) {
                        $total_worked_hours  = sprintf('%02d:%02d', $hours, $minutes);
                        $extrahours          = Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon::createFromTimeString($company_hour));
                        $extra_hours        += $extrahours;
                    }
                }
            }
        }
        // end
        $overtime_hours       = Setting::where("key", "overtime_hours")->first();
        $overtime_hours_limit = 00;
        if ($overtime_hours) {
            $overtime_hours_limit = $overtime_hours->value;
        }
        if ($extra_hours >= $overtime_hours_limit) {
            $year               = Carbon::parse($attendance->clockout_date)->format('Y');
            $month              = Carbon::parse($attendance->clockout_date)->format('m');
            $extra_hourshours   = intdiv($extra_hours, 60);
            $extra_hoursminutes = $extra_hours % 60;
            $extra_hours        = $extra_hourshours . '.' . $extra_hoursminutes;
            $thismonthadd       = extraWorkRequest::where([
                'user_id' => $user->id,
                'date'    => $attendance->clockout_date,
            ])
                ->first();
            if ($thismonthadd) {
                $thismonthadd->update([
                    'user_id'     => $user->id,
                    'added_by'    => auth()->id(),
                    'extra_hours' => $extra_hours,
                    'hours'       => $extra_hourshours,
                    'minit'       => $extra_hoursminutes,
                    'month'       => $month,
                    'year'        => $year,
                    'status'      => 0,
                    'date'        => $attendance->clockout_date,
                ]);
            } else {
                $add = extraWorkRequest::create([
                    'user_id'     => $user->id,
                    'added_by'    => auth()->id(),
                    'extra_hours' => $extra_hours,
                    'hours'       => $extra_hourshours,
                    'minit'       => $extra_hoursminutes,
                    'month'       => $month,
                    'year'        => $year,
                    'status'      => 0,
                    'date'        => $attendance->clockout_date,
                ]);
            }
        }
        // end
        // add cancel off leave
        $checkcanceloffleave = Setting::where('key', 'cancel_off_leave_module')->value('value');
        if ($checkcanceloffleave == true) {
            $usershift = UsersShift::where('user_id', $user->id)
                ->whereDate('assigned_for_date', $date)
                ->whereHas('shift_schedule_information.shift', function ($q) {
                    $q->where('is_weekend', 1);
                })
                ->with(['shift_schedule_information.shift'])
                ->first();
            if ($usershift) {
                $shiftdata = $usershift->shift_schedule_information->shift ?? null;
                if ($shiftdata && $shiftdata->is_weekend == 1) {
                    $isCheckin = Attendance::where('user_id', $user->id)
                        ->whereIn('status', [
                            AttendanceStatus::Present,
                            AttendanceStatus::Late,
                            AttendanceStatus::EarlyOut,
                            AttendanceStatus::Weekend,
                        ])
                        ->whereDate('date', $date)
                        ->latest()
                        ->first();
                    if ($isCheckin) {
                        $cankeywords        = ['CANCEL OFF', 'cancel off', 'canceloff'];
                        $is_canceloff_leave = LeaveType::where(function ($query) use ($cankeywords) {
                            foreach ($cankeywords as $cankeyword) {
                                $query->orWhere('name', 'like', "%$cankeyword%");
                            }
                        })->first();
                        // $is_cancel_off = LeaveBalance::where([['year', Carbon::now()->year], ['user_id', $user->id], ['leave_type_id', $is_canceloff_leave->id]])->first();
                        $is_cancel_off = LeaveBalance::firstOrCreate(
                            [
                                'year'          => Carbon::now()->year,
                                'user_id'       => $user->id,
                                'leave_type_id' => $is_canceloff_leave->id,
                            ],
                            [
                                'available'    => 0,
                                'monthwiseDay' => 0,
                            ]
                        );
                        if ($is_canceloff_leave) {
                            if (($isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) || ($isCheckin->visit_in != null && $isCheckin->visit_in != '00:00:00')) {
                                if ($is_cancel_off) {
                                    $isaddransaction = UserLeaveBalanceTransaction::where([
                                        'user_id'          => $user->id,
                                        'transaction_date' => $date,
                                        'leave_type_id'    => $is_canceloff_leave->id,
                                    ])->first();
                                    if (! $isaddransaction) {
                                        $addtransaction = UserLeaveBalanceTransaction::create([
                                            'user_id'          => $user->id,
                                            'leave_type_id'    => $is_canceloff_leave->id,
                                            'transaction_type' => 'add',
                                            'old_balance'      => $is_cancel_off->available,
                                            'update_balance'   => 1,
                                            'new_balance'      => ($is_cancel_off->available + 1),
                                            'transaction_date' => $date,
                                            'description'      => 'Add CANCEL OFF Leave From Manual CheckIn: ' . $is_canceloff_leave->name,
                                        ]);
                                        $is_cancel_off->update([
                                            'available'    => $is_cancel_off->available + 1,
                                            'monthwiseDay' => $is_cancel_off->monthwiseDay + 1,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $date    = Carbon::now()->toDateString();
        $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
        
        if ($holiday) {
            $is_phleave = LeaveType::where('name', 'like', '%PH%')->first();
            if (! $is_phleave) {
                $is_phleave = LeaveType::create([
                    'name'         => 'PH',
                    'days'         => 0,
                    'no_of_leaves' => 0,
                    'is_paid'      => 0,
                    'is_recurring' => 0,
                    'type'         => 'working',
                ]);
            }
            if ($is_phleave) {
                $isleaveBL = LeaveBalance::where([['year', Carbon::now()->year], ['user_id', $user->id], ['leave_type_id', $is_phleave->id]])->first();


                if ($isleaveBL) {
                    $settingadd = Setting::where('key', 'is_given_without_attend_PH')->first();
                    $isCheckin  = Attendance::where('user_id', $user->id)
                        ->whereIn('status', [
                            AttendanceStatus::Present,
                            AttendanceStatus::Late,
                            AttendanceStatus::EarlyOut,
                        ])
                        ->whereDate('date', $date)
                        ->latest()
                        ->first();
                    if ($isCheckin && $isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) {
                        $checkindata = 1;
                    } else {
                        $checkindata = 0;
                    }
                    if ($settingadd && $settingadd->value == 1) {
                        $checkindata = 1;
                    }
                    if ($checkindata == 1) {

                        $isaddinreport = PHLeaveReport::where([
                            'user_id' => $user->id,
                            'date'    => $date,
                        ])->first();
                        if (! $isaddinreport) {
                            $addinreport = PHLeaveReport::create([
                                'user_id'       => $user->id,
                                'holiday_id'    => $holiday->id,
                                'leave_type_id' => $is_phleave->id,
                                'date'          => $date,
                            ]);
                            $addPHtransaction = UserLeaveBalanceTransaction::create([
                                'user_id'          => $user->id,
                                'leave_type_id'    => $is_phleave->id,
                                'transaction_type' => 'add',
                                'old_balance'      => $isleaveBL->available,
                                'update_balance'   => 1,
                                'new_balance'      => ($isleaveBL->available + 1),
                                'transaction_date' => $date,
                                'description'      => 'Check in time, Add 1 PH Leave for holiday: ' . $holiday->detail,
                            ]);
                            $isleaveBL->update([
                                'available'    => $isleaveBL->available + 1,
                                'monthwiseDay' => $isleaveBL->monthwiseDay + 1,
                            ]);
                        }
                    }
                }
            }
        }

        // end
        $date      = now()->parse($date);
        $month     = $date->month;
        $year      = $date->year;
        $monthDays = $date->daysInMonth;
        $user      = User::where('id', $user->id)
            ->with('attendances', function ($query) use ($month, $year) {
                $query->whereMonth('date', $month)->whereYear('date', $year);
            })
            ->first();
        $html = view('attendance::attendance.partials.user-attendance-row', compact('user', 'month', 'year', 'monthDays'))->render();
        return response()->json([
            'success' => true,
            'message' => __trans('attendance_record_saved_successfully'),
            'html'    => $html,
        ]);
    }

    public function getBulkUserAttendance(Request $request)
    {

        $html = view('attendance::attendance.bulk-attendance')->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function sampleDownload(Request $request)
    {

        $clientName = getSetting('site_title');
        $safeName   = strtolower(str_replace(' ', '_', $clientName));

        $startDate = $request->start_date ?? date('Y-m-d');
        $endDate   = $request->end_date ?? date('Y-m-d');
        $user_ids  = $request->user_ids ?? ["0"];

        $users = User::query()->notAdmin()->where('status', User::STATUS_ACTIVE)->select('id', 'name', 'employee_id')
            ->when(! (count($user_ids) === 1 && $user_ids[0] === "0"), function ($query) use ($user_ids) {
                // Only apply whereIn if the array is not ["0"]
                $query->whereIn('id', $user_ids);
            })
            ->get();
        $attendanceStatuses = AttendanceStatus::cases();

        $fileName = $safeName . '_users_bulk_attendance_' . time() . '.xlsx';

        return Excel::download(new UserAttendanceSampleExport($startDate, $endDate, $users, $attendanceStatuses), $fileName);
    }

    public function updateBulkUserAttendance(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import users'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new UserAttendanceImport();
            $import->import($request->file);

            $failedRows = $import->getFailedRows(); //this will return failed rows

            if (! empty($failedRows)) {
                $filePath = 'uploads/failedexport/employee_update_import_failed.xlsx';
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                try {
                    Excel::store(new FailedRowsUpdateExport($failedRows), $filePath, 'real_public');
                } catch (\Exception $e) {
                    \Log::error('Error storing Excel file: ' . $e->getMessage());
                    print_r($e->getMessage());
                    die();
                }
                $response                 = getSuccessResponse(createFlashMessage('File', 'imported partially'));
                $response['download_url'] = asset($filePath);
            } else {
                $response = getSuccessResponse(createFlashMessage('File', 'imported'));
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
    /**
     * Generate current date attendance of the employees
     */
    public function generate(): JsonResponse
    {
        Artisan::call("attendance:generate");

        return response()->json([
            'success'  => true,
            'message'  => createFlashMessage('Attendance', 'generated'),
            'redirect' => route('backend.attendances.index'),
        ]);
    }

    public function userattendancehistory($user, Request $request)
    {
        view()->share('activeLink', 'marked-attendances');
        if ($request->ajax()) {
                                                // $data = Checkin::where(['user_id' => $user, 'is_auto_update' => 0])->orderBy('id','desc')->get();
                                                // $data = Checkin::where('user_id', $user)->orderBy('id', 'desc')->get();
                                                // Fetch Checkins
                                                // $checkins = Checkin::where('user_id', $user)
                                                //     ->orderBy('id', 'desc')
                                                //     ->select('id', 'user_id', 'date', 'time', 'type', 'is_auto_update', 'checkout_reason', 'latitude', 'longitude', \DB::raw("'checkin' as source"))
                                                //     ->get();
            $checkins = Checkin::with('branch') // eager load branch
                ->where('user_id', $user)
                ->orderBy('id', 'desc')
                ->select(
                    'id',
                    'user_id',
                    'date',
                    'time',
                    'type',
                    'is_auto_update',
                    'checkout_reason',
                    'latitude',
                    'longitude',
                    'branch_id',
                    \DB::raw("'checkin' as source")
                )
                ->get();

            // Fetch Breakins (apply same order)
            $breakins = Breakin::where('user_id', $user)
                ->orderBy('id', 'desc')
                ->select(
                    'id',
                    'user_id',
                    'date',
                    'time',
                    'type',
                    \DB::raw("0 as is_auto_update"),
                    \DB::raw("'' as checkout_reason"),
                    \DB::raw("'' as latitude"),
                    \DB::raw("'' as longitude"),
                    \DB::raw("NULL as branch_id"),
                    \DB::raw("'breakin' as source")
                )
                ->get();

            // Merge & sort by id DESC to match your request
            // $data = $checkins->concat($breakins)
            //     ->sortByDesc('id')
            //     ->values();
            $data = $checkins->concat($breakins)
                ->sortByDesc(function ($item) {
                    return strtotime($item->date . ' ' . $item->time);
                })
                ->values();

            return DataTables::of($data)
                ->editColumn('id', function ($payslip) {
                    $user = User::where('id', $payslip->user_id)->first();
                    $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
                    return $html;
                })
                ->editColumn('date', function ($payslip) {
                    // $user = User::where('id',$payslip->user_id)->first();
                    return $payslip->date;
                })
                ->addColumn('time', function ($payslip) {
                    //$user = User::with('department')->where('id',$payslip->user_id)->first();
                    return $payslip->time;
                })
                ->addColumn('mode', function ($payslip) {
                    $type = '';
                    if ($payslip->is_auto_update == '1') {
                        $type = 'System';
                    } else {
                        $type = 'Manual';
                    }
                    return $type;
                })
                ->addColumn('checkout_reason', function ($payslip) {
                    $reason = '';
                    $reason = $payslip->checkout_reason;
                    return $reason;
                })
                ->editColumn('type', function ($payslip) {
                    // $user = User::with('salary')->where('id',$payslip->user_id)->first();
                    // $result = $user->salary->basic;
                    if ($payslip->source == 'checkin' && $payslip->type == 'out') {
                        return "Check Out";
                    } else if ($payslip->source == 'checkin' && $payslip->type == 'in') {
                        return "Check In";
                    } elseif ($payslip->source == 'breakin' && $payslip->type == 'out') {
                        return "Break Out";
                    } else if ($payslip->source == 'breakin' && $payslip->type == 'in') {
                        return "Break In";
                    }
                    //return $payslip->type;
                })
                ->editColumn('is_rider', function ($payslip) {
                    $user = User::with('workdetail')->where('id', $payslip->user_id)->first();
                    // $result = $user->workdetail->basic;
                    if ($user->workdetail->is_rider) {
                        return "Yes";
                    } else {
                        return "No";
                    }
                    return $workdetail->is_rider;
                })
                ->editColumn('location', function ($payslip) {

                    if ($payslip->latitude && $payslip->longitude) {

                        return $payslip->location;
                    }
                })
                ->addColumn('branch', function ($payslip) {
                    return $payslip->branch?->name ?? 'N/A';
                })
            // ->editColumn('latitude', function ($payslip) {
            //     return $payslip->latitude;
            // })
            // ->editColumn('longitude', function ($payslip) {
            //     return $payslip->longitude;
            // })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
        // if ($request->post() && $request->button = "export") {
        //     // $userdata = Checkin::where('user_id', $user)->orderBy('id', 'desc')->get();
        //     $userdata = Checkin::with('branch')
        //         ->where('user_id', $user)
        //         ->orderBy('id', 'desc')
        //         ->get();

        //     $exportExcel = array();
        //     $headers = [
        //         __trans('#'),
        //         __trans('date'),
        //         __trans('time'),
        //         __trans('type'),
        //         __trans('mode'),
        //         __trans('checkout_reason'),
        //         __trans('is_rider'),
        //         __trans('location'),
        //         __trans('branch'),
        //     ];
        //     foreach ($userdata as $i => $data) {
        //         $user = User::where('id', $data->user_id)->first();
        //         $exportExcel[$i]['#'] = $user->employee_id;
        //         $exportExcel[$i]['date'] = $data->date;
        //         $exportExcel[$i]['time'] = $data->time;
        //         $exportExcel[$i]['type'] = $data->type == 'out' ? 'Check Out' : 'Check In';
        //         $exportExcel[$i]['mode'] = $data->is_auto_update == '1' ? 'System' : 'Manual';
        //         $exportExcel[$i]['checkout_reason'] = $data->checkout_reason;
        //         $user_work = User::with('workdetail')->where('id', $data->user_id)->first();
        //         $exportExcel[$i]['is_rider'] = $user->workdetail->is_rider == "1" ? "Yes" : "No";
        //         $exportExcel[$i]['location'] = $data->location;
        //         $exportExcel[$i]['branch'] = $data->branch?->name ?? 'N/A';
        //     }
        //     // dd($exportExcel);
        //     $export = new ExcelExport($exportExcel, $headers);
        //     return Excel::download($export, 'attenadance_history_report_' . $user->employee_id . '.xlsx');
        //     dd($data);
        // }
        // dd($request->post());
        if ($request->post()) {

                                                // Fetch Checkins
            $checkins = Checkin::with('branch') // eager load branch
                ->where('user_id', $user)
                ->orderBy('id', 'desc')
                ->select(
                    'id',
                    'user_id',
                    'date',
                    'time',
                    'type',
                    'is_auto_update',
                    'checkout_reason',
                    'latitude',
                    'longitude',
                    'branch_id',
                    \DB::raw("'checkin' as source")
                )
                ->get();

            // Fetch Breakins
            $breakins = Breakin::where('user_id', $user)
                ->orderBy('id', 'desc')
                ->select(
                    'id',
                    'user_id',
                    'date',
                    'time',
                    'type',
                    \DB::raw("0 as is_auto_update"),
                    \DB::raw("'' as checkout_reason"),
                    \DB::raw("'' as latitude"),
                    \DB::raw("'' as longitude"),
                    \DB::raw("NULL as branch_id"),
                    \DB::raw("'breakin' as source")
                )
                ->get();

            // Merge both
            $records = $checkins->concat($breakins)
                ->sortByDesc(function ($item) {
                    return strtotime($item->date . ' ' . $item->time);
                })
                ->values();

            $exportExcel = [];
            $headers     = [
                __trans('#'),
                __trans('date'),
                __trans('time'),
                __trans('type'),
                __trans('mode'),
                __trans('checkout_reason'),
                __trans('is_rider'),
                __trans('location'),
                __trans('branch'),
            ];

            foreach ($records as $i => $record) {
                $employee = User::withoutGlobalScopes()->with('workdetail')->find($record->user_id);

                $exportExcel[$i]['#']               = $employee->employee_id;
                $exportExcel[$i]['date']            = $record->date;
                $exportExcel[$i]['time']            = $record->time;
                $exportExcel[$i]['type']            = $record->type == 'out' ? 'Check Out' : 'Check In';
                $exportExcel[$i]['mode']            = $record->is_auto_update == '1' ? 'System' : 'Manual';
                $exportExcel[$i]['checkout_reason'] = $record->checkout_reason;
                $exportExcel[$i]['is_rider']        = optional($employee->workdetail)->is_rider == "1" ? "Yes" : "No";
                $exportExcel[$i]['location']        = $record->latitude && $record->longitude
                    ? $record->latitude . ',' . $record->longitude
                    : '';
                $exportExcel[$i]['branch'] = $record->branch->name ?? '';
            }

            $export = new ExcelExport($exportExcel, $headers);
            return Excel::download($export, 'attendance_history_report_' . $employee->employee_id . '.xlsx');
        }

        $userName = User::where('id', $user)->first()->name;
        return view('attendance::attendance.history', compact('user', 'userName'));
    }

    public function uservisithistory($user, Request $request)
    {
        view()->share('activeLink', 'marked-attendances');
        if ($request->ajax()) {
            $data = LocationVisits::where('user_id', $user)->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->editColumn('id', function ($payslip) {
                    $user = User::where('id', $payslip->user_id)->first();
                    $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
                    return $html;
                })
                ->editColumn('date', function ($payslip) {
                    // $user = User::where('id',$payslip->user_id)->first();
                    return $payslip->date;
                })
                ->addColumn('location', function ($payslip) {
                    //$user = User::with('department')->where('id',$payslip->user_id)->first();
                    return $payslip->location;
                })
                ->addColumn('visit_purpose', function ($payslip) {
                    //$user = User::with('department')->where('id',$payslip->user_id)->first();
                    return $payslip->visit_purpose;
                })
                ->addColumn('visit_start', function ($payslip) {
                    //$user = User::with('department')->where('id',$payslip->user_id)->first();
                    return $payslip->visit_in;
                })
                ->addColumn('visit_end', function ($payslip) {
                    //$user = User::with('department')->where('id',$payslip->user_id)->first();
                    return $payslip->visit_out;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
        $startdate = $request->start_date ? $request->start_date : '';
        $enddate   = $request->end_date ? $request->end_date : '';
        $userName  = User::where('id', $user)->first()->name;
        return view('attendance::attendance.visit', compact('user', 'userName', 'startdate', 'enddate'));
    }

    public function userVisitReport(Request $request)
    {

        $user = User::where('id', $request->user_id)->first();
        if ($request->has('export') && $request->export == 1) {
            $userIds = (array) $request->user_id;
            return Excel::download(
                new VisitReportExport($userIds, $request->start_date, $request->end_date),
                $user->name . '_visit_report_' . date('Y-m-d') . '.xlsx'
            );
        }
        if ($request->has('export') && $request->export == 2) {
            $userIds = array_filter((array) $request->user_id);
            $month   = $request->month ? $request->month : date('m');
            $year    = $request->year ? $request->year : date('Y');
            $reports = LocationVisits::query()
                ->when(! empty($userIds), fn($q) => $q->whereIn('user_id', $userIds))
                ->when($request->start_date, fn($q) => $q->whereDate('date', '>=', $request->start_date))
                ->when($request->end_date, fn($q) => $q->whereDate('date', '<=', $request->end_date))
                ->when($request->start_date == null && $request->end_date == null, function ($query) use ($request, $month, $year) {
                    $query->whereMonth('date', $month)
                        ->whereYear('date', $year);
                })
                ->orderBy('date', 'asc')
                ->get();
            $pdf = Pdf::loadView('attendance::attendance.visit_report_pdf', compact('reports', 'user'))
                ->setPaper('a4', 'portrait');
            return $pdf->download($user->name . '_visit_report_' . date('Y-m-d') . '.pdf');
        }
    }

    public function showAttendanceReport(Request $request)
    {

        $users  = [];
        $search = false;
        if ($request->post()) {

            $users = User::query()
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                })
                ->withWhereHas('attendances', function ($query) use ($request) {
                    if ($request->start_date && $request->end_date) {
                        $query->whereBetween('date', [$request->start_date, $request->end_date]);
                    } else {
                        $query->whereMonth('date', $request->month)->whereYear('date', $request->year);
                    }
                })
                ->when($request->employee, function ($query) use ($request) {
                    $query->whereIn('id', $request->employee);
                })
                ->when($request->department, function ($query) use ($request) {
                    $query->where('department_id', $request->department);
                })
                ->get();
        }
        if ($request->has('export') && $request->export == 1) {
            canPerform('Export Attendance');
            return Excel::download(new AttendanceExport($request), 'attendance' . date('Y-m-d') . '.xlsx');
        }
        if ($request->has('export') && $request->export == 2) {
            canPerform('Export Attendance');
            $attendanceExport = new AttendanceExport($request);
            $users            = $attendanceExport->query()->get();

            if ($request->start_date != '') {
                $period = new \DatePeriod(
                    new \DateTime($request->start_date),
                    new \DateInterval('P1D'),
                    (new \DateTime($request->end_date))->modify('+1 day')
                );
            } else {
                $yeardata  = $request->year;
                $monthdata = $request->month;

                $startDate = Carbon::create($yeardata, $monthdata, 1)->startOfMonth()->toDateString();
                $endDate   = Carbon::create($yeardata, $monthdata, 1)->endOfMonth()->toDateString();

                $period = new \DatePeriod(
                    new \DateTime($startDate),
                    new \DateInterval('P1D'),
                    (new \DateTime($endDate))->modify('+1 day')
                );
            }
            $pdf = Pdf::loadView('attendance::attendance.exportPDF', [
                'monthdata'   => $attendanceExport->monthdata,
                'departname'  => $attendanceExport->departname,
                'data'        => $users,
                'dateHeaders' => $period,
            ])->setPaper('tabloid', 'landscape');

            return $pdf->download('attendance' . date('Y-m-d') . '.pdf');
        }
        $year             = $request->year ? $request->year : date('Y');
        $month            = $request->month ? $request->month : date('m');
        $startdate        = $request->start_date ? $request->start_date : '';
        $enddate          = $request->end_date ? $request->end_date : '';
        $filterEmployees  = User::whereIn('id', $request->employee ?? [])->get();
        $department       = $request->department ? $request->department : '';
        $filterDepartment = Department::get();
        view()->share('activeLink', 'attendance-report');
        return view('attendance::attendance.showReport', compact('users', 'month', 'year', 'filterEmployees', 'filterDepartment', 'department', 'search', 'startdate', 'enddate'));
    }

    public function exportPdf(Request $request)
    {

        $year  = $request->year ? $request->year : date('Y');
        $month = $request->month ? $request->month : date('m');

        $perPage = $request->input('per_page', 10);
        $users   = User::query()->where('status', User::STATUS_ACTIVE)->notAdmin()
            ->with('attendances', function ($query) use ($month, $year, $request) {
                $query->whereMonth('date', $month)->whereYear('date', $year);
            })
            ->when($request->employee, function ($query) use ($request) {
                $query->whereIn('id', $request->employee);
            })
            ->when($request->department, function ($query) use ($request) {
                $query->where('department_id', $request->department);
            })
            ->paginate($perPage);
        $filterEmployees  = User::whereIn('id', $request->employee ?? [])->get();
        $filterDepartment = Department::where('id', $request->department)->first();

        $pdf = Pdf::loadView('attendance::attendance.attendancePDF', [
            'month'            => $month,
            'year'             => $year,
            'users'            => $users,
            'filterEmployees'  => $filterEmployees,
            'filterDepartment' => $filterDepartment,
        ])->setPaper('tabloid', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        return $pdf->download('attendance' . date('Y-m-d') . '.pdf');
    }

    public function extra_work_show(Request $request)
    {

        canPerform('View Over Time Request');
        $month = $request->month ?? 'all';

        if ($request->ajax()) {
            $currentYear       = date('Y');
            $lastYear          = $currentYear - 1;
            $extraWorkRequests = extraWorkRequest::whereIn('year', [$currentYear, $lastYear])
                ->when($request->filled('month') && $request->month !== 'all', function ($query) use ($request) {
                    return $query->where('month', $request->month);
                })
                ->with('user')
                ->orderBy('date', 'desc')
                ->get();
            return DataTables::of($extraWorkRequests)
                ->addColumn('select', function ($row) {
                    if ($row->status == 0) {
                        $input = '<input type="checkbox" name="select_check" class="selectCheck" value="' . $row->id . '">';
                    } else {
                        $input = '';
                    }
                    return $input;
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user != null ? Str::limit($row->user->name, 25) . ' (' . $row->user->employee_id . ')' : '-';
                })
                ->addColumn('added_by', function ($row) {
                    $addedby = User::find($row->added_by);
                    return $addedby != null ? Str::limit($addedby->name, 25) : '-';
                })
                ->addColumn('extra_hours', function ($row) {
                    return "{$row->hours} Hours {$row->minit} Minutes";
                })
                ->addColumn('total_hours', function ($row) {
                    $extraminit = round($row->minit / 60, 2);
                    $extraHours = floatval($row->hours + $extraminit);
                    return number_format($extraHours, 2);
                })
                ->addColumn('date', function ($row) {
                    return [
                        'display'   => \Carbon\Carbon::parse($row->date)->format('d-m-Y'),
                        'timestamp' => \Carbon\Carbon::parse($row->date)->timestamp,
                    ];
                })
                ->addColumn('status', function ($row) {
                    switch ($row->status) {
                        case 0:
                            return "<span class='badge badge-warning' style='color: black'>Pending</span>";
                        case 1:
                            return "<span class='badge badge-success' style='color: black'>Added To Payroll</span>";
                        case 2:
                            return "<span class='badge badge-info' style='color: black'>Added To Leave</span>";
                        case 3:
                            return "<span class='badge badge-danger' style='color: black'>Rejected</span>";
                        default:
                            return "Unknown";
                    }
                })
                ->addColumn('cash_amount', function ($row) {
                    $user = $row->user != null ? User::find($row->user->id) : null;
                    if ($user) {
                        $today   = $row->date;
                        $holiday = Holiday::whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->first();
                        $rate         = $holiday ? 1.50 : 1.25;
                        $totalDays    = Carbon::parse($today)->daysInMonth;
                        $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
                        $user_shifts  = User::find($user->id)
                            ->assigned_shifts()
                            ->with('shift_schedule_information')
                            ->where('assigned_for_date', $today)
                            ->get();
                        if ($company_hour < 0) {
                            foreach ($user_shifts as $index => $shiftData) {
                                $shift = $shiftData->shift_schedule_information;
                                // Convert shift start and end times to Carbon instances
                                $shiftStart = Carbon::parse($shift->shift_start);
                                $shiftEnd   = Carbon::parse($shift->shift_end);

                                // Calculate the hours between shift start and end
                                if ($shiftEnd->lessThan($shiftStart)) {
                                    $shiftEnd->addDay();
                                }
                                $hoursDifference   = $shiftEnd->diffInMinutes($shiftStart);
                                $totalShiftMinuts += $hoursDifference;
                            }
                            $working_hours = $hours . '.' . $minutes;
                        } else {
                            $working_hours = $company_hour;
                        }
                        $extraminit        = round($row->minit / 60, 2);
                        $extraHours        = floatval($row->hours + $extraminit);
                        $basicSalary       = $user->salary ? $user->salary->basic : 0;
                        $calculated_amount = round(($basicSalary / $totalDays / $working_hours) * $rate * $extraHours, 2);
                    } else {
                        $calculated_amount = '-';
                    }
                    return $calculated_amount;
                })
                ->addColumn('actions', function ($row) {
                    $actions = '';
                    $payslip = UserPaySlip::where([['user_id', $row->user->id], ['month_code', $row->month], ['year', $row->year]])->first();
                    if ($payslip) {
                        $isclose = $payslip->is_close;
                    } else {
                        $isclose = 0;
                    }
                    if ($row->status == 0) {
                        if ($isclose == 0) {
                            if (hasPermission('Manage Over Time Request')) {
                                $actions .= "<a href='" . route('backend.updateRequest', [$row->id, 1]) . "' class='btn btn-sm btn-success me-2'><i class='fa fa-pen'></i> Add To Payroll</a>";
                                $actions .= "<a href='" . route('backend.updateRequest', [$row->id, 2]) . "' class='btn btn-sm btn-info me-2'><i class='fa fa-edit'></i> Add To Leave</a>";
                                $actions .= "<a href='" . route('backend.updateRequest', [$row->id, 3]) . "' onclick='return confirmDelete(this)' class='btn btn-sm btn-danger me-2'>Reject</a>";
                            }
                            if (hasPermission('Edit Over Time Request')) {
                                $actions .= "<a href='" . route('backend.adminEditRequest', $row->id) . "' class='btn btn-sm inline-block me-2  btn-warning edit-button'><i class='fa fa-edit'></i> Edit</a>";
                            }
                        } else {
                            $actions .= "<span class='badge badge-warning' style='color: black'>Payroll closed</span>";
                        }
                    } elseif ($row->status == 3) {
                        if (hasPermission('Manage Over Time Request')) {
                            $actions .= "<a href='" . route('backend.adminRemoveRequest', $row->id) . "' onclick='return confirmDelete(this)' class='btn btn-sm btn-danger me-2'>Delete</a>";
                        }
                    } elseif ($row->status == 1) {
                        if ($isclose == 0) {
                            if (hasPermission('Manage Over Time Request')) {
                                $actions .= "<a href='" . route('backend.revertRequest', [$row->id, 1]) . "' class='btn btn-sm btn-success me-2'><i class='fa fa-pen'></i> Revert To Payroll</a>";
                            }
                        } else {
                            $actions .= "<span class='badge badge-warning' style='color: black'>Payroll closed</span>";
                        }
                    } elseif ($row->status == 2) {
                        if ($isclose == 0) {
                            if (hasPermission('Manage Over Time Request')) {
                                $actions .= "<a href='" . route('backend.revertRequest', [$row->id, 2]) . "' class='btn btn-sm btn-info me-2'><i class='fa fa-edit'></i> Revert To Leave</a>";
                            }
                        } else {
                            $actions .= "<span class='badge badge-warning' style='color: black'>Payroll closed</span>";
                        }
                    }
                    return $actions;
                })
                ->rawColumns(['added_by', 'select', 'total_hours', 'status', 'cash_amount', 'actions'])
                ->make(true);
        }
        view()->share('activeLink', 'extra-work');
        return view('attendance::attendance.show_extra_work', compact('month'));
    }

    public function extra_work_show_report(Request $request)
    {

        canPerform('Manage Over Time Request');
        return Excel::download(new ExtraWorkHoursExport($request), 'extra_work_hours' . date('Y-m-d') . '.xlsx');
    }

    public function addEmpExtraHours()
    {

        canPerform('Create Over Time Request');
        $users = User::whereNotIn('name', [
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ])->where('status', 'active')->get();
        $html     = view('attendance::attendance.create_request', compact('users'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function storeEmpExtraHours(Request $request)
    {

        canPerform('Create Over Time Request');
        $request->validate([
            'user_id'     => 'required',
            'extra_hours' => 'required',
            'date'        => 'required',
        ]);

        $year  = Carbon::parse($request->date)->format('Y');
        $month = Carbon::parse($request->date)->format('m');

        $hoursdata = explode('.', number_format($request->extra_hours, 2, '.', ''));
        $hours     = $hoursdata[0];
        $minits    = $hoursdata[1];

        extraWorkRequest::create([
            'user_id'     => $request->user_id,
            'added_by'    => auth()->id(),
            'extra_hours' => $request->extra_hours,
            'hours'       => $hours,
            'minit'       => $minits,
            'month'       => $month,
            'year'        => $year,
            'status'      => 0,
            'date'        => $request->date,
        ]);

        return redirect()->back()->with('success', 'Extra work request added successfully.');
    }

    public function show_extra_hours_report(Request $request)
    {

        canPerform('Manage Over Time Request');
        $year  = $request->year ? $request->year : date('Y');
        $month = $request->month ? $request->month : date('m');

        if ($request->ajax()) {

            $extraWorkReport = extraWorkRequest::select(
                'user_id',
                DB::raw("SUM(CASE WHEN status = 1 THEN hours ELSE 0 END) AS total_payroll_hours"),
                DB::raw("SUM(CASE WHEN status = 2 THEN hours ELSE 0 END) AS total_leave_hours"),
                DB::raw("SUM(CASE WHEN status = 1 THEN minit ELSE 0 END) AS total_payroll_minit"),
                DB::raw("SUM(CASE WHEN status = 2 THEN minit ELSE 0 END) AS total_leave_minit")
            )
                ->where('year', $year)
                ->groupBy('user_id')
                ->with('user')
                ->get();
            return DataTables::of($extraWorkReport)
                ->addColumn('user_name', function ($row) {
                    return $row->user != null ? Str::limit($row->user->name, 25) . ' (' . $row->user->employee_id . ')' : '-';
                })
                ->addColumn('total_payroll_hours', function ($row) {
                    $addhours = $row->total_payroll_hours;
                    $hours    = intdiv($row->total_payroll_minit, 60);
                    $minutes  = $row->total_payroll_minit % 60;
                    return ($addhours + $hours) . ':' . $minutes;
                })
                ->addColumn('total_leave_hours', function ($row) {
                    $addhours = $row->total_leave_hours;
                    $hours    = intdiv($row->total_leave_minit, 60);
                    $minutes  = $row->total_leave_minit % 60;
                    return ($addhours + $hours) . ':' . $minutes;
                })
                ->make(true);
        }

        view()->share('activeLink', 'extra-work-report');
        return view('attendance::attendance.show_extra_work_report');
    }

    public function updateRequest(Request $request, $id, $status)
    {

        canPerform('Manage Over Time Request');
        $extraWorkRequest = extraWorkRequest::where('id', $id)->first();

        $year  = $extraWorkRequest->year;
        $month = $extraWorkRequest->month;
        if ($extraWorkRequest) {
            $chstatus = 0;
            if ($status == 2) {
                $keywords     = ['DIL Leave', 'dil Leave', 'dilLeave'];
                $is_dil_leave = LeaveType::where(function ($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $query->orWhere('name', 'like', "%$keyword%");
                    }
                })->first();
                if ($is_dil_leave) {
                    $availableDay = $extraWorkRequest->extra_hours / 10;

                    $checkLeaveBalance = LeaveBalance::where([
                        'user_id'       => $extraWorkRequest->user_id,
                        'leave_type_id' => $is_dil_leave->id,
                        'year'          => date('Y'),
                    ])->first();
                    $checkLeaveBalance->update([
                        'available'    => $checkLeaveBalance->available + $availableDay,
                        'monthwiseDay' => $checkLeaveBalance->monthwiseDay + $availableDay,
                    ]);
                    $date           = Carbon::now()->toDateString();
                    $addtransaction = UserLeaveBalanceTransaction::create([
                        'user_id'          => $extraWorkRequest->user_id,
                        'leave_type_id'    => $is_dil_leave->id,
                        'transaction_type' => 'add',
                        'old_balance'      => $checkLeaveBalance->available,
                        'update_balance'   => $availableDay,
                        'new_balance'      => ($checkLeaveBalance->available + $availableDay),
                        'transaction_date' => $date,
                        'description'      => 'Add ' . $availableDay . ' leave days into extra hours request: ' . $extraWorkRequest->id,
                    ]);
                    $chstatus = 2;
                } else {
                    $chstatus = 0;
                    return redirect()->back()->with('error', 'Ther is no any Dil Leave type!');
                }
            }
            if ($status == 1) {
                $user    = User::find($extraWorkRequest->user_id);
                $today   = $extraWorkRequest->date;
                $holiday = Holiday::whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->first();
                $rate         = $holiday ? 1.50 : 1.25;
                $totalDays    = Carbon::parse($today)->daysInMonth;
                $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
                $user_shifts  = User::find($user->id)
                    ->assigned_shifts()
                    ->with('shift_schedule_information')
                    ->where('assigned_for_date', $today)
                    ->get();
                if ($company_hour < 0) {
                    foreach ($user_shifts as $index => $shiftData) {
                        $shift = $shiftData->shift_schedule_information;
                        // Convert shift start and end times to Carbon instances
                        $shiftStart = Carbon::parse($shift->shift_start);
                        $shiftEnd   = Carbon::parse($shift->shift_end);

                        // Calculate the hours between shift start and end
                        if ($shiftEnd->lessThan($shiftStart)) {
                            $shiftEnd->addDay();
                        }
                        $hoursDifference   = $shiftEnd->diffInMinutes($shiftStart);
                        $totalShiftMinuts += $hoursDifference;
                    }
                    $working_hours = $hours . '.' . $minutes;
                } else {
                    $working_hours = $company_hour;
                }
                $extraminit        = $extraWorkRequest->minit / 60;
                $extraHours        = floatval($extraWorkRequest->hours + $extraminit);
                $calculated_amount = round(($user->salary->basic / $totalDays / $working_hours) * $rate * $extraHours, 2);
                $overyear          = $extraWorkRequest->year;
                $overmonth         = $extraWorkRequest->month;

                $addOvertime = UserOvertime::create([
                    'overtime_type'     => 'ot3',
                    'rate_per_hour'     => $rate,
                    'hours'             => $extraWorkRequest->extra_hours,
                    'date'              => $extraWorkRequest->date,
                    'month_code'        => $overmonth,
                    'year'              => $overyear,
                    'user_id'           => $extraWorkRequest->user_id,
                    'calculated_amount' => $calculated_amount,
                    'is_system_add'     => 1,
                ]);
                $chstatus = 1;
            }
            if ($status == 3) {
                $chstatus = 3;
            }
            $empextraWorkupdate = $extraWorkRequest->update([
                'status' => $chstatus,
            ]);
        } else {
            return redirect()->back()->with('error', 'Extra work request was not found!');
        }
        return redirect()->back()->with('success', 'Extra work request status updated successfully.');
    }

    public function allRequestUpdate(Request $request)
    {

        canPerform('Manage Over Time Request');
        $selectIds         = $request->selected_ids;
        $status            = $request->action;
        $extraWorkRequests = extraWorkRequest::whereIn('id', $selectIds)->get();

        foreach ($extraWorkRequests as $extraWorkRequest) {

            if ($extraWorkRequest) {
                $year  = $extraWorkRequest->year;
                $month = $extraWorkRequest->month;

                $chstatus = 0;
                if ($status == 2) {
                    $keywords     = ['DIL Leave', 'dil Leave', 'dilLeave'];
                    $is_dil_leave = LeaveType::where(function ($query) use ($keywords) {
                        foreach ($keywords as $keyword) {
                            $query->orWhere('name', 'like', "%$keyword%");
                        }
                    })->first();
                    if ($is_dil_leave) {
                        $availableDay = $extraWorkRequest->extra_hours / 10;

                        $checkLeaveBalance = LeaveBalance::where([
                            'user_id'       => $extraWorkRequest->user_id,
                            'leave_type_id' => $is_dil_leave->id,
                            'year'          => date('Y'),
                        ])->first();
                        $checkLeaveBalance->update([
                            'available' => $checkLeaveBalance->available + $availableDay,
                        ]);
                        $chstatus = 2;
                    } else {
                        $chstatus = 0;
                        return redirect()->back()->with('error', 'Ther is no any Dil Leave type!');
                    }
                }
                if ($status == 1) {
                    $user    = User::find($extraWorkRequest->user_id);
                    $today   = $extraWorkRequest->date;
                    $holiday = Holiday::whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->first();
                    $rate         = $holiday ? 1.50 : 1.25;
                    $totalDays    = Carbon::parse($today)->daysInMonth;
                    $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
                    $user_shifts  = User::find($user->id)
                        ->assigned_shifts()
                        ->with('shift_schedule_information')
                        ->where('assigned_for_date', $today)
                        ->get();
                    if ($company_hour < 0) {
                        foreach ($user_shifts as $index => $shiftData) {
                            $shift = $shiftData->shift_schedule_information;
                            // Convert shift start and end times to Carbon instances
                            $shiftStart = Carbon::parse($shift->shift_start);
                            $shiftEnd   = Carbon::parse($shift->shift_end);

                            // Calculate the hours between shift start and end
                            if ($shiftEnd->lessThan($shiftStart)) {
                                $shiftEnd->addDay();
                            }
                            $hoursDifference   = $shiftEnd->diffInMinutes($shiftStart);
                            $totalShiftMinuts += $hoursDifference;
                        }
                        $working_hours = $hours . '.' . $minutes;
                    } else {
                        $working_hours = $company_hour;
                    }
                    $extraminit        = $extraWorkRequest->minit / 60;
                    $extraHours        = floatval($extraWorkRequest->hours + $extraminit);
                    $calculated_amount = round(($user->salary->basic / $totalDays / $working_hours) * $rate * $extraHours, 2);

                    $addOvertime = UserOvertime::create([
                        'overtime_type'     => 'ot3',
                        'rate_per_hour'     => $rate,
                        'hours'             => $extraWorkRequest->extra_hours,
                        'date'              => $today, //Carbon::now()->toDateString(),
                        'month_code'        => $month,
                        'year'              => $year,
                        'user_id'           => $extraWorkRequest->user_id,
                        'calculated_amount' => $calculated_amount,
                        'is_system_add'     => 1,
                    ]);
                    $chstatus = 1;
                }
                if ($status == 3) {
                    $chstatus = 3;
                }
                $empextraWorkupdate = $extraWorkRequest->update([
                    'status' => $chstatus,
                ]);
            } else {
                return redirect()->back()->with('error', 'Extra work request was not found!');
            }
        }
        return redirect()->back()->with('success', 'Extra work request status updated successfully.');
    }

    public function revertRequest(Request $request, $id, $status)
    {

        canPerform('Manage Over Time Request');
        $extraWorkRequest = extraWorkRequest::where('id', $id)->first();

        $year  = date('Y');
        $month = date('m');
        if ($extraWorkRequest) {
            $chstatus = 0;
            if ($status == 2) {
                $keywords     = ['DIL Leave', 'dil Leave', 'dilLeave'];
                $is_dil_leave = LeaveType::where(function ($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $query->orWhere('name', 'like', "%$keyword%");
                    }
                })->first();
                if ($is_dil_leave) {
                    $availableDay      = $extraWorkRequest->extra_hours / 10;
                    $checkLeaveBalance = LeaveBalance::where([
                        'user_id'       => $extraWorkRequest->user_id,
                        'leave_type_id' => $is_dil_leave->id,
                        'year'          => date('Y'),
                    ])->first();

                    if ($checkLeaveBalance && round($checkLeaveBalance->available, 2) >= round($availableDay, 2)) {
                        $checkLeaveBalance->update([
                            'available' => round($checkLeaveBalance->available - $availableDay, 2), //$checkLeaveBalance->available - $availableDay,
                        ]);
                        $chstatus = 0;
                    } else {
                        $chstatus           = 2;
                        $empextraWorkupdate = $extraWorkRequest->update([
                            'status' => $chstatus,
                        ]);
                        return redirect()->back()->with('error', 'not enough leave balance!');
                    }
                } else {
                    $chstatus = 2;
                    return redirect()->back()->with('error', 'Ther is no any Dil Leave type!');
                }
            }
            if ($status == 1) {
                $user    = User::find($extraWorkRequest->user_id);
                $today   = $extraWorkRequest->date;
                $holiday = Holiday::whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->first();
                $rate         = $holiday ? 1.50 : 1.25;
                $totalDays    = Carbon::parse($today)->daysInMonth;
                $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
                $user_shifts  = User::find($user->id)
                    ->assigned_shifts()
                    ->with('shift_schedule_information')
                    ->where('assigned_for_date', $today)
                    ->get();
                if ($company_hour < 0) {
                    foreach ($user_shifts as $index => $shiftData) {
                        $shift = $shiftData->shift_schedule_information;
                        // Convert shift start and end times to Carbon instances
                        $shiftStart = Carbon::parse($shift->shift_start);
                        $shiftEnd   = Carbon::parse($shift->shift_end);

                        // Calculate the hours between shift start and end
                        if ($shiftEnd->lessThan($shiftStart)) {
                            $shiftEnd->addDay();
                        }
                        $hoursDifference   = $shiftEnd->diffInMinutes($shiftStart);
                        $totalShiftMinuts += $hoursDifference;
                    }
                    $working_hours = $hours . '.' . $minutes;
                } else {
                    $working_hours = $company_hour;
                }
                $extraminit        = $extraWorkRequest->minit / 60;
                $extraHours        = floatval($extraWorkRequest->hours + $extraminit);
                $calculated_amount = round(($user->salary->basic / $totalDays / $working_hours) * $rate * $extraHours, 2);

                $payroll = UserPaySlip::where([
                    'month_code' => $extraWorkRequest->month,
                    'year'       => $extraWorkRequest->year,
                    'user_id'    => $extraWorkRequest->user_id,
                    'is_close'   => 1,
                ])->first();
                if ($payroll) {
                    $chstatus           = 1;
                    $empextraWorkupdate = $extraWorkRequest->update([
                        'status' => $chstatus,
                    ]);
                    return redirect()->back()->with('error', 'User payroll was closed!');
                } else {
                    $removeOvertime = UserOvertime::where([
                        'month_code' => $extraWorkRequest->month,
                        'year'       => $extraWorkRequest->year,
                        'user_id'    => $extraWorkRequest->user_id,
                        // 'hours' => $extraWorkRequest->extra_hours,
                    ])->first();

                    if ($removeOvertime) {
                        $removeOvertime->delete();
                        $chstatus = 0;
                    } else {
                        $chstatus           = 1;
                        $empextraWorkupdate = $extraWorkRequest->update([
                            'status' => $chstatus,
                        ]);
                        return redirect()->back()->with('error', 'User overtime not found!');
                    }
                }
            }
            $empextraWorkupdate = $extraWorkRequest->update([
                'status' => $chstatus,
            ]);
        } else {
            return redirect()->back()->with('error', 'Extra work request was not found!');
        }
        return redirect()->back()->with('success', 'Extra work request revert successfully.');
    }

    public function admin_edit_request_extra_work(Request $request, $id)
    {

        canPerform('Edit Over Time Request');
        $extraWorkRequest = extraWorkRequest::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $year  = date('Y');
            $month = date('m');

            $data = $request->validate([
                'extra_hours' => 'required|min:0',
            ]);
            $response  = getErrorResponse();
            $hoursdata = explode('.', number_format($request->extra_hours, 2, '.', ''));
            $hours     = $hoursdata[0];
            $minits    = $hoursdata[1];

            if ($extraWorkRequest && $extraWorkRequest->status == 0) {
                $empextraWorkupdate = $extraWorkRequest->update([
                    'extra_hours' => $request->extra_hours,
                    'hours'       => $hours,
                    'minit'       => $minits,
                ]);
                $response = getSuccessResponse('Extra work request updated successfully.');
            }

            return response()->json($response);
        }
        $users = User::whereNotIn('name', [
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ])->where('status', 'active')->get();
        $html = view('attendance::attendance.edit_request', compact('extraWorkRequest', 'users'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function admin_remove_request_extra_work($id)
    {

        canPerform('Manage Over Time Request');
        $response = getErrorResponse();
        try {
            $workrequest  = extraWorkRequest::find($id);
            $year         = date('Y');
            $month        = date('m');
            $empextraWork = extraWork::where([['user_id', $workrequest->user_id], ['year', $year], ['month', $month]])->first();
            if ($workrequest->status == 1 && $workrequest->status == 2) {
                $empextraWork->update([
                    'extra_hours' => $empextraWork->extra_hours - $workrequest->extra_hours,
                ]);
                $workrequest->delete();
            }
            $workrequest->delete();

            return redirect()->back()->with('error', 'Extra work request deleted!');
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "This service is already associated, cannot be removed.";
                $response['error']   = "This service is already associated, cannot be removed.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }
        return response()->json($response);
    }

    public function late_come_show(Request $request)
    {

        $month = $request->month ?? 'all';

        if ($request->ajax()) {
            $currentYear      = date('Y');
            $lastYear         = $currentYear - 1;
            $lateComeRequests = lateCome::whereYear('date', $currentYear)
                ->when($request->filled('month') && $request->month !== 'all', function ($query) use ($request) {
                    return $query->whereMonth('date', $request->month);
                })
                ->with('user')
                ->orderBy('date', 'desc')
                ->get();
            return DataTables::of($lateComeRequests)
                ->addColumn('select', function ($row) {
                    if ($row->status == 0) {
                        $input = '<input type="checkbox" name="select_check" class="selectCheck" value="' . $row->id . '">';
                    } else {
                        $input = '';
                    }
                    return $input;
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user != null ? Str::limit($row->user->name, 25) . ' (' . $row->user->employee_id . ')' : '-';
                })
                ->addColumn('late_come', function ($row) {
                    return $row->late_minute;
                })
                ->addColumn('date', function ($row) {
                    return [
                        'display'   => \Carbon\Carbon::parse($row->date)->format('d-m-Y'),
                        'timestamp' => \Carbon\Carbon::parse($row->date)->timestamp,
                    ];
                })
                ->addColumn('status', function ($row) {
                    switch ($row->status) {
                        case 0:
                            return "<span class='badge badge-warning' style='color: black'>Pending</span>";
                        case 1:
                            return "<span class='badge badge-success' style='color: black'>Approved</span>";
                        case 2:
                            return "<span class='badge badge-info' style='color: black'>Rejected</span>";
                        default:
                            return "Unknown";
                    }
                })
                ->addColumn('charge_amount', function ($row) {
                    return $row->charge_amount;
                })
                ->addColumn('actions', function ($row) {
                    $actions = '';
                    $date    = Carbon::parse($row->date);
                    $year    = $date->year;
                    $month   = $date->month;
                    $payslip = UserPaySlip::where([['user_id', $row->user->id], ['month_code', $month], ['year', $year]])->first();
                    if ($payslip) {
                        $isclose = $payslip->is_close;
                    } else {
                        $isclose = 0;
                    }
                    if ($row->status == 0) {
                        if ($isclose == 0) {
                            $actions .= "<a href='" . route('backend.updateLateRequest', [$row->id, 1]) . "' class='btn btn-sm btn-success me-2'><i class='fa fa-pen'></i>Approved</a>";
                            $actions .= "<a href='" . route('backend.updateLateRequest', [$row->id, 2]) . "' onclick='return confirmDelete(this)' class='btn btn-sm btn-danger me-2'>Reject</a>";
                            $actions .= "<a href='" . route('backend.editRequest', $row->id) . "' class='btn btn-sm inline-block me-2  btn-warning edit-button'><i class='fa fa-edit'></i> Edit</a>";
                        } else {
                            $actions .= "<span class='badge badge-warning' style='color: black'>Payroll closed</span>";
                        }
                    }
                    return $actions;
                })
                ->rawColumns(['select', 'status', 'charge_amount', 'actions'])
                ->make(true);
        }

        view()->share('activeLink', 'late-come');
        return view('attendance::attendance.show_late_come', compact('month'));
    }

    public function updateLateRequest(Request $request, $id, $status)
    {

        $extraWorkRequest = lateCome::where('id', $id)->first();

        if ($extraWorkRequest) {
            if ($status == 1) {
                $date        = Carbon::parse($extraWorkRequest->date);
                $year        = $date->year;
                $month       = $date->month;
                $addOvertime = UserDeduction::create([
                    'user_id'                    => $extraWorkRequest->user_id,
                    'title'                      => 'Late come',
                    'deduction_type'             => 'fixed',
                    'amount'                     => $extraWorkRequest->charge_amount,
                    'date'                       => $extraWorkRequest->date,
                    'month_code'                 => $month,
                    'year'                       => $year,
                    'is_fixed_for_current_month' => 1,
                ]);
            }
            $empextraWorkupdate = $extraWorkRequest->update([
                'status' => $status,
            ]);
        } else {
            return redirect()->back()->with('error', 'Extra work request was not found!');
        }
        return redirect()->back()->with('success', 'Extra work request status updated successfully.');
    }

    public function editRequest(Request $request, $id)
    {

        $lateComeRequest = lateCome::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $year  = date('Y');
            $month = date('m');

            $data = $request->validate([
                'late_minute' => 'required|min:0',
            ]);
            $response = getErrorResponse();

            $date         = Carbon::parse($lateComeRequest->date);
            $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
            $month        = Carbon::parse($date)->format('m');
            $year         = Carbon::parse($date)->format('Y');
            $user         = User::find($lateComeRequest->user_id);
            $gross_salary = $this->getGrossSalary($user, $month, $year);
            $dailyRate    = $gross_salary * 12 / 365;
            $hoursRate    = $dailyRate / $company_hour;
            $minitRate    = $hoursRate / 60;
            $lateAmount   = $minitRate * $request->late_minute;
            $lateAmount   = round($lateAmount, 2);

            if ($lateComeRequest && $lateComeRequest->status == 0) {
                $updatedata = $lateComeRequest->update([
                    'late_minute'   => $request->late_minute,
                    'charge_amount' => $lateAmount,
                ]);

                $response = getSuccessResponse('Late Come request updated successfully.');
            }

            return response()->json($response);
        }
        $users = User::whereNotIn('name', [
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ])->where('status', 'active')->get();
        $html = view('attendance::attendance.edit_late_come', compact('lateComeRequest', 'users'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function autoAddExtraWork(Request $request)
    {

        $isAllowed = $request->input('allow');

        if ($isAllowed) {
            $settingadd = Setting::where('key', 'auto_add_extra_work')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'auto_add_extra_work',
                    'value' => true,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'auto_add_extra_work',
                    'value' => true,
                ]);
            }
        } else {
            $settingadd = Setting::where('key', 'auto_add_extra_work')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'auto_add_extra_work',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'auto_add_extra_work',
                    'value' => false,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function showUserVisitReport(Request $request)
    {

        $users  = [];
        $search = false;

        if ($request->ajax()) {
            $data = LocationVisits::query()
                ->with(['user.department'])
                ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                    if ($request->start_date && $request->end_date) {
                        $query->whereBetween('date', [
                            $request->start_date,
                            $request->end_date,
                        ]);
                    }
                })
                ->when($request->start_date == null && $request->end_date == null, function ($query) use ($request) {
                    $query->whereMonth('date', $request->month)
                        ->whereYear('date', $request->year);
                })
                ->when($request->employee, function ($query) use ($request) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->whereIn('id', $request->employee);
                    });
                })
                ->when($request->department, function ($query) use ($request) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('department_id', $request->department);
                    });
                })
                ->orderBy('id', 'desc');
            return DataTables::of($data)
                ->editColumn('id', function ($visit) {
                    $user = User::where('id', $visit->user_id)->first();
                    $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">(' . $user->employee_id . ') ' . $user->name . '</span></a>';
                    return $html;
                })
                ->editColumn('date', function ($visit) {
                    return $visit->date;
                })
                ->addColumn('location', function ($visit) {
                    return $visit->location;
                })
                ->addColumn('visit_purpose', function ($visit) {
                    return $visit->visit_purpose;
                })
                ->addColumn('visit_start', function ($visit) {
                    return $visit->visit_in;
                })
                ->addColumn('visit_end', function ($visit) {
                    return $visit->visit_out;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
        $year             = $request->year ? $request->year : date('Y');
        $month            = $request->month ? $request->month : date('m');
        $startdate        = $request->start_date ? $request->start_date : '';
        $enddate          = $request->end_date ? $request->end_date : '';
        $filterEmployees  = User::whereIn('id', $request->employee ?? [])->get();
        $department       = $request->department ? $request->department : '';
        $filterDepartment = Department::get();
        view()->share('activeLink', 'show-user-visit-report');

        return view('attendance::attendance.showVisitReport', compact('users', 'month', 'year', 'filterEmployees', 'filterDepartment', 'department', 'search', 'startdate', 'enddate'));

    }

    public function exportVisitReport(Request $request)
    {

        if ($request->has('export') && $request->export == 1) {
            $userIds = (array) $request->employee;
            return Excel::download(
                new VisitReportExport($userIds, $request->start_date, $request->end_date, $request->department, $request->month, $request->year),
                'visit_report_' . date('Y-m-d') . '.xlsx'
            );
        }
        if ($request->has('export') && $request->export == 2) {
            $month   = $request->month ? $request->month : date('m');
            $year    = $request->year ? $request->year : date('Y');
            $reports = LocationVisits::query()
                ->with(['user.department'])
                ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                    if ($request->start_date && $request->end_date) {
                        $query->whereBetween('date', [
                            $request->start_date,
                            $request->end_date,
                        ]);
                    }
                })
                ->when($request->start_date == null && $request->end_date == null, function ($query) use ($request, $month, $year) {
                    $query->whereMonth('date', $month)
                        ->whereYear('date', $year);
                })
                ->when($request->employee, function ($query) use ($request) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->whereIn('id', $request->employee);
                    });
                })
                ->when($request->department, function ($query) use ($request) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('department_id', $request->department);
                    });
                })
                ->orderBy('date', 'asc')
                ->get();
            $pdf = Pdf::loadView('attendance::attendance.visit_report_pdf', compact('reports'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('visit_report_' . date('Y-m-d') . '.pdf');
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx',
            'month'       => 'required|integer',
            'year'        => 'required|integer',
        ]);

        $month = $request->month;
        $year  = $request->year;

        Excel::import(new AttendanceImport($month, $year), $request->file('import_file'));

        return response()->json([
            'status'  => true,
            'message' => 'Attendance imported successfully using Checkin logic.',
        ]);
    }

    public function sampleExport(Request $request)
    {
        $month = (int) $request->month;
        $year  = (int) $request->year;
        $user  = User::findOrFail($request->user_id);

        return Excel::download(
            new AttendanceMonthSampleExport($user, $month, $year),
            "attendance_sample_{$user->id}_{$month}_{$year}.xlsx"
        );
    }

}
