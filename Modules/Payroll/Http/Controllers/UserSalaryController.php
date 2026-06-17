<?php
namespace Modules\Payroll\Http\Controllers;

use App\Models\allTypeOfTransaction;
use App\Models\EMIAllowance;
use App\Models\EMIAllowanceData;
use App\Models\EMIDeduction;
use App\Models\EMIDeductionData;
use App\Models\EmployeeWorkingDay;
use App\Models\Setting;
use App\Models\User;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Attendance\Entities\Attendance;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserSalary;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Entities\UserSalaryIncrement;
use Modules\Payroll\Traits\SalaryCalculation;
use Yajra\DataTables\Facades\DataTables;

class UserSalaryController extends Controller
{
    protected $fcmService;
    use SalaryCalculation;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'leaves');
        $this->fcmService = $fcmService;

        view()->share('activeLink', 'salaries');

    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $current_month = date('m');
        $current_year  = date('Y');
        $date          = Carbon::createFromDate($current_year, $current_month, 1);
        $monthName     = $date->format('F') . ' ' . $current_year;
        if ($request->ajax()) {

            if (getSetting('attendance_base_payroll') == 'true') {
                $data = User::with(['salary', 'department'])
                    ->with('attendances', function ($query) use ($current_month, $current_year) {
                        $query->whereMonth('date', $current_month)->whereYear('date', $current_year);
                    })
                    ->where('settlement_status', 0)
                    ->whereDoesntHave('roles', function ($query) {
                        return $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                    });
                return DataTables::of($data)
                    ->addColumn('department', function ($row) {
                        return $row->department?->name ?? 'NA';
                    })
                    ->editColumn('id', function ($user) {
                        $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
                        return $html;
                    })
                    ->editColumn('basic', function ($user) {
                        return $user->salary ? $user->salary->basic : __trans('not_set');
                    })
                    ->addColumn('working_days', function ($user) {
                        // if (getSetting('payroll_calculation') == 'hourly') {
                        //     $working_days = $user->attendances()
                        //         ->whereIn('status', [
                        //             \Modules\Attendance\Enums\AttendanceStatus::Present,
                        //             \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                        //         ])
                        //         ->whereYear('date', date('Y')) // Fixed: Use 'Y' for a four-digit year
                        //         ->whereMonth('date', date('m'))
                        //         ->sum('total_worked');

                        //     $count = round(floatval($working_days / 60), 2); // Ensures correct floating-point division

                        // } else {
                        //     $count = $user->attendances->whereIn('status', [
                        //         \Modules\Attendance\Enums\AttendanceStatus::Present,
                        //         \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                        //     ])->groupby('date')->count();
                        // }

                        // return $count;
                        if (getSetting('payroll_calculation') == 'hourly') {
                            $userworkingday = $user->attendances
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->sum('total_worked');

                            $userworkingday = round(floatval($userworkingday / 60), 2);
                        } else {
                            $workingDays  = userWorkingDays($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"));
                            $presentDays  = $workingDays['present_count'] ?? 0;
                            $paidleave    = $workingDays['user_leave'] ?? 0;
                            $holidaycount = $workingDays['holiday_count'] ?? 0;
                            // $user = $user->attendances->whereIn('status', [
                            //     \Modules\Attendance\Enums\AttendanceStatus::Present,
                            //     \Modules\Attendance\Enums\AttendanceStatus::Late,
                            //     \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                            //     \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                            // ])->count();

                            $userworkingday = $presentDays . " (" . $paidleave . " paid leave)(" . $holidaycount . " Holiday)";
                        }
                        return $userworkingday;
                    })
                    ->editColumn('gross', function ($user) {
                        // $result = $this->getGrossSalary($user);
                        $result = $this->getGrossSalary($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"));
                        return $result;
                    })
                    ->addColumn('net_salary', function ($user) {

                        // $result = isset($user->salary) ? $user->salary->basic : 0;
                        // if (getSetting('payroll_calculation') == 'hourly') {
                        //     $working_days = $user->attendances()
                        //         ->whereIn('status', [
                        //             \Modules\Attendance\Enums\AttendanceStatus::Present,
                        //             \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                        //         ])
                        //         ->whereYear('date', date('Y')) // Fixed: Use 'Y' for a four-digit year
                        //         ->whereMonth('date', date('m'))
                        //         ->sum('total_worked');

                        //     $working_days = round(floatval($working_days / 60), 2);

                        //     $basic = isset($user->salary) ? $user->salary->basic : 0;

                        //     $net_salary = round($basic * $working_days, 2);
                        // } else {
                        //     $net_salary = $this->getNetSalaryAsPerAttendance($user);
                        // }
                        // $net_salary = round(floatval($net_salary), 2);
                        // return $net_salary;

                        $net_salary = $this->getNetSalaryAsPerAttendance($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"));
                        $net_salary = round(floatval($net_salary), 2);
                        return $net_salary;
                    })
                    ->addColumn('total_net_salary', function ($user) {
                        // if (getSetting('payroll_calculation') == 'hourly') {
                        //     $working_days = $user->attendances()
                        //         ->whereIn('status', [
                        //             \Modules\Attendance\Enums\AttendanceStatus::Present,
                        //             \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                        //         ])
                        //         ->whereYear('date', date('Y')) // Fixed: Use 'Y' for a four-digit year
                        //         ->whereMonth('date', date('m'))
                        //         ->sum('total_worked');

                        //     $working_days = round(floatval($working_days / 60), 2);
                        //     $basic        = isset($user->salary) ? $user->salary->basic : 0;

                        //     $total_net_salary = round($basic * $working_days, 2);
                        // } else {
                        //     $total_net_salary = $this->getTotalNetSalary($user);
                        // }
                        // $total_net_salary = round(floatval($total_net_salary), 2);

                        // return $total_net_salary;
                        $total_net_salary = $this->getTotalNetSalary($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"));
                        $total_net_salary = round(floatval($total_net_salary), 2);
                        return $total_net_salary;
                    })
                    ->addColumn('action', function ($user) {
                        $btn = createActionButton(route('backend.payroll.user.user-salaries.show', [$user, $user->id]), '', 'btn-success view-button', 'fa fa-arrow-right');
                        return $btn;
                    })
                    ->rawColumns(['action', 'id'])
                    ->make(true);
            } else {
                $data = User::with(['salary', 'department'])
                    ->with('attendances', function ($query) use ($current_month, $current_year) {
                        $query->whereMonth('date', $current_month)->whereYear('date', $current_year);
                    })
                    ->where('settlement_status', 0)
                    ->whereDoesntHave('roles', function ($query) {
                        return $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                    });
                return DataTables::of($data)
                    ->addColumn('department', function ($row) {
                        return $row->department?->name ?? 'NA';
                    })
                    ->editColumn('id', function ($user) {
                        $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
                        return $html;
                    })
                    ->editColumn('basic', function ($user) {
                        return $user->salary ? $user->salary->basic : __trans('not_set');
                    })
                    ->addColumn('working_days', function ($user) {
                        if (getSetting('payroll_calculation') == 'hourly') {

                            $working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [date("Y-m-1"), date("Y-m-t")])
                                ->sum('total_worked');

                            $working_days = round(floatval($working_days / 60), 2);
                        } else {
                            $working_days = EmployeeWorkingDay::where(['month_code' => date("m"), 'year' => date("Y"), 'user_id' => $user->id])->value('total_working_days');
                        }
                        return $working_days;
                    })
                    ->editColumn('gross', function ($user) {
                        // $result = $this->getGrossSalary($user);
                        $result = $this->getGrossSalary($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"));
                        return $result;
                    })
                    ->addColumn('net_salary', function ($user) {

                        if (getSetting('payroll_calculation') == 'hourly') {
                            $user         = User::where('id', $user->id)->first();
                            $working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [date("Y-m-1"), date("Y-m-t")])
                                ->sum('total_worked');
                            $working_days = $working_days / 60;

                            $basic = isset($user->salary) ? $user->salary->basic : 0;

                            $net_salary = $basic * $working_days;
                        } else {
                            $working_days = EmployeeWorkingDay::where(['month_code' => date("m"), 'year' => date("Y"), 'user_id' => $user->id])->value('total_working_days');
                            $net_salary   = $this->getNetSalaryAsPerAttendance_EXTRA($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"), $working_days);
                        }
                        $net_salary = round(floatval($net_salary), 2);
                        return $net_salary;
                    })
                    ->addColumn('total_net_salary', function ($user) {

                        $working_days = 0;
                        if (getSetting('payroll_calculation') == 'hourly') {
                            $user         = User::where('id', $user->id)->first();
                            $working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [date("Y-m-1"), date("Y-m-t")])
                                ->sum('total_worked');
                            $working_days = $working_days / 60;

                            $basic = isset($user->salary) ? $user->salary->basic : 0;

                            $total_net_salary = $basic * $working_days;
                        } else {
                            $working_days     = EmployeeWorkingDay::where(['month_code' => date("m"), 'year' => date("Y"), 'user_id' => $user->id])->value('total_working_days');
                            $total_net_salary = $this->getTotalNetSalary_EXTRA($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"), $working_days);
                        }
                        $total_net_salary = round(floatval($total_net_salary), 2);
                        return $total_net_salary;
                    })
                    ->addColumn('action', function ($user) {
                        $btn = createActionButton(route('backend.payroll.user.user-salaries.show', [$user, $user->id]), '', 'btn-success view-button', 'fa fa-arrow-right');
                        return $btn;
                    })
                    ->rawColumns(['action', 'id'])
                    ->make(true);
            }
        }

        return view('payroll::salary.index', compact('monthName'));
    }

    // protected function getGrossSalary($user)
    // {
    //     $current_month   = date('m');
    //     $current_year    = date('Y');
    //     $overtime_amount = 0;
    //     $total_allowance = 0;
    //     $total_deduction = 0;

    //     $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user);
    //     $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user);

    //     $basic_salary = $user->salary ? $user->salary->basic : 0;

    //     // Extra added 18-03-2024
    //     $fixed_entity_allowance = 0;
    //     if (isset($user->salary->fixed_allowances)) {
    //         $fixed_entity_allowance = json_decode($user->salary->fixed_allowances, true);
    //         $fixed_entity_allowance = is_array($fixed_entity_allowance) ? array_sum($fixed_entity_allowance) : 0;
    //     }

    //     $fixed_entity_deduction = 0;
    //     if (isset($user->salary->fixed_deductions)) {
    //         $fixed_entity_deduction = json_decode($user->salary->fixed_deductions, true);
    //         $fixed_entity_deduction = is_array($fixed_entity_deduction) ? array_sum($fixed_entity_deduction) : 0;
    //     }
    //     // End
    //     $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');

    //     $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;
    //     $total_allowance = $monthly_fixed['total_allowance'] + $fixed_entity_allowance;
    //     // $total = $basic_salary + $total_allowance + $overtime_amount;
    //     // Overtime commented as Per Gross Salary Formula
    //     $total = $basic_salary + $total_allowance;
    //     // Code Commented as Per Gross Salary Formula we not minus any deduction from total 12-09-2023
    //     // if($total > 0){
    //     //     $total = $total - $total_deduction;
    //     // }
    //     return $total ? $total : __trans('not_set');
    // }

    // protected function getNetSalaryAsPerAttendance($user)
    // {
    //     //Formula : Gross Salary / 31 * No Of Days Working
    //     $gross_salary = $this->getGrossSalary($user);
    //     //$total_working_days = $user->salary->total_working_days;
    //     //Rewirte code for calculate total working days 04-04-2024
    //     $total_working_days = $user->attendances->whereIn('status', [
    //         \Modules\Attendance\Enums\AttendanceStatus::Present,
    //         \Modules\Attendance\Enums\AttendanceStatus::Weekend,
    //     ])->count();
    //     $net_salary = ((float) $gross_salary / 31) * $total_working_days;
    //     return number_format((float) $net_salary, 2, '.', '');
    // }

    // protected function getTotalNetSalary($user)
    // {
    //     $current_month        = date('m');
    //     $current_year         = date('Y');
    //     $overtime_amount      = 0;
    //     $total_deduction      = 0;
    //     $fixed_allowance      = 0;
    //     $percentage_allowance = 0;
    //     $total_allowance      = 0;

    //     $attendanceBaseSalary = $this->getNetSalaryAsPerAttendance($user);
    //     $monthly_fixed        = $this->monthlyfixedExpensesCalculation($user);
    //     $monthly_not_fixed    = $this->monthlynotfixedExpensesCalculation($user);

    //     $fixed_allowance = UserSalaryAllowance::where([
    //         'user_id'                    => $user->id,
    //         'allowance_type'             => 'fixed',
    //         'month_code'                 => $current_month,
    //         'year'                       => $current_year,
    //         'is_fixed_for_current_month' => 1,
    //     ])->sum('amount');

    //     $percentage_allowance = UserSalaryAllowance::where([
    //         'user_id'                    => $user->id,
    //         'allowance_type'             => 'percentage',
    //         'month_code'                 => $current_month,
    //         'year'                       => $current_year,
    //         'is_fixed_for_current_month' => 1,
    //     ])->sum('percentage_amount');
    //     $total_allowance = $fixed_allowance + $percentage_allowance;

    //     $fixed_entity_deduction = isset($user->salary->fixed_deductions) ? json_decode($user->salary->fixed_deductions, true) : null;
    //     if ($fixed_entity_deduction == null) {
    //         $fixed_entity_deduction = 0;
    //     } else {
    //         $fixed_entity_deduction = array_sum($fixed_entity_deduction);
    //     }

    //     $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;
    //     $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');

    //     $total_net_salary = ($attendanceBaseSalary - $total_deduction) + $overtime_amount + $total_allowance;
    //     return number_format((float) $total_net_salary, 2, '.', '');
    // }
    /**
     * get monthly based allowance & deduction calculation.
     */

    // protected function monthlyfixedExpensesCalculation($user)
    // {
    //     $current_month        = date('m');
    //     $current_year         = date('Y');
    //     $fixed_allowance      = 0;
    //     $percentage_allowance = 0;
    //     $total_allowance      = 0;
    //     $fixed_deduction      = 0;
    //     $percentage_deduction = 0;
    //     $total_deduction      = 0;

    //     $fixed_deduction = UserDeduction::where([
    //         'user_id'                    => $user->id,
    //         'deduction_type'             => 'fixed',
    //         'month_code'                 => $current_month,
    //         'year'                       => $current_year,
    //         'is_fixed_for_current_month' => 1,
    //     ])->sum('amount');

    //     $percentage_deduction = UserDeduction::where([
    //         'user_id'                    => $user->id,
    //         'deduction_type'             => 'percentage',
    //         'month_code'                 => $current_month,
    //         'year'                       => $current_year,
    //         'is_fixed_for_current_month' => 1,
    //     ])->sum('percentage_amount');

    //     $total_allowance = $fixed_allowance + $percentage_allowance;
    //     $total_deduction = $fixed_deduction + $percentage_deduction;
    //     $result          = [
    //         'total_allowance' => $total_allowance,
    //         'total_deduction' => $total_deduction,
    //     ];

    //     return $result;
    // }
    /**
     * get allowance & deduction calculation which are not restricted by month or any condition.
     */
    // protected function monthlynotfixedExpensesCalculation($user)
    // {
    //     $fixed_allowance      = 0;
    //     $percentage_allowance = 0;
    //     $total_allowance      = 0;
    //     $fixed_deduction      = 0;
    //     $percentage_deduction = 0;
    //     $total_deduction      = 0;

    //     $fixed_allowance = UserSalaryAllowance::where([
    //         'user_id'                    => $user->id,
    //         'allowance_type'             => 'fixed',
    //         'is_fixed_for_current_month' => 0,
    //     ])->sum('amount');

    //     $percentage_allowance = UserSalaryAllowance::where([
    //         'user_id'                    => $user->id,
    //         'allowance_type'             => 'percentage',
    //         'is_fixed_for_current_month' => 0,
    //     ])->sum('percentage_amount');

    //     $fixed_deduction = UserDeduction::where([
    //         'user_id'                    => $user->id,
    //         'deduction_type'             => 'fixed',
    //         'is_fixed_for_current_month' => 0,
    //     ])->sum('amount');

    //     $percentage_deduction = UserDeduction::where([
    //         'user_id'                    => $user->id,
    //         'deduction_type'             => 'percentage',
    //         'is_fixed_for_current_month' => 0,
    //     ])->sum('percentage_amount');

    //     $total_allowance = $fixed_allowance + $percentage_allowance;
    //     $total_deduction = $fixed_deduction + $percentage_deduction;
    //     $result          = [
    //         'total_allowance' => $total_allowance,
    //         'total_deduction' => $total_deduction,
    //     ];

    //     return $result;
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create(User $user)
    {
        canPerform('Create Salary');
        $html     = view('payroll::salary.create', compact('user'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(User $user, Request $request)
    {
        $data = $request->validate([
            'basic' => 'required|numeric',
            //'total_working_days' => 'required|integer|between:0,31'
        ]);
        $response = getErrorResponse();
        try {
            $user->salary()->create($data);

            $response = getSuccessResponse(createFlashMessage('User Basic Salary', 'added'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the specified resource.
     */
    public function show(User $user, Request $request)
    {

        // canPerform('Manage Employee Salary');
        // $gross_salary = $this->getGrossSalary($user);
        $gross_salary = $this->getGrossSalary($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"));

        //$net_salary = $this->getNetSalaryAsPerAttendance($user);
        $net_salary        = $this->getNetSalaryAsPerAttendance($user, date("m"), date("Y"), date("Y-m-1"), date("Y-m-t"));
        $current_month     = date('m');
        $current_year      = date('Y');
        $allowance         = UserSalaryAllowance::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        $notfixedallowance = UserSalaryAllowance::where('user_id', $user->id)->where('is_fixed_for_current_month', 0)->get();
        $allowance         = $allowance->merge($notfixedallowance);

        //print_r($allowance); die();
        if ($request->ajax()) {
            return DataTables::of($allowance)
                ->editColumn('employee_name', function ($allowance) {
                    $employee_name = User::select('name')->where('id', $allowance->user_id)->first();
                    return $employee_name->name;
                })
                ->editColumn('type', function ($allowance) {
                    if ($allowance->allowance_type == 'fixed') {
                        return 'Fixed';
                    } else {
                        return 'Percentage';
                    }
                })->editColumn('amount', function ($allowance) {
                if ($allowance->allowance_type == 'percentage') {
                    return $allowance->amount . '% (' . $allowance->percentage_amount . ')';
                } else {
                    return $allowance->amount;
                }
            })->editColumn('monthly_fixed', function ($allowance) {
                if ($allowance->is_fixed_for_current_month == 1) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
                ->addColumn('action', function ($user) {
                    $btn  = '';
                    //print_r($user); die();
                    $btn .= createActionButton(route('backend.payroll.user.user-salaries.editallowance', [$user->user_id, $user->id]), '', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('backend.payroll.user.user-salaries.destroyallowance', $user), '', 'btn-danger action-button', 'fa fa-trash');
                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
        return view('payroll::salary.show', compact('user', 'allowance', 'gross_salary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user, UserSalary $userSalary)
    {

        canPerform('Edit Salary User');
        // $userSalaryIncrement = UserSalaryIncrement::query();
        $userSalaryIncrement = UserSalaryIncrement::where('user_id', $user->id)->get();
        $allowance           = SetAllowanceDeducation::get();

        // dd($userSalaryIncrement);
        $html     = view('payroll::salary.edit', compact('user', 'userSalary', 'userSalaryIncrement', 'allowance'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user, UserSalary $userSalary)
    {
        // dd($user);
        $data = $request->validate([
            'basic' => 'required|numeric',
            //'total_working_days' => 'required|integer|between:0,31',
        ]);
        $response = getErrorResponse();
        try {

            $allowances  = SetAllowanceDeducation::select('name', 'type')->get();
            $requestData = $request->all();

            $allowanceData  = [];
            $deducationData = [];
            foreach ($allowances as $allowance) {
                $allowanceName           = $allowance->name;
                $normalizedAllowanceName = str_replace(' ', '_', $allowanceName);
                if ($allowance->type == 1) {
                    if (array_key_exists($allowanceName, $requestData) || array_key_exists($normalizedAllowanceName, $requestData)) {
                        $allowanceData[$allowanceName] = isset($requestData[$normalizedAllowanceName]) ? $requestData[$normalizedAllowanceName] : $requestData[$allowanceName]; // Add matched allowance
                    }
                }
                if ($allowance->type == 2) {
                    if (array_key_exists($allowanceName, $requestData) || array_key_exists($normalizedAllowanceName, $requestData)) {
                        $deducationData[$allowanceName] = isset($requestData[$normalizedAllowanceName]) ? $requestData[$normalizedAllowanceName] : $requestData[$allowanceName]; // Add matched allowance
                    }
                }
            }
            $data['fixed_allowances'] = json_encode(array_merge([
                "housing_allowance"        => $request->housing_allowance,
                "transportation_allowance" => $request->transportation_allowance,
                "functional_allowance"     => $request->functional_allowance,
                "other_allowance"          => $request->other_allowance,
                "tips"                     => $request->tips,
            ], $allowanceData));

            $data['fixed_deductions'] = json_encode(array_merge([
                "advance_salary"  => $request->advance_salary,
                "loan_deduction"  => $request->loan_deduction,
                "other_deduction" => $request->other_deduction,
            ], $deducationData));

            $delete = UserSalaryIncrement::where('user_id', $user->id)->delete();
            if ($request->increment) {

                for ($i = 0; $i < count($request->increment); $i++) {

                    $userSalaryIncrement = UserSalaryIncrement::create([
                        'before_increment' => $request['before_increment'][$i] ? $request['before_increment'][$i] : $request->basic,
                        'increment'        => $request['increment'][$i],
                        'after_increment'  => $request['after_increment'][$i] ? $request['after_increment'][$i] : ($request->basic + $request['increment'][$i]),
                        'increment_date'   => $request->increment_date[$i],
                        'user_id'          => $user->id,
                    ]);
                }
                $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Increment Letter Generate', 'Increment created', 1);
            }
            if ($userSalary->basic != $request->basic) {
                $addtransaction = allTypeOfTransaction::create([
                    'user_id'          => $user->id,
                    'transaction_type' => 'salary',
                    'old_value'        => $userSalary->basic,
                    'update_value'     => ($request->basic - $userSalary->basic),
                    'new_value'        => $request->basic,
                    'transaction_date' => Carbon::now(),
                    'description'      => 'Some change in this user:' . $user->name . ' basic salary, from this user: ' . auth()->user()->name,
                ]);
            }
            $userSalary->update($data);
            $userSalary->save();
            $response = getSuccessResponse(createFlashMessage('User Basic Salary', 'update'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the allowance form for creating a new resource.
     */
    public function createallowance(User $user)
    {
        canPerform('Edit Salary User');
        $fixedAllowance = SetAllowanceDeducation::where('type', 1)->get();

        $html     = view('payroll::allowance.create', compact('user', 'fixedAllowance'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created allowance in storage.
     */
    public function storeallowance(User $user, Request $request)
    {
        $month_code = date('m');
        $year       = date('Y');
        if ($request->hidden_my != 'NA') {
            $str1       = $request->hidden_my;
            $str1       = str_replace('gS', '', $str1);
            $year       = substr($str1, -4);
            $month_code = substr($str1, 0, -4);
        }

        $data = $request->validate([
            'title'          => [
                'required',
                Rule::unique('user_salary_allowances')->where(function ($query) use ($user, $month_code, $year) {
                    return $query->where('user_id', $user->id)->where('month_code', $month_code)->where('year', $year);
                }),
            ],
            'amount'         => 'required|numeric',
            'allowance_type' => 'required|string',
            // 'travel_allowance' => 'required|numeric',
            // 'other_allowance' => 'required|numeric',
        ]);
        $response = getErrorResponse();
        try {
            if ($request->allowance_type == 'percentage') {
                if (isset($user->salary->basic)) {
                    if ($user->salary->other_allowance) {
                        $existing_allowance            = 0;
                        $existing_allowance            = $user->salary->other_allowance;
                        $calculated_amount             = (($user->salary->basic) * $request->amount) / 100;
                        $total_sum                     = $calculated_amount + $existing_allowance;
                        $user->salary->other_allowance = $total_sum;
                        $user->salary->update();
                        $data['amount']            = $request->amount;
                        $data['salary_id']         = $user->salary->id;
                        $data['percentage_amount'] = $calculated_amount;
                    } else {
                        $total                         = (($user->salary->basic) * $request->amount) / 100;
                        $user->salary->other_allowance = $total;
                        $user->salary->update();
                        $data['amount']            = $request->amount;
                        $data['salary_id']         = $user->salary->id;
                        $data['percentage_amount'] = $total;
                    }
                } else {
                    $data['amount'] = $request->amount;
                }
            } else if ($request->allowance_type == 'fixed') {
                if (isset($user->salary->basic)) {
                    if (isset($user->salary->other_allowance)) {
                        $existing_allowance            = 0;
                        $existing_allowance            = $user->salary->other_allowance;
                        $total_sum                     = $existing_allowance + $data['amount'];
                        $user->salary->other_allowance = $total_sum;
                        $user->salary->update();
                        $data['amount']    = $request->amount;
                        $data['salary_id'] = $user->salary->id;
                    } else {
                        $data['amount']                = $request->amount;
                        $user->salary->other_allowance = $data['amount'];
                        $user->salary->update();
                        $data['salary_id'] = $user->salary->id;
                    }
                } else {
                    $data['amount'] = $request->amount;
                }
            } else {
                '';
            }
            if (isset($request->is_fixed_for_current_month) == 'on') {
                $data['is_fixed_for_current_month'] = 1;
            } else {
                $data['is_fixed_for_current_month'] = 0;
            }
            if ($request->hidden_my != 'NA') {
                $str1               = $request->hidden_my;
                $str1               = str_replace('gS', '', $str1);
                $data['date']       = date('Y-m-d');
                $data['year']       = substr($str1, -4);
                $data['month_code'] = substr($str1, 0, -4);
            } else {
                $data['date']       = date('Y-m-d');
                $data['month_code'] = date('m');
                $data['year']       = date('Y');
            }
            $data['remark'] = $request->remark;
            if (isset($request->is_fixed_for_current_month) == 'on') {
                if (isset($request->monthCount) && $request->monthCount > 0) {
                    $repeatCount      = $request->monthCount;
                    $current_month_no = date('m');
                    for ($i = $current_month_no; $i <= $repeatCount; $i++) {
                        $data['month_code'] = $i;
                        $user->allowance()->create($data);
                    }
                } else {
                    $user->allowance()->create($data);
                }
            } else {
                $user->allowance()->create($data);
            }
            $response = getSuccessResponse(createFlashMessage('Allowace', 'added'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editallowance(User $user, UserSalaryAllowance $allowance)
    {
        canPerform('Edit Salary User');
        $fixedAllowance = SetAllowanceDeducation::where('type', 1)->get();
        $html           = view('payroll::allowance.edit', compact('user', 'allowance', 'fixedAllowance'))->render();
        $response       = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Update the specified resource in allowance.
     */
    public function updateallowance(Request $request, User $user, UserSalaryAllowance $allowance)
    {
        $data = $request->validate([
            'title'          => [
                'required',
                Rule::unique('user_salary_allowances')->where(function ($query) use ($user, $allowance) {
                    return $query->where('user_id', $user->id)->where('month_code', $allowance->month_code)->where('year', $allowance->year);
                })
                    ->ignore($allowance->id),
            ],
            'allowance_type' => 'required|string',
            'amount'         => 'required|numeric',
        ]);
        $response = getErrorResponse();
        try {
            if ($request->allowance_type == 'percentage') {
                if (isset($user->salary->basic)) {
                    if ($user->salary->other_allowance) {
                        $existing_allowance            = 0;
                        $existing_allowance            = $user->salary->other_allowance;
                        $existing_allowance            = $existing_allowance - $allowance->percentage_amount;
                        $calculated_amount             = (($user->salary->basic) * $request->amount) / 100;
                        $total_sum                     = $calculated_amount + $existing_allowance;
                        $user->salary->other_allowance = $total_sum;
                        $user->salary->update();
                        $data['amount']            = $request->amount;
                        $data['salary_id']         = $user->salary->id;
                        $data['percentage_amount'] = $calculated_amount;
                    } else {
                        $total                         = (($user->salary->basic) * $request->amount) / 100;
                        $user->salary->other_allowance = $total;
                        $user->salary->update();
                        $data['amount']            = $request->amount;
                        $data['salary_id']         = $user->salary->id;
                        $data['percentage_amount'] = $total;
                    }
                } else {
                    $data['amount'] = $request->amount;
                }
            } else if ($request->allowance_type == 'fixed') {
                if (isset($user->salary->basic)) {
                    if (isset($user->salary->other_allowance)) {
                        $existing_allowance            = 0;
                        $existing_allowance            = $user->salary->other_allowance;
                        $existing_allowance            = $existing_allowance - $allowance->amount;
                        $total_sum                     = $existing_allowance + $data['amount'];
                        $user->salary->other_allowance = $total_sum;
                        $user->salary->update();
                        $data['amount']            = $request->amount;
                        $data['salary_id']         = $user->salary->id;
                        $data['percentage_amount'] = 0;
                    } else {
                        $data['amount']                = $request->amount;
                        $user->salary->other_allowance = $data['amount'];
                        $user->salary->update();
                        $data['salary_id']         = $user->salary->id;
                        $data['percentage_amount'] = 0;
                    }
                } else {
                    $data['amount'] = $request->amount;
                }
            } else {
                '';
            }
            if (isset($request->is_fixed_for_current_month) == 'on') {
                $data['is_fixed_for_current_month'] = 1;
            } else {
                $data['is_fixed_for_current_month'] = 0;
            }
            $data['remark'] = $request->remark;

            $allowance->update($data);
            $allowance->save();
            $response = getSuccessResponse(createFlashMessage('User Salary Allowance', 'update'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the allowance resource from storage.
     */
    public function destroyallowance(UserSalaryAllowance $allowance)
    {
        canPerform('Edit Salary User');
        $response = getErrorResponse();
        try {
            $allowance->delete();
            $response = getSuccessResponse(createFlashMessage('Allowance', 'Deleted'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
    public function getCurrentTimezone()
    {
        // Get the current timezone
        $timezone = date_default_timezone_get();

        return response()->json(['timezone' => $timezone]);
    }

    public function hourlysalary(User $user, Request $request)
    {
        if ($request->ajax()) {
            $basic_salary = $user->salary ? $user->salary->basic : 0;
            $hourly_rate  = 0;
            if ($basic_salary == 0) {
                $data = [];
                return DataTables::of($data)->make(true);
            }
            $hourly_rate         = round($basic_salary / (31 * 9 * 5), 2);
            $total_hourly_salary = 0;
            //$total_hours_worked = $this->calculateTotalWorkedPerMonth('2023');
            $total_hours_worked = $this->calculateTotalWorkedPerDay(Carbon::now()->format('Y'), Carbon::now()->format('n'), $user->id);
            foreach ($total_hours_worked as $key => $value) {
                $total_hourly_salary += $hourly_rate * ($value->total_worked) / 60;
                $value->hourly_rate   = $hourly_rate;
                $value->total         = $hourly_rate * ($value->total_worked) / 60;
            }
            $data = $total_hours_worked;
            return DataTables::of($data)->make(true);
        }
        return view('payroll::salary.index');
    }

    public function calculateTotalWorkedPerMonth($start_year, $userId, $end_year = null, $month = null)
    {
        $startDate = Carbon::parse($start_year . '-01-01');
        if ($end_year == null) {
            $endDate = Carbon::now();
        } else {
            $endDate = Carbon::parse($end_year . '-12-31');
        }
        $totalWorkedPerMonth = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(total_worked) as total_worked');
        if ($month !== null) {
            $totalWorkedPerMonth->whereMonth('date', $month);
        }
        $totalWorkedPerMonth = $totalWorkedPerMonth->groupBy('year', 'month')
            ->get();

        return $totalWorkedPerMonth;
    }

    public function calculateTotalWorkedPerDay($start_year, $start_month, $userId, $end_year = null, $end_month = null)
    {
        $startDate = Carbon::parse($start_year . '-' . $start_month . '-01');

        if ($end_year == null) {
            $endDate = Carbon::now();
        } else {
            if ($end_month == null) {
                $end_month = Carbon::now()->format('n'); // Default to December if end_month is not provided
            }
            $endDate = Carbon::parse($end_year . '-' . $end_month . '-01')->endOfMonth();
        }
        $totalWorkedPerDay = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('date, SUM(total_worked) as total_worked')
            ->groupBy('date')
            ->get();

        return $totalWorkedPerDay;
    }
    /**
     * Show the overtime form for creating a new resource.
     */
    public function createovertime(User $user)
    {
        canPerform('Edit Salary User');
        $html     = view('payroll::overtime.create', compact('user'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created overtime in storage.
     */
    public function storeovertime(User $user, Request $request)
    {
        $data = $request->validate([
            'overtime_type' => 'required|string',
            'rate_per_hour' => 'required|numeric',
            'hours'         => 'required|numeric',
        ]);
        $response = getErrorResponse();

        if ($user->salary->basic == 0) {
            $response = getErrorResponse($message = "User Basic Salary is not set | Please Add", $error = null);
            return response()->json($response);
        }
        try {
            $rate_per_hour = 0;
            $overtime_type = $request->overtime_type;
            $hours         = $request->hours;
            switch ($overtime_type) {
                case "ot1":
                    $rate_per_hour = 1.25;
                    break;
                case "ot2":
                    $rate_per_hour = 1.25;
                    break;
                case "ot3":
                    $rate_per_hour = 1.50;
                    break;
                case "ot4":
                    $rate_per_hour = 1.50;
                    break;
                default:
                    $rate_per_hour = 0;
            }
            // Formula for get Overtime Amount
            // Basic/30/8 * rate_per_hour * hours
            //$data['calculated_amount'] = $rate_per_hour * $hours;
            $totalDays    = date('t');
            $today        = Carbon::today()->toDateString();
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
            $data['calculated_amount'] = round(($user->salary->basic / $totalDays / $working_hours) * $rate_per_hour * $hours, 2);
            if ($request->hidden_my != 'NA') {
                $str1               = $request->hidden_my;
                $str1               = str_replace('gS', '', $str1);
                $data['date']       = date('Y-m-d');
                $data['year']       = substr($str1, -4);
                $data['month_code'] = substr($str1, 0, -4);
            } else {
                $data['date']       = date('Y-m-d');
                $data['month_code'] = date('m');
                $data['year']       = date('Y');
            }
            $user->overtime()->create($data);
            $response = getSuccessResponse(createFlashMessage('Overtime', 'added'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the Overtime.
     */
    public function userovertimelist(User $user, Request $request)
    {
        // canPerform('Manage Employee Overtime');
        $current_month = date('m');
        $current_year  = date('Y');
        $overtime      = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        if ($request->ajax()) {
            return DataTables::of($overtime)
                ->editColumn('employee_name', function ($overtime) {
                    $employee_name = User::select('name')->where('id', $overtime->user_id)->first();
                    return $employee_name->name;
                })
                ->addColumn('action', function ($user) {
                    $btn  = '';
                    $btn .= createActionButton(route('backend.payroll.user.user-salaries.editovertime', [$user->user_id, $user->id]), '', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('backend.payroll.user.user-salaries.destroyovertime', $user), '', 'btn-danger action-button', 'fa fa-trash');
                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
        return view('payroll::salary.show', compact('user', 'overtime'));
    }

    /**
     * Show the form for editing the specified overtime.
     */
    public function editovertime(User $user, Userovertime $overtime)
    {
        canPerform('Edit Salary User');

        $html     = view('payroll::overtime.edit', compact('user', 'overtime'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Update the specified resource in overtime.
     */
    public function updateovertime(Request $request, User $user, UserOvertime $overtime)
    {
        $data = $request->validate([
            'overtime_type' => 'required|string',
            'rate_per_hour' => 'required|numeric',
            'hours'         => 'required|numeric',
        ]);
        $response = getErrorResponse();

        if ($user->salary->basic == 0) {
            $response = getErrorResponse($message = "User Basic Salary is not set | Please Add", $error = null);
            return response()->json($response);
        }
        try {
            $rate_per_hour = 0;
            $overtime_type = $request->overtime_type;
            $hours         = $request->hours;
            switch ($overtime_type) {
                case "ot1":
                    $rate_per_hour = 1.25;
                    break;
                case "ot2":
                    $rate_per_hour = 1.25;
                    break;
                case "ot3":
                    $rate_per_hour = 1.50;
                    break;
                case "ot4":
                    $rate_per_hour = 1.50;
                    break;
                default:
                    $rate_per_hour = 0;
            }

            // Formula for get Overtime Amount
            // Basic/30/8 * rate_per_hour * hours
            //$data['calculated_amount'] = $rate_per_hour * $hours;
            $today        = Carbon::today()->toDateString();
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
            $totalDays                 = date('t');
            $data['calculated_amount'] = round(($user->salary->basic / $totalDays / $working_hours) * $rate_per_hour * $hours, 2);
            $overtime->update($data);
            $response = getSuccessResponse(createFlashMessage('Overtime', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the overtime resource from storage.
     */
    public function destroyovertime(UserOvertime $overtime)
    {
        canPerform('Edit Salary User');
        $response = getErrorResponse();
        try {
            $overtime->delete();
            $response = getSuccessResponse(createFlashMessage('Overtime', 'Deleted'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the overtime form for creating a new resource.
     */
    public function creatededuction(User $user)
    {
        canPerform('Edit Salary User');
        $fixedDeduction = SetAllowanceDeducation::where('type', 2)->get();
        $html           = view('payroll::deduction.create', compact('user', 'fixedDeduction'))->render();
        $response       = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created overtime in storage.
     */
    public function storededuction(User $user, Request $request)
    {
        $data = $request->validate([
            'title'          => [
                'required',
                Rule::unique('user_deductions')->where(function ($query) use ($user, $request) {
                    return $query->where('user_id', $user->id)->where('month_code', $request->monthCount)->where('year', date('Y'));
                }),
            ],
            'amount'         => 'required|numeric',
            'deduction_type' => 'required|string',
        ]);
        $response = getErrorResponse();
        try {
            if ($request->deduction_type == 'percentage') {
                if (isset($user->salary->basic)) {
                    $total                     = (($user->salary->basic) * $request->amount) / 100;
                    $data['amount']            = $request->amount;
                    $data['salary_id']         = $user->salary->id;
                    $data['percentage_amount'] = $total;
                } else {
                    $data['amount'] = $request->amount;
                }
            } else if ($request->deduction_type == 'fixed') {
                $data['amount']    = $request->amount;
                $data['salary_id'] = $user->salary->id;
            } else {
                '';
            }
            if (isset($request->is_fixed_for_current_month) == 'on') {
                $data['is_fixed_for_current_month'] = 1;
            } else {
                $data['is_fixed_for_current_month'] = 0;
            }
            if ($request->hidden_my != 'NA') {
                $str1               = $request->hidden_my;
                $str1               = str_replace('gS', '', $str1);
                $data['date']       = date('Y-m-d');
                $data['year']       = substr($str1, -4);
                $data['month_code'] = substr($str1, 0, -4);
            } else {
                $data['date']       = date('Y-m-d');
                $data['month_code'] = date('m');
                $data['year']       = date('Y');
            }
            $data['remark'] = $request->remark;
            // $user->deduction()->create($data);
            if (isset($request->is_fixed_for_current_month) == 'on') {
                if (isset($request->monthCount) && $request->monthCount > 0) {
                    $repeatCount      = $request->monthCount;
                    $current_month_no = date('m');
                    for ($i = $current_month_no; $i <= $repeatCount; $i++) {
                        $data['month_code'] = $i;
                        $user->deduction()->create($data);
                    }
                } else {
                    $user->deduction()->create($data);
                }
            } else {
                $user->deduction()->create($data);
            }
            $response = getSuccessResponse(createFlashMessage('Deduction', 'added'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the Overtime.
     */
    public function userdeductionlist(User $user, Request $request)
    {
        // canPerform('Manage Employee Deduction');
        $current_month     = date('m');
        $current_year      = date('Y');
        $deduction         = UserDeduction::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        $notfixeddeduction = UserDeduction::where('user_id', $user->id)->where('is_fixed_for_current_month', 0)->get();
        $deduction         = $deduction->merge($notfixeddeduction);
        if ($request->ajax()) {
            return DataTables::of($deduction)
                ->editColumn('employee_name', function ($deduction) {
                    $employee_name = User::select('name')->where('id', $deduction->user_id)->first();
                    return $employee_name->name;
                })
                ->editColumn('deduction_type', function ($deduction) {
                    if ($deduction->deduction_type == 'fixed') {
                        return 'Fixed';
                    } else {
                        return 'Percentage';
                    }
                })->editColumn('amount', function ($deduction) {
                if ($deduction->deduction_type == 'percentage') {
                    return $deduction->amount . '% (' . $deduction->percentage_amount . ')';
                } else {
                    return $deduction->amount;
                }
            })->editColumn('monthly_fixed', function ($deduction) {
                if ($deduction->is_fixed_for_current_month == 1) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
                ->addColumn('action', function ($deduction) {
                    $btn  = '';
                    $btn .= createActionButton(route('backend.payroll.user.user-salaries.editdeduction', [$deduction->user_id, $deduction->id]), '', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('backend.payroll.user.user-salaries.destroydeduction', $deduction), '', 'btn-danger action-button', 'fa fa-trash');
                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
        return view('payroll::salary.show', compact('user', 'overtime'));
    }

    /**
     * Show the form for editing the specified overtime.
     */
    public function editdeduction(User $user, UserDeduction $deduction)
    {
        canPerform('Edit Salary User');
        $fixedDeduction = SetAllowanceDeducation::where('type', 2)->get();
        $html           = view('payroll::deduction.edit', compact('user', 'deduction', 'fixedDeduction'))->render();
        $response       = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Update the specified resource in overtime.
     */
    public function updatededuction(Request $request, User $user, UserDeduction $deduction)
    {
        $data = $request->validate([
            'deduction_type' => 'required|string',
            'amount'         => 'required|numeric',
            'title'          => [
                'required',
                Rule::unique('user_deductions')->where(function ($query) use ($user, $deduction) {
                    return $query->where('user_id', $user->id)->where('month_code', $deduction->month_code)->where('year', $deduction->year);
                })
                    ->ignore($deduction->id),
            ],
        ]);
        $response = getErrorResponse();
        try {
            if ($request->deduction_type == 'percentage') {
                if (isset($user->salary->basic)) {
                    $total                     = (($user->salary->basic) * $request->amount) / 100;
                    $data['amount']            = $request->amount;
                    $data['salary_id']         = $user->salary->id;
                    $data['percentage_amount'] = $total;
                } else {
                    $data['amount'] = $request->amount;
                }
            } else if ($request->deduction_type == 'fixed') {
                $data['amount']    = $request->amount;
                $data['salary_id'] = $user->salary->id;
            } else {
                '';
            }
            if (isset($request->is_fixed_for_current_month) == 'on') {
                $data['is_fixed_for_current_month'] = 1;
            } else {
                $data['is_fixed_for_current_month'] = 0;
            }
            $data['remark'] = $request->remark;
            $deduction->update($data);
            $response = getSuccessResponse(createFlashMessage('Deduction', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the overtime resource from storage.
     */
    public function destroydeduction(UserDeduction $deduction)
    {
        canPerform('Edit Salary User');
        $response = getErrorResponse();
        try {
            $deduction->delete();
            $response = getSuccessResponse(createFlashMessage('Deduction', 'Deleted'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function getUserSalary()
    {
        $user         = User::where('id', auth()->id())->first();
        $gross_salary = $this->getGrossSalary($user);
        return view('payroll::salary.employee-salary', compact('user', 'gross_salary'));
    }

    public function getUserSalaryAllowance(Request $request)
    {
        $user              = User::where('id', auth()->id())->first();
        $gross_salary      = $this->getGrossSalary($user);
        $current_month     = date('m');
        $current_year      = date('Y');
        $allowance         = UserSalaryAllowance::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        $notfixedallowance = UserSalaryAllowance::where('user_id', $user->id)->where('is_fixed_for_current_month', 0)->get();
        $allowance         = $allowance->merge($notfixedallowance);
        if ($request->ajax()) {
            return DataTables::of($allowance)
                ->editColumn('employee_name', function ($allowance) {
                    $employee_name = User::select('name')->where('id', $allowance->user_id)->first();
                    return $employee_name->name;
                })
                ->editColumn('type', function ($allowance) {
                    if ($allowance->allowance_type == 'fixed') {
                        return 'Fixed';
                    } else {
                        return 'Percentage';
                    }
                })->editColumn('amount', function ($allowance) {
                if ($allowance->allowance_type == 'percentage') {
                    return $allowance->amount . '% (' . $allowance->percentage_amount . ')';
                } else {
                    return $allowance->amount;
                }
            })->editColumn('monthly_fixed', function ($allowance) {
                if ($allowance->is_fixed_for_current_month == 1) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
                ->make(true);
        }
    }

    public function getUserSalaryOvertime(Request $request)
    {
        $user          = User::where('id', auth()->id())->first();
        $current_month = date('m');
        $current_year  = date('Y');
        $overtime      = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        if ($request->ajax()) {
            return DataTables::of($overtime)
                ->editColumn('employee_name', function ($overtime) {
                    $employee_name = User::select('name')->where('id', $overtime->user_id)->first();
                    return $employee_name->name;
                })
                ->make(true);
        }
    }

    public function getUserSalaryDeduction(Request $request)
    {
        $user              = User::where('id', auth()->id())->first();
        $current_month     = date('m');
        $current_year      = date('Y');
        $deduction         = UserDeduction::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        $notfixeddeduction = UserDeduction::where('user_id', $user->id)->where('is_fixed_for_current_month', 0)->get();
        $deduction         = $deduction->merge($notfixeddeduction);
        if ($request->ajax()) {
            return DataTables::of($deduction)
                ->editColumn('employee_name', function ($deduction) {
                    $employee_name = User::select('name')->where('id', $deduction->user_id)->first();
                    return $employee_name->name;
                })
                ->editColumn('deduction_type', function ($deduction) {
                    if ($deduction->deduction_type == 'fixed') {
                        return 'Fixed';
                    } else {
                        return 'Percentage';
                    }
                })->editColumn('amount', function ($deduction) {
                if ($deduction->deduction_type == 'percentage') {
                    return $deduction->amount . '% (' . $deduction->percentage_amount . ')';
                } else {
                    return $deduction->amount;
                }
            })->editColumn('monthly_fixed', function ($deduction) {
                if ($deduction->is_fixed_for_current_month == 1) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
                ->make(true);
        }
    }

    public function set_allowance_deducation(Request $request)
    {

        canPerform('General Settings');
        $allowanceDeducation = SetAllowanceDeducation::orderBy('id', 'desc')->get();
        return view('payroll::allowance_deducation.index', compact('allowanceDeducation'));
    }

    public function set_allowance()
    {

        canPerform('General Settings');
        $html     = view('payroll::allowance_deducation.create')->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function update_allowance($id)
    {

        canPerform('General Settings');
        $allowance = SetAllowanceDeducation::where('id', $id)->first();
        $html      = view('payroll::allowance_deducation.update', compact('allowance'))->render();
        $response  = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function delete_allowance($id)
    {
        canPerform('General Settings');
        $allowance = SetAllowanceDeducation::where('id', $id)->first();

        if ($allowance) {
            $allowance->delete();
            $response = getSuccessResponse('Allowance remove successfully.');
            return response()->json($response);
        } else {
            $response = getSuccessResponse('No data found!');
            return response()->json($response);
        }
    }

    //
    public function set_deducation()
    {

        canPerform('General Settings');
        $html     = view('payroll::allowance_deducation.deducationCreate')->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function update_deducation($id)
    {

        canPerform('General Settings');
        $deducation = SetAllowanceDeducation::where('id', $id)->first();
        $html       = view('payroll::allowance_deducation.deducationUpdate', compact('deducation'))->render();
        $response   = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function delete_deducation($id)
    {
        canPerform('General Settings');
        $deducation = SetAllowanceDeducation::where('id', $id)->first();

        if ($deducation) {
            $deducation->delete();
            $response = getSuccessResponse('Deducation remove successfully.');
            return response()->json($response);
        } else {
            $response = getSuccessResponse('No data found!');
            return response()->json($response);
        }
    }

    public function save_allowance_deducation(Request $request)
    {

        canPerform('General Settings');
        $data = $request->validate([
            'name' => [
                'required',
                Rule::unique('set_allowance_deducations')->where(function ($query) use ($request) {
                    return $query->where('type', $request->type);
                }),
            ],
            //'amount' => 'required|integer|min:0',
        ]);

        $allow = SetAllowanceDeducation::create([
            'type'   => $request->type,
            'name'   => $request->name,
            'amount' => 0,
        ]);

        if ($request->type == 1) {
            $response = getSuccessResponse('Allowance created successfully.');
        } else {
            $response = getSuccessResponse('Deducation created successfully.');
        }
        return response()->json($response);
    }

    public function update_allowance_deducation(Request $request, $id)
    {

        canPerform('General Settings');
        $data = $request->validate([
            'name' => [
                'required',
                Rule::unique('set_allowance_deducations')->where(function ($query) use ($request) {
                    return $query->where('type', $request->type);
                })->ignore($id),
            ],
            //'amount' => 'required|integer|min:0',
        ]);
        $allowance = SetAllowanceDeducation::find($id);

        $allowance->update([
            'type'   => $request->type,
            'name'   => $request->name,
            'amount' => 0,
        ]);

        if ($request->type == 1) {
            $response = getSuccessResponse('Allowance updated successfully.');
        } else {
            $response = getSuccessResponse('Deducation updated successfully.');
        }
        return response()->json($response);
    }

    public function add_bulk_allowance_deduction(Request $request)
    {

        canPerform('General Settings');
        $users        = [];
        $departmentId = '';
        $searchEmp    = '';
        $search       = false;
        $userslist    = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->get();
        if ($request->post()) {
            $search       = true;
            $departmentId = $request->department_id;
            $searchEmp    = $request->search_emp;
            $query        = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                });

            if ($departmentId !== 'all') {
                $query->where('department_id', $departmentId);
            }

            if (! empty($searchEmp)) {
                $query->where('id', $searchEmp);
                // $query->where('name', 'like', '%' . $searchEmp . '%');
            }

            $users = $query->get();
        }

        $allowanceDeducation = SetAllowanceDeducation::orderBy('id', 'desc')->get();
        return view('payroll::bulk_allowance_deducation.index', compact('allowanceDeducation', 'users', 'departmentId', 'search', 'searchEmp', 'userslist'));
    }

    public function store_bulk_allowance_deduction(Request $request)
    {

        foreach ($request->emp_id as $key => $userid) {
            $user = User::where('id', $userid)->first();

            if ($user) {
                $altitlekey  = 'new_al_title_' . $user->id;
                $alamountkey = 'new_al_amount_' . $user->id;
                if ($request->has($altitlekey) && ! empty($request->input($altitlekey))) {

                    $salaryid = UserSalary::where('user_id', $userid)->first();
                    foreach ($request->input($altitlekey) as $key1 => $addnew) {
                        $allowance = UserSalaryAllowance::where('title', $addnew)->where([['user_id', $userid], ['month_code', date('m')], ['year', date('Y')]])->first();
                        if ($allowance) {
                            $allowance->update([
                                'amount' => $request->input($alamountkey)[$key1],
                            ]);
                        } else {
                            if (isset($addnew) && ! empty($addnew)) {

                                $allowance = UserSalaryAllowance::create([
                                    'title'                      => $addnew,
                                    'amount'                     => isset($request->input($alamountkey)[$key1]) ? $request->input($alamountkey)[$key1] : 0,
                                    'user_id'                    => $userid,
                                    'allowance_type'             => 'fixed',
                                    'salary_id'                  => $salaryid ? $salaryid->id : 0,
                                    'percentage_amount'          => 0.00,
                                    'date'                       => now()->toDateString(),
                                    'month_code'                 => date('m'),
                                    'year'                       => date('Y'),
                                    'is_fixed_for_current_month' => 1,
                                ]);
                            }
                        }
                    }
                }

                $dedutitlekey  = 'new_dedu_title_' . $user->id;
                $deduamountkey = 'new_dedu_amount_' . $user->id;
                if ($request->has($dedutitlekey) && ! empty($request->input($dedutitlekey))) {
                    $salaryid = UserSalary::where('user_id', $userid)->first();
                    foreach ($request->input($dedutitlekey) as $key2 => $addnew) {
                        $deduction = UserDeduction::where('title', $addnew)->where([['user_id', $userid], ['month_code', date('m')], ['year', date('Y')]])->first();
                        if ($deduction) {
                            $deduction->update([
                                'amount' => $request->input($deduamountkey)[$key2],
                            ]);
                        } else {
                            if (isset($addnew) && ! empty($addnew)) {

                                $dedu = UserDeduction::create([
                                    'title'                      => $addnew,
                                    'amount'                     => isset($request->input($deduamountkey)[$key2]) ? $request->input($deduamountkey)[$key2] : 0,
                                    'user_id'                    => $userid,
                                    'deduction_type'             => 'fixed',
                                    'salary_id'                  => $salaryid ? $salaryid->id : 0,
                                    'percentage_amount'          => 0.00,
                                    'date'                       => now()->toDateString(),
                                    'month_code'                 => date('m'),
                                    'year'                       => date('Y'),
                                    'is_fixed_for_current_month' => 1,
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return redirect('payroll/set-allowance-deduction')->with('store', 'Allowance & Deduction data updated successfully.');
    }

    public function showEMIAllowance(User $user, Request $request)
    {

        $emi_allowance = EMIAllowance::with('emiData')->where('user_id', $user->id)->get();

        if ($request->ajax()) {
            return DataTables::of($emi_allowance)
                ->editColumn('employee_name', function ($allowance) {
                    $employee_name = User::select('name')->where('id', $allowance->user_id)->first();
                    return $employee_name->name;
                })
                ->editColumn('title', function ($allowance) {
                    return $allowance->title;
                })
                ->editColumn('total_amount', function ($allowance) {
                    return $allowance->total_amount;
                })
                ->editColumn('total_emi', function ($allowance) {
                    $total_month = EMIAllowanceData::where('emi_id', $allowance->id)->count();
                    return $total_month;
                })
                ->editColumn('create_at', function ($allowance) {
                    return $allowance->created_at->format('d M Y');
                })
                ->addColumn('action', function ($allowance) {
                    $btn  = '';
                    $btn .= createActionButton(route('backend.payroll.editEMIAllowance', $allowance->id), '', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('backend.payroll.destroyEMIAllowance', $allowance->id), '', 'btn-danger action-button', 'fa fa-trash');

                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
    }

    public function createEMIAllowance(User $user, $monthyear, Request $request)
    {
        // canPerform('Edit Salary User');
        $fixedAllowance = SetAllowanceDeducation::where('type', 1)->get();
        $html           = view('payroll::emi_allowance.create', compact('user', 'monthyear', 'fixedAllowance'))->render();
        $response       = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function storeEMIAllowance($user, $monthyear, Request $request)
    {

        $request->validate([
            'allowance'    => 'required',
            'total_amount' => 'required|numeric|min:0',
            'month_year'   => 'required|array',
            'month_amount' => 'required|array',
        ]);

        $month_code = date('m');
        $year       = date('Y');
        if ($request->hidden_my != 'NA') {
            $str1       = $request->hidden_my;
            $str1       = str_replace('gS', '', $str1);
            $year       = substr($str1, -4);
            $month_code = substr($str1, 0, -4);
        }

        $user        = User::where('id', $user)->firstOrFail();
        $allowance   = SetAllowanceDeducation::where('id', $request->allowance)->firstOrFail();
        $existingEMI = EMIAllowance::where('user_id', $user->id)
            ->where('allowance_id', $allowance->id)
            ->where('fully_paid', 0)
            ->first();
        if ($existingEMI) {
            return response()->json([
                'success' => false,
                'message' => 'allowance for this user, already exists or not fully paid.',
            ]);
        }
        $emi_allo = EMIAllowance::create([
            'user_id'      => $user->id,
            'allowance_id' => $allowance->id,
            'title'        => $allowance->name,
            'total_amount' => $request->total_amount,
            'remark'       => $request->remark,
            'created_by'   => auth()->id(),
        ]);

        foreach ($request->month_year as $index => $monthYear) {
            if (! $monthYear) {
                continue;
            }
            [$month, $year] = explode('-', $monthYear);
            EMIAllowanceData::create([
                'emi_id'       => $emi_allo->id,
                'month'        => (int) $month,
                'year'         => (int) $year,
                'month_amount' => $request->month_amount[$index],
                'created_by'   => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'EMI allowance saved successfully',
        ]);
    }

    public function editEMIAllowance($id, Request $request)
    {
        // canPerform('Edit Salary User');
        $emi_allowance    = EMIAllowance::where('id', $id)->firstOrFail();
        $emiAllowancedata = EMIAllowanceData::where('emi_id', $emi_allowance->id)->orderBy('year', 'asc')->orderBy('month', 'asc')->get();
        $fixedAllowance   = SetAllowanceDeducation::where('type', 1)->get();

        $user     = User::where('id', $emi_allowance->user_id)->firstOrFail();
        $html     = view('payroll::emi_allowance.edit', compact('emiAllowancedata', 'emi_allowance', 'fixedAllowance', 'user'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function updateEMIAllowance($id, Request $request)
    {

        // dd($request->all());
        $request->validate([
            'allowance'    => 'required',
            'total_amount' => 'required|numeric|min:0',
            'month_year'   => 'required|array',
            'month_amount' => 'required|array',
        ]);

        $allowance     = SetAllowanceDeducation::where('id', $request->allowance)->firstOrFail();
        $emi_allowance = EMIAllowance::where('id', $id)->firstOrFail();
        $emi_allowance->update([
            'allowance_id' => $allowance->id,
            'title'        => $allowance->name,
            'total_amount' => $request->total_amount,
            'created_by'   => auth()->id(),
            'remark'       => $request->remark,
        ]);

        // remove not paid EMI allowance data
        $emiAllowanceData = EMIAllowanceData::where('emi_id', $emi_allowance->id)->where('is_paid', 0)->delete();

        foreach ($request->month_year as $index => $monthYear) {
            if (! $monthYear) {
                continue;
            }
            [$month, $year] = explode('-', $monthYear);
            EMIAllowanceData::create([
                'emi_id'       => $emi_allowance->id,
                'month'        => (int) $month,
                'year'         => (int) $year,
                'month_amount' => $request->month_amount[$index],
                'created_by'   => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'EMI allowance updated successfully',
        ]);
    }

    public function destroyEMIAllowance($id)
    {
        $emi_allowance    = EMIAllowance::where('id', $id)->firstOrFail();
        $emiAllowanceData = EMIAllowanceData::where('emi_id', $emi_allowance->id)->where('is_paid', 0)->delete();

        $emi_allowance->delete();

        return response()->json([
            'success' => true,
            'message' => 'EMI allowance deleted successfully',
        ]);
    }

    public function showEMIDeduction(User $user, Request $request)
    {

        $emi_deduction = EMIDeduction::with('emiData')->where('user_id', $user->id)->get();

        if ($request->ajax()) {
            return DataTables::of($emi_deduction)
                ->editColumn('employee_name', function ($deduction) {
                    $employee_name = User::select('name')->where('id', $deduction->user_id)->first();
                    return $employee_name->name;
                })
                ->editColumn('title', function ($deduction) {
                    return $deduction->title;
                })
                ->editColumn('total_amount', function ($deduction) {
                    return $deduction->total_amount;
                })
                ->editColumn('total_emi', function ($deduction) {
                    $total_month = EMIDeductionData::where('emi_id', $deduction->id)->count();
                    return $total_month;
                })
                ->editColumn('create_at', function ($deduction) {
                    return $deduction->created_at->format('d M Y');
                })
                ->addColumn('action', function ($deduction) {
                    $btn  = '';
                    $btn .= createActionButton(route('backend.payroll.editEMIDeduction', $deduction->id), '', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('backend.payroll.destroyEMIDeduction', $deduction->id), '', 'btn-danger action-button', 'fa fa-trash');

                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
    }

    public function createEMIDeduction(User $user, $monthyear, Request $request)
    {
        // canPerform('Edit Salary User');
        $fixedDeduction = SetAllowanceDeducation::where('type', 2)->get();
        $html           = view('payroll::emi_deduction.create', compact('user', 'monthyear', 'fixedDeduction'))->render();
        $response       = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function storeEMIDeduction($user, $monthyear, Request $request)
    {

        $request->validate([
            'deduction'    => 'required',
            'total_amount' => 'required|numeric|min:0',
            'month_year'   => 'required|array',
            'month_amount' => 'required|array',
        ]);

        $month_code = date('m');
        $year       = date('Y');
        if ($request->hidden_my != 'NA') {
            $str1       = $request->hidden_my;
            $str1       = str_replace('gS', '', $str1);
            $year       = substr($str1, -4);
            $month_code = substr($str1, 0, -4);
        }

        $user        = User::where('id', $user)->firstOrFail();
        $deduction   = SetAllowanceDeducation::where('id', $request->deduction)->firstOrFail();
        $existingEMI = EMIDeduction::where('user_id', $user->id)
            ->where('deduction_id', $deduction->id)
            ->where('fully_paid', 0)
            ->first();
        if ($existingEMI) {
            return response()->json([
                'success' => false,
                'message' => 'deduction for this user, already exists or not fully paid.',
            ]);
        }
        $emi_deduction = EMIDeduction::create([
            'user_id'      => $user->id,
            'deduction_id' => $deduction->id,
            'title'        => $deduction->name,
            'total_amount' => $request->total_amount,
            'created_by'   => auth()->id(),
            'remark'       => $request->remark,
        ]);

        foreach ($request->month_year as $index => $monthYear) {
            if (! $monthYear) {
                continue;
            }
            [$month, $year] = explode('-', $monthYear);
            EMIDeductionData::create([
                'emi_id'       => $emi_deduction->id,
                'month'        => (int) $month,
                'year'         => (int) $year,
                'month_amount' => $request->month_amount[$index],
                'created_by'   => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'EMI deduction saved successfully',
        ]);
    }

    public function editEMIDeduction($id, Request $request)
    {
        // canPerform('Edit Salary User');
        $emi_deduction    = EMIDeduction::where('id', $id)->firstOrFail();
        $emiDeductionData = EMIDeductionData::where('emi_id', $emi_deduction->id)->orderBy('year', 'asc')->orderBy('month', 'asc')->get();
        $fixedDeduction   = SetAllowanceDeducation::where('type', 2)->get();

        $user     = User::where('id', $emi_deduction->user_id)->firstOrFail();
        $html     = view('payroll::emi_deduction.edit', compact('emiDeductionData', 'emi_deduction', 'fixedDeduction', 'user'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    public function updateEMIDeduction($id, Request $request)
    {

        $request->validate([
            'deduction'    => 'required',
            'total_amount' => 'required|numeric|min:0',
            'month_year'   => 'required|array',
            'month_amount' => 'required|array',
        ]);

        $deduction     = SetAllowanceDeducation::where('id', $request->deduction)->firstOrFail();
        $emi_deduction = EMIDeduction::where('id', $id)->firstOrFail();
        $emi_deduction->update([
            'deduction_id' => $deduction->id,
            'title'        => $deduction->name,
            'total_amount' => $request->total_amount,
            'created_by'   => auth()->id(),
            'remark'       => $request->remark,
        ]);

        // remove not paid EMI deduction data
        $emiDeductionData = EMIDeductionData::where('emi_id', $emi_deduction->id)->where('is_paid', 0)->delete();

        foreach ($request->month_year as $index => $monthYear) {
            if (! $monthYear) {
                continue;
            }
            [$month, $year] = explode('-', $monthYear);
            EMIDeductionData::create([
                'emi_id'       => $emi_deduction->id,
                'month'        => (int) $month,
                'year'         => (int) $year,
                'month_amount' => $request->month_amount[$index],
                'created_by'   => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'EMI deduction updated successfully',
        ]);
    }

    public function destroyEMIDeduction($id)
    {
        $emi_deduction    = EMIDeduction::where('id', $id)->firstOrFail();
        $emiDeductionData = EMIDeductionData::where('emi_id', $emi_deduction->id)->where('is_paid', 0)->delete();

        $emi_deduction->delete();

        return response()->json([
            'success' => true,
            'message' => 'EMI deduction deleted successfully',
        ]);
    }
}
