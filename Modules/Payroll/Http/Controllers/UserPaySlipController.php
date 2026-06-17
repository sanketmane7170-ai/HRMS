<?php
namespace Modules\Payroll\Http\Controllers;

use App\Exports\ExcelExport;
use App\Exports\SalaryInformationExport;
use App\Exports\UsersPaySlipExport;
use App\Models\AdvanceRequestHistory;
use App\Models\allTypeOfTransaction;
use App\Models\EMIAllowance;
use App\Models\EMIAllowanceData;
use App\Models\EMIDeduction;
use App\Models\EMIDeductionData;
use App\Models\EmployeeWorkingDay;
use App\Models\offBoarding;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDocument;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\CompanyDocument\Entities\CompanyDocument;
use Modules\Document\Entities\DocumentType;
use Modules\Expense\Entities\Expense;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Payroll\Entities\AdvanceRequest;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserPaySlip;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Traits\SalaryCalculation;
use Yajra\DataTables\Facades\DataTables;

class UserPaySlipController extends Controller
{
    use SalaryCalculation;

    public function __construct()
    {
        view()->share('activeLink', 'payslip');
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $year    = $request->year ? $request->year : date('Y');
        $month   = $request->month ? $request->month : date('m');
        $company = $request->company_document_id ? $request->company_document_id : "0";
        // dd($company_document_id);
        $companyDocuments = \Modules\CompanyDocument\Entities\CompanyDocument::all();

        if ($request->ajax()) {

            if (str_contains(getSetting('currency'), 'AED')) {
                $AEDCurrency = '<img src="' . asset("assets/currency/aedb.png") . '" alt="AED" style="width:18px; height:18px; vertical-align:middle;">';
            } else {
                $AEDCurrency = getSetting('currency');
            }
            if (getSetting('attendance_base_payroll') == 'true') {

                $data = UserPaySlip::where(['month_code' => $month, 'year' => $year])
                    ->whereHas('user', function ($query) use ($company) {
                        $query->where('status', User::STATUS_ACTIVE);
                        $query->where('settlement_status', 0);

                        if ($company > 0) {
                            $query->where('company_document_id', $company);
                        }
                    })
                    ->whereDoesntHave('user.roles', function ($q) {
                        $q->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                    })
                    ->get();
                $totalBasicSalary      = 0;
                $totalGrossSalary      = 0;
                $netSalaryTotal        = 0;
                $totalNetSalary        = 0;
                $totalAllowance        = 0;
                $totalDeduction        = 0;
                $totalExpense          = 0;
                $total_overtime_amount = 0.00;
                $total_fixed_allowance = 0.00;
                foreach ($data as $payslip) {
                    $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                    $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                    $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                        // $query->whereBetween('date', [$start_date, $end_date]);
                        $query->whereBetween('date', [$start_date, $end_date]);
                    }])->with('salary')->where('id', $payslip->user_id)->first();
                    if (getSetting('show_basic_salary') == 1) {
                        $totalBasicSalary += $user->salary->basic;
                    }
                    if (getSetting('show_gross_salary') == 1) {
                        $totalGrossSalary += $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                    }
                    if (getSetting('show_net_salary') == 1) {
                        $netSalaryTotal += $this->getNetSalaryAsPerAttendance($user, $month, $year, $start_date, $end_date);
                    }
                    if (getSetting('show_total_net_salary') == 1) {
                        $totalNetSalary += $this->getTotalNetSalary($user, $month, $year, $start_date, $end_date);
                    }

                    if (getSetting('show_total_allowance') == 1) {
                        $fixed_allowance           = isset($user->salary) ? json_decode($user->salary->fixed_allowances, true) : 0;
                        $housing_allowance         = isset($fixed_allowance['housing_allowance']) ? $fixed_allowance['housing_allowance'] : 0;
                        $transportation_allowance  = isset($fixed_allowance['transportation_allowance']) ? $fixed_allowance['transportation_allowance'] : 0;
                        $other_allowance           = isset($fixed_allowance['other_allowance']) ? $fixed_allowance['other_allowance'] : 0;
                        $tips                      = isset($fixed_allowance['tips']) ? $fixed_allowance['tips'] : 0;
                        $totalUserAllowance        = $this->getTotalUserAllowance($user, $month, $year, $start_date, $end_date);
                        $totalUserAllowance        = $totalUserAllowance + (float) $housing_allowance + (float) $transportation_allowance + (float) $other_allowance + (float) $tips;
                        $totalAllowance           += $totalUserAllowance;
                    }
                    if (getSetting('show_total_deduction') == 1) {
                        $fixed_deduction     = isset($user->salary) ? json_decode($user->salary->fixed_deductions, true) : 0;
                        $advance_salary      = isset($fixed_deduction['advance_salary']) ? $fixed_deduction['advance_salary'] : 0;
                        $loan_deduction      = isset($fixed_deduction['loan_deduction']) ? $fixed_deduction['loan_deduction'] : 0;
                        $other_deduction     = isset($fixed_deduction['other_deduction']) ? $fixed_deduction['other_deduction'] : 0;
                        $totalUserDeduction  = $this->getTotalUserDeduction($user, $month, $year, $start_date, $end_date);
                        $totalUserDeduction  = $totalUserDeduction + (float) $advance_salary + (float) $loan_deduction + (float) $other_deduction;
                        $totalDeduction     += $totalUserDeduction;
                    }
                    if (getSetting('show_total_expense') == 1) {
                        $totalExpense += $this->getTotalUserExpense($user, $month, $year, $start_date, $end_date);
                    }
                }
                if (count($data) > 0) {
                    if (getSetting('show_total_overtime_amount') == 1) {
                        $total_overtime_amount = UserOvertime::where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                        // $overtime_amount = UserOvertime::where('user_id', $user->id)
                        //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                        //     ->sum('calculated_amount');
                    }
                    if (getSetting('show_total_fixed_allowance') == 1) {
                        $total_fixed_allowance  = UserSalaryAllowance::where([
                            'allowance_type'             => 'fixed',
                            'month_code'                 => $month,
                            'year'                       => $year,
                            'is_fixed_for_current_month' => 1,
                        ])->sum('amount');
                        $percentage_allowance  = UserSalaryAllowance::where([
                            'allowance_type'             => 'percentage',
                            'month_code'                 => $month,
                            'year'                       => $year,
                            'is_fixed_for_current_month' => 1,
                        ])->sum('percentage_amount');
                        $total_fixed_allowance += $percentage_allowance;
                    }
                } else {
                    $total_overtime_amount = 0.00;
                    $total_fixed_allowance = 0.00;
                }
                return DataTables::of($data, $AEDCurrency)
                    ->editColumn('id', function ($payslip) {
                        $user = User::where('id', $payslip->user_id)->first();
                        $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
                        return $html;
                    })
                    ->editColumn('name', function ($payslip) {
                        $user = User::where('id', $payslip->user_id)->first();
                        return $user->name;
                    })

