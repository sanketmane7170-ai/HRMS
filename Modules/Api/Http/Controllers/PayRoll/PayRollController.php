<?php

namespace Modules\Api\Http\Controllers\PayRoll;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Modules\Payroll\Http\Controllers\UserSalaryController;
use ReflectionMethod;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Api\Transformers\AllowanceResource;
use Modules\Api\Transformers\OvertimeResource;
use Modules\Api\Transformers\DeductionResource;
use Modules\Payroll\Entities\UserPaySlip;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Api\Transformers\PaySlipResource;
use App\Models\Setting;
use App\Models\EmployeeWorkingDay;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use DateTime;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Document\Entities\DocumentType;
use Carbon\Carbon;
use Modules\Payroll\Traits\SalaryCalculation;

class PayRollController extends Controller
{
    use SalaryCalculation;

    public function index()
    {
        try {
            $user = User::where('id', auth()->id())->first();
            // Create an instance of UserSalaryController
            // $userSalaryController = new UserSalaryController();
            $userSalaryController = app(\Modules\Payroll\Http\Controllers\UserSalaryController::class);

    
            // Use reflection to access the protected method
            $reflectionMethod = new ReflectionMethod($userSalaryController, 'getGrossSalary');
            $reflectionMethod->setAccessible(true);
            $gross_salary = $reflectionMethod->invoke($userSalaryController, $user);
    
            $current_month = date('m'); $current_year = date('Y');
    
            $allowance = UserSalaryAllowance::where('user_id',$user->id)->where(['month_code'=>$current_month,'year'=>$current_year])->get();
            $notfixedallowance = UserSalaryAllowance::where('user_id',$user->id)->where('is_fixed_for_current_month',0)->get();
            $merge_allowance = $allowance->merge($notfixedallowance);
            
            $all_allowance = AllowanceResource::collection($merge_allowance);
            
            $all_overtime = OvertimeResource::collection(UserOvertime::where(['user_id'=>$user->id,'month_code'=> $current_month,'year'=>$current_year])->get());
    
            $deduction = UserDeduction::where('user_id',$user->id)->where(['month_code'=> $current_month,'year'=>$current_year])->get();
            $notfixeddeduction = UserDeduction::where('user_id',$user->id)->where('is_fixed_for_current_month',0)->get();
            $merge_deduction = $deduction->merge($notfixeddeduction);
    
            $all_deduction = DeductionResource::collection($merge_deduction);
    
            $data = [
                'basic' => strval($user->salary->basic),
                'gross' => strval($gross_salary),
                'allowance' => $all_allowance,
                'overtime' => $all_overtime,
                'deduction' => $all_deduction
            ];
            return response()->success(__trans('salary_information_fetched_successfully'), $data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->error($e->getMessage());
        }
    }

    public function getUserPaySlip($month,$year){
        try {
            $year = $year ? $year : date('Y');
            $month = $month ? $month : date('m');
            $user = User::with('department')->where('id', auth()->id())->first();
            $payslip = PaySlipResource::collection(UserPaySlip::where(['month_code'=> $month,'year'=> $year,'user_id'=> $user->id])->get());
            Log::error("Payslip Log   ". json_encode($payslip));

            return response()->success(__trans('payslip_information_fetched_successfully'), $payslip);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->error($e->getMessage());
        }
    }

    public function getUserNewPaySlip($month,$year){
        try {
            $year = $year ? $year : date('Y');
            $month = $month ? $month : date('m');
            $user = User::with('department')->where('id', auth()->id())->first();
            $payslip = UserPaySlip::where([
                'month_code' => str_pad($month, 2, '0', STR_PAD_LEFT),
                'year' => (int)$year,
                'user_id' => $user->id,
            ])->first();
            // start payslip generation logics
            if ($payslip) {

                $setting      = Setting::whereIn('id', [1, 4])->get();
                $payslip_date = strtoupper(date('F', strtotime(date('Y') . '-' . $payslip->month_code))) . ' ' . $payslip->year;
                $start_date = $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
                $end_date   = $payslip->end_date   ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));
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

                $working_days = 0;
                $working_days = EmployeeWorkingDay::where(['month_code' => $payslip->month_code, 'year' => $payslip->year, 'user_id' => $payslip->user_id])->value('total_working_days');
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

                    $net_salary = number_format((float) $net_salary, 2, '.', '');
                    $total_net_salary = number_format((float) $total_net_salary, 2, '.', '');
                } else {
                    if (getSetting('attendance_base_payroll') == 'true') {
                        $working_days = $user->attendances()
                            ->whereIn('status', [
                                \Modules\Attendance\Enums\AttendanceStatus::Present,
                                \Modules\Attendance\Enums\AttendanceStatus::Late,
                                \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                \Modules\Attendance\Enums\AttendanceStatus::Weekend
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
                    'attendance_salary' => $net_salary,
                    'total_deduction'   => 0,
                    'net_salary'        => $total_net_salary,
                    'attendance_deduction' => $gross_salary - $net_salary,
                    'gross_salary' => $gross_salary,
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
                
                $monthStart = $payslip->year . '-' . $payslip->month_code . '-01';
                $monthEnd   = date('Y-m-t', strtotime($monthStart));
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
                
                $dayinMonth = cal_days_in_month(CAL_GREGORIAN, $payslip->month_code, $payslip->year);
                $housingAllowance = isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : 0;
                $transportationAllowance = isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : 0;
                $otherAllowance = isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : 0;

                $payable_basic_salary = ($user->salary->basic / $dayinMonth) * $working_days;
                $payable_housing_allowance = ((int) $housingAllowance / $dayinMonth) * $working_days;
                $payable_transportation_allowance = ((int) $transportationAllowance / $dayinMonth) * $working_days;
                $payable_other_allowance = ((int) $otherAllowance / $dayinMonth) * $working_days;

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
                $year = $payslip->year;
                $month = str_pad($payslip->month_code, 2, '0', STR_PAD_LEFT); // Ensure 2-digit month

                // Get start and end dates
                $startDate = Carbon::createFromFormat('Y-m', "$year-$month")->startOfMonth()->format('d-m-Y');
                $endDate = Carbon::createFromFormat('Y-m', "$year-$month")->endOfMonth()->format('d-m-Y');
                if(str_contains(getSetting('currency'), 'AED')){
                    $AEDCurrency = '<img src="' . asset("assets/currency/aedb.png") . '" alt="AED" style="width:18px; height:18px; vertical-align:middle;">';
                } else {
                    $AEDCurrency = getSetting('currency');
                }
                $data = [
                    '[[company_name]]'             => $setting[0]['value'],
                    '[[company_address]]'          => $setting[1]['value'],
                    '[[currency]]'                 => $AEDCurrency,
                    '[[month]]'                    => DateTime::createFromFormat('!m', $payslip->month_code)->format('F'),
                    '[[year]]'                     => $payslip->year,
                    '[[start_date]]'               => $startDate,
                    '[[end_date]]'                 => $endDate,
                    '[[payslip_date]]'             => $payslip_date,
                    '[[username]]'                 => $user->name,
                    '[[emp_code]]'                 => $user->employee_id,
                    '[[designation]]'              => $user->designation->name,
                    '[[present]]'                  => $total_present,
                    '[[joining_date]]'             => $user->workDetail?->joining_date->format(config('project.date_format')) ?? '',
                    '[[department]]'               => $user->department->name ?? '',
                    '[[bank_name]]'                => $user->bankDetail->bank_name ?? '',
                    '[[account_number]]'           => $user->bankDetail->account_number ?? '',
                    '[[off_day]]'                  => $total_weekend,
                    '[[sick_leave_balance]]'       => $totalTakenSickLeaves,
                    '[[cancel_off_leave_balance]]' => $totalTakenCancelOffLeaves,
                    '[[annual_leave_balance]]'     => $total_vacation_leave,
                    '[[extra_leave_taken]]'        => $totalTakenExtraLeaves,
                    '[[ph_leave_balance]]'         => isset($total_ph_leave) ? $total_ph_leave : "0",
                    '[[basic_salary]]'             => $user->salary->basic,

                    '[[payable_basic_salary]]'             => round($payable_basic_salary, 2),
                    '[[payable_housing_allowance]]'        => round($payable_housing_allowance ,2),
                    '[[payable_transportation_allowance]]' => round($payable_transportation_allowance ,2),
                    '[[payable_other_allowance]]'          => round($payable_other_allowance ,2),

                    '[[housing_allowance]]'        => isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : "",
                    '[[transportation_allowance]]' => isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : "",
                    '[[other_allowance]]'          => isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : "",
                    '[[tips]]'                     => "",
                    '[[salary_allowances]]'        => $salary_allowance_html,
                    '[[deduction_allowances]]'     => $deduction_allowance_html,
                    '[[total_working_days]]'     => $working_days,
                    '[[total_earning]]'           => round(floatval($calculations['attendance_salary']), 2),
                    '[[net_amount]]'               => round(floatval($calculations['net_salary']), 2),
                    '[[attendance_deduction]]'     => round(floatval($calculations['attendance_deduction']), 2),
                    '[[gross_salary]]'     => round(floatval($calculations['gross_salary']), 2),
                    '[[total_deduction]]'         => round(floatval($totalDeduction), 2),
                    '[[total_deduction_with_attendance]]' =>   round(floatval($totalDeduction + $calculations['attendance_deduction']), 2),

                    '[[logo]]'             => $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 100px;">' : '',
                    '[[small_logo]]'             => $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 100px;">' : '',
                    '[[sign]]'             => $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 100px;">' : '',
                    '[[header]]'             => $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 100px;">' : '',
                    '[[footer]]'             => $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 100px;">' : '',

                ];

                if (isset($PayslipTemplate)) {
                    $template = $PayslipTemplate->template;
                    foreach ($data as $placeholder => $value) {
                        $template = str_replace($placeholder, $value, $template);
                    }
                    $html = view('payroll::payslip.openinvoicedynamic', compact('user', 'template'))->render();
                    $pdf = \Pdf::loadHTML($html)
                                    ->setOptions([
                                        'isHtml5ParserEnabled' => true,
                                        'isRemoteEnabled' => true,
                                        'defaultFont' => 'DejaVu Sans',
                                    ])->setPaper('tabloid', 'landscape');
                    return $pdf->download('payslip_' . date('Y-m-d') . '.pdf');
                } else {
                    $template = view('payroll::payslip.simplePayslipFormate');
                    foreach ($data as $placeholder => $value) {
                        $template = str_replace($placeholder, $value, $template);
                    }
                    $html = view('payroll::payslip.openinvoicedynamic', compact('user', 'template'))->render();
                    $pdf = \Pdf::loadHTML($html)
                                    ->setOptions([
                                        'isHtml5ParserEnabled' => true,
                                        'isRemoteEnabled' => true,
                                        'defaultFont' => 'DejaVu Sans',
                                    ])->setPaper('tabloid', 'landscape');
                    return $pdf->download('payslip_' . date('Y-m-d') . '.pdf');
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payslip data not found!',
                    'data' => []
                ]);
            }
            // end
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->error($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('api::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('api::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('api::edit');
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
    public function destroy($id)
    {
        //
    }
}