                    ->addColumn('department_name', function ($payslip) {
                        $user = User::with('department')->where('id', $payslip->user_id)->first();
                        return $user->department?->name ?? 'NA' ?? "N/A";
                    })
                    ->editColumn('start_date', function ($payslip) {
                        return $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
                    })
                    ->editColumn('end_date', function ($payslip) {
                        return $payslip->end_date ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));
                    })
                    ->addColumn('total_working_days', function ($payslip) {
                        $month = $payslip->month_code;
                        $year  = $payslip->year;

                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));

                        $user = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])
                            ->find($payslip->user_id);

                        if ($user) {
                            // return $user->attendances->whereIn('status' , [\Modules\Attendance\Enums\AttendanceStatus::Present,
                            // \Modules\Attendance\Enums\AttendanceStatus::Weekend])->count();
                            // Trello task that it will include weekend days as working days so excluded | Gagan 22-07-2024
                            $paidleave    = $this->paidLeaveCount($user, $year, $month, $start_date, $end_date);
                            $holidaycount = $this->holdaydayCount($user, $year, $month, $start_date, $end_date);

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
                                $workingDays  = userWorkingDays($user, $month, $year, $start_date, $end_date);
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
                        }
                        return 0;
                    })
                    ->editColumn('basic_salary', function ($payslip) {
                        $user   = User::with('salary')->where('id', $payslip->user_id)->first();
                        $result = $user->salary->basic;
                        return $result;
                    })
                    ->editColumn('gross_salary', function ($payslip) use ($AEDCurrency) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])->where('id', $payslip->user_id)->first();
                        $net_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                        $net_salary = round(floatval($net_salary), 2);
                        return $net_salary . '-' . $AEDCurrency;
                    })
                    ->addColumn('net_salary', function ($payslip) use ($AEDCurrency) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])->where('id', $payslip->user_id)->first();
                        $net_salary = $this->getNetSalaryAsPerAttendance($user, $month, $year, $start_date, $end_date);
                        $net_salary = round(floatval($net_salary), 2);
                        return $net_salary . '-' . $AEDCurrency;
                    })
                    ->addColumn('total_allowance', function ($payslip) use ($AEDCurrency) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])->with('salary')->where('id', $payslip->user_id)->first();
                        $totalAllowance = $this->getTotalUserAllowance($user, $month, $year, $start_date, $end_date);
                        return $totalAllowance . '-' . $AEDCurrency;
                    })
                    ->addColumn('total_deduction', function ($payslip) use ($AEDCurrency) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])->with('salary')->where('id', $payslip->user_id)->first();
                        $totalDeduction = $this->getTotalUserDeduction($user, $month, $year, $start_date, $end_date);
                        return $totalDeduction . '-' . $AEDCurrency;
                    })
                    ->addColumn('total_overtime', function ($payslip) use ($AEDCurrency) {
                        $month           = $payslip->month_code;
                        $year            = $payslip->year;
                        $start_date      = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date        = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $overtime_amount = UserOvertime::where('user_id', $payslip->user_id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                        // $overtime_amount = UserOvertime::where('user_id', $payslip->user_id)
                        //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                        //     ->sum('calculated_amount');
                        return $overtime_amount . '-' . $AEDCurrency;
                    })
                    ->addColumn('total_net_salary', function ($payslip) use ($AEDCurrency) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])->with('salary')->where('id', $payslip->user_id)->first();
                        $total_net_salary = $this->getTotalNetSalary($user, $month, $year, $start_date, $end_date);
                        $total_net_salary = round(floatval($total_net_salary), 2);
                        return $total_net_salary . '-' . $AEDCurrency;
                    })
                    ->addColumn('status', function ($payslip) {
                        return __trans($payslip->status);
                    })
                    ->addColumn('action', function ($payslip) {
                        $user  = User::where('id', $payslip->user_id)->first();
                        $btn   = '';
                        $btn  .= createActionButton(route('backend.payslip.invoice', [$user, $payslip]), 'Payslip', 'btn-primary edit-button', '');
                        //$btn .= createActionButton(route('backend.payroll.user.user-salaries.show', [$user,$user->id]), 'Click To Pay', 'btn-success view-button', '');
                        if ($payslip->is_close == 0) {
                            $btn .= createActionButton(route('backend.payslip.user-payslip.editpayslip', [$user, $payslip]), '', 'btn-warning view-button', 'fa fa-edit');
                        } else {
                            $btn .= '<button class="btn btn-sm inline-block me-2  btn-info ">Closed</button>';
                        }
                        //$btn .= createActionButton(route('backend.payroll.user.user-salaries.show', [$user,$user->id]), '','btn-danger action-button', 'fa fa-trash');
                        return $btn;
                    })
                    ->rawColumns(['action', 'id', 'gross_salary', 'total_net_salary', 'net_salary', 'total_allowance', 'total_deduction', 'total_overtime'])
                    ->with([
                        'totalNetSalary'        => $totalNetSalary,
                        'totalBasicSalary'      => $totalBasicSalary,
                        'totalGrossSalary'      => $totalGrossSalary,
                        'netSalaryTotal'        => $netSalaryTotal,
                        'totalAllowance'        => $totalAllowance,
                        'totalDeduction'        => $totalDeduction,
                        'totalExpense'          => $totalExpense,
                        'total_overtime_amount' => $total_overtime_amount,
                        'total_fixed_allowance' => $total_fixed_allowance,
                    ])
                    ->make(true);
            } else {
                // $data             = UserPaySlip::where(['month_code' => $month, 'year' => $year])->get();

                $data = UserPaySlip::where(['month_code' => $month, 'year' => $year])
                    ->whereHas('user', function ($query) use ($company) {
                        $query->where('status', User::STATUS_ACTIVE);
                        $query->where('settlement_status', 0);
                        $query->whereDoesntHave('roles', function ($q) {
                            $q->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                        });
                        if ($company > 0) {
                            $query->where('company_document_id', $company);
                        }
                    })
                    ->get();

                $totalBasicSalary      = 0;
                $totalGrossSalary      = 0;
                $netSalaryTotal        = 0;
                $totalNetSalary        = 0;
                $totalAllowance        = 0;
                $totalDeduction        = 0;
                $totalExpense          = 0;
                $total_overtime_amount = 0.00;
                $total_fixed_allowance = 0.00;
                foreach ($data as $payslip) {
                    $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                    $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                    $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                        $query->whereBetween('date', [$start_date, $end_date]);
                    }])->with('salary')->where('id', $payslip->user_id)->first();
                    $working_days = 0;
                    $working_days = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $user->id])->value('total_working_days');
                    if (getSetting('show_basic_salary') == 1) {
                        $totalBasicSalary += isset($user->salary->basic) ? $user->salary->basic : 0;
                    }
                    if (getSetting('show_gross_salary') == 1) {
                        $totalGrossSalary += $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                    }
                    if (getSetting('show_net_salary') == 1) {
                        $netSalaryTotal += $this->getNetSalaryAsPerAttendance_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
                    }
                    if (getSetting('show_total_net_salary') == 1) {
                        $totalNetSalary += $this->getTotalNetSalary_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
                    }

                    if (getSetting('show_total_allowance') == 1) {
                        $fixed_allowance           = isset($user->salary) ? json_decode($user->salary->fixed_allowances, true) : 0;
                        $housing_allowance         = isset($fixed_allowance['housing_allowance']) ? $fixed_allowance['housing_allowance'] : 0;
                        $transportation_allowance  = isset($fixed_allowance['transportation_allowance']) ? $fixed_allowance['transportation_allowance'] : 0;
                        $other_allowance           = isset($fixed_allowance['other_allowance']) ? $fixed_allowance['other_allowance'] : 0;
                        $tips                      = isset($fixed_allowance['tips']) ? $fixed_allowance['tips'] : 0;
                        $totalUserAllowance        = $this->getTotalUserAllowance($user, $month, $year, $start_date, $end_date);
                        $totalUserAllowance        = $totalUserAllowance + (float) $housing_allowance + (float) $transportation_allowance + (float) $other_allowance + (float) $tips;
                        $totalAllowance           += $totalUserAllowance;
                    }
                    if (getSetting('show_total_deduction') == 1) {
                        $fixed_deduction     = isset($user->salary) ? json_decode($user->salary->fixed_deductions, true) : 0;
                        $advance_salary      = isset($fixed_deduction['advance_salary']) ? $fixed_deduction['advance_salary'] : 0;
                        $loan_deduction      = isset($fixed_deduction['loan_deduction']) ? $fixed_deduction['loan_deduction'] : 0;
                        $other_deduction     = isset($fixed_deduction['other_deduction']) ? $fixed_deduction['other_deduction'] : 0;
                        $totalUserDeduction  = $this->getTotalUserDeduction($user, $month, $year, $start_date, $end_date);
                        $totalUserDeduction  = $totalUserDeduction + (float) $advance_salary + (float) $loan_deduction + (float) $other_deduction;
                        $totalDeduction     += $totalUserDeduction;
                    }
                    if (getSetting('show_total_expense') == 1) {
                        $totalExpense += $this->getTotalUserExpense($user, $month, $year, $start_date, $end_date);
                    }
                }
                if (count($data) > 0) {
                    if (getSetting('show_total_overtime_amount') == 1) {
                        $total_overtime_amount = UserOvertime::where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                        // $overtime_amount = UserOvertime::where('user_id', $payslip->user_id)
                        //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                        //     ->sum('calculated_amount');
                    }
                    if (getSetting('show_total_fixed_allowance') == 1) {
                        $total_fixed_allowance  = UserSalaryAllowance::where([
                            'allowance_type'             => 'fixed',
                            'month_code'                 => $month,
                            'year'                       => $year,
                            'is_fixed_for_current_month' => 1,
                        ])->sum('amount');
                        $percentage_allowance  = UserSalaryAllowance::where([
                            'allowance_type'             => 'percentage',
                            'month_code'                 => $month,
                            'year'                       => $year,
                            'is_fixed_for_current_month' => 1,
                        ])->sum('percentage_amount');
                        $total_fixed_allowance += $percentage_allowance;
                    }
                } else {
                    $total_overtime_amount = 0.00;
                    $total_fixed_allowance = 0.00;
                }

                return DataTables::of($data)
                    ->editColumn('id', function ($payslip) {
                        $user = User::where('id', $payslip->user_id)->first();
                        $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
                        return $html;
                    })
                    ->editColumn('name', function ($payslip) {
                        $user = User::where('id', $payslip->user_id)->first();
                        return $user->name;
                    })
                    ->addColumn('department_name', function ($payslip) {
                        $user = User::with('department')->where('id', $payslip->user_id)->first();
                        return $user->department?->name ?? 'NA';
                    })
                    ->editColumn('start_date', function ($payslip) {
                        return $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
                    })
                    ->editColumn('end_date', function ($payslip) {
                        return $payslip->end_date ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));
                    })

                    ->addColumn('total_working_days', function ($payslip) {
                        $month        = $payslip->month_code;
                        $year         = $payslip->year;
                        $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $working_days = 0;
                        if (getSetting('payroll_calculation') == 'hourly') {
                            $user         = User::where('id', $payslip->user_id)->first();
                            $working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total_worked');

                            $working_days = round(floatval($working_days / 60), 2);
                        } else {
                            $working_days = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $payslip->user_id])->value('total_working_days');
                        }
                        return $working_days;
                    })
                    ->editColumn('basic_salary', function ($payslip) use ($AEDCurrency) {
                        $user   = User::with('salary')->where('id', $payslip->user_id)->first();
                        $result = isset($user->salary) ? $user->salary->basic : 0;
                        return $result . '-' . $AEDCurrency;
                    })
                    ->editColumn('gross_salary', function ($payslip) use ($AEDCurrency) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])->where('id', $payslip->user_id)->first();
                        $net_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                        $net_salary = round(floatval($net_salary), 2);
                        return $net_salary . '-' . $AEDCurrency;
                    })
                    ->addColumn('net_salary', function ($payslip) use ($AEDCurrency) {
                        $month        = $payslip->month_code;
                        $year         = $payslip->year;
                        $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user         = User::where('id', $payslip->user_id)->first();
                        $working_days = 0;
                        if (getSetting('payroll_calculation') == 'hourly') {
                            $user         = User::where('id', $payslip->user_id)->first();
                            $working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total_worked');
                            $working_days = $working_days / 60;

                            $basic = isset($user->salary) ? $user->salary->basic : 0;

                            $net_salary = $basic * $working_days;
                        } else {
                            $working_days = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $payslip->user_id])->value('total_working_days');
                            $net_salary   = $this->getNetSalaryAsPerAttendance_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
                        }
                        $net_salary = round(floatval($net_salary), 2);
                        return $net_salary . '-' . $AEDCurrency;
                    })
                    ->addColumn('total_allowance', function ($payslip) use ($AEDCurrency) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])->with('salary')->where('id', $payslip->user_id)->first();
                        $totalAllowance = $this->getTotalUserAllowance($user, $month, $year, $start_date, $end_date);
                        return $totalAllowance . '-' . $AEDCurrency;
                    })
                    ->addColumn('total_deduction', function ($payslip) use ($AEDCurrency) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user       = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
                            $query->whereBetween('date', [$start_date, $end_date]);
                        }])->with('salary')->where('id', $payslip->user_id)->first();
                        $totalDeduction = $this->getTotalUserDeduction($user, $month, $year, $start_date, $end_date);
                        return $totalDeduction . '-' . $AEDCurrency;
                    })
                    ->addColumn('total_overtime', function ($payslip) use ($AEDCurrency) {
                        $month           = $payslip->month_code;
                        $year            = $payslip->year;
                        $start_date      = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date        = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $overtime_amount = UserOvertime::where('user_id', $payslip->user_id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                        // $overtime_amount = UserOvertime::where('user_id', $payslip->user_id)
                        //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                        //     ->sum('calculated_amount');
                        return $overtime_amount . '-' . $AEDCurrency;
                    })
                    ->addColumn('total_net_salary', function ($payslip) use ($AEDCurrency) {
                        $month        = $payslip->month_code;
                        $year         = $payslip->year;
                        $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user         = User::where('id', $payslip->user_id)->first();
                        $working_days = 0;
                        if (getSetting('payroll_calculation') == 'hourly') {
                            $user         = User::where('id', $payslip->user_id)->first();
                            $working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total_worked');
                            $working_days = $working_days / 60;

                            $basic = isset($user->salary) ? $user->salary->basic : 0;

                            $total_net_salary = $basic * $working_days;
                        } else {
                            $working_days     = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $payslip->user_id])->value('total_working_days');
                            $total_net_salary = $this->getTotalNetSalary_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
                        }
                        $total_net_salary = round(floatval($total_net_salary), 2);
                        return $total_net_salary . '-' . $AEDCurrency;
                    })
                    ->addColumn('status', function ($payslip) {
                        return __trans($payslip->status);
                    })
                    ->addColumn('action', function ($payslip) {
                        $user  = User::where('id', $payslip->user_id)->first();
                        $btn   = '';
                        $btn  .= createActionButton(route('backend.payslip.invoice', [$user, $payslip]), 'Payslip', 'btn-primary edit-button', '');
                        //$btn .= createActionButton(route('backend.payroll.user.user-salaries.show', [$user,$user->id]), 'Click To Pay', 'btn-success view-button', '');
                        if ($payslip->is_close == 0) {
                            $btn .= createActionButton(route('backend.payslip.user-payslip.editpayslip', [$user, $payslip]), '', 'btn-warning view-button', 'fa fa-edit');
                        } else {
                            $btn .= '<button class="btn btn-sm inline-block me-2  btn-info ">Closed</button>';
                        }
                        //$btn .= createActionButton(route('backend.payroll.user.user-salaries.show', [$user,$user->id]), '','btn-danger action-button', 'fa fa-trash');
                        return $btn;
                    })
                    ->rawColumns(['action', 'id', 'basic_salary', 'gross_salary', 'total_net_salary', 'net_salary', 'total_allowance', 'total_deduction', 'total_overtime'])
                    ->with([
                        'totalNetSalary'        => $totalNetSalary,
                        'totalBasicSalary'      => $totalBasicSalary,
                        'totalGrossSalary'      => $totalGrossSalary,
                        'netSalaryTotal'        => $netSalaryTotal,
                        'totalAllowance'        => $totalAllowance,
                        'totalDeduction'        => $totalDeduction,
                        'totalExpense'          => $totalExpense,
                        'total_overtime_amount' => $total_overtime_amount,
                        'total_fixed_allowance' => $total_fixed_allowance,
                    ])
                    ->make(true);
            }
        }
        return view('payroll::payslip.index', compact('month', 'year', 'companyDocuments', 'company'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('payroll::payslip.create');
    }

    public function showSalaryTransaction(Request $request)
    {

        if ($request->ajax()) {
            $data = allTypeOfTransaction::where('transaction_type', 'salary')->orderBy('id', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('employee_name', function ($row) {
                    return User::where('id', $row->user_id)->value('name');
                })
                ->addColumn('transaction_type', function ($row) {
                    return $row->transaction_type;
                })
                ->addColumn('previous_value', function ($row) {
                    return $row->old_value;
                })
                ->addColumn('updated_value', function ($row) {
                    return $row->update_value;
                })
                ->addColumn('new_value', function ($row) {
                    return $row->new_value;
                })
                ->addColumn('transaction_date', function ($row) {
                    return Carbon::parse($row->transaction_date)->format('d-m-Y');
                })
                ->addColumn('description', function ($row) {
                    return $row->description;
                })
                ->addColumn('updated_at', function ($row) {
                    return Carbon::parse($row->updated_at)->format('d-m-Y H:i:s');
                })
                ->make(true);
        }
        return view('payroll::payslip.salary-transaction');
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $company = $request->company_document_id ?? "0";

        // $users = User::query()->where('status', User::STATUS_ACTIVE)->where('settlement_status', 0)->notAdmin()->with('salary')->get();
        $users = User::query()
            ->where('status', User::STATUS_ACTIVE)
            ->where('settlement_status', 0)
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->when($company > 0, function ($query) use ($company) {
                $query->where('company_document_id', $company);
            })

            ->with('salary')
            ->get();
        $count    = 0;
        $response = getErrorResponse();

        /*
        foreach ($users as $user) {
            if(isset($user->salary->basic)){
                echo $user->salary->basic.' ';
            }else{
                echo 'userid-'.$user->id.' ';
            }
        }
        die();*/

        try {
            foreach ($users as $user) {
                if (isset($user->salary->basic)) {
                    $answer = UserPaySlip::exists($user->id, $request->month, $request->year);

                    if ($answer == 'false') {
                        $start_date = $request->start_date ?? Carbon::createFromDate($request->year, $request->month, 1)->startOfDay()->toDateString();
                        $end_date   = $request->end_date ?? Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth()->toDateString();

                        $offboarding = offBoarding::where('user_id', $user->id)->first();
                        if ($offboarding) {
                            if ($offboarding->settlement_type == 'in_payroll') {
                                $payslip = $user->payslip()->create([
                                    'slip_generation_date' => now(), // Use Carbon's now() for the current date
                                    'month_code'           => $request->month,
                                    'year'                 => $request->year,
                                    'start_date'           => $start_date,
                                    'end_date'             => $end_date,
                                ]);
                            }
                        } else {
                            $payslip = $user->payslip()->create([
                                'slip_generation_date' => now(), // Use Carbon's now() for the current date
                                'month_code'           => $request->month,
                                'year'                 => $request->year,
                                'start_date'           => $start_date,
                                'end_date'             => $end_date,
                            ]);
                        }
                    } else {
                        $count++;
                    }
                }
            }
            if ($count == count($users)) {
                $response = getErrorResponse($message = "Pay slip already generated for selected month.", $error = null);
                return response()->json($response);
            }

            $response = getSuccessResponse(createFlashMessage('PaySlip ', 'generated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $fixedAllowance = SetAllowanceDeducation::where('type', 1)->get();
        return view('payroll::edit', compact('fixedAllowance'));
    }

    /* edit Payslip Page */
    public function editPaySlip(User $user, UserPaySlip $payslip, Request $request)
    {
        // canPerform('Manage Employee Salary');
        $query         = UserPaySlip::select('month_code', 'year', 'start_date', 'end_date')->where('id', $payslip->id)->first();
        $current_month = $query->month_code;
        $current_year  = $query->year;
        $start_date    = $query->start_date ?? date('Y-m-01', strtotime("$current_year-$current_month-01"));
        $end_date      = $query->end_date ?? date('Y-m-t', strtotime("$current_year-$current_month-01"));

        $gross_salary      = $this->getGrossSalary($user, $current_month, $current_year, $start_date, $end_date);
        $allowance         = UserSalaryAllowance::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        $notfixedallowance = UserSalaryAllowance::where('user_id', $user->id)->where('is_fixed_for_current_month', 0)->get();
        $allowance         = $allowance->merge($notfixedallowance);
        // Create a Carbon instance for the specified date
        $date = Carbon::createFromDate($current_year, $current_month, 1);
        // Get the month name
        $monthName = $date->format('F') . ' ' . $current_year;
        $monthyear = $current_month . $current_year;
        return view(
            'payroll::payslip.edit',
            compact(
                'user',
                'allowance',
                'gross_salary',
                'payslip',
                'monthName',
                'monthyear',
                'start_date',
                'end_date'
            )
        );
    }

    // public function close_report(Request $request, $month, $year, $company)
    // {

    //     // $users = User::query()->where('settlement_status', 0)->notAdmin()->with('salary')->get();
    //     // $company = $request->company_document_id ?? "0";
    //     // $users = User::query()->where('status', User::STATUS_ACTIVE)->where('settlement_status', 0)->notAdmin()->with('salary')->get();
    //     $users = User::query()
    //         ->where('status', User::STATUS_ACTIVE)
    //         ->where('settlement_status', 0)
    //         ->when($company > 0, function ($query) use ($company) {
    //             $query->where('company_document_id', $company);
    //         })
    //         ->notAdmin()
    //         ->with('salary')
    //         ->get();

    //     $count = 0;

    //     foreach ($users as $user) {
    //         if (isset($user->salary->basic)) {
    //             $answer = UserPaySlip::exists($user->id, $month, $year);
    //             $report = UserPaySlip::where([['user_id', $user->id], ['month_code', $month], ['year', $year]])->first();
    //             if ($answer == 'true' && $report->is_close == 0) {
    //                 $report->is_close = 1;
    //                 $report->save();

    //                 $invoiceResponse = $this->openinvoice($user, $report);
    //                 if ($invoiceResponse->getStatusCode() === 200) {
    //                     $invoiceData = $invoiceResponse->getData(true); // true to get array
    //                     if (isset($invoiceData['html'])) {
    //                         // Generate PDF from HTML
    //                         dd(1);
    //                         $currency = getSetting('currency');
    //                         $currencyParts = explode('-', $currency);
    //                         $OtherCurrency = "";
    //                         if (isset($currencyParts[1])) {
    //                             $OtherCurrency = $currencyParts[1]; // This will be "AED"
    //                             $invoiceHtml = $invoiceData['html'];
    //                             $invoiceHtml = str_replace("-" . $OtherCurrency, '', $invoiceHtml);
    //                             $invoiceData['html'] = $invoiceHtml;
    //                         }
    //                         dd(2);
    //                         $pdf = \Pdf::loadHTML($invoiceData['html'])
    //                             ->setOptions([
    //                                 'isHtml5ParserEnabled' => true,
    //                                 'isRemoteEnabled' => true,
    //                                 'defaultFont' => 'DejaVu Sans', // or Roboto, Noto Sans, etc.
    //                             ]);
    //                         dd(3);
    //                         // Send Email with PDF attachment
    //                         \Mail::to($user->email)->send(new \App\Mail\PayslipMailWithPdf($user, $request, $pdf->output()));
    //                     }
    //                 }
    //                 dd(1);

    //                 // advance loan
    //                 $advanceRequest = AdvanceRequest::where([
    //                     'user_id' => $user->id,
    //                     'status'  => 'approved',
    //                 ])
    //                     ->whereYear('start_month', $year)
    //                     ->get();
    //                 foreach ($advanceRequest as $advanceRe) {
    //                     $howmanymonths = $advanceRe->loan_months;
    //                     $startmonth    = $advanceRe->start_month;
    //                     $stmonth       = Carbon::parse($startmonth);
    //                     for ($i = 1; $i <= $howmanymonths; $i++) {
    //                         if ($stmonth->format('m') == $month) {
    //                             $advanceRe->update([
    //                                 'installments_paid'    => $advanceRe->installments_paid + 1,
    //                                 'installments_pending' => $advanceRe->installments_pending - 1,
    //                             ]);
    //                             if ($advanceRe->loan_months == $advanceRe->installments_paid) {
    //                                 $advanceRe->update([
    //                                     'status' => 'closed',
    //                                 ]);
    //                             }
    //                         }
    //                         $stmonth->addMonth();
    //                     }
    //                 }
    //                 //end
    //             }
    //         }
    //         $count++;
    //     }
    //     dd($report->is_close);

    //     if ($count == count($users)) {
    //         $response = getSuccessResponse(createFlashMessage('PaySlip ', 'closed'));
    //         return redirect('payslip/user-payslip')->with('success', 'Report closed successfully');
    //     }
    // }
    public function close_report(Request $request, $month, $year, $company = null, )
    {
        Log::info("==== CLOSE REPORT START ====", [
            'month'   => $month,
            'year'    => $year,
            'company' => $company,
        ]);

        $users = User::query()
            ->where('status', User::STATUS_ACTIVE)
            ->where('settlement_status', 0)
            ->when($company > 0, function ($query) use ($company) {
                $query->where('company_document_id', $company);
            })
            ->notAdmin()
            ->with('salary')
            ->get();

        Log::info("Users fetched for closing process", [
            'total_users' => $users->count(),
        ]);

        $count = 0;
        foreach ($users as $user) {

            Log::info("Processing user", [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);

            if (isset($user->salary->basic)) {

                Log::info("Salary exists for user", [
                    'user_id' => $user->id,
                ]);

                $answer = UserPaySlip::exists($user->id, $month, $year);
                Log::info("PaySlip exists check", [
                    'user_id' => $user->id,
                    'exists'  => $answer,
                ]);

                $report = UserPaySlip::where([
                    ['user_id', $user->id],
                    ['month_code', $month],
                    ['year', $year],
                ])->first();

                Log::info("Payslip record fetched", [
                    'user_id'      => $user->id,
                    'report_found' => (bool) $report,
                    'is_close'     => $report?->is_close,
                ]);

              
                if ($answer == 'true' && $report->is_close == 0) {
                    Log::info("Closing payslip now", [
                        'user_id' => $user->id,
                    ]);

                    $report->is_close = 1;
                    $report->status   = 'paid';
                    $report->save();

                    Log::info("Payslip closed successfully", [
                        'user_id' => $user->id,
                    ]);

                    // ============= OPEN INVOICE =============
                    $invoiceResponse = $this->openinvoice($user, $report, "pdf");

                    Log::info("Invoice API Response", [
                        'status_code' => $invoiceResponse->getStatusCode(),
                        'user_id'     => $user->id,
                    ]);

                    if ($invoiceResponse->getStatusCode() === 200) {

                        $invoiceData = $invoiceResponse->getData(true);
                        Log::info("Invoice HTML received", [
                            'html_exists' => isset($invoiceData['html']),
                            'user_id'     => $user->id,
                        ]);

                        if (isset($invoiceData['html'])) {

                            Log::info("Processing invoice HTML before PDF", [
                                'user_id' => $user->id,
                            ]);

                            $currency      = getSetting('currency');
                            $currencyParts = explode('-', $currency);
                            $OtherCurrency = "";

                            if (isset($currencyParts[1])) {
                                $OtherCurrency = $currencyParts[1];

                                $invoiceHtml         = $invoiceData['html'];
                                $invoiceHtml         = str_replace("-" . $OtherCurrency, '', $invoiceHtml);
                                $invoiceData['html'] = $invoiceHtml;

                                Log::info("Removed other currency from HTML", [
                                    'user_id'          => $user->id,
                                    'removed_currency' => $OtherCurrency,
                                ]);
                            }

                            Log::info("Generating PDF for user", [
                                'user_id' => $user->id,
                            ]);

                            // $pdf = \Pdf::loadHTML($invoiceData['html'])
                            //     ->setOptions([
                            //         'isHtml5ParserEnabled' => true,
                            //         'isRemoteEnabled' => true,
                            //         'defaultFont' => 'DejaVu Sans',
                            //     ]);
                            $pdf = \PDF::loadHTML($invoiceData['html'])
                                ->setPaper('A4', 'portrait')
                                ->setOptions([
                                    'isHtml5ParserEnabled'    => true,
                                    'isRemoteEnabled'         => true,
                                    'defaultFont'             => 'DejaVu Sans',
                                    'isPhpEnabled'            => true, // helps for dynamic HTML
                                    'isFontSubsettingEnabled' => true, // important for mixed languages
                                    'debugPng'                => false,
                                    'debugCss'                => false,
                                    'debugLayout'             => false,
                                ]);

                            Log::info("PDF generated successfully", [
                                'user_id' => $user->id,
                            ]);

                            // \Mail::to("nchouhan191.nc@gmail.com")->send(
                            //     new \App\Mail\PayslipMailWithPdf($user, $request, $pdf->output())
                            // );

                            $useremail = "";
                            if (getSetting('payslip_email') == "personal_email") {
                                $useremail = $user->profile->personal_email;
                            } elseif (getSetting('payslip_email') == "work_email") {
                                $useremail = $user->email;
                            }
                            if (! empty($useremail)) {
                                \Mail::to($useremail)->send(new \App\Mail\PayslipMailWithPdf($user, $request, $pdf->output()));
                                // dd($invoiceData['html']);

                                Log::info("Email sent with PDF attachment", [
                                    'user_id' => $user->id,
                                    'email'   => $user->email,
                                ]);
                            }
                        }
                    }

                    // ============= ADVANCE LOAN PROCESS =============
                    Log::info("Checking advance loans", [
                        'user_id' => $user->id,
                    ]);

                    $advanceRequest = AdvanceRequest::where([
                        'user_id' => $user->id,
                        'status'  => 'approved',
                    ])
                        ->get();

                    Log::info("Advance loan fetched", [
                        'user_id' => $user->id,
                        'count'   => $advanceRequest->count(),
                    ]);

                    foreach ($advanceRequest as $advanceRe) {

                        Log::info("Processing advance loan installment", [
                            'advance_id' => $advanceRe->id,
                            'user_id'    => $user->id,
                        ]);

                        $howmanymonths = $advanceRe->loan_months;
                        $stmonth       = Carbon::parse($advanceRe->start_month);

                        for ($i = 1; $i <= $howmanymonths; $i++) {
                            if ($stmonth->format('m') == $month) {

                                $logadd = AdvanceRequestHistory::create([
                                    'advance_request_id'   => $advanceRe->id,
                                    'user_id'              => $user->id,
                                    'action_date'          => now(),
                                    'amount'               => $advanceRe->installment_amount,
                                    'approved_amount'      => $advanceRe->approved_amount,
                                    'installments_paid'    => $advanceRe->installments_paid + 1,
                                    'installments_pending' => $advanceRe->installments_pending - 1,
                                    'description'          => 'Monthly Installment Deducted From Payslip For ' . $stmonth->format('F Y'),
                                ]);

                                $advanceRe->update([
                                    'installments_paid'    => $advanceRe->installments_paid + 1,
                                    'installments_pending' => $advanceRe->installments_pending - 1,
                                ]);

                                Log::info("Advance installment updated", [
                                    'advance_id' => $advanceRe->id,
                                    'user_id'    => $user->id,
                                    'paid'       => $advanceRe->installments_paid,
                                    'pending'    => $advanceRe->installments_pending,
                                ]);

                                if ($advanceRe->loan_months == $advanceRe->installments_paid) {
                                    $advanceRe->update([
                                        'status' => 'closed',
                                    ]);

                                    Log::info("Advance loan closed", [
                                        'advance_id' => $advanceRe->id,
                                        'user_id'    => $user->id,
                                    ]);
                                }
                            }
                            $stmonth->addMonth();
                        }
                    }
                    // ====== end ========
                    // ============= EMI allowance & deduction check=============
                    $emi_allowance = EMIAllowance::where('user_id', $user->id)->where('fully_paid', 0)->get();
                    foreach ($emi_allowance as $emiAllo) {
                        $emiAllowanceData = EMIAllowanceData::where('emi_id', $emiAllo->id)
                            ->where([
                                ['month', $month],
                                ['year', $year],
                            ])
                            ->where('is_paid', 0)
                            ->first();
                        if ($emiAllowanceData) {
                            $emiAllowanceData->update([
                                'is_paid' => 1,
                            ]);
                            Log::info("EMI Allowance marked as paid", [
                                'emi_allowance_id'      => $emiAllo->id,
                                'emi_allowance_data_id' => $emiAllowanceData->id,
                                'user_id'               => $user->id,
                            ]);

                            $remainingAllowanceData = EMIAllowanceData::where('emi_id', $emiAllo->id)->where('is_paid', 0)->count();
                            if ($remainingAllowanceData == 0) {
                                $emiAllo->update([
                                    'fully_paid' => 1,
                                ]);

                                Log::info("EMI Allowance marked as fully paid", [
                                    'emi_allowance_id' => $emiAllo->id,
                                    'user_id'          => $user->id,
                                ]);
                            }
                        }
                    }
                    $emi_deduction = EMIDeduction::where('user_id', $user->id)->where('fully_paid', 0)->get();
                    foreach ($emi_deduction as $emidedu) {
                        $emiDeductionData = EMIDeductionData::where('emi_id', $emidedu->id)
                            ->where([
                                ['month', $month],
                                ['year', $year],
                            ])
                            ->where('is_paid', 0)
                            ->first();
                        if ($emiDeductionData) {
                            $emiDeductionData->update([
                                'is_paid' => 1,
                            ]);
                            Log::info("EMI Deduction marked as paid", [
                                'emi_deduction_id'      => $emidedu->id,
                                'emi_deduction_data_id' => $emiDeductionData->id,
                                'user_id'               => $user->id,
                            ]);

                            $remainingDeductionData = EMIDeductionData::where('emi_id', $emidedu->id)->where('is_paid', 0)->count();
                            if ($remainingDeductionData == 0) {
                                $emidedu->update([
                                    'fully_paid' => 1,
                                ]);

                                Log::info("EMI Deduction marked as fully paid", [
                                    'emi_deduction_id' => $emidedu->id,
                                    'user_id'          => $user->id,
                                ]);
                            }
                        }
                    }
                    // ====== end ========
                }

            }

            $count++;
        }

        Log::info("Processing completed for all users", [
            'processed' => $count,
            'total'     => count($users),
        ]);

        Log::info("===== CLOSE REPORT END =====");

        if ($count == count($users)) {
            return redirect('payslip/user-payslip')->with('success', 'Report closed successfully');
        }
    }

    /* show Allowance List */
    public function showallowance(User $allowance, Request $request)
    {
        // canPerform('Manage Employee Salary');
        $user          = $allowance;
        $query         = UserPaySlip::select('month_code', 'year', 'start_date', 'end_date')->where('id', $request->payslip_id)->first();
        $current_month = $query->month_code;
        $current_year  = $query->year;
        $start_date    = $query->start_date ?? date('Y-m-01', strtotime("$current_year-$current_month-01"));
        $end_date      = $query->end_date ?? date('Y-m-t', strtotime("$current_year-$current_month-01"));

        $gross_salary = $this->getGrossSalary($user, $current_month, $current_year, $start_date, $end_date);
        $allowance    = UserSalaryAllowance::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        // $notfixedallowance = UserSalaryAllowance::where('user_id', $user->id)->where('is_fixed_for_current_month', 0)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        // $allowance         = $allowance->merge($notfixedallowance);
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
                    $btn .= createActionButton(route('backend.payroll.user.user-salaries.editallowance', [$user->user_id, $user->id]), '', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('backend.payroll.user.user-salaries.destroyallowance', $user), '', 'btn-danger action-button', 'fa fa-trash');

                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
    }

    /* create Allowance Modal */
    public function createallowance(User $user, $monthyear)
    {
        canPerform('Create Allowance');
        $fixedAllowance = SetAllowanceDeducation::where('type', 1)->get();
        $html           = view('payroll::allowance.create', compact('user', 'monthyear', 'fixedAllowance'))->render();
        $response       = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /* show the Overtime. */
    public function showovertime(User $user, Request $request)
    {
        canPerform('Manage Employee Overtime');
        $query         = UserPaySlip::select('month_code', 'year', 'start_date', 'end_date')->where('id', $request->payslip_id)->first();
        $current_month = $query->month_code;
        $current_year  = $query->year;
        $start_date    = $query->start_date ?? date('Y-m-01', strtotime("$current_year-$current_month-01"));
        $end_date      = $query->end_date ?? date('Y-m-t', strtotime("$current_year-$current_month-01"));
        //$current_month = date('m'); $current_year = date('Y');
        $overtime = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        // $overtime = UserOvertime::where('user_id', $user->id)
        //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
        //     ->get();
        if ($request->ajax()) {
            return DataTables::of($overtime)
                ->editColumn('employee_name', function ($overtime) {
                    $employee_name = User::select('name')->where('id', $overtime->user_id)->first();
                    return $employee_name->name;
                })
                ->addColumn('action', function ($user) {
                    $btn = '';
                    if ($user->is_system_add != 1) {
                        $btn .= createActionButton(route('backend.payroll.user.user-salaries.editovertime', [$user->user_id, $user->id]), '', 'btn-warning edit-button', 'fa fa-edit');
                        $btn .= createActionButton(route('backend.payroll.user.user-salaries.destroyovertime', $user), '', 'btn-danger action-button', 'fa fa-trash');
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
    }

    /* create overtime Modal */
    public function createovertime(User $user, $monthyear)
    {
        canPerform('Create Overtime');
        $html     = view('payroll::overtime.create', compact('user', 'monthyear'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Show the deductions.
     */
    public function showdeduction(User $user, Request $request)
    {
        // canPerform('Manage Employee Deduction');
        $query         = UserPaySlip::select('month_code', 'year', 'start_date', 'end_date')->where('id', $request->payslip_id)->first();
        $current_month = $query->month_code;
        $current_year  = $query->year;
        $start_date    = $query->start_date ?? date('Y-m-01', strtotime("$current_year-$current_month-01"));
        $end_date      = $query->end_date ?? date('Y-m-t', strtotime("$current_year-$current_month-01"));

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
    }

    /* create Deduction modal */
    public function creatededuction(User $user, $monthyear)
    {
        canPerform('Add Deduction');
        $fixedDeduction = SetAllowanceDeducation::where('type', 2)->get();
        $html           = view('payroll::deduction.create', compact('user', 'monthyear', 'fixedDeduction'))->render();
        $response       = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /* export payslip */
    public function payslipexport($month, $year, $company = null, )
    {
        return Excel::download(new UsersPaySlipExport($month, $year, $company), 'payslip_' . time() . '.xlsx');
    }

    /* open payslip invoice modal */
    // public function openinvoice(User $user, UserPaySlip $payslip, $pdfOrHtml = "html")
    // {
    //     //canPerform('Open Invoice Modal');
    //     $setting      = Setting::whereIn('id', [1, 4])->get();
    //     // $payslip_date = date('F', strtotime(date('Y') . '-' . $payslip->month_code)) . ' ' . $payslip->year; //date('Y-m-d H:i:s');
    //     $payslip_date = strtoupper(date('F', strtotime(date('Y') . '-' . $payslip->month_code))) . ' ' . $payslip->year;
    //     $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
    //     $end_date   = $payslip->end_date   ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));
    //     $user_salary  = User::with([
    //         'all_overtime'  => function ($query) use ($payslip) {
    //             // $query->whereBetween('date', [$start_date, $end_date]);
    //             $query->where([['month_code', $payslip->month_code], ['year', $payslip->year]]);
    //         },
    //         'all_allowance' => function ($query) use ($payslip) {

    //             $query->where(function ($subquery) use ($payslip) {
    //                 $subquery->where([['month_code', $payslip->month_code], ['year', $payslip->year]])
    //                     ->orWhere('is_fixed_for_current_month', 0);
    //             });
    //         },
    //         'all_deduction' => function ($query) use ($payslip) {
    //             $query->where(function ($subquery) use ($payslip) {
    //                 $subquery->where([['month_code', $payslip->month_code], ['year', $payslip->year]])
    //                     ->orWhere('is_fixed_for_current_month', 0);
    //             });
    //         },
    //     ])->where('id', $user->id)->get();
    //     $user = User::with(['attendances' => function ($query) use ($payslip) {
    //         $query->whereMonth('date', $payslip->month_code)->whereYear('date', $payslip->year);
    //     }])->with('salary')->where('id', $user->id)->first();

    //     $working_days = 0;
    //     $working_days = EmployeeWorkingDay::where(['month_code' => $payslip->month_code, 'year' => $payslip->year, 'user_id' => $payslip->user_id])->value('total_working_days');
    //     $attendance_deduction = 0;
    //     if (getSetting('payroll_calculation') == 'hourly') {
    //         $total_working_hour = $user->attendances()
    //             ->whereIn('status', [
    //                 \Modules\Attendance\Enums\AttendanceStatus::Present,
    //                 \Modules\Attendance\Enums\AttendanceStatus::Late,
    //                 \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
    //                 \Modules\Attendance\Enums\AttendanceStatus::Weekend,
    //             ])
    //             ->whereBetween('date', [$start_date, $end_date])
    //             ->sum('total_worked');
    //         $basic            = isset($user->salary) ? $user->salary->basic : 0;
    //         $net_salary       = $basic * $total_working_hour / 60;
    //         $total_net_salary = $basic * $total_working_hour / 60;

    //         $net_salary = number_format((float) $net_salary, 2, '.', '');
    //         $total_net_salary = number_format((float) $total_net_salary, 2, '.', '');
    //     } else {
    //         if (getSetting('attendance_base_payroll') == 'true') {
    //             $working_days = $user->attendances()
    //                 ->whereIn('status', [
    //                     \Modules\Attendance\Enums\AttendanceStatus::Present,
    //                     \Modules\Attendance\Enums\AttendanceStatus::Late,
    //                     \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
    //                     \Modules\Attendance\Enums\AttendanceStatus::Weekend
    //                 ])
    //                 ->whereBetween('date', [$start_date, $end_date])
    //                 // ->distinct('date')
    //                 //->groupby('date')
    //                 ->count();

    //             $net_salary       = $this->getNetSalaryAsPerAttendance($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
    //             $total_net_salary = $this->getTotalNetSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
    //             // $attendance_deduction = $this->getAttendanceDiduction($user, $payslip->month_code, $payslip->year);
    //         } else {
    //             $net_salary       = $this->getNetSalaryAsPerAttendance_EXTRA($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date, $working_days);
    //             $total_net_salary = $this->getTotalNetSalary_EXTRA($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date, $working_days);
    //         }
    //     }
    //     $gross_salary = $this->getGrossSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

    //     $expense = $this->monthlyExpensesCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

    //     $calculations = [
    //         'attendance_salary' => $net_salary,
    //         'total_deduction'   => 0,
    //         'net_salary'        => $total_net_salary,
    //         'attendance_deduction' => $gross_salary - $net_salary,
    //         'gross_salary' => $gross_salary,
    //     ];
    //     // Extra added 18-03-2024
    //     $fixed_entity_allowance = [];
    //     if (isset($user->salary->fixed_allowances)) {
    //         $decoded_allowances = json_decode($user->salary->fixed_allowances, true);
    //         if (is_array($decoded_allowances)) {
    //             $fixed_entity_allowance = $decoded_allowances;
    //         }
    //     }

    //     $fixed_entity_deduction = [];
    //     if (isset($user->salary->fixed_deductions)) {
    //         $decoded_deductions = json_decode($user->salary->fixed_deductions, true);
    //         if (is_array($decoded_deductions)) {
    //             $fixed_entity_deduction = $decoded_deductions;
    //         }
    //     }

    //     $all_fixed_entity          = array_merge($fixed_entity_allowance, $fixed_entity_deduction);
    //     $gettotaladAdvanceSalary   = $this->monthlyfixedAdvanceSalaryCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
    //     $totaladAdvanceSalary      = $gettotaladAdvanceSalary['AdvanceSalaryAmount'];
    //     $approvedAdvanceLoanAmount = $gettotaladAdvanceSalary['loanAmount'];
    //     $totalDeduction            = $this->getTotalUserDeduction($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
    //     // dd($expense);

    //     $total_present = $user->attendances()
    //         ->where('status', \Modules\Attendance\Enums\AttendanceStatus::Present)
    //         ->whereYear('date', $payslip->year)
    //         ->whereMonth('date', $payslip->month_code)
    //         ->count();

    //     $total_weekend = $user->attendances()
    //         ->where('status', \Modules\Attendance\Enums\AttendanceStatus::Weekend)
    //         ->whereYear('date', $payslip->year)
    //         ->whereMonth('date', $payslip->month_code)
    //         ->count();

    //     // $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
    //     //     ->where('year', $payslip->year)
    //     //     ->whereHas('leaveType', function ($q) {
    //     //         $q->where('name', 'Vacation');
    //     //     })
    //     //     ->value('monthwiseDay');
    //     $checkmonthwise = Setting::where('key', 'is_month_wise_show_leave')->value('value');
    //     if ($checkmonthwise == 1) {
    //         $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
    //             ->where('year', $payslip->year)
    //             ->whereHas('leaveType', function ($q) {
    //                 $q->where('name', 'Vacation');
    //             })
    //             ->value('monthwiseDay');
    //     } else {
    //         $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
    //             ->where('year', $payslip->year)
    //             ->whereHas('leaveType', function ($q) {
    //                 $q->where('name', 'Vacation');
    //             })
    //             ->value('available');
    //     }

    //     $totalTakenSickLeaves = Leave::where('user_id', $user->id)
    //         ->where('year', $payslip->year)
    //         ->whereHas('type', function ($q) {
    //             $q->where('name', 'sick');
    //         })
    //         ->sum('total_leave_days');

    //     $totalTakenExtraLeaves = Leave::where('user_id', $user->id)
    //         ->where('year', $payslip->year)
    //         ->whereHas('type', function ($q) {
    //             $q->where('name', 'extra')
    //                 ->orWhere('name', 'extra leave');
    //         })
    //         ->sum('total_leave_days');

    //     $total_ph_leave = LeaveBalance::where('user_id', $user->id)
    //         ->where('year', $payslip->year)
    //         ->whereHas('leaveType', function ($q) {
    //             $q->where('name', 'ph');
    //         })
    //         ->value('available');

    //     $Payslip = DocumentType::where("name", "Payslip")->first();
    //     if (isset($Payslip)) {

    //         $template                 = $Payslip->template;
    //         $deduction_allowance_html = view('payroll::payslip.deduction_allowance', [
    //             'all_fixed_entity'          => $all_fixed_entity,
    //             'allowances'                => SetAllowanceDeducation::get(),
    //             'user_salary'               => $user_salary,
    //             'approvedAdvanceLoanAmount' => $approvedAdvanceLoanAmount,
    //         ])->render();

    //         $salary_allowance_html = view('payroll::payslip.salary_allowance', [
    //             'all_fixed_entity' => $all_fixed_entity,
    //             'user_salary'      => $user_salary,
    //             'expense'          => $expense,
    //             'allowances'       => SetAllowanceDeducation::get(),
    //         ])->render();
    //         $year = $payslip->year;
    //         $month = str_pad($payslip->month_code, 2, '0', STR_PAD_LEFT); // Ensure 2-digit month

    //         // Get start and end dates
    //         $startDate = Carbon::createFromFormat('Y-m', "$year-$month")->startOfMonth()->format('d-m-Y');
    //         $endDate = Carbon::createFromFormat('Y-m', "$year-$month")->endOfMonth()->format('d-m-Y');

    //         $data = [
    //             '[[company_name]]'             => $setting[0]['value'],
    //             '[[company_address]]'          => $setting[1]['value'],
    //             '[[currency]]'                 => getSetting('currency'),
    //             '[[month]]'                    => DateTime::createFromFormat('!m', $payslip->month_code)->format('F'),
    //             '[[year]]'                     => $payslip->year,
    //             '[[start_date]]'               => $startDate,
    //             '[[end_date]]'                 => $endDate,
    //             '[[payslip_date]]'             => $payslip_date,
    //             '[[username]]'                 => $user->name,
    //             '[[emp_code]]'                 => $user->employee_id,
    //             '[[designation]]'              => $user->designation->name,
    //             '[[present]]'                  => $total_present,
    //             '[[joining_date]]'             => $user->workDetail->joining_date->format(config('project.date_format')) ?? '',
    //             '[[department]]'               => $user->department?->name ?? 'NA' ?? '',
    //             '[[bank_name]]'                => $user->bankDetail->bank_name ?? '',
    //             '[[account_number]]'           => $user->bankDetail->account_number ?? '',
    //             '[[off_day]]'                  => $total_weekend,
    //             '[[sick_leave_taken]]'         => $totalTakenSickLeaves,
    //             '[[annual_leave_balance]]'     => $total_vacation_leave,
    //             '[[extra_leave_taken]]'        => $totalTakenExtraLeaves,
    //             '[[ph_leave_balance]]'         => isset($total_ph_leave) ? $total_ph_leave : "0",
    //             '[[basic_salary]]'             => $user->salary->basic,
    //             '[[housing_allowance]]'        => isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : "",
    //             '[[transportation_allowance]]' => isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : "",
    //             '[[other_allowance]]'          => isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : "",
    //             '[[tips]]'                     => "",
    //             '[[salary_allowances]]'        => $salary_allowance_html,
    //             '[[deduction_allowances]]'     => $deduction_allowance_html,
    //             '[[total_working_days]]'     => $working_days,
    //             '[[total_earning]]'           => round(floatval($calculations['attendance_salary']), 2) . '- ' . getSetting('currency'),
    //             '[[net_amount]]'               => round(floatval($calculations['net_salary']), 2) . '-' . getSetting('currency'),
    //             '[[attendance_deduction]]'     => round(floatval($calculations['attendance_deduction']), 2) . '-' . getSetting('currency'),
    //             '[[gross_salary]]'     => round(floatval($calculations['gross_salary']), 2) . '-' . getSetting('currency'),
    //             '[[total_deduction]]'         => round(floatval($totalDeduction), 2) . '-' . getSetting('currency'),
    //             '[[total_deduction_with_attendance]]' =>   round(floatval($totalDeduction + $calculations['attendance_deduction']), 2) . '-' . getSetting('currency'),

    //             '[[logo]]'             => $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 100px;">' : '',
    //             '[[small_logo]]'             => $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 100px;">' : '',
    //             '[[sign]]'             => $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 100px;">' : '',
    //             '[[header]]'             => $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 100px;">' : '',
    //             '[[footer]]'             => $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 100px;">' : '',

    //         ];

    //         // Replace placeholders with actual data
    //         foreach ($data as $placeholder => $value) {
    //             $template = str_replace($placeholder, $value, $template);
    //         }
    //         $html     = view('payroll::payslip.openinvoicedynamic', compact('user', 'template'))->render();
    //         $response = [
    //             'success' => true,
    //             'html'    => $html,
    //         ];

    //         return response()->json($response);
    //     } elseif ($pdfOrHtml == "pdf") {
    //         $html     = view('payroll::payslip.openinvoicepdf', compact('user', 'payslip', 'setting', 'user_salary', 'calculations', 'payslip_date', 'all_fixed_entity', 'expense', 'totaladAdvanceSalary', 'approvedAdvanceLoanAmount'))->render();
    //         $response = [
    //             'success' => true,
    //             'html'    => $html,
    //         ];

    //         return response()->json($response);
    //     }

    //     $html     = view('payroll::payslip.openinvoice', compact('user', 'payslip', 'setting', 'user_salary', 'calculations', 'payslip_date', 'all_fixed_entity', 'expense', 'totaladAdvanceSalary', 'approvedAdvanceLoanAmount'))->render();
    //     $response = [
    //         'success' => true,
    //         'html'    => $html,
    //     ];

    //     return response()->json($response);
    // }
    public function openinvoice(User $user, UserPaySlip $payslip, $pdfOrHtml = "html")
    {
        //canPerform('Open Invoice Modal');
        $setting = Setting::whereIn('id', [1, 4])->get();
        // $payslip_date = date('F', strtotime(date('Y') . '-' . $payslip->month_code)) . ' ' . $payslip->year; //date('Y-m-d H:i:s');
        $payslip_date = strtoupper(date('F', strtotime(date('Y') . '-' . $payslip->month_code))) . ' ' . $payslip->year;
        $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
        $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));
        $user_salary  = User::with([
            'all_overtime'  => function ($query) use ($payslip) {
                // $query->whereBetween('date', [$start_date, $end_date]);
                $query->where([['month_code', $payslip->month_code], ['year', $payslip->year]]);
            },
            'all_allowance' => function ($query) use ($payslip) {

                $query->where(function ($subquery) use ($payslip) {
                    $subquery->where([['month_code', $payslip->month_code], ['year', $payslip->year]])
                        ->orWhere('is_fixed_for_current_month', 0);
                });
            },
            'all_deduction' => function ($query) use ($payslip) {
                $query->where(function ($subquery) use ($payslip) {
                    $subquery->where([['month_code', $payslip->month_code], ['year', $payslip->year]])
                        ->orWhere('is_fixed_for_current_month', 0);
                });
            },
        ])->where('id', $user->id)->get();
        $user = User::with(['attendances' => function ($query) use ($payslip) {
            $query->whereMonth('date', $payslip->month_code)->whereYear('date', $payslip->year);
        }])->with('salary')->where('id', $user->id)->first();

        $working_days         = 0;
        $working_days         = EmployeeWorkingDay::where(['month_code' => $payslip->month_code, 'year' => $payslip->year, 'user_id' => $payslip->user_id])->value('total_working_days');
        $attendance_deduction = 0;
        if (getSetting('payroll_calculation') == 'hourly') {
            $total_working_hour = $user->attendances()
                ->whereIn('status', [
                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                ])
                ->whereBetween('date', [$start_date, $end_date])
                ->sum('total_worked');
            $basic            = isset($user->salary) ? $user->salary->basic : 0;
            $net_salary       = $basic * $total_working_hour / 60;
            $total_net_salary = $basic * $total_working_hour / 60;

            $net_salary       = number_format((float) $net_salary, 2, '.', '');
            $total_net_salary = number_format((float) $total_net_salary, 2, '.', '');
        } else {
            if (getSetting('attendance_base_payroll') == 'true') {
                $working_days = $user->attendances()
                    ->whereIn('status', [
                        \Modules\Attendance\Enums\AttendanceStatus::Present,
                        \Modules\Attendance\Enums\AttendanceStatus::Late,
                        \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                        \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                    ])
                    ->whereBetween('date', [$start_date, $end_date])
                // ->distinct('date')
                //->groupby('date')
                    ->count();

                $net_salary       = $this->getNetSalaryAsPerAttendance($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
                $total_net_salary = $this->getTotalNetSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
                // $attendance_deduction = $this->getAttendanceDiduction($user, $payslip->month_code, $payslip->year);
            } else {
                $net_salary       = $this->getNetSalaryAsPerAttendance_EXTRA($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date, $working_days);
                $total_net_salary = $this->getTotalNetSalary_EXTRA($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date, $working_days);
            }
        }
        $gross_salary = $this->getGrossSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

        $expense = $this->monthlyExpensesCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

        $calculations = [
            'attendance_salary'    => $net_salary,
            'total_deduction'      => 0,
            'net_salary'           => $total_net_salary,
            'attendance_deduction' => $gross_salary - $net_salary,
            'gross_salary'         => $gross_salary,
        ];
        // Extra added 18-03-2024
        $fixed_entity_allowance = [];
        if (isset($user->salary->fixed_allowances)) {
            $decoded_allowances = json_decode($user->salary->fixed_allowances, true);
            if (is_array($decoded_allowances)) {
                $fixed_entity_allowance = $decoded_allowances;
            }
        }

        $fixed_entity_deduction = [];
        if (isset($user->salary->fixed_deductions)) {
            $decoded_deductions = json_decode($user->salary->fixed_deductions, true);
            if (is_array($decoded_deductions)) {
                $fixed_entity_deduction = $decoded_deductions;
            }
        }

        $all_fixed_entity          = array_merge($fixed_entity_allowance, $fixed_entity_deduction);
        $gettotaladAdvanceSalary   = $this->monthlyfixedAdvanceSalaryCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
        $totaladAdvanceSalary      = $gettotaladAdvanceSalary['AdvanceSalaryAmount'];
        $approvedAdvanceLoanAmount = $gettotaladAdvanceSalary['loanAmount'];
        $totalDeduction            = $this->getTotalUserDeduction($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
        // dd($expense);

        $total_present = $user->attendances()
            ->where('status', \Modules\Attendance\Enums\AttendanceStatus::Present)
            ->whereYear('date', $payslip->year)
            ->whereMonth('date', $payslip->month_code)
            ->count();

        $total_weekend = $user->attendances()
            ->where('status', \Modules\Attendance\Enums\AttendanceStatus::Weekend)
            ->whereYear('date', $payslip->year)
            ->whereMonth('date', $payslip->month_code)
            ->count();

        // $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
        //     ->where('year', $payslip->year)
        //     ->whereHas('leaveType', function ($q) {
        //         $q->where('name', 'Vacation');
        //     })
        //     ->value('monthwiseDay');
        $checkmonthwise = Setting::where('key', 'is_month_wise_show_leave')->value('value');
        if ($checkmonthwise == 1) {
            $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
                ->where('year', $payslip->year)
                ->whereHas('leaveType', function ($q) {
                    $q->where('name', 'Vacation');
                })
                ->value('monthwiseDay');
        } else {
            $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
                ->where('year', $payslip->year)
                ->whereHas('leaveType', function ($q) {
                    $q->where('name', 'Vacation');
                })
                ->value('available');
        }
        $monthStart           = $payslip->year . '-' . $payslip->month_code . '-01';
        $monthEnd             = date('Y-m-t', strtotime($monthStart));
        $totalTakenSickLeaves = Leave::where('user_id', $user->id)
            ->where('year', $payslip->year)
            ->whereDate('start_date', '<=', $monthEnd)
            ->whereDate('end_date', '>=', $monthStart)
            ->whereHas('type', function ($q) {
                $q->where('name', 'sick');
            })
            ->sum('total_leave_days');

        $totalTakenCancelOffLeaves = Leave::where('user_id', $user->id)
            ->where('year', $payslip->year)
            ->whereHas('type', function ($q) {
                $q->where('name', 'cancel off');
            })
            ->sum('total_leave_days');

        $totalTakenExtraLeaves = Leave::where('user_id', $user->id)
            ->where('year', $payslip->year)
            ->whereHas('type', function ($q) {
                $q->where('name', 'extra')
                    ->orWhere('name', 'extra leave');
            })
            ->sum('total_leave_days');

        $total_ph_leave = LeaveBalance::where('user_id', $user->id)
            ->where('year', $payslip->year)
            ->whereHas('leaveType', function ($q) {
                $q->where('name', 'ph');
            })
            ->value('available');
        $dayinMonth              = cal_days_in_month(CAL_GREGORIAN, $payslip->month_code, $payslip->year);
        $housingAllowance        = isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : 0;
        $transportationAllowance = isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : 0;
        $functionalAllowance     = isset($all_fixed_entity['functional_allowance']) ? $all_fixed_entity['functional_allowance'] : 0;
        $otherAllowance          = isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : 0;

        $payable_basic_salary             = ($user->salary->basic / $dayinMonth) * $working_days;
        $payable_housing_allowance        = ((int) $housingAllowance / $dayinMonth) * $working_days;
        $payable_transportation_allowance = ((int) $transportationAllowance / $dayinMonth) * $working_days;
        $payable_functional_allowance     = ((int) $functionalAllowance / $dayinMonth) * $working_days;
        $payable_other_allowance          = ((int) $otherAllowance / $dayinMonth) * $working_days;
        //air ticket allowance
        $totalAirTicketAllowance = $this->monthlyAirTicketAllowance($user, $payslip->month_code, $payslip->year);
        $totalPayrollAllowance   = $this->monthlyPayrollAllowance($user, $payslip->month_code, $payslip->year);
        $totalPayrollDeduction   = $this->monthlyPayrollDeduction($user, $payslip->month_code, $payslip->year);

        $Payslip = DocumentType::where("name", "Payslip")->first();
        if (isset($Payslip)) {

            $template                 = $Payslip->template;
            $deduction_allowance_html = view('payroll::payslip.deduction_allowance', [
                'all_fixed_entity'          => $all_fixed_entity,
                'allowances'                => SetAllowanceDeducation::get(),
                'user_salary'               => $user_salary,
                'approvedAdvanceLoanAmount' => $approvedAdvanceLoanAmount,
            ])->render();

            $salary_allowance_html = view('payroll::payslip.salary_allowance', [
                'all_fixed_entity' => $all_fixed_entity,
                'user_salary'      => $user_salary,
                'expense'          => $expense,
                'allowances'       => SetAllowanceDeducation::get(),
            ])->render();
            $year  = $payslip->year;
            $month = str_pad($payslip->month_code, 2, '0', STR_PAD_LEFT); // Ensure 2-digit month

            // Get start and end dates
            $startDate = Carbon::createFromFormat('Y-m', "$year-$month")->startOfMonth()->format('d-m-Y');
            $endDate   = Carbon::createFromFormat('Y-m', "$year-$month")->endOfMonth()->format('d-m-Y');
            if (str_contains(getSetting('currency'), 'AED')) {
                $AEDCurrency = '<img src="' . asset("assets/currency/aedb.png") . '" alt="AED" style="width:15px; height:15px; vertical-align:middle;">';
            } else {
                $AEDCurrency = getSetting('currency');
            }
            $data = [
                '[[company_name]]'                     => $setting[0]['value'],
                '[[company_address]]'                  => $setting[1]['value'],
                '[[currency]]'                         => $AEDCurrency,
                '[[month]]'                            => DateTime::createFromFormat('!m', $payslip->month_code)->format('F'),
                '[[year]]'                             => $payslip->year,
                '[[start_date]]'                       => $startDate,
                '[[end_date]]'                         => $endDate,
                '[[payslip_date]]'                     => $payslip_date,
                '[[username]]'                         => $user->name,
                '[[emp_code]]'                         => $user->employee_id,
                '[[designation]]'                      => $user->designation->name,
                '[[present]]'                          => $total_present,
                '[[joining_date]]'                     => $user->workDetail->joining_date->format(config('project.date_format')) ?? '',
                '[[department]]'                       => $user->department?->name ?? 'NA' ?? '',
                '[[bank_name]]'                        => $user->bankDetail->bank_name ?? '',
                '[[account_number]]'                   => $user->bankDetail->account_number ?? '',
                '[[off_day]]'                          => $total_weekend,
                '[[sick_leave_balance]]'               => $totalTakenSickLeaves,
                '[[cancel_off_leave_balance]]'         => $totalTakenCancelOffLeaves,
                '[[annual_leave_balance]]'             => $total_vacation_leave,
                '[[extra_leave_taken]]'                => $totalTakenExtraLeaves,
                '[[ph_leave_balance]]'                 => isset($total_ph_leave) ? $total_ph_leave : "0",
                '[[basic_salary]]'                     => $user->salary->basic,
                '[[housing_allowance]]'                => isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : "",
                '[[transportation_allowance]]'         => isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : "",
                '[[functional_allowance]]'             => isset($all_fixed_entity['functional_allowance']) ? $all_fixed_entity['functional_allowance'] : "",
                '[[other_allowance]]'                  => isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : "",

                '[[payable_basic_salary]]'             => round($payable_basic_salary, 2),
                '[[payable_housing_allowance]]'        => round($payable_housing_allowance, 2),
                '[[payable_transportation_allowance]]' => round($payable_transportation_allowance, 2),
                '[[payable_functional_allowance]]'     => round($payable_functional_allowance, 2),
                '[[payable_other_allowance]]'          => round($payable_other_allowance, 2),
                '[[air_ticket_allowance]]'             => round($totalAirTicketAllowance, 2),
                '[[total_payroll_allowance]]'          => round($totalPayrollAllowance, 2),

                '[[tips]]'                             => "",
                '[[salary_allowances]]'                => $salary_allowance_html,
                '[[deduction_allowances]]'             => $deduction_allowance_html,
                '[[total_working_days]]'               => $working_days,
                '[[total_earning]]'                    => round(floatval($calculations['attendance_salary']), 2),
                '[[net_amount]]'                       => round(floatval($calculations['net_salary']), 2),
                '[[attendance_deduction]]'             => round(floatval($calculations['attendance_deduction']), 2),
                '[[gross_salary]]'                     => round(floatval($calculations['gross_salary']), 2),
                '[[total_deduction]]'                  => round(floatval($totalDeduction), 2),
                '[[total_deduction_with_attendance]]'  => round(floatval($totalDeduction + $calculations['attendance_deduction']), 2),
                '[[total_payroll_deduction]]'          => round($totalPayrollDeduction, 2),

                '[[logo]]'                             => $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 100px;">' : '',
                '[[small_logo]]'                       => $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 100px;">' : '',
                '[[sign]]'                             => $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 100px;">' : '',
                '[[header]]'                           => $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 100px;">' : '',
                '[[footer]]'                           => $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 100px;">' : '',

            ];

            // Replace placeholders with actual data
            foreach ($data as $placeholder => $value) {
                $template = str_replace($placeholder, $value, $template);
            }
            if ($pdfOrHtml == "pdf") {
                $html     = view('payroll::payslip.openinvoicedynamicpdf', compact('user', 'template'))->render();
                $response = [
                    'success' => true,
                    'html'    => $html,
                ];
                return response()->json($response);
            }

            $html     = view('payroll::payslip.openinvoicedynamic', compact('user', 'template'))->render();
            $response = [
                'success' => true,
                'html'    => $html,
            ];
            return response()->json($response);
        } elseif ($pdfOrHtml == "pdf") {
            $html     = view('payroll::payslip.openinvoicepdf', compact('user', 'payslip', 'setting', 'user_salary', 'calculations', 'payslip_date', 'all_fixed_entity', 'expense', 'totaladAdvanceSalary', 'approvedAdvanceLoanAmount', 'totalAirTicketAllowance', 'totalPayrollAllowance', 'totalPayrollDeduction'))->render();
            $response = [
                'success' => true,
                'html'    => $html,
            ];

            return response()->json($response);
        }

        $html     = view('payroll::payslip.openinvoice', compact('user', 'payslip', 'setting', 'user_salary', 'calculations', 'payslip_date', 'all_fixed_entity', 'expense', 'totaladAdvanceSalary', 'approvedAdvanceLoanAmount', 'totalAirTicketAllowance', 'totalPayrollAllowance', 'totalPayrollDeduction'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Request $request)
    {
        $company = $request->company_document_id ?? "0";

        try {
            $query = UserPaySlip::query()
                ->where('month_code', $request->month)
                ->where('year', $request->year);

            // Filter by company if provided
            if ($company > 0) {
                $query->whereHas('user', function ($q) use ($company) {
                    $q->where('company_document_id', $company);
                });
            }

            $payslips = $query->get();

            if ($payslips->isEmpty()) {
                return response()->json(getErrorResponse("No payroll found for selected filters."));
            }

            foreach ($payslips as $payslip) {
                $payslip->delete();
            }

            return response()->json(getSuccessResponse(createFlashMessage('PaySlip', 'deleted')));
        } catch (\Exception $e) {
            return response()->json(getErrorResponse("Error deleting payroll", $e->getMessage()));
        }
    }

    public function getUserSalaryPayslip(Request $request)
    {
        $year    = $request->year ? $request->year : date('Y');
        $month   = $request->month ? $request->month : date('m');
        $user_id = auth()->id();
        if ($request->ajax()) {
            if (getSetting('attendance_base_payroll') == 'true') {
                $data = UserPaySlip::where(['month_code' => $month, 'year' => $year, 'user_id' => $user_id])->get();

                return DataTables::of($data)
                    ->editColumn('id', function ($payslip) {
                        $user = User::where('id', $payslip->user_id)->first();
                        $html = '<a href=' . route('backend.employee.profile') . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
                        return $html;
                    })
                    ->editColumn('name', function ($payslip) {
                        $user = User::where('id', $payslip->user_id)->first();
                        return $user->name;
                    })
                    ->addColumn('department_name', function ($payslip) {
                        $user = User::with('department')->where('id', $payslip->user_id)->first();
                        return $user->department?->name ?? 'NA';
                    })
                    ->editColumn('basic_salary', function ($payslip) {
                        $user   = User::with('salary')->where('id', $payslip->user_id)->first();
                        $result = $user->salary->basic;
                        return $result;
                    })
                    ->addColumn('net_salary', function ($payslip) {
                        $month      = $payslip->month_code;
                        $year       = $payslip->year;
                        $user       = User::where('id', $payslip->user_id)->first();
                        $net_salary = $this->getNetSalaryAsPerAttendance($user, $month, $year, $payslip->start_date, $payslip->end_date);
                        return $net_salary;
                    })
                    ->addColumn('total_net_salary', function ($payslip) {
                        $month            = $payslip->month_code;
                        $year             = $payslip->year;
                        $user             = User::where('id', $payslip->user_id)->first();
                        $total_net_salary = $this->getTotalNetSalary($user, $month, $year, $payslip->start_date, $payslip->end_date);
                        return $total_net_salary;
                    })
                    ->addColumn('status', function ($payslip) {
                        return __trans($payslip->status);
                    })
                    ->addColumn('action', function ($payslip) {
                        $user  = User::where('id', $payslip->user_id)->first();
                        $btn   = '';
                        $btn  .= createActionButton(route('backend.my-salary.viewPayslipModal', [$payslip]), 'Payslip', 'btn-primary edit-button', '');
                        return $btn;
                    })
                    ->rawColumns(['action', 'id'])
                    ->make(true);
            } else {
                // $data             = UserPaySlip::where(['month_code' => $month, 'year' => $year])->get();

                $data = UserPaySlip::where(['month_code' => $month, 'year' => $year, 'user_id' => $user_id])->get();

                return DataTables::of($data)
                    ->editColumn('id', function ($payslip) {
                        $user = User::where('id', $payslip->user_id)->first();
                        $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
                        return $html;
                    })
                    ->editColumn('name', function ($payslip) {
                        $user = User::where('id', $payslip->user_id)->first();
                        return $user->name;
                    })
                    ->addColumn('department_name', function ($payslip) {
                        $user = User::with('department')->where('id', $payslip->user_id)->first();
                        return $user->department?->name ?? 'NA';
                    })

                    ->editColumn('basic_salary', function ($payslip) {
                        $user   = User::with('salary')->where('id', $payslip->user_id)->first();
                        $result = isset($user->salary) ? $user->salary->basic : 0;
                        return $result;
                    })

                    ->addColumn('net_salary', function ($payslip) {
                        $month        = $payslip->month_code;
                        $year         = $payslip->year;
                        $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user         = User::where('id', $payslip->user_id)->first();
                        $working_days = 0;
                        if (getSetting('payroll_calculation') == 'hourly') {
                            $user         = User::where('id', $payslip->user_id)->first();
                            $working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total_worked');
                            $working_days = $working_days / 60;

                            $basic = isset($user->salary) ? $user->salary->basic : 0;

                            $net_salary = $basic * $working_days;
                        } else {
                            $working_days = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $payslip->user_id])->value('total_working_days');
                            $net_salary   = $this->getNetSalaryAsPerAttendance_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
                        }
                        $net_salary = round(floatval($net_salary), 2);
                        return $net_salary;
                    })

                    ->addColumn('total_net_salary', function ($payslip) {
                        $month        = $payslip->month_code;
                        $year         = $payslip->year;
                        $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
                        $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$year-$month-01"));
                        $user         = User::where('id', $payslip->user_id)->first();
                        $working_days = 0;
                        if (getSetting('payroll_calculation') == 'hourly') {
                            $user         = User::where('id', $payslip->user_id)->first();
                            $working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [$start_date, $end_date])
                                ->sum('total_worked');
                            $working_days = $working_days / 60;

                            $basic = isset($user->salary) ? $user->salary->basic : 0;

                            $total_net_salary = $basic * $working_days;
                        } else {
                            $working_days     = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $payslip->user_id])->value('total_working_days');
                            $total_net_salary = $this->getTotalNetSalary_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
                        }
                        $total_net_salary = round(floatval($total_net_salary), 2);
                        return $total_net_salary;
                    })
                    ->addColumn('status', function ($payslip) {
                        return __trans($payslip->status);
                    })
                    ->addColumn('action', function ($payslip) {
                        $user  = User::where('id', $payslip->user_id)->first();
                        $btn   = '';
                        $btn  .= createActionButton(route('backend.my-salary.viewPayslipModal', [$payslip]), 'Payslip', 'btn-primary edit-button', '');
                        return $btn;
                    })
                    ->rawColumns(['action', 'id'])
                    ->make(true);
            }
        }
        return view('payroll::payslip.employee-payslip', compact('month', 'year'));
    }

    public function viewPayslipModal(UserPaySlip $payslip)
    {
        try {
            $year    = $payslip->year ? $payslip->year : date('Y');
            $month   = $payslip->month_code ? $payslip->month_code : date('m');
            $user    = User::with('department')->where('id', auth()->id())->first();
            $payslip = UserPaySlip::where([
                'month_code' => str_pad($month, 2, '0', STR_PAD_LEFT),
                'year'       => (int) $year,
                'user_id'    => $user->id,
            ])->first();
            // start payslip generation logics
            if ($payslip) {
                $setting      = Setting::whereIn('id', [1, 4])->get();
                $payslip_date = strtoupper(date('F', strtotime(date('Y') . '-' . $payslip->month_code))) . ' ' . $payslip->year;
                $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
                $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));
                $user_salary  = User::with([
                    'all_overtime'  => function ($query) use ($payslip) {
                        // $query->whereBetween('date', [$start_date, $end_date]);
                        $query->where([['month_code', $payslip->month_code], ['year', $payslip->year]]);
                    },
                    'all_allowance' => function ($query) use ($payslip) {

                        $query->where(function ($subquery) use ($payslip) {
                            $subquery->where([['month_code', $payslip->month_code], ['year', $payslip->year]])
                                ->orWhere('is_fixed_for_current_month', 0);
                        });
                    },
                    'all_deduction' => function ($query) use ($payslip) {
                        $query->where(function ($subquery) use ($payslip) {
                            $subquery->where([['month_code', $payslip->month_code], ['year', $payslip->year]])
                                ->orWhere('is_fixed_for_current_month', 0);
                        });
                    },
                ])->where('id', $user->id)->get();
                $user = User::with(['attendances' => function ($query) use ($payslip) {
                    $query->whereMonth('date', $payslip->month_code)->whereYear('date', $payslip->year);
                }])->with('salary')->where('id', $user->id)->first();

                $working_days         = 0;
                $working_days         = EmployeeWorkingDay::where(['month_code' => $payslip->month_code, 'year' => $payslip->year, 'user_id' => $payslip->user_id])->value('total_working_days');
                $attendance_deduction = 0;
                if (getSetting('payroll_calculation') == 'hourly') {
                    $total_working_hour = $user->attendances()
                        ->whereIn('status', [
                            \Modules\Attendance\Enums\AttendanceStatus::Present,
                            \Modules\Attendance\Enums\AttendanceStatus::Late,
                            \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                            \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                        ])
                        ->whereBetween('date', [$start_date, $end_date])
                        ->sum('total_worked');
                    $basic            = isset($user->salary) ? $user->salary->basic : 0;
                    $net_salary       = $basic * $total_working_hour / 60;
                    $total_net_salary = $basic * $total_working_hour / 60;

                    $net_salary       = number_format((float) $net_salary, 2, '.', '');
                    $total_net_salary = number_format((float) $total_net_salary, 2, '.', '');
                } else {
                    if (getSetting('attendance_base_payroll') == 'true') {
                        $working_days = $user->attendances()
                            ->whereIn('status', [
                                \Modules\Attendance\Enums\AttendanceStatus::Present,
                                \Modules\Attendance\Enums\AttendanceStatus::Late,
                                \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                            ])
                            ->whereBetween('date', [$start_date, $end_date])
                        // ->distinct('date')
                        //->groupby('date')
                            ->count();

                        $net_salary       = $this->getNetSalaryAsPerAttendance($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
                        $total_net_salary = $this->getTotalNetSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
                        // $attendance_deduction = $this->getAttendanceDiduction($user, $payslip->month_code, $payslip->year);
                    } else {
                        $net_salary       = $this->getNetSalaryAsPerAttendance_EXTRA($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date, $working_days);
                        $total_net_salary = $this->getTotalNetSalary_EXTRA($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date, $working_days);
                    }
                }
                $gross_salary = $this->getGrossSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

                $expense = $this->monthlyExpensesCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

                $calculations = [
                    'attendance_salary'    => $net_salary,
                    'total_deduction'      => 0,
                    'net_salary'           => $total_net_salary,
                    'attendance_deduction' => $gross_salary - $net_salary,
                    'gross_salary'         => $gross_salary,
                ];
                // Extra added 18-03-2024
                $fixed_entity_allowance = [];
                if (isset($user->salary->fixed_allowances)) {
                    $decoded_allowances = json_decode($user->salary->fixed_allowances, true);
                    if (is_array($decoded_allowances)) {
                        $fixed_entity_allowance = $decoded_allowances;
                    }
                }

                $fixed_entity_deduction = [];
                if (isset($user->salary->fixed_deductions)) {
                    $decoded_deductions = json_decode($user->salary->fixed_deductions, true);
                    if (is_array($decoded_deductions)) {
                        $fixed_entity_deduction = $decoded_deductions;
                    }
                }

                $all_fixed_entity          = array_merge($fixed_entity_allowance, $fixed_entity_deduction);
                $gettotaladAdvanceSalary   = $this->monthlyfixedAdvanceSalaryCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
                $totaladAdvanceSalary      = $gettotaladAdvanceSalary['AdvanceSalaryAmount'];
                $approvedAdvanceLoanAmount = $gettotaladAdvanceSalary['loanAmount'];
                $totalDeduction            = $this->getTotalUserDeduction($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
                // dd($expense);

                $total_present = $user->attendances()
                    ->where('status', \Modules\Attendance\Enums\AttendanceStatus::Present)
                    ->whereYear('date', $payslip->year)
                    ->whereMonth('date', $payslip->month_code)
                    ->count();

                $total_weekend = $user->attendances()
                    ->where('status', \Modules\Attendance\Enums\AttendanceStatus::Weekend)
                    ->whereYear('date', $payslip->year)
                    ->whereMonth('date', $payslip->month_code)
                    ->count();

                $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
                    ->where('year', $payslip->year)
                    ->whereHas('leaveType', function ($q) {
                        $q->where('name', 'Vacation');
                    })
                    ->value('available');

                $monthStart           = $payslip->year . '-' . $payslip->month_code . '-01';
                $monthEnd             = date('Y-m-t', strtotime($monthStart));
                $totalTakenSickLeaves = Leave::where('user_id', $user->id)
                    ->where('year', $payslip->year)
                    ->whereDate('start_date', '<=', $monthEnd)
                    ->whereDate('end_date', '>=', $monthStart)
                    ->whereHas('type', function ($q) {
                        $q->where('name', 'sick');
                    })
                    ->sum('total_leave_days');

                $totalTakenCancelOffLeaves = Leave::where('user_id', $user->id)
                    ->where('year', $payslip->year)
                    ->whereHas('type', function ($q) {
                        $q->where('name', 'cancel off');
                    })
                    ->sum('total_leave_days');

                $totalTakenExtraLeaves = Leave::where('user_id', $user->id)
                    ->where('year', $payslip->year)
                    ->whereHas('type', function ($q) {
                        $q->where('name', 'extra')
                            ->orWhere('name', 'extra leave');
                    })
                    ->sum('total_leave_days');

                $total_ph_leave = LeaveBalance::where('user_id', $user->id)
                    ->where('year', $payslip->year)
                    ->whereHas('leaveType', function ($q) {
                        $q->where('name', 'ph');
                    })
                    ->value('available');

                $dayinMonth              = cal_days_in_month(CAL_GREGORIAN, $payslip->month_code, $payslip->year);
                $housingAllowance        = isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : 0;
                $transportationAllowance = isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : 0;
                $otherAllowance          = isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : 0;
                $functionalAllowance     = isset($all_fixed_entity['functional_allowance']) ? $all_fixed_entity['functional_allowance'] : 0;

                $payable_basic_salary             = ($user->salary->basic / $dayinMonth) * $working_days;
                $payable_housing_allowance        = ((int) $housingAllowance / $dayinMonth) * $working_days;
                $payable_transportation_allowance = ((int) $transportationAllowance / $dayinMonth) * $working_days;
                $payable_other_allowance          = ((int) $otherAllowance / $dayinMonth) * $working_days;
                $payable_functional_allowance     = ((int) $functionalAllowance / $dayinMonth) * $working_days;
                //air ticket allowance
                $totalAirTicketAllowance = $this->monthlyAirTicketAllowance($user, $payslip->month_code, $payslip->year);

                $totalPayrollAllowance = $this->monthlyPayrollAllowance($user, $payslip->month_code, $payslip->year);
                $totalPayrollDeduction = $this->monthlyPayrollDeduction($user, $payslip->month_code, $payslip->year);

                $PayslipTemplate = DocumentType::where("name", "Payslip")->first();

                $deduction_allowance_html = view('payroll::payslip.deduction_allowance', [
                    'all_fixed_entity'          => $all_fixed_entity,
                    'allowances'                => SetAllowanceDeducation::get(),
                    'user_salary'               => $user_salary,
                    'approvedAdvanceLoanAmount' => $approvedAdvanceLoanAmount,
                ])->render();

                $salary_allowance_html = view('payroll::payslip.salary_allowance', [
                    'all_fixed_entity' => $all_fixed_entity,
                    'user_salary'      => $user_salary,
                    'expense'          => $expense,
                    'allowances'       => SetAllowanceDeducation::get(),
                ])->render();
                $year  = $payslip->year;
                $month = str_pad($payslip->month_code, 2, '0', STR_PAD_LEFT); // Ensure 2-digit month

                // Get start and end dates
                $startDate = Carbon::createFromFormat('Y-m', "$year-$month")->startOfMonth()->format('d-m-Y');
                $endDate   = Carbon::createFromFormat('Y-m', "$year-$month")->endOfMonth()->format('d-m-Y');
                if (str_contains(getSetting('currency'), 'AED')) {
                    $AEDCurrency = '<img src="' . asset("assets/currency/aedb.png") . '" alt="AED" style="width:18px; height:18px; vertical-align:middle;">';
                } else {
                    $AEDCurrency = getSetting('currency');
                }
                $data = [
                    '[[company_name]]'                     => $setting[0]['value'],
                    '[[company_address]]'                  => $setting[1]['value'],
                    '[[currency]]'                         => $AEDCurrency,
                    '[[month]]'                            => DateTime::createFromFormat('!m', $payslip->month_code)->format('F'),
                    '[[year]]'                             => $payslip->year,
                    '[[start_date]]'                       => $startDate,
                    '[[end_date]]'                         => $endDate,
                    '[[payslip_date]]'                     => $payslip_date,
                    '[[username]]'                         => $user->name,
                    '[[emp_code]]'                         => $user->employee_id,
                    '[[designation]]'                      => $user->designation->name,
                    '[[present]]'                          => $total_present,
                    '[[joining_date]]'                     => $user->workDetail->joining_date->format(config('project.date_format')) ?? '',
                    '[[department]]'                       => $user->department->name ?? '',
                    '[[bank_name]]'                        => $user->bankDetail->bank_name ?? '',
                    '[[account_number]]'                   => $user->bankDetail->account_number ?? '',
                    '[[off_day]]'                          => $total_weekend,
                    '[[sick_leave_balance]]'               => $totalTakenSickLeaves,
                    '[[cancel_off_leave_balance]]'         => $totalTakenCancelOffLeaves,
                    '[[annual_leave_balance]]'             => $total_vacation_leave,
                    '[[extra_leave_taken]]'                => $totalTakenExtraLeaves,
                    '[[ph_leave_balance]]'                 => isset($total_ph_leave) ? $total_ph_leave : "0",
                    '[[basic_salary]]'                     => $user->salary->basic,

                    '[[payable_basic_salary]]'             => round($payable_basic_salary, 2),
                    '[[payable_housing_allowance]]'        => round($payable_housing_allowance, 2),
                    '[[payable_transportation_allowance]]' => round($payable_transportation_allowance, 2),
                    '[[payable_functional_allowance]]'     => round($payable_functional_allowance, 2),
                    '[[payable_other_allowance]]'          => round($payable_other_allowance, 2),
                    '[[air_ticket_allowance]]'             => round($totalAirTicketAllowance, 2),
                    '[[total_payroll_allowance]]'          => round($totalPayrollAllowance, 2),

                    '[[housing_allowance]]'                => isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : "",
                    '[[transportation_allowance]]'         => isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : "",
                    '[[other_allowance]]'                  => isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : "",
                    '[[functional_allowance]]'             => isset($all_fixed_entity['functional_allowance']) ? $all_fixed_entity['functional_allowance'] : "",
                    '[[tips]]'                             => "",
                    '[[salary_allowances]]'                => $salary_allowance_html,
                    '[[deduction_allowances]]'             => $deduction_allowance_html,
                    '[[total_working_days]]'               => $working_days,
                    '[[total_earning]]'                    => round(floatval($calculations['attendance_salary']), 2),
                    '[[net_amount]]'                       => round(floatval($calculations['net_salary']), 2),
                    '[[attendance_deduction]]'             => round(floatval($calculations['attendance_deduction']), 2),
                    '[[gross_salary]]'                     => round(floatval($calculations['gross_salary']), 2),
                    '[[total_deduction]]'                  => round(floatval($totalDeduction), 2),
                    '[[total_deduction_with_attendance]]'  => round(floatval($totalDeduction + $calculations['attendance_deduction']), 2),
                    '[[total_payroll_deduction]]'          => round($totalPayrollDeduction, 2),

                    '[[logo]]'                             => $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 100px;">' : '',
                    '[[small_logo]]'                       => $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 100px;">' : '',
                    '[[sign]]'                             => $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 100px;">' : '',
                    '[[header]]'                           => $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 100px;">' : '',
                    '[[footer]]'                           => $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 100px;">' : '',

                ];

                if (isset($PayslipTemplate)) {
                    $template = $PayslipTemplate->template;
                    foreach ($data as $placeholder => $value) {
                        $template = str_replace($placeholder, $value, $template);
                    }
                    $html     = view('payroll::payslip.openinvoicedynamic', compact('user', 'template'))->render();
                    $response = [
                        'success' => true,
                        'html'    => $html,
                    ];

                    return response()->json($response);
                } else {
                    $template = view('payroll::payslip.simplePayslipFormate');
                    foreach ($data as $placeholder => $value) {
                        $template = str_replace($placeholder, $value, $template);
                    }
                    $html     = view('payroll::payslip.openinvoice', compact('user', 'payslip', 'setting', 'user_salary', 'calculations', 'payslip_date', 'all_fixed_entity', 'totaladAdvanceSalary', 'approvedAdvanceLoanAmount', 'expense', 'totalAirTicketAllowance', 'totalPayrollAllowance', 'totalPayrollDeduction'))->render();
                    $response = [
                        'success' => true,
                        'html'    => $html,
                    ];

                    return response()->json($response);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payslip data not found!',
                    'data'    => [],
                ]);
            }
            // end
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->error($e->getMessage());
        }
    }

    // public function viewPayslipModal(UserPaySlip $payslip)
    // {

    //     $setting      = Setting::whereIn('id', [1, 4])->get();
    //     $user         = User::where('id', auth()->id())->first();
    //     $payslip_date = date('Y-m-d H:i:s');
    //     $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
    //     $end_date   = $payslip->end_date   ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));

    //     $user_salary  = User::with([
    //         // 'all_overtime'  => function ($query) use ($payslip) {
    //         //     $query->where('month_code', $payslip->month_code);
    //         // },
    //         'all_overtime'  => function ($query) use ($payslip) {
    //             $query->where([['month_code', $payslip->month_code], ['year', $payslip->year]]);
    //         },
    //         'all_allowance' => function ($query) use ($payslip) {
    //             //$query->where('month_code',$payslip->month_code);
    //             $query->where(function ($subquery) use ($payslip) {
    //                 $subquery->where('month_code', $payslip->month_code)
    //                     ->orWhere('is_fixed_for_current_month', 0);
    //             });
    //         },
    //         'all_deduction' => function ($query) use ($payslip) {
    //             //$query->where('month_code',$payslip->month_code);
    //             $query->where(function ($subquery) use ($payslip) {
    //                 $subquery->where('month_code', $payslip->month_code)
    //                     ->orWhere('is_fixed_for_current_month', 0);
    //             });
    //         },
    //     ])->where('id', $user->id)->get();
    //     $net_salary       = $this->getNetSalaryAsPerAttendance($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
    //     $total_net_salary = $this->getTotalNetSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
    //     $calculations     = [
    //         'attendance_salary' => $net_salary,
    //         'total_deduction'   => 0,
    //         'net_salary'        => $total_net_salary,
    //     ];

    //     // Extra added 18-03-2024
    //     $fixed_entity_allowance = [];
    //     if (isset($user->salary->fixed_allowances)) {
    //         $decoded_allowances = json_decode($user->salary->fixed_allowances, true);
    //         if (is_array($decoded_allowances)) {
    //             $fixed_entity_allowance = $decoded_allowances;
    //         }
    //     }

    //     $fixed_entity_deduction = [];
    //     if (isset($user->salary->fixed_deductions)) {
    //         $decoded_deductions = json_decode($user->salary->fixed_deductions, true);
    //         if (is_array($decoded_deductions)) {
    //             $fixed_entity_deduction = $decoded_deductions;
    //         }
    //     }
    //     $gettotaladAdvanceSalary   = $this->monthlyfixedAdvanceSalaryCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
    //     $totaladAdvanceSalary      = $gettotaladAdvanceSalary['AdvanceSalaryAmount'];
    //     $approvedAdvanceLoanAmount = $gettotaladAdvanceSalary['loanAmount'];
    //     $all_fixed_entity          = array_merge($fixed_entity_allowance, $fixed_entity_deduction);

    //     $expense = $this->monthlyExpensesCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

    //     $payslip_date = date('Y-m-d H:i:s');

    //     $html     = view('payroll::payslip.openinvoice', compact('user', 'payslip', 'setting', 'user_salary', 'calculations', 'payslip_date', 'all_fixed_entity', 'totaladAdvanceSalary', 'approvedAdvanceLoanAmount', 'expense'))->render();
    //     $response = [
    //         'success' => true,
    //         'html'    => $html,
    //     ];

    //     return response()->json($response);
    // }

    /* export SIF File */
    // public function sifexport($month, $year)
    // {
    //     return Excel::download(new SalaryInformationExport($month, $year), 'SIF_' . time() . '.csv');
    // }

    // public function sifexport($month, $year, Request $request)
    // {
    //     $companyDocumentId = $request->query('company_document_id');
    //     // You can use $companyDocumentId to filter or add additional logic

    //     // For example:
    //     Log::info("Generating SIF for company_document_id: " . $companyDocumentId);

    //     $columns = array_map('trim', explode(',', $request->get('columns', '')));
    //     if (empty($columns[0])) {
    //         $columns = [
    //             'record_type',
    //             'labour_id',
    //             'emp_name',
    //             'routing_number',
    //             'iban_number',
    //             'pay_start_date',
    //             'pay_end_date',
    //             'number_of_days',
    //             'fixed_income_amount',
    //             'variable_income_amount',
    //             'days_on_leave',
    //             'housing_allowance',
    //             'conveyance_allowance',
    //             'medical_allowance',
    //             'annual_passage_allowance',
    //             'overtime_allowance',
    //             'other_allowance',
    //             'leave_encashment',
    //         ];
    //     }

    //     $allowedColumns = [
    //         'record_type',
    //         'labour_id',
    //         'emp_name',
    //         'routing_number',
    //         'iban_number',
    //         'pay_start_date',
    //         'pay_end_date',
    //         'number_of_days',
    //         'fixed_income_amount',
    //         'variable_income_amount',
    //         'days_on_leave',
    //         'housing_allowance',
    //         'conveyance_allowance',
    //         'medical_allowance',
    //         'annual_passage_allowance',
    //         'overtime_allowance',
    //         'other_allowance',
    //         'leave_encashment'
    //     ];
    //     $columns = array_filter($columns, fn($col) => in_array($col, $allowedColumns));

    //     $scrColumns = array_map('trim', explode(',', $request->get('scr_columns', '')));
    //     if (empty($scrColumns[0])) {
    //         $scrColumns = [
    //             'record_type',
    //             'mol_company_number',
    //             'routing_bank_code',
    //             'file_creation_date',
    //             'file_creation_time',
    //             'salary_month',
    //             'edr_count',
    //             'total_salary',
    //             'payment_currency',
    //             'employer_reference',
    //         ];
    //     }

    //     $export = new SalaryInformationExport($month, $year, $companyDocumentId, $columns, $scrColumns);

    //     return Excel::download($export, 'SIF_' . time() . '.csv');
    // }
    public function sifexport($month, $year, $company = null, Request $request)
    {
        // $companyDocumentId = $request->query('company_document_id');
        $companyDocumentId = 0;
        if ($company > 0) {
            $companyDocumentId = $company;
            Log::info("Generating SIF for company_document_id: " . $companyDocumentId);
        }

        // DATA columns
        $columns = array_map('trim', explode(',', $request->get('columns', '')));
        if (empty($columns[0])) {
            $columns = [
                'record_type',
                'labour_id',
                'emp_name',
                'routing_number',
                'account_number',
                'iban_number',
                'pay_start_date',
                'pay_end_date',
                'number_of_days',
                'fixed_income_amount',
                'variable_income_amount',
                'days_on_leave',
                'housing_allowance',
                'conveyance_allowance',
                'medical_allowance',
                'annual_passage_allowance',
                'overtime_allowance',
                'other_allowance',
                'leave_encashment',
            ];
        }

        $allowedColumns = [
            'record_type',
            'labour_id',
            'emp_name',
            'routing_number',
            'account_number',
            'iban_number',
            'pay_start_date',
            'pay_end_date',
            'number_of_days',
            'fixed_income_amount',
            'variable_income_amount',
            'days_on_leave',
            'housing_allowance',
            'conveyance_allowance',
            'medical_allowance',
            'annual_passage_allowance',
            'overtime_allowance',
            'other_allowance',
            'leave_encashment',
        ];
        $columns = array_filter($columns, fn($col) => in_array($col, $allowedColumns));

        // SCR columns
        $scrColumns = array_map('trim', explode(',', $request->get('scr_columns', '')));
        if (empty($scrColumns[0])) {
            $scrColumns = [
                'record_type',
                'mol_company_number',
                'routing_bank_code',
                'file_creation_date',
                'file_creation_time',
                'salary_month',
                'edr_count',
                'total_salary',
                'payment_currency',
                'employer_reference',
            ];
        }

        // Header toggles
        $showDataHeaders = $request->query('show_data_headers', 1);
        $showScrHeaders  = $request->query('show_scr_headers', 1);

        // Export
        $export = new SalaryInformationExport(
            $month,
            $year,
            $companyDocumentId,
            $columns,
            $scrColumns,
            $showDataHeaders,
            $showScrHeaders
        );

        return Excel::download($export, 'SIF_' . time() . '.csv');
    }

    public function saveSifSettings(Request $request, $id)
    {
        $company = CompanyDocument::findOrFail($id);

        $company->column_index = $request->settings;
        $company->save();

        return response()->json(['status' => 'success']);
    }

    public function getSifSettings($id)
    {
        $company = CompanyDocument::findOrFail($id);

        return response()->json(json_decode($company->column_index ?? '{"columns":[],"scr":[]}'));
    }

    public function sifexportxls($month, $year, $company = null, Request $request)
    {
        // dd($company);
        $companyDocumentId = 0;
        if ($company > 0) {
            $companyDocumentId = $company;
            Log::info("Generating SIF for company_document_id: " . $companyDocumentId);
        }

        $companyDocumentId = ($company > 0) ? $company : 0;

        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        // Read settings saved in DB (JSON)
        $settings = CompanyDocument::where('id', $companyDocumentId)->first();

        $dataColumns = [];
        $scrColumns  = [];

        if ($settings) {
            $saved = json_decode($settings->column_index, true);

            $dataColumns = $saved['columns'] ?? [];
            $scrColumns  = $saved['scr'] ?? [];
        }
        // DATA columns
        // $columns = array_map('trim', explode(',', $request->get('columns', '')));
        // if (empty($columns[0])) {
        //     $columns = [
        //         'record_type',
        //         'labour_id',
        //         'emp_name',
        //         'routing_number',
        //         'account_number',
        //         'iban_number',
        //         'pay_start_date',
        //         'pay_end_date',
        //         'number_of_days',
        //         'fixed_income_amount',
        //         'variable_income_amount',
        //         'days_on_leave',
        //         'housing_allowance',
        //         'conveyance_allowance',
        //         'medical_allowance',
        //         'annual_passage_allowance',
        //         'overtime_allowance',
        //         'other_allowance',
        //         'leave_encashment',
        //     ];
        // }

        // $allowedColumns = [
        //     'record_type',
        //     'labour_id',
        //     'emp_name',
        //     'routing_number',
        //     'account_number',
        //     'iban_number',
        //     'pay_start_date',
        //     'pay_end_date',
        //     'number_of_days',
        //     'fixed_income_amount',
        //     'variable_income_amount',
        //     'days_on_leave',
        //     'housing_allowance',
        //     'conveyance_allowance',
        //     'medical_allowance',
        //     'annual_passage_allowance',
        //     'overtime_allowance',
        //     'other_allowance',
        //     'leave_encashment'
        // ];
        // $columns = array_filter($columns, fn($col) => in_array($col, $allowedColumns));

        // // SCR columns
        // $scrColumns = array_map('trim', explode(',', $request->get('scr_columns', '')));
        // if (empty($scrColumns[0])) {
        //     $scrColumns = [
        //         'record_type',
        //         'mol_company_number',
        //         'routing_bank_code',
        //         'file_creation_date',
        //         'file_creation_time',
        //         'salary_month',
        //         'edr_count',
        //         'total_salary',
        //         'payment_currency',
        //         'employer_reference',
        //     ];
        // }

        // If no saved columns → load default
        if (empty($dataColumns)) {
            $dataColumns = collect([
                ['field' => 'record_type', 'name' => 'Record Type'],
                ['field' => 'labour_id', 'name' => 'Labour ID'],
                ['field' => 'emp_name', 'name' => 'Employee Name'],
                ['field' => 'routing_number', 'name' => 'Routing No'],
                ['field' => 'account_number', 'name' => 'Account No'],
                ['field' => 'iban_number', 'name' => 'IBAN'],
                ['field' => 'pay_start_date', 'name' => 'Start Date'],
                ['field' => 'pay_end_date', 'name' => 'End Date'],
                ['field' => 'number_of_days', 'name' => 'Days'],
                ['field' => 'fixed_income_amount', 'name' => 'Fixed Income'],
                ['field' => 'variable_income_amount', 'name' => 'Variable Income'],
                ['field' => 'days_on_leave', 'name' => 'Days On Leave'],
                ['field' => 'housing_allowance', 'name' => 'Housing Allowance'],
                ['field' => 'conveyance_allowance', 'name' => 'Conveyance Allowance'],
                ['field' => 'medical_allowance', 'name' => 'Medical Allowance'],
                ['field' => 'annual_passage_allowance', 'name' => 'Annual Passage Allowance'],
                ['field' => 'overtime_allowance', 'name' => 'Overtime Allowance'],
                ['field' => 'other_allowance', 'name' => 'Other Allowance'],
                ['field' => 'leave_encashment', 'name' => 'Leave Encashment'],
            ]);
        }

        if (empty($scrColumns)) {
            $scrColumns = collect([
                ['field' => 'record_type', 'name' => 'Record Type'],
                ['field' => 'mol_company_number', 'name' => 'Company No'],
                ['field' => 'routing_bank_code', 'name' => 'Bank Code'],
                ['field' => 'file_creation_date', 'name' => 'Creation Date'],
                ['field' => 'salary_month', 'name' => 'Salary Month'],
                ['field' => 'edr_count', 'name' => 'EDR Count'],
                ['field' => 'total_salary', 'name' => 'Total Salary'],
                ['field' => 'payment_currency', 'name' => 'Payment Currency'],
                ['field' => 'employer_reference', 'name' => 'Employer Reference'],
            ]);
        }

        // Header toggles
        $showDataHeaders = $request->query('show_data_headers', 1);
        $showScrHeaders  = $request->query('show_scr_headers', 1);

        // Export
        $export = new SalaryInformationExport(
            $month,
            $year,
            $companyDocumentId,
            $dataColumns,
            $scrColumns,
            $showDataHeaders,
            $showScrHeaders
        );

        return Excel::download($export, 'SIF_' . time() . '.csv');
    }

    // public function sifexportsif($month, $year, $company = null, Request $request)
    // {
    //     if ($company > 0) {
    //         $companyDocumentId = $company;
    //     }
    //     // Log::info("Generating SIF for company_document_id: " . $companyDocumentId);
    //     // $company = request()->query('company_id');
    //     // if (!$company) {
    //     //     $company = auth()->user()->current_company_id;
    //     //     if (!$company) {
    //     //         $company = auth()->user()->company_id;
    //     //     }
    //     // }

    //     $setting                   = Setting::whereIn('key', ['employer_unique_id', 'bank_code', 'employer_reference_number'])->get();
    //     $license_number = "";
    //     foreach ($setting as $data) {
    //         if ($data->key == 'employer_unique_id') {
    //             $license_number = $data->value;
    //         }
    //     }

    //     if (auth()->check() && auth()->user()->hasRole('admin')) {
    //         if (!empty($company)) {
    //             $companydocument = CompanyDocument::Where("id", $company)->first();
    //         } else {
    //             $companydocument = CompanyDocument::first();
    //         }
    //     } else {
    //         $companydocument = CompanyDocument::Where("id", $company)->first();
    //     }
    //     if (! empty($companydocument)) {
    //         $license_number               = $companydocument->license_number;
    //     }
    //     $current_date              = date('ymd');
    //     $current_time              = date('Hi');

    //     // return Excel::download(new SalaryInformationExport($month, $year, $company), $license_number.$current_date.$current_time.'00.sif', ExcelFormat::CSV);
    //     return Excel::download(
    //         new SalaryInformationExportSIF($month, $year, $company, $current_time),
    //         $license_number . $current_date . $current_time . '00.SIF',
    //         \Maatwebsite\Excel\Excel::CSV,
    //         ['Content-Type' => 'text/plain']
    //     );
    // }

    public function sifexportsif($month, $year, $company = null, Request $request)
    {
        // Determine company document
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            $companydocument = $company ? CompanyDocument::find($company) : CompanyDocument::first();
        } else {
            $companydocument = CompanyDocument::find($company);
        }

        $license_number = $companydocument->license_number ?? '';
        $mol_code       = $companydocument->mol_code ?? '';
        $routing_number = $companydocument->routing_number ?? '';
        $short_name     = $companydocument->short_name ?? '';

        $current_date = date('ymd');
        $current_time = date('Hi');
        $salary_month = sprintf('%02d', $month) . $year;
        $roundoff     = getSetting('roundoff') ? getSetting('roundoff') : 0;

        // Fetch all UserPaySlips for this company/month/year
        $userPayslips = UserPaySlip::with('user', 'bank_details', 'user_salary')
            ->where(['month_code' => $month, 'year' => $year])
            ->whereHas('user', function ($q) use ($company) {
                if ($company) {
                    $q->where('company_document_id', $company);
                }
                $q->whereHas('workDetail', fn($sub) => $sub->where('salary_mode', 'account'));
            })
            ->get();

        $lines               = [];
        $total_salary_amount = 0;
        $totalFixedIncome    = 0.00;
        $totalVariableIncome = 0.00;
        $totalNetIncome      = 0.00;

        // EDR lines (one per employee)
        $total_salary_amount = 0;
        $totalNetIncome      = 0.00;
        foreach ($userPayslips as $userpayslip) {

            $totalFixedIncome    = 0.00;
            $totalVariableIncome = 0.00;

            // $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
            // $end_date   = $payslip->end_date ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));

            // $net_salary = round($this->getTotalNetSalary($payslip->user, $month, $year, $start_date, $end_date), 2);

            // $ministry_no = UserDocument::where([
            //     'user_id' => $payslip->user->id,
            //     'type'    => 'labor_card_no',
            // ])->value('ministry_of_labor_personal_no') ?? 'Not exist';

            // $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            // $lines[] = implode(',', [
            //     'EDR',
            //     $ministry_no,
            //     $payslip->bank_details->routing_number ?? '',
            //     $payslip->bank_details->iba_number ?? '',
            //     $start_date,
            //     $end_date,
            //     (int) $totalDaysInMonth,
            //     (float) $net_salary,
            //     "0.00",
            //     "0.00",
            // ]);
            $start_date = $userpayslip->start_date ?? date('Y-m-01', strtotime("$userpayslip->year-$userpayslip->month_code-01"));
            $end_date   = $userpayslip->end_date ?? date('Y-m-t', strtotime("$userpayslip->year-$userpayslip->month_code-01"));

            $salary = $this->allSalaryCalculations($userpayslip->user, $userpayslip->month_code, $userpayslip->year, $userpayslip->start_date, $userpayslip->end_date);
            if (getSetting('attendance_base_payroll') == 'true') {
                $net_salary = $this->getTotalNetSalary($userpayslip->user, $userpayslip->month_code, $userpayslip->year, $userpayslip->start_date, $userpayslip->end_date);
            } else {
                $working_days = EmployeeWorkingDay::where(['month_code' => $userpayslip->month_code, 'year' => $userpayslip->year, 'user_id' => $userpayslip->user_id])->value('total_working_days');

                $net_salary = $this->getTotalNetSalary_EXTRA($userpayslip->user, $userpayslip->month_code, $userpayslip->year, $userpayslip->start_date, $userpayslip->end_date, $working_days);
            }
            // $start_date = $this->year . '-' . $this->month . '-' . '01';
            // $start_date = "'" . $this->year . '-' . sprintf('%02d', $this->month) . '-' . '01';

            // $end_date          = $this->year . '-' . sprintf('%02d', $this->month) . '-' . '30';
            $month_name                    = date('F', strtotime(date('Y') . '-' . $userpayslip->month_code));
            $month_year                    = $month_name . ' ' . $userpayslip->year;
            $employee_document             = UserDocument::select('ministry_of_labor_personal_no')->where(['user_id' => $userpayslip->user->id, 'type' => 'labor_card_no'])->pluck('ministry_of_labor_personal_no')->first();
            $ministry_of_labor_personal_no = ($employee_document !== null) ? $employee_document : 'Not exist';
            // Get the total number of days in the specified month and year
            $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            // $start_date =  $year . '-' . sprintf('%02d', $month) . '-' . '01';

            // $start_date         =  date("$year-$month-'01'");
            // $end_date         =  date("$year-$month-$totalDaysInMonth");

            $fixedIncome      = $salary['total_fixed_income'];
            $totalFixedIncome += $fixedIncome;

            $variableIncome       = $salary['total_variable_income'];
            $totalVariableIncome += $variableIncome;

            $absentCount  = $salary['absentCount'] ? $salary['absentCount'] : '0';

            $total_net_salary = round((float) $net_salary, $roundoff);
            // $total_net_salary = $net_salary;
            // $total_net_salary = number_format((float) $total_net_salary, 2, '.', '');

            // $totalNetIncome += $total_net_salary;
            $totalNetIncome = round($totalNetIncome + $total_net_salary, $roundoff);
            $net_salary     = round((float) $net_salary, $roundoff);

            //Log::error($this->totalVariableIncome);
            $lines[] = implode(',', [
                'EDR',
                $ministry_of_labor_personal_no,
                (isset($userpayslip->bank_details->routing_number)) ? $userpayslip->bank_details->routing_number : '',
                (isset($userpayslip->bank_details->iba_number)) ? $userpayslip->bank_details->iba_number : '',
                $start_date,
                $end_date,
                (int) $totalDaysInMonth,
                (float) $net_salary,
                "0.00",
                "0",
            ]);
        }

        // SCR line (summary)
        $total_salary_amount = $totalNetIncome;

        $edr_count = str_pad($userPayslips->count(), 2, '0', STR_PAD_LEFT);

        if (! empty($companydocument)) {
            // $employer_id               = $companydocument->license_number;
            $mol_code       = $companydocument->mol_code;
            $short_name     = $companydocument->short_name;
            $routing_number = $companydocument->routing_number;
            // if ($companydocument->sif_scr_employer_refrence == 0) {
            //     $employer_reference_number = $monthName;
            // }
        }
        $total_salary_amount = $totalNetIncome;

        $lines[] = implode(',', [
            'SCR',
            $mol_code,
            $routing_number,
            date('Y-m-d'),
            $current_time,
            $salary_month,
            $edr_count,
            round($total_salary_amount, $roundoff),
            'AED',
            'Salary',
        ]);

        // Convert all lines to a single string
        $content = implode(PHP_EOL, $lines);

        // Set filename
        // $filename = $license_number . $current_date . $current_time . '00.SIF';
        $filename = $mol_code . date('dmy') . date('His') . '.SIF';

        // Return as plain text download
        return Response::make($content, 200, [
            'Content-Type'        => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function openIframeSlip(User $user, UserPaySlip $payslip)
    {
        $setting      = Setting::whereIn('id', [1, 4])->get();
        $user_profile = User::with('profile')->where('id', $user->id)->first();
        $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
        $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));

        $user_salary = User::with([
            // 'all_overtime'  => function ($query) use ($payslip) {
            //     $query->where('month_code', $payslip->month_code);
            // },
            'all_overtime'  => function ($query) use ($payslip) {
                $query->where('month_code', $payslip->month_code)->where('year', $payslip->year);
            },
            'all_allowance' => function ($query) use ($payslip) {
                $query->where('month_code', $payslip->month_code);
            },
            'all_deduction' => function ($query) use ($payslip) {
                $query->where('month_code', $payslip->month_code);
            },
        ])->where('id', $user->id)->get();
        log::info("User_Salary::" . json_encode($user_salary));
        $net_salary       = $this->getNetSalaryAsPerAttendance($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
        $taxes            = $this->getTaxationDetails($user->id, $net_salary);
        $advance          = $this->getLoanDetails($user->id, $net_salary);
        $total_net_salary = $this->getTotalNetSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
        //log::info("total_net_salary::".json_encode($total_net_salary));
        log::info("user_profile::" . json_encode($user_profile));
        log::info("taxes::" . json_encode($taxes));
        $calculations = [
            'attendance_salary' => $net_salary,
            'total_deduction'   => 0,
            'net_salary'        => $total_net_salary,
        ];

        return view('payroll::payslip.paysliptemplate', compact('user', 'user_profile', 'payslip', 'setting', 'user_salary', 'calculations', 'taxes', 'advance'));
    }

    public function showexpense(User $user, Request $request)
    {
        // canPerform('Manage Employee Deduction');
        $query = UserPaySlip::select('month_code', 'year', 'start_date', 'end_date')
            ->where('id', $request->payslip_id)
            ->first();

        $current_month = $query->month_code;
        $current_year  = $query->year;

        // if start_date/end_date available in payslip → use them
        // otherwise fallback to month start and end
        $start_date = $query->start_date ?? Carbon::createFromDate($current_year, $current_month, 1)->startOfDay()->toDateString();
        $end_date   = $query->end_date ?? Carbon::createFromDate($current_year, $current_month, 1)->endOfMonth()->endOfDay()->toDateString();

        $expense = Expense::with('type', 'user', 'documents', 'creator')
            ->whereBetween('date', [$start_date, $end_date])
            ->where("user_id", $user->id)
            ->where("status", 'approved')
            ->where("payment_mode", 'Payroll')
            ->get();

        if ($request->ajax()) {
            return DataTables::of($expense)
            // ->editColumn('employee_name', function ($expense) {
            //     $employee_name = User::select('name')->where('id', $expense->user_id)->first();
            //     return $employee_name->name;
            // })

                ->rawColumns(['id'])
                ->make(true);
        }
    }

    public function gratuity_report_download(Request $request)
    {
        $validated = $request->validate([
            'chosen_date' => 'required|date',
        ]);

        $chosenDate = Carbon::parse($validated['chosen_date']);

        $employees = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->where('status', 'active')->get();
        $data = $employees->map(function ($employee) use ($chosenDate) {
            $gratuity = $employee->calculateGratuity($chosenDate);
            return [
                'Emp ID'      => $employee->employee_id,
                'Full Name'   => $employee->name,
                'DOJ'         => $gratuity['joining_date']->format('Y-m-d'),
                'Designation' => $gratuity['designation'],
                'LWD'         => $gratuity['based_date']->format('Y-m-d'),
                'Basic'       => $gratuity['basic_salary'],
                // 'Days' => $gratuity['day'],
                // 'Months' => $gratuity['month'],
                // 'Year' => $gratuity['year'],
                // 'Below 5 Year' => $gratuity['below5year'],
                // 'Above 5 Year' => $gratuity['above5year'],
                // 'Below 5 Grant' => $gratuity['below5yearsOfAmount'],
                // 'Above 5 Grant' => $gratuity['above5yearsOfAmount'],
                'Total Grant' => $gratuity['totalamount'],
            ];
        });

        // Calculate the total gratuity
        $totalamount = $data->sum('totalamount');

        // Add a summary row for the total gratuity
        $data->push([
            'Emp ID'      => null,
            'Full Name'   => "Total",
            'DOJ'         => null,
            'Designation' => null,
            'LWD'         => null,
            'Basic'       => null,
            // 'Days' => null,
            // 'Months' => null,
            // 'Year' => null,
            // 'Below 5 Year' => null,
            // 'Above 5 Year' => null,
            // 'Below 5 Grant' => null,
            // 'Above 5 Grant' => null,
            'Total Grant' => $totalamount,
        ]);

        $headers = [
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
        foreach ($data as $i => $emp) {
            $exportExcel[$i][] = $emp;
        }
        $export = new ExcelExport($exportExcel, $headers);
        return Excel::download($export, 'gratuity_report.xlsx');
    }

    public function medical_insurance_report_download(Request $request)
    {
        $query = User::with('workDetail')->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->where('status', 'active');
        $employees      = $query->get();
        $employees_data = [];

        $totalannual_premium  = 0;
        $totalmonthly_premium = 0;
        foreach ($employees as $row => $employee) {

            $employees_data[$row]['employee_id'] = $employee->employee_id;
            $employees_data[$row]['name']        = $employee->name;

            if ($employee->workDetail) {
                $employees_data[$row]['medical_insurance_provided']  = $employee->workDetail->medical_insurance_provided == 1 ? "Yes" : "No";
                $employees_data[$row]['annual_premium']              = number_format($employee->workDetail->annual_premium, 2);
                $totalannual_premium                                += $employee->workDetail->annual_premium;
                $employees_data[$row]['monthly_premium']             = $employee->workDetail->annual_premium > 0 ? number_format($employee->workDetail->annual_premium / 12, 2) : 0;
                $totalmonthly_premium                               += $employees_data[$row]['monthly_premium'];
            }
        }

        $employees_data[] = [
            'Emp ID'                     => null,
            'Employee Name'              => "Total",
            'medical_insurance_provided' => null,
            'annual_premium'             => number_format($totalannual_premium, 2),
            'monthly_premium'            => number_format($totalmonthly_premium, 2),
        ];
        // Add a summary row for the total gratuity

        $headers = [
            __trans('Emp ID'),
            __trans('Employee Name'),
            __trans('medical_insurance_provided'),
            __trans('annual_premium'),
            __trans('monthly_premium'),
        ];
        $export = new ExcelExport($employees_data, $headers);
        return Excel::download($export, 'medical_insurance_report.xlsx');
    }

    public function air_ticket_report_download(Request $request)
    {
        $query = User::with('workDetail')->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->where('status', 'active');
        $employees = $query->get();

        $employees_data = [];
        $totalamount    = 0;
        foreach ($employees as $row => $employee) {

            $employees_data[$row]['employee_id']          = $employee->employee_id;
            $employees_data[$row]['name']                 = $employee->name;
            $employees_data[$row]['policy_name ']         = "";
            $employees_data[$row]['air_ticket_provided '] = "No";
            $employees_data[$row]['amount ']              = "0";
            if ($employee->workDetail && $employee->workDetail->air_ticket_setting_id > 0) {
                $airticketsetting = AirTicketSetting::findOrFail($employee->workDetail->air_ticket_setting_id);
                if ($airticketsetting) {

                    $request_after_months                          = ! empty($airticketsetting->request_after_months) ? $airticketsetting->request_after_months : 0;
                    $allowance_amount                              = ! empty($airticketsetting->allowance_amount) ? $airticketsetting->allowance_amount : 0;
                    $employees_data[$row]['policy_name ']          = $airticketsetting->policy_name;
                    $employees_data[$row]['air_ticket_provided ']  = "Yes";
                    $employees_data[$row]['amount ']               = number_format(($allowance_amount / $request_after_months), 2);
                    $totalamount                                  += $employees_data[$row]['amount '];
                }
            }
        }
        $employees_data[] = [
            'Emp ID'              => null,
            'Employee Name'       => "Total",
            'air_ticket_provided' => null,
            'policy_name'         => null,
            'amount'              => number_format($totalamount, 2),
        ];
        // Add a summary row for the total gratuity
        $headers = [
            __trans('Emp ID'),
            __trans('Employee Name'),
            __trans('policy_name'),
            __trans('air_ticket_provided'),
            __trans('amount'),
        ];
        $export = new ExcelExport($employees_data, $headers);
        return Excel::download($export, 'air_ticket_report.xlsx');
    }
    public function leave_salary_report_download(Request $request)
    {
        $query = User::with('salary')->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->where('status', 'active');
        $employees = $query->get();

        $employees_data = [];
        $totalamount    = 0;
        foreach ($employees as $row => $employee) {

            $employees_data[$row]['employee_id'] = $employee->employee_id;
            $employees_data[$row]['name']        = $employee->name;
            if ($employee->salary && $employee->salary->basic > 0) {
                $fixed_allowance  = isset($employee->salary) ? json_decode($employee->salary->fixed_allowances, true) : " ";
                $hra              = isset($fixed_allowance['housing_allowance']) ? $fixed_allowance['housing_allowance'] : 0;
                $hra              = intval($hra);
                $travel_allowance = isset($fixed_allowance['transportation_allowance']) ? $fixed_allowance['transportation_allowance'] : 0;
                $travel_allowance = intval($travel_allowance);

                $other_allowance = isset($fixed_allowance['other_allowance']) ? $fixed_allowance['other_allowance'] : 0;
                $other_allowance = intval($other_allowance);
                $food_allowance  = isset($employee->salary->food_allowance) ? $employee->salary->food_allowance : 0;
                // if (getSetting('salary_paid_on') === 'gross') {
                //     $amount = round(
                //         ($employee->salary->basic +
                //             $employee->salary->hra +
                //             $employee->salary->food_allowance +
                //             $employee->salary->travel_allowance +
                //             $employee->salary->other_allowance)
                //             / 12,
                //         2
                //     );
                // }
                // if (getSetting('salary_paid_on') === 'basic') {
                //     $amount = round($employee->salary->basic / 12, 2);
                // }

                // if (getSetting('salary_paid_on') === 'basic_housing') {
                //     $amount = round(($employee->salary->basic + $employee->salary->hra) / 12, 2);
                // }
                if (getSetting('leave_salary') == 'yes') {
                    if (getSetting('salary_paid_on') === 'gross') {
                        $amount = round(
                            ($employee->salary->basic +
                                $hra +
                                $food_allowance +
                                $travel_allowance +
                                $other_allowance)
                            / 12,
                            2
                        );
                    } elseif (getSetting('salary_paid_on') === 'basic') {
                        $amount = round($employee->salary->basic / 12, 2);
                    } elseif (getSetting('salary_paid_on') === 'basic_housing') {
                        $amount = round(($employee->salary->basic + $hra) / 12, 2);
                    }
                }

                $employees_data[$row]['amount '] = $amount;
                $totalamount                     += $amount;
            }
        }
        $employees_data[] = [
            'Emp ID'        => null,
            'Employee Name' => "Total",
            'amount'        => number_format($totalamount, 2),
        ];
        // Add a summary row for the total gratuity
        $headers = [
            __trans('Emp ID'),
            __trans('Employee Name'),
            __trans('amount'),
        ];
        $export = new ExcelExport($employees_data, $headers);
        return Excel::download($export, 'leave_salary_report.xlsx');
    }

    public function get_payroll_details($month, $company_id)
    {

        $query = UserPaySlip::query()
            ->where('month_code', $month)
            ->where('year', date('Y'));
        // Filter by company if provided
        if ($company_id > 0) {
            $query->whereHas('user', function ($q) use ($company_id) {
                $q->where('company_document_id', $company_id);
            });
        }

        $payslips = $query->first();
        // $report = UserPaySlip::where([
        //     ['month_code', $month],
        //     ['year', date('Y')]
        // ])->first();
        if ($payslips) {
            return response()->json(['is_close' => $payslips->is_close]);
        } else {
            return response()->json(['is_close' => 0]);
        }

    }

    private function allSalaryCalculations($user, $month, $year, $start_date, $end_date)
    {
        $total_fixed_income    = 0.00;
        $total_variable_income = 0.00;

        $basic_salary       = $user->salary ? $user->salary->basic : 0;
        $monthly_fixed      = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $total_fixed_income = $basic_salary + $monthly_fixed['total_allowance'];

        // $overtime_amount       = UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');
        $monthly_not_fixed     = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $total_variable_income = $overtime_amount + $monthly_not_fixed['total_allowance'];

        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $formattedMonth   = sprintf("%02d", $month); // Format month with leading zeros
                                                     // $start_date       = $year . '-' . $formattedMonth . '-' . '01';
                                                     // $end_date         = date("$year-$formattedMonth-$totalDaysInMonth");
        $start_date = $start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
        $end_date   = $end_date ?? date('Y-m-t', strtotime("$year-$month-01"));

        $absentCount = $user->attendances->where('status', AttendanceStatus::Absent)->whereBetween('date', [$start_date, $end_date])->count();
        Log::info('absent count = ' . $absentCount . ' ' . $start_date . ' ' . $end_date);
        $collection = [
            'total_fixed_income'    => $total_fixed_income,
            'total_variable_income' => $total_variable_income,
            'absentCount'           => $absentCount,
        ];

        return $collection;
    }

}
