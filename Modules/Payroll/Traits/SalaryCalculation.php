<?php
namespace Modules\Payroll\Traits;

use App\Models\AirTicketRequest;
use App\Models\EMIAllowance;
use App\Models\EMIAllowanceData;
use App\Models\EMIDeduction;
use App\Models\EMIDeductionData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Entities\Holiday;
use Modules\Expense\Entities\Expense;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Modules\Payroll\Entities\AdvanceRequest;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\PolicySetting\Entities\PolicySettings;

trait SalaryCalculation
{
    public function getGrossSalaryOld($user, $month, $year, $start_date, $end_date)
    {
        $current_month   = $month ? $month : date('m');
        $current_year    = $year ? $year : date('Y');
        $overtime_amount = 0;
        $total_allowance = 0;
        $total_deduction = 0;

        $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $fixed_entity_allowance = 0;
        $basic_salary           = $user->salary->basic;
        if (isset($user->salary->fixed_allowances)) {
            $fixed_entity_allowance = json_decode($user->salary->fixed_allowances, true);
            $fixed_entity_allowance = is_array($fixed_entity_allowance) ? array_sum($fixed_entity_allowance) : 0;
        }

        $fixed_entity_deduction = 0;
        if (isset($user->salary->fixed_deductions)) {
            $fixed_entity_deduction = json_decode($user->salary->fixed_deductions, true);
            $fixed_entity_deduction = is_array($fixed_entity_deduction) ? array_sum($fixed_entity_deduction) : 0;
        }
        // $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');

        $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;
        // $total_allowance = $monthly_fixed['total_allowance'] + $monthly_not_fixed['total_allowance'] + $fixed_entity_allowance;
        $total_allowance = $monthly_fixed['total_allowance'] + $fixed_entity_allowance;

        // $total = $basic_salary + $total_allowance + $overtime_amount;
        // Overtime commented as Per Gross Salary Formula
        $total = $basic_salary + $total_allowance;
        // Code Commented as Per Gross Salary Formula we not minus any deduction from total 12-09-2023
        // if($total > 0){
        //     $total = $total - $total_deduction;
        // }
        return $total ? $total : __trans('not_set');
    }

    // public function getGrossSalary($user, $month, $year, $start_date, $end_date)
    // {
    //     $current_month = $month ? $month : date('m');
    //     $current_year  = $year ? $year : date('Y');

    //     $overtime_amount = 0;
    //     $total_allowance = 0;
    //     $total_deduction = 0;

    //     $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
    //     $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

    //     // if (getSetting('payroll_calculation') == 'salary') {
    //     //     $basic_salary = $user->salary ? $user->salary->basic : 0;
    //     // }else if (getSetting('payroll_calculation') == 'hourly') {
    //     //     $basic_salary = $user->salary ? $user->salary->hourly : 0;
    //     // }
    //     // else{
    //     //     $basic_salary = $user->salary ? $user->salary->basic : 0;
    //     // }
    //     $basic_salary = $user->salary ? $user->salary->basic : 0;

    //     // Extra added 18-03-2024
    //     $fixed_entity_allowance = (isset($user->salary->fixed_allowances) && ! empty($user->salary->fixed_allowances)) ? json_decode($user->salary->fixed_allowances, true) : [];
    //     $fixed_entity_allowance = array_sum($fixed_entity_allowance);

    //     $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty(isset($user->salary->fixed_deductions))) ? json_decode($user->salary->fixed_deductions, true) : [];
    //     $fixed_entity_deduction = array_sum($fixed_entity_deduction);
    //     // End
    //     // $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');
    //     // $overtime_amount = UserOvertime::where('user_id', $user->id)
    //     //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
    //     //     ->sum('calculated_amount');

    //     // $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;
    //     // $total_allowance = $monthly_fixed['total_allowance'] + $monthly_not_fixed['total_allowance'] + $fixed_entity_allowance;
    //     $total_allowance = $fixed_entity_allowance;

    //     // $total = $basic_salary + $total_allowance + $overtime_amount;
    //     // Overtime commented as Per Gross Salary Formula

    //     // $allowances = SetAllowanceDeducation::get();
    //     // $fixed_allowance = isset($user->salary) ? json_decode($user->salary->fixed_allowances, true) :" ";
    //     // $fixed_deduction = isset($user->salary) ? json_decode($user->salary->fixed_deductions, true) : " ";
    //     // $allowanceData = [];
    //     // $deducationData = [];
    //     // $totalAllowances = 0;
    //     // $totalDeductions = 0;
    //     // foreach ($allowances as $allowance) {
    //     //     $allowanceName = $allowance->name;
    //     //     $normalizedAllowanceName = str_replace(' ', '_', strtolower($allowanceName));
    //     //     if($allowance->type==1){
    //     //         if (array_key_exists($allowanceName, $fixed_allowance)) {
    //     //             $totalAllowances += isset($fixed_allowance[$allowanceName]) ? $fixed_allowance[$allowanceName] : $allowance->amount;
    //     //         } else {
    //     //             $totalAllowances += isset($fixed_allowance[$allowanceName]) ? $fixed_allowance[$allowanceName] : $allowance->amount;
    //     //         }
    //     //     }
    //     //     if($allowance->type==2){
    //     //         if (array_key_exists($allowanceName, $fixed_deduction)) {
    //     //             $totalDeductions += isset($fixed_deduction[$allowanceName])? $fixed_deduction[$allowanceName] : $allowance->amount;
    //     //         } else {
    //     //             $totalDeductions += isset($fixed_deduction[$allowanceName])? $fixed_deduction[$allowanceName] : $allowance->amount;
    //     //         }
    //     //     }
    //     // }

    //     // // Sum the allowance data
    //     // if (!empty($allowanceData)) {
    //     //     $totalAllowances = array_sum($allowanceData);
    //     // }

    //     // // Sum the deduction data
    //     // if (!empty($deducationData)) {
    //     //     $totalDeductions = array_sum($deducationData);
    //     // }
    //     // $totalAllowances = $basic_salary==0 ? 0 : $totalAllowances;
    //     // $totalDeductions = $basic_salary==0 ? 0 : $totalDeductions;

    //     $total = $basic_salary + $total_allowance; // - $total_deduction;
    //                                                // Code Commented as Per Gross Salary Formula we not minus any deduction from total 12-09-2023
    //                                                // if($total > 0){
    //                                                //     $total = $total - $total_deduction;
    //                                                // }
    //     $total = number_format((float) $total, 2, '.', '');
    //     return $total ? $total : 0;
    // }

    public function getGrossSalary($user, $month, $year, $start_date, $end_date)
    {
        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');

        /*
    |---------------------------------------------------------------
    | 1. Basic Salary
    |---------------------------------------------------------------
    */
        $basic_salary = $user->salary->basic ?? 0;

        /*
    |---------------------------------------------------------------
    | 2. Fixed Entity Allowance (JSON)
    |---------------------------------------------------------------
    */
        $fixed_entity_allowance = 0;

        if (! empty($user->salary->fixed_allowances)) {
            $decoded                = json_decode($user->salary->fixed_allowances, true);
            $fixed_entity_allowance = is_array($decoded) ? array_sum($decoded) : 0;
        }

        /*
    |---------------------------------------------------------------
    | 3. Policy Check
    |---------------------------------------------------------------
    */
        $policy = PolicySettings::where('type', 'payroll')
            ->where('status', 1)
            ->latest()
            ->first();

        $useFullAllowance = false;

        if ($policy) {
            $formulas = json_decode($policy->policy, true);

            foreach ($formulas as $formula) {
                if (
                    in_array(($formula['source'] ?? ''), ['gross_plus_allowance', 'allowance']) ||
                    in_array(($formula['value'] ?? ''), ['gross_plus_allowance', 'allowance'])
                ) {
                    $useFullAllowance = true;
                    break;
                }
            }
        }

        /*
    |---------------------------------------------------------------
    | 4. If Policy Uses Allowance → Include All
    |---------------------------------------------------------------
    */
        if ($useFullAllowance) {

            // DB Allowances
            $fixed_allowance = UserSalaryAllowance::where([
                'user_id'                    => $user->id,
                'allowance_type'             => 'fixed',
                'month_code'                 => $current_month,
                'year'                       => $current_year,
                'is_fixed_for_current_month' => 1,
            ])->sum('amount');

            $percentage_allowance = UserSalaryAllowance::where([
                'user_id'                    => $user->id,
                'allowance_type'             => 'percentage',
                'month_code'                 => $current_month,
                'year'                       => $current_year,
                'is_fixed_for_current_month' => 1,
            ])->sum('percentage_amount');

            $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation(
                $user,
                $month,
                $year,
                $start_date,
                $end_date
            );

            $notFixedAllowance = (float) ($monthly_not_fixed['total_allowance'] ?? 0);

            $total_allowance =
                $fixed_entity_allowance +
                $fixed_allowance +
                $percentage_allowance +
                $notFixedAllowance;

            $gross_salary = $basic_salary + $total_allowance;

        } else {

            /*
        |---------------------------------------------------------------
        | 5. Default (Old Behavior)
        |---------------------------------------------------------------
        */
            $gross_salary = $basic_salary + $fixed_entity_allowance;
        }

        return (float) round($gross_salary, 2);
    }

    public function getNetSalaryAsPerAttendanceOld($user, $month, $year, $start_date, $end_date)
    {
        //Formula : Gross Salary / 31 * No Of Days Working
        $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
        //$total_working_days = $user->salary->total_working_days;
        //Rewirte code for calculate total working days 04-04-2024
        $total_working_days = $user->attendances->whereIn('status', [
            \Modules\Attendance\Enums\AttendanceStatus::Present,
            \Modules\Attendance\Enums\AttendanceStatus::Late,
            \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
            \Modules\Attendance\Enums\AttendanceStatus::Weekend,
        ])->count();
        $net_salary = ((float) $gross_salary / 31) * $total_working_days;
        return number_format((float) $net_salary, 2, '.', '');
    }

    public function getNetSalaryAsPerAttendance($user, $month, $year, $start_date, $end_date)
    {

        $policy = PolicySettings::where('type', 'payroll')
            ->where('status', 1)
            ->latest()
            ->first();

        if ($policy) {
            return $this->calculateSalaryFromPolicy($policy, $user, $month, $year, $start_date, $end_date);
        }
        if (! $user) {
            return 0;
        }

        //Formula : Gross Salary / 31 * No Of Days Working
        // $gross_salary = $this->getGrossSalary($user, $month, $year);
        //$total_working_days = $user->salary->total_working_days;
        //Rewirte code for calculate total working days 04-04-2024

        ////// Total  working days not adding month or year so adding this

        //$total_working_days = $user->attendances->whereIn('status' , [\Modules\Attendance\Enums\AttendanceStatus::Present,\Modules\Attendance\Enums\AttendanceStatus::Weekend])->count();
        $total_working_days = $user->attendances()
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

        $total_working_hour = $user->attendances()
            ->whereIn('status', [
                \Modules\Attendance\Enums\AttendanceStatus::Present,
                \Modules\Attendance\Enums\AttendanceStatus::Late,
                \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                \Modules\Attendance\Enums\AttendanceStatus::Weekend,
            ])
            ->whereBetween('date', [$start_date, $end_date])
            ->sum('total_worked'); // Replace 'total_worked' with the actual column storing worked hours.

        // $net_salary = ((float) $gross_salary / 31) * $total_working_days;
        // return number_format((float) $net_salary, 2, '.', '');
        $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
        //$total_working_days = $user->salary->total_working_days;
        //Rewirte code for calculate total working days 04-04-2024
        // $total_working_days = $user->attendances->whereIn('status' , [\Modules\Attendance\Enums\AttendanceStatus::Present,
        // \Modules\Attendance\Enums\AttendanceStatus::Weekend])->count();
        // ->when($this->except, function ($query) {
        //     return $query->whereNotIn('id', [$this->except]);
        // })
        // $paidleave = $this->paidLeaveCount($user, $year, $month, $start_date, $end_date);
        // $holiday = $this->holdaydayCount($user, $year, $month, $start_date, $end_date);

        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');

        // $days_in_month = Carbon::createFromDate($current_year, $current_month)->daysInMonth;
        $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        if (getSetting('payroll_calculation') == 'hourly') {
            $net_salary = ((float) $gross_salary * ($total_working_hour / 60));
        } else {
            $workingDays  = userWorkingDays($user, $month, $year, $start_date, $end_date);
            $presentDays  = $workingDays['present_count'] ?? 0;
            $paidleave    = $workingDays['user_leave'] ?? 0;
            $holidaycount = $workingDays['holiday_count'] ?? 0;

            $total_working_days = $presentDays + $paidleave + $holidaycount;
            $net_salary         = (((float) $gross_salary / $days_in_month) * $total_working_days);
        }

        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return number_format((float) $net_salary, $roundoff, '.', '');
    }

    public function paidLeaveCount($user, $year, $month, $start_date, $end_date)
    {

        $start_date = $start_date ?? date('Y-m-01', strtotime("$year-$month-01"));
        $end_date   = $end_date ?? date('Y-m-t', strtotime("$year-$month-01"));

        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');

        $leave = Leave::where('user_id', $user->id)
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('start_date', [$start_date, $end_date]) // leave starts in month
                    ->orWhereBetween('end_date', [$start_date, $end_date])       // leave ends in month
                    ->orWhere(function ($q) use ($start_date, $end_date) {       // leave covers entire month
                        $q->where('start_date', '<=', $start_date)
                            ->where('end_date', '>=', $end_date);
                    });
            })
            ->whereIn('status', [LeaveStatus::Approved->value])
            ->get();

        $paidleave = 0;
        foreach ($leave as $value) {
            $type = LeaveType::find($value->leave_type_id);
            if ($type->is_paid == 1) {
                $startDate = Carbon::parse($value->start_date)->startOfDay();
                $endDate   = Carbon::parse($value->end_date)->endOfDay();

                // $firstOfMonth = Carbon::create($year, $month, 1)->startOfDay();
                // $lastOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
                $firstOfMonth = Carbon::parse($start_date)->startOfDay();
                $lastOfMonth  = Carbon::parse($end_date)->endOfDay();

                if ($startDate->lt($firstOfMonth)) {
                    $startDate = $firstOfMonth;
                }

                if ($endDate->gt($lastOfMonth)) {
                    $endDate = $lastOfMonth;
                }

                $daysInMonth = $startDate->diffInDays($endDate) + 1;
                if ($value->is_half_day == 1) {
                    $daysInMonth = 0.5;
                }
                $holidayCount = 0;
                $prCount      = 0;
                for ($i = 0; $i < $daysInMonth; $i++) {
                    $day = Carbon::parse($startDate)->addDays($i)->toDateString();
                    // Check if the current $day falls within any holiday period
                    $isHoliday = Holiday::whereDate('start_date', '<=', $day)
                        ->whereDate('end_date', '>=', $day)
                        ->exists();
                    if ($isHoliday) {
                        if ($type == 'working') {
                            $holidayCount += 1;
                            if ($value->is_half_day == 1) {
                                $daysInMonth -= 0.5;
                            } else {
                                $daysInMonth -= 1;
                            }
                        }
                    }
                    $is_present = $user->attendances()
                        ->whereIn('status', [
                            \Modules\Attendance\Enums\AttendanceStatus::Present,
                            \Modules\Attendance\Enums\AttendanceStatus::Late,
                            \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                            \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                        ])
                        ->whereDate('date', $day)
                        ->first();
                    if ($is_present) {
                        if ($type == 'working') {
                            $prCount += 1;
                            if ($value->is_half_day == 1) {
                                $daysInMonth -= 0.5;
                            } else {
                                $daysInMonth -= 1;
                            }
                        }
                    }
                }
                // $daysInMonth -= $holidayCount;
                $paidleave += $daysInMonth;
            }
        }
        return $paidleave;
    }

    public function holdaydayCount($user, $year, $month, $start_date, $end_date)
    {

        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');
        $start_date    = $start_date ?? Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end_date      = $end_date ?? Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        // $holiday = Holiday::where(function ($query) use ($year, $month) {
        //     $query->where(function ($subQuery) use ($year, $month) {
        //         $subQuery->whereYear('start_date', $year)
        //             ->whereMonth('start_date', $month);
        //     })
        //         ->orWhere(function ($subQuery) use ($year, $month) {
        //             $subQuery->whereYear('end_date', $year)
        //                 ->whereMonth('end_date', $month);
        //         });
        // })
        //     ->get();
        $holiday = Holiday::where(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('start_date', [$start_date, $end_date])  // holiday starts inside range
                ->orWhereBetween('end_date', [$start_date, $end_date])        // holiday ends inside range
                ->orWhere(function ($subQuery) use ($start_date, $end_date) { // holiday covers entire range
                    $subQuery->where('start_date', '<=', $start_date)
                        ->where('end_date', '>=', $end_date);
                });
        })
            ->get();

        $holidaycount = 0;
        foreach ($holiday as $value) {
            $startDate = Carbon::parse($value->start_date)->startOfDay();
            $endDate   = Carbon::parse($value->end_date)->endOfDay();

            // $firstOfMonth = Carbon::create($year, $month, 1)->startOfDay();
            // $lastOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
            $firstOfMonth = Carbon::parse($start_date)->startOfDay();
            $lastOfMonth  = Carbon::parse($end_date)->endOfDay();

            if ($startDate->lt($firstOfMonth)) {
                $startDate = $firstOfMonth;
            }

            if ($endDate->gt($lastOfMonth)) {
                $endDate = $lastOfMonth;
            }

            $daysInMonth  = $startDate->diffInDays($endDate) + 1;
            $day          = Carbon::parse($startDate)->toDateString();
            $userLeave    = 0;
            $isPresentDay = 0;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                // Check if the current $day falls within any leave period
                $leave = Leave::where('user_id', $user->id)
                    ->whereDate('start_date', '<=', $day)
                    ->whereDate('end_date', '>=', $day)
                    ->with('type')
                //->whereIn('status', [LeaveStatus::Approved->value])
                    ->first();
                // Process the leave data as needed
                if ($leave) {
                    $leaveType = $leave->type ? $leave->type->type->value : '';
                    if ($leaveType == 'calendar') {
                        if ($leave->type->is_paid == 1) {
                            $userLeave += 1;
                        }
                    }
                }
                $is_present = $user->attendances()
                    ->whereIn('status', [
                        \Modules\Attendance\Enums\AttendanceStatus::Present,
                        \Modules\Attendance\Enums\AttendanceStatus::Late,
                        \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                        \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                    ])
                    ->whereDate('date', $day)
                    ->first();
                if ($is_present) {
                    $isPresentDay += 1;
                }
                // Move to the next day
                $day  = Carbon::parse($startDate)->addDays($i)->toDateString();
            }
            $daysInMonth = $daysInMonth - $userLeave - $isPresentDay;

            $holidaycount += $daysInMonth;
        }
        return $holidaycount;
    }

    public function getTotalNetSalaryOld($user, $month, $year, $start_date, $end_date)
    {
        $current_month   = $month ? $month : date('m');
        $current_year    = $year ? $year : date('Y');
        $overtime_amount = 0;
        $total_deduction = 0;

        $attendanceBaseSalary = $this->getNetSalaryAsPerAttendance($user, $month, $year, $start_date, $end_date);
        $monthly_fixed        = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_not_fixed    = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'];
        // $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');

        $total_net_salary = ($attendanceBaseSalary - $total_deduction) + $overtime_amount;
        return number_format((float) $total_net_salary, 2, '.', '');
    }

    public function getTotalNetSalary_old($user, $month, $year, $start_date, $end_date)
    {
        $current_month   = $month ? $month : date('m');
        $current_year    = $year ? $year : date('Y');
        $overtime_amount = 0;
        $total_deduction = 0;

        $attendanceBaseSalary              = (float) $this->getNetSalaryAsPerAttendance($user, $current_month, $current_year, $start_date, $end_date);
        $monthly_fixed                     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed_advance_salary_loan = $this->monthlyfixedAdvanceSalaryCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed_advance_salary      = $monthly_fixed_advance_salary_loan['AdvanceSalaryAmount'];
        $monthly_fixed_advance_loan        = (int) $monthly_fixed_advance_salary_loan['loanAmount'];
        $monthly_not_fixed                 = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $payrollDeduction                  = $this->monthlyPayrollDeduction($user, $month, $year, $start_date, $end_date);

        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty(isset($user->salary->fixed_deductions))) ? json_decode($user->salary->fixed_deductions, true) : [];
        $fixed_entity_deduction = array_sum($fixed_entity_deduction);

        $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction + $payrollDeduction;
        $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');
        // $overtime_amount = UserOvertime::where('user_id', $user->id)
        //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
        //     ->sum('calculated_amount');
        $fixed_allowance      = 0;
        $percentage_allowance = 0;

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');
        $airTicketAllowance = $this->monthlyAirTicketAllowance($user, $month, $year, $start_date, $end_date);
        $payrollAllowance   = $this->monthlyPayrollAllowance($user, $month, $year, $start_date, $end_date);

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $total_allowance   = $fixed_allowance + $percentage_allowance + $monthly_not_fixed['total_allowance'] + $airTicketAllowance + $payrollAllowance;

        $monthly_expense = $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $total_net_salary = (($attendanceBaseSalary + $monthly_fixed_advance_loan) + $overtime_amount + $monthly_expense + $total_allowance) - ($total_deduction + $monthly_fixed_advance_salary);

        return number_format((float) $total_net_salary, 2, '.', '');
    }

    public function getTotalNetSalary_new($user, $month, $year, $start_date, $end_date)
    {
        if (! $user) {

            return 0;
        }
        $current_month   = $month ? $month : date('m');
        $current_year    = $year ? $year : date('Y');
        $overtime_amount = 0;
        $total_deduction = 0;

        $attendanceBaseSalary              = $this->getNetSalaryAsPerAttendance($user, $current_month, $current_year, $start_date, $end_date);
        $monthly_fixed                     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed_advance_salary_loan = $this->monthlyfixedAdvanceSalaryCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed_advance_salary      = $monthly_fixed_advance_salary_loan['AdvanceSalaryAmount'];
        $monthly_fixed_advance_loan        = $monthly_fixed_advance_salary_loan['loanAmount'];
        $monthly_not_fixed                 = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
        $fixed_entity_deduction = array_sum($fixed_entity_deduction);

        $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;
        // $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');

        $monthly_expense = $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $totalUserAllowance = $this->getTotalUserAllowance($user, $month, $year, $start_date, $end_date);

        $total_net_salary = (($attendanceBaseSalary + $monthly_fixed_advance_loan) + $overtime_amount + $monthly_expense + $totalUserAllowance) - ($total_deduction + $monthly_fixed_advance_salary);
        // return number_format((float) $total_net_salary, 2, '.', '');
        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return round((float) $total_net_salary, $roundoff);
    }

    public function getTotalNetSalary($user, $month, $year, $start_date, $end_date)
    {

        $policy = PolicySettings::where('type', 'payroll')
            ->where('status', 1)
            ->latest()
            ->first();
        $includeAllowanceSeparately = true;

        if ($policy) {
            $formulas = json_decode($policy->policy, true);

            // check if policy already uses gross_plus_allowance
            foreach ($formulas as $formula) {
                if (
                    ($formula['source'] ?? '') === 'gross_plus_allowance' ||
                    ($formula['value'] ?? '') === 'gross_plus_allowance'
                ) {
                    $includeAllowanceSeparately = false;
                    break;
                }
            }
        }
        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');

        /*
        |--------------------------------------------------------------------------
        | 1. Base Salary (As Per Attendance)
        |--------------------------------------------------------------------------
        */
        $attendanceBaseSalary = (float) $this->getNetSalaryAsPerAttendance(
            $user,
            $current_month,
            $current_year,
            $start_date,
            $end_date
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Fixed & Not Fixed Expenses
        |--------------------------------------------------------------------------
        */
        $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_expense   = $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);

        /*
        |--------------------------------------------------------------------------
        | 3. Advance Salary & Loan
        |--------------------------------------------------------------------------
        */
        $monthly_fixed_advance_salary_loan = $this->monthlyfixedAdvanceSalaryCalculation(
            $user,
            $month,
            $year,
            $start_date,
            $end_date
        );

        $monthly_fixed_advance_salary = (float) ($monthly_fixed_advance_salary_loan['AdvanceSalaryAmount'] ?? 0);
        $monthly_fixed_advance_loan   = (float) ($monthly_fixed_advance_salary_loan['loanAmount'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | 4. Payroll Deduction
        |--------------------------------------------------------------------------
        */
        $payrollDeduction = (float) $this->monthlyPayrollDeduction(
            $user,
            $month,
            $year,
            $start_date,
            $end_date
        );

        /*
        |--------------------------------------------------------------------------
        | 5. Fixed Entity Deduction (From Salary JSON)
        |--------------------------------------------------------------------------
        */
        $fixed_entity_deduction = 0;

        if (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) {
            $decoded                = json_decode($user->salary->fixed_deductions, true);
            $fixed_entity_deduction = is_array($decoded) ? array_sum($decoded) : 0;
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Total Deduction
        |--------------------------------------------------------------------------
        */
        $total_deduction =
        (float) ($monthly_fixed['total_deduction'] ?? 0) +
        (float) ($monthly_not_fixed['total_deduction'] ?? 0) +
            $fixed_entity_deduction +
            $payrollDeduction;

        /*
        |--------------------------------------------------------------------------
        | 7. Overtime (Date Range Based)
        |--------------------------------------------------------------------------
        */
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date])
            ->sum('calculated_amount');

        /*
        |--------------------------------------------------------------------------
        | 8. Allowances
        |--------------------------------------------------------------------------
        */

        // Fixed Allowance
        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        // Percentage Allowance
        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        // Air Ticket Allowance
        $airTicketAllowance = (float) $this->monthlyAirTicketAllowance(
            $user,
            $month,
            $year,
            $start_date,
            $end_date
        );

        // Payroll Allowance
        $payrollAllowance = (float) $this->monthlyPayrollAllowance(
            $user,
            $month,
            $year,
            $start_date,
            $end_date
        );

        // Not Fixed Allowance
        $notFixedAllowance = (float) ($monthly_not_fixed['total_allowance'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | 9. Total Allowance
        |--------------------------------------------------------------------------
        */
        $total_allowance =
        (float) $fixed_allowance +
        (float) $percentage_allowance +
            $notFixedAllowance +
            $airTicketAllowance +
            $payrollAllowance;

        /*
        |--------------------------------------------------------------------------
        | 10. Final Net Salary
        |--------------------------------------------------------------------------
        */
        // $total_net_salary =
        //     (
        //     ($attendanceBaseSalary + $monthly_fixed_advance_loan)
        //      + $overtime_amount
        //      + $monthly_expense
        //      + $total_allowance
        // )
        //      -
        //     (
        //     $total_deduction
        //      + $monthly_fixed_advance_salary
        // );
        $salary_base = ($attendanceBaseSalary + $monthly_fixed_advance_loan)
             + $overtime_amount
             + $monthly_expense;

        // ✅ Only add allowance if NOT handled in policy
        if ($includeAllowanceSeparately) {
            $salary_base += $total_allowance;
        }

        $total_net_salary =
            $salary_base
             -
            (
            $total_deduction
             + $monthly_fixed_advance_salary
        );

        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return round((float) $total_net_salary, $roundoff);
    }

    public function getTotalUserDeduction($user, $month, $year, $start_date, $end_date)
    {
        $total_deduction = 0;

        $monthly_fixed             = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_not_fixed         = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_payroll_deduction = $this->monthlyPayrollDeduction($user, $month, $year, $start_date, $end_date);

        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
        $fixed_entity_deduction = array_sum($fixed_entity_deduction);

        $monthly_fixed_advance_salary_loan = $this->monthlyfixedAdvanceSalaryCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed_advance_salary      = $monthly_fixed_advance_salary_loan['AdvanceSalaryAmount'];

        $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction + $monthly_fixed_advance_salary + $monthly_payroll_deduction;

        return number_format((float) $total_deduction, 2, '.', '');
    }

    public function getTotalUserExpense($user, $month, $year, $start_date, $end_date)
    {
        $total_expense = 0;

        $total_expense = $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);

        return number_format((float) $total_expense, 2, '.', '');
    }

    public function getTotalUserAllowance($user, $month, $year, $start_date, $end_date)
    {
        $total_allowance           = 0;
        $monthly_not_fixed         = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_payroll_allowance = $this->monthlyPayrollAllowance($user, $month, $year, $start_date, $end_date);
        $current_month             = $month ? $month : date('m');
        $current_year              = $year ? $year : date('Y');
        $fixed_allowance           = 0;
        $percentage_allowance      = 0;

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        $total_allowance = $percentage_allowance + $monthly_not_fixed['total_allowance'] + $fixed_allowance + $monthly_payroll_allowance;

        return number_format((float) $total_allowance, 2, '.', '');
    }
    /**
     * get monthly based allowance & deduction calculation.
     */

    public function monthlyfixedExpensesCalculationOld($user, $month, $year, $start_date, $end_date)
    {
        $current_month        = $month ? $month : date('m');
        $current_year         = $year ? $year : date('Y');
        $fixed_allowance      = 0;
        $percentage_allowance = 0;
        $total_allowance      = 0;
        $fixed_deduction      = 0;
        $percentage_deduction = 0;
        $total_deduction      = 0;

        // $fixed_allowance = UserSalaryAllowance::where([
        //     'user_id' => $user->id,
        //     'allowance_type' => 'fixed',
        //     'month_code' => $current_month,
        //     'year' => $current_year,
        //     'is_fixed_for_current_month' => 1
        // ])->sum('amount');

        // $percentage_allowance = UserSalaryAllowance::where([
        //     'user_id' => $user->id,
        //     'allowance_type' => 'percentage',
        //     'month_code' => $current_month,
        //     'year' => $current_year,
        //     'is_fixed_for_current_month' => 1
        // ])->sum('percentage_amount');

        $fixed_deduction = UserDeduction::where([
            'user_id'                    => $user->id,
            'deduction_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_deduction = UserDeduction::where([
            'user_id'                    => $user->id,
            'deduction_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        $total_allowance = $fixed_allowance + $percentage_allowance;
        $total_deduction = $fixed_deduction + $percentage_deduction;
        $result          = [
            'total_allowance' => $total_allowance,
            'total_deduction' => $total_deduction,
        ];

        return $result;
    }

    public function monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date)
    {
        $current_month        = $month ? $month : date('m');
        $current_year         = $year ? $year : date('Y');
        $fixed_allowance      = 0;
        $percentage_allowance = 0;
        $total_allowance      = 0;
        $fixed_deduction      = 0;
        $percentage_deduction = 0;
        $total_deduction      = 0;

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        $fixed_deduction = UserDeduction::where([
            'user_id'                    => $user->id,
            'deduction_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_deduction = UserDeduction::where([
            'user_id'                    => $user->id,
            'deduction_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        $total_allowance = $fixed_allowance + $percentage_allowance;
        $total_deduction = $fixed_deduction + $percentage_deduction;
        $result          = [
            'total_allowance' => $total_allowance,
            'total_deduction' => $total_deduction,
        ];

        return $result;
    }

    public function monthlyfixedAdvanceSalaryCalculation($user, $month, $year, $start_date, $end_date)
    {
        $current_month       = $month ? $month : date('m');
        $current_year        = $year ? $year : date('Y');
        $AdvanceSalaryAmount = 0;
        $loanAmount          = 0;
        $advanceRequest      = AdvanceRequest::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'closed'])
            ->get();

        foreach ($advanceRequest as $advanceRe) {
            $approveDate     = Carbon::parse($advanceRe->approved_date);
            $approveDatmonth = $approveDate->format('m');
            $approveDatyear  = $approveDate->format('Y');
            if ($current_year == $approveDatyear) {
                if ($approveDatmonth == $current_month) {
                    if ($advanceRe->loan_mode == 'payroll') {
                        $loanAmount += $advanceRe->approved_amount;
                    }
                }
            }
            $howmanymonths = $advanceRe->loan_months;
            $startmonth    = $advanceRe->start_month;
            $month         = Carbon::parse($startmonth);
            for ($i = 1; $i <= $howmanymonths; $i++) {
                if ($current_year == $month->format('Y')) {
                    if ($month->format('m') == $current_month) {
                        $AdvanceSalaryAmount += $advanceRe->installment_amount;
                    }
                }
                $month->addMonth();
            }
        }
        return [
            'AdvanceSalaryAmount' => $AdvanceSalaryAmount,
            'loanAmount'          => $loanAmount,
        ];
    }

    public function monthlyExpensesCalculation($user, $month, $year, $start_date = null, $end_date = null)
    {
        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');

        // Decide date range
        $start = $start_date ?? Carbon::createFromDate($current_year, $current_month, 1)->startOfDay()->toDateString();
        $end   = $end_date ?? Carbon::createFromDate($current_year, $current_month, 1)->endOfMonth()->endOfDay()->toDateString();

        $total_expense = Expense::where([
            'user_id'      => $user->id,
            'status'       => 'approved',
            'payment_mode' => 'Payroll',
        ])
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        return $total_expense;
    }

    public function monthlyAirTicketAllowance($user, $month, $year, $start_date = null, $end_date = null)
    {
        $airticketRequest = AirTicketRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('payment_mode', 'payroll')
            ->whereYear('approved_date', $year)
            ->whereMonth('approved_date', $month);

        if ($start_date && $end_date) {
            $airticketRequest->whereBetween('approved_date', [$start_date, $end_date]);
        }
        $airticketRequest = $airticketRequest->get();

        $total_airticket_allowance = 0;
        foreach ($airticketRequest as $airticket) {
            $total_airticket_allowance += $airticket->requested_amount;
        }
        return $total_airticket_allowance;
    }

    /**
     * get allowance & deduction calculation which are not restricted by month or any condition.
     */
    public function monthlynotfixedExpensesCalculationOld($user, $month, $year, $start_date, $end_date)
    {
        $fixed_allowance      = 0;
        $percentage_allowance = 0;
        $total_allowance      = 0;
        $fixed_deduction      = 0;
        $percentage_deduction = 0;
        $total_deduction      = 0;

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'is_fixed_for_current_month' => 0,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'is_fixed_for_current_month' => 0,
        ])->sum('percentage_amount');

        $fixed_deduction = UserDeduction::where([
            'user_id'                    => $user->id,
            'deduction_type'             => 'fixed',
            'is_fixed_for_current_month' => 0,
        ])->sum('amount');

        $percentage_deduction = UserDeduction::where([
            'user_id'                    => $user->id,
            'deduction_type'             => 'percentage',
            'is_fixed_for_current_month' => 0,
        ])->sum('percentage_amount');

        $total_allowance = $fixed_allowance + $percentage_allowance;
        $total_deduction = $fixed_deduction + $percentage_deduction;
        $result          = [
            'total_allowance' => $total_allowance,
            'total_deduction' => $total_deduction,
        ];

        return $result;
    }
    public function monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date)
    {
        $fixed_allowance      = 0;
        $percentage_allowance = 0;
        $total_allowance      = 0;
        $fixed_deduction      = 0;
        $percentage_deduction = 0;
        $total_deduction      = 0;

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'is_fixed_for_current_month' => 0,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'is_fixed_for_current_month' => 0,
        ])->sum('percentage_amount');

        $fixed_deduction = UserDeduction::where([
            'user_id'                    => $user->id,
            'deduction_type'             => 'fixed',
            'is_fixed_for_current_month' => 0,
        ])->sum('amount');

        $percentage_deduction = UserDeduction::where([
            'user_id'                    => $user->id,
            'deduction_type'             => 'percentage',
            'is_fixed_for_current_month' => 0,
        ])->sum('percentage_amount');

        $total_allowance = $fixed_allowance + $percentage_allowance;
        $total_deduction = $fixed_deduction + $percentage_deduction;
        $result          = [
            'total_allowance' => $total_allowance,
            'total_deduction' => $total_deduction,
        ];

        return $result;
    }

    /*
     **
       Without Attendance Base Payroll Generate Functions
     **
    */

    // public function getNetSalaryAsPerAttendance_EXTRA($user, $month, $year, $start_date, $end_date, $working_days)
    // {
    //     $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
    //     /*
    //       Updated Formula According to Bazzar Gross
    //       ((Gross_salary * 12 Months )/365 Days) * Total_Working_Days
    //     */
    //     // $annual_salary = (float)$gross_salary * 12;
    //     // $daily_salary = $annual_salary / 365;
    //     // $net_salary = $daily_salary * $working_days;
    //     // $days_in_month = Carbon::createFromDate($year, $month)->daysInMonth;
    //     $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

    //     $net_salary = ((float) $gross_salary / $days_in_month) * $working_days;

    //     if ($working_days == $days_in_month) {
    //         // NET SALARY WILL BE GROSS SALARY | FULL MONTH DAYS SALARY
    //         $net_salary = $gross_salary;
    //     }
    //     // if($working_days == 31 && $gross_salary < $net_salary){
    //     //     $net_salary = $gross_salary;
    //     // }
    //     //$net_salary = ((float)$gross_salary * 12)/365*$working_days;
    //     return number_format((float) $net_salary, 2, '.', '');
    // }

    public function getNetSalaryAsPerAttendance_EXTRA($user, $month, $year, $start_date, $end_date, $working_days)
    {
        $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);

        $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

        /*
        |--------------------------------------------------------------------------
        | POLICY BASED CALCULATION
        |--------------------------------------------------------------------------
        */

        $policy = PolicySettings::where('type', 'payroll')
            ->where('status', 1)
            ->latest()
            ->first();

        if ($policy) {

            $formulas = json_decode($policy->policy, true);

            $variables = [
                'gross'         => $gross_salary,
                'working_days'  => $working_days,
                'days_in_month' => $days_in_month,
                'month'         => $month,
                'year'          => $year,
            ];

            $total_working_days = $working_days;
            $net_salary         = $this->calculateSalaryFromPolicy_EXTRA($policy, $user, $month, $year, $start_date, $end_date, $total_working_days);

            $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
            return number_format((float) $net_salary, $roundoff, '.', '');
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT FORMULA (CURRENT LOGIC)
        |--------------------------------------------------------------------------
        */

        $net_salary = ((float) $gross_salary / $days_in_month) * $working_days;

        if ($working_days == $days_in_month) {
            $net_salary = $gross_salary;
        }

        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return number_format((float) $net_salary, $roundoff, '.', '');
    }
    // public function getTotalNetSalary_EXTRA($user, $month, $year, $start_date, $end_date, $working_days)
    // {
    //     $current_month   = $month ? $month : date('m');
    //     $current_year    = $year ? $year : date('Y');
    //     $overtime_amount = 0;
    //     $total_deduction = 0;

    //     $attendanceBaseSalary = $this->getNetSalaryAsPerAttendance_EXTRA($user, $current_month, $current_year, $start_date, $end_date, $working_days);
    //     $monthly_fixed        = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
    //     $monthly_not_fixed    = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

    //     $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
    //     $fixed_entity_deduction = array_sum($fixed_entity_deduction);
    //     $payrollDeduction       = $this->monthlyPayrollDeduction($user, $month, $year, $start_date, $end_date);

    //     $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction + $payrollDeduction;
    //     $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');
    //     // $overtime_amount = UserOvertime::where('user_id', $user->id)
    //     //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
    //     //     ->sum('calculated_amount');
    //     $monthly_expense = $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);

    //     $monthly_fixed_advance_salary_loan = $this->monthlyfixedAdvanceSalaryCalculation($user, $month, $year, $start_date, $end_date);
    //     $monthly_fixed_advance_salary      = $monthly_fixed_advance_salary_loan['AdvanceSalaryAmount'];
    //     $monthly_fixed_advance_loan        = (int) $monthly_fixed_advance_salary_loan['loanAmount'];
    //     $airTicketAllowance                = $this->monthlyAirTicketAllowance($user, $month, $year, $start_date, $end_date);
    //     $payrollAllowance                  = $this->monthlyPayrollAllowance($user, $month, $year, $start_date, $end_date);

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
    //     $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
    //     $total_allowance   = $fixed_allowance + $percentage_allowance + $monthly_not_fixed['total_allowance'] + $airTicketAllowance + $payrollAllowance;

    //     $total_net_salary = ($attendanceBaseSalary + $overtime_amount + $monthly_expense + $total_allowance + $monthly_fixed_advance_loan) - ($total_deduction + $monthly_fixed_advance_salary);
    //     return number_format((float) $total_net_salary, 2, '.', '');
    // }
    public function getTotalNetSalary_EXTRA($user, $month, $year, $start_date, $end_date, $working_days)
    {
        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');

        /*
    |------------------------------------------------------------------
    | 1. Policy Check (IMPORTANT)
    |------------------------------------------------------------------
    */
        $policy = PolicySettings::where('type', 'payroll')
            ->where('status', 1)
            ->latest()
            ->first();

        $includeAllowanceSeparately = true;

        if ($policy) {
            $formulas = json_decode($policy->policy, true);

            foreach ($formulas as $formula) {
                if (
                    in_array(($formula['source'] ?? ''), ['gross_plus_allowance', 'allowance']) ||
                    in_array(($formula['value'] ?? ''), ['gross_plus_allowance', 'allowance'])
                ) {
                    $includeAllowanceSeparately = false;
                    break;
                }
            }
        }

        /*
    |------------------------------------------------------------------
    | 2. Base Salary
    |------------------------------------------------------------------
    */
        $attendanceBaseSalary = $this->getNetSalaryAsPerAttendance_EXTRA(
            $user,
            $current_month,
            $current_year,
            $start_date,
            $end_date,
            $working_days
        );

        /*
    |------------------------------------------------------------------
    | 3. Expenses & Deductions
    |------------------------------------------------------------------
    */
        $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions))
            ? array_sum(json_decode($user->salary->fixed_deductions, true))
            : 0;

        $payrollDeduction = $this->monthlyPayrollDeduction($user, $month, $year, $start_date, $end_date);

        $total_deduction =
            ($monthly_fixed['total_deduction'] ?? 0) +
            ($monthly_not_fixed['total_deduction'] ?? 0) +
            $fixed_entity_deduction +
            $payrollDeduction;

        /*
    |------------------------------------------------------------------
    | 4. Overtime
    |------------------------------------------------------------------
    */
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->where(['month_code' => $current_month, 'year' => $current_year])
            ->sum('calculated_amount');

        /*
    |------------------------------------------------------------------
    | 5. Expenses
    |------------------------------------------------------------------
    */
        $monthly_expense = $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);

        /*
    |------------------------------------------------------------------
    | 6. Advance Salary / Loan
    |------------------------------------------------------------------
    */
        $monthly_fixed_advance_salary_loan = $this->monthlyfixedAdvanceSalaryCalculation($user, $month, $year, $start_date, $end_date);

        $monthly_fixed_advance_salary = (float) ($monthly_fixed_advance_salary_loan['AdvanceSalaryAmount'] ?? 0);
        $monthly_fixed_advance_loan   = (float) ($monthly_fixed_advance_salary_loan['loanAmount'] ?? 0);

        /*
    |------------------------------------------------------------------
    | 7. Allowances
    |------------------------------------------------------------------
    */
        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        $airTicketAllowance = (float) $this->monthlyAirTicketAllowance($user, $month, $year, $start_date, $end_date);
        $payrollAllowance   = (float) $this->monthlyPayrollAllowance($user, $month, $year, $start_date, $end_date);

        $notFixedAllowance = (float) ($monthly_not_fixed['total_allowance'] ?? 0);

        $total_allowance =
            $fixed_allowance +
            $percentage_allowance +
            $notFixedAllowance +
            $airTicketAllowance +
            $payrollAllowance;

        /*
    |------------------------------------------------------------------
    | 8. Final Calculation
    |------------------------------------------------------------------
    */
        $salary_base =
            ($attendanceBaseSalary + $monthly_fixed_advance_loan)
             + $overtime_amount
             + $monthly_expense;

        // ✅ Avoid double allowance
        if ($includeAllowanceSeparately) {
            $salary_base += $total_allowance;
        }

        $total_net_salary =
            $salary_base
             -
            (
            $total_deduction +
            $monthly_fixed_advance_salary
        );

        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return number_format((float) $total_net_salary, $roundoff, '.', '');
    }

    public function getNetSalaryWithoutAttendance($user, $month, $year, $start_date, $end_date, $working_days)
    {
        $total_working_days = $working_days;

        $total_working_hour = $user->attendances()
            ->whereIn('status', [
                \Modules\Attendance\Enums\AttendanceStatus::Present,
                \Modules\Attendance\Enums\AttendanceStatus::Late,
                \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                \Modules\Attendance\Enums\AttendanceStatus::Weekend,
            ])
            ->whereBetween('date', [$start_date, $end_date])
            ->sum('total_worked');

        $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);

        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');

        // $days_in_month = Carbon::createFromDate($current_year, $current_month)->daysInMonth;
        $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

        if (getSetting('payroll_calculation') == 'hourly') {
            $net_salary = ((float) $gross_salary * ($total_working_hour / 60));
        } else {
            $total_working_days = $total_working_days;
            $net_salary         = (((float) $gross_salary / $days_in_month) * $total_working_days);
        }

        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return number_format((float) $net_salary, $roundoff, '.', '');
    }

    public function getTotalNetsalaryWithoutAttendance($user, $month, $year, $start_date, $end_date, $working_days)
    {

        $current_month   = $month ? $month : date('m');
        $current_year    = $year ? $year : date('Y');
        $overtime_amount = 0;
        $total_deduction = 0;

        $attendanceBaseSalary              = $this->getNetSalaryWithoutAttendance($user, $current_month, $current_year, $start_date, $end_date, $working_days);
        $monthly_fixed                     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed_advance_salary_loan = $this->monthlyfixedAdvanceSalaryCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed_advance_salary      = (int) $monthly_fixed_advance_salary_loan['AdvanceSalaryAmount'];
        $monthly_fixed_advance_loan        = (int) $monthly_fixed_advance_salary_loan['loanAmount'];
        $monthly_not_fixed                 = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $airTicketAllowance                = $this->monthlyAirTicketAllowance($user, $month, $year, $start_date, $end_date);
        $payrollAllowance                  = $this->monthlyPayrollAllowance($user, $month, $year, $start_date, $end_date);

        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
        $fixed_entity_deduction = array_sum($fixed_entity_deduction);
        $payrollDeduction       = $this->monthlyPayrollDeduction($user, $month, $year, $start_date, $end_date);

        $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction + $payrollDeduction;
        // $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->sum('calculated_amount');
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');

        $monthly_expense = $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $total_allowance   = $fixed_allowance + $percentage_allowance + $monthly_not_fixed['total_allowance'] + $airTicketAllowance + $payrollAllowance;

        $total_net_salary = (($attendanceBaseSalary + $monthly_fixed_advance_loan) + $overtime_amount + $monthly_expense + $total_allowance) - ($total_deduction + $monthly_fixed_advance_salary);

        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return number_format((float) $total_net_salary, $roundoff, '.', '');
    }

    public function getTotalNetSalary_byDay($user, $month, $departure_date, $monthday = null)
    {
        $month           = $month ? $month : date('m');
        $year            = date('Y');
        $overtime_amount = 0;
        $total_deduction = 0;
        $current_month   = $month ? $month : date('m');
        $current_year    = $year ? $year : date('Y');
        // day wise calculation
        $start_date = Carbon::parse($departure_date)->startOfMonth();
        $end_date   = Carbon::parse($departure_date)->endOfDay();

        $total_working_days = $user->attendances()
            ->whereIn('status', [
                \Modules\Attendance\Enums\AttendanceStatus::Present,
                \Modules\Attendance\Enums\AttendanceStatus::Late,
                \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                \Modules\Attendance\Enums\AttendanceStatus::Weekend,
            ])
            ->whereBetween('date', [$start_date, $end_date])
            ->count();

        $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);

        $monthly_expense   = $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
        $fixed_entity_deduction = array_sum($fixed_entity_deduction);

        $total_deduction = $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;
        // $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $month,
            'year'                       => $year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $month,
            'year'                       => $year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $total_allowance   = $fixed_allowance + $percentage_allowance + $monthly_not_fixed['total_allowance'];

        // $paidleave = $this->paidLeaveCount($user, $year, $month, $start_date, $end_date);
        // $holiday = $this->holdaydayCount($user, $year, $month, $start_date, $end_date);
        $total_working_hour = $user->attendances()
            ->whereIn('status', [
                \Modules\Attendance\Enums\AttendanceStatus::Present,
                \Modules\Attendance\Enums\AttendanceStatus::Late,
                \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                \Modules\Attendance\Enums\AttendanceStatus::Weekend,
            ])
            ->whereBetween('date', [$start_date, $end_date])
            ->sum('total_worked');

        // $days_in_month = Carbon::createFromDate($year, $month)->daysInMonth;
        $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

        if (getSetting('payroll_calculation') == 'hourly') {
            $net_salary = ((float) $gross_salary * ($total_working_hour / 60));
        } else {
            $workdetails = $user->workDetail()->first();
            if ($workdetails->attendance_base == 'yes') {
                $workingDays  = userWorkingDays($user, $month, $year, $start_date, $end_date);
                $presentDays  = $workingDays['present_count'] ?? 0;
                $paidleave    = $workingDays['user_leave'] ?? 0;
                $holidaycount = $workingDays['holiday_count'] ?? 0;

                $total_working_days = $presentDays + $paidleave + $holidaycount;
                $net_salary         = ((float) $gross_salary / $days_in_month) * $total_working_days;
                $net_salary         = ($net_salary - $total_deduction) + $monthly_expense + $overtime_amount + $total_allowance;
            } else {
                $working_days = $monthday; //Carbon::parse($departure_date)->day;
                $net_salary   = $this->getTotalNetSalary_EXTRA($user, $month, date('Y'), $start_date, $end_date, $working_days);
            }
        }
        // end

        $attendanceBaseSalary = $net_salary;

        $total_net_salary = $attendanceBaseSalary;
        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return number_format((float) $total_net_salary, $roundoff, '.', '');
    }

    public function getAttendanceDiduction($user, $month, $year, $start_date, $end_date)
    {

        $total_working_days = $user->attendances()
            ->whereIn('status', [
                \Modules\Attendance\Enums\AttendanceStatus::Present,
                \Modules\Attendance\Enums\AttendanceStatus::Late,
                \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                \Modules\Attendance\Enums\AttendanceStatus::Weekend,
            ])
            ->whereBetween('date', [$start_date, $end_date])
            ->count();

        $gross_salary  = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
        $paidleave     = $this->paidLeaveCount($user, $year, $month, $start_date, $end_date);
        $holiday       = $this->holdaydayCount($user, $year, $month, $start_date, $end_date);
        $current_month = $month ? $month : date('m');
        $current_year  = $year ? $year : date('Y');

        // $days_in_month = Carbon::createFromDate($current_year, $current_month)->daysInMonth;
        $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

        $absent_days = $days_in_month - ($total_working_days + $paidleave + $holiday);
        if ($absent_days < 0) {
            $absent_days = 0;
        }
        $per_day_salary = (float) $gross_salary / $days_in_month;
        $deduction      = $per_day_salary * $absent_days;

        return number_format((float) $deduction, 2, '.', '');
    }

    public function monthlyPayrollAllowance($user, $month, $year, $start_date = null, $end_date = null)
    {
        $emi_allowance     = EMIAllowance::where('user_id', $user->id)->where('fully_paid', 0)->get();
        $totalEMIAllowance = 0;
        foreach ($emi_allowance as $emiAllo) {
            $emiAllowanceData  = EMIAllowanceData::where([
                ['emi_id', '=', $emiAllo->id],
                ['month', '=', $month],
                ['year', '=', $year],
                ['is_paid', '=', 0],
            ])
                ->sum('month_amount');
            $totalEMIAllowance += $emiAllowanceData;
        }
        return $totalEMIAllowance;
    }

    public function monthlyPayrollDeduction($user, $month, $year, $start_date = null, $end_date = null)
    {
        $emi_deduction     = EMIDeduction::where('user_id', $user->id)->where('fully_paid', 0)->get();
        $totalEMIDeduction = 0;
        foreach ($emi_deduction as $emiDedu) {
            $emiDeductionData  = EMIDeductionData::where([
                ['emi_id', '=', $emiDedu->id],
                ['month', '=', $month],
                ['year', '=', $year],
                ['is_paid', '=', 0],
            ])
                ->sum('month_amount');
            $totalEMIDeduction += $emiDeductionData;
        }
        return $totalEMIDeduction;
    }

    // private function calculateSalaryFromPolicy($policy, $user, $month, $year, $start_date, $end_date)
    // {
    //     $formulas = json_decode($policy->policy, true);

    //     if (! $formulas) {
    //         return 0;
    //     }

    //     $variables = [];

    //     // Base values
    //     $variables['gross'] = (float) ($user->salary->gross_salary ?? 0);

    //     $total_working_days = $user->attendances()
    //         ->whereIn('status', [
    //             \Modules\Attendance\Enums\AttendanceStatus::Present,
    //             \Modules\Attendance\Enums\AttendanceStatus::Late,
    //             \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
    //             \Modules\Attendance\Enums\AttendanceStatus::Weekend,
    //         ])
    //         ->whereBetween('date', [$start_date, $end_date])
    //         ->count();

    //     $variables['gross']  = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);

    //     $paidleave     = $this->paidLeaveCount($user, $year, $month, $start_date, $end_date);
    //     $holiday       = $this->holdaydayCount($user, $year, $month, $start_date, $end_date);
    //     $current_month = $month ? $month : date('m');
    //     $current_year  = $year ? $year : date('Y');

    //     // $days_in_month = Carbon::createFromDate($current_year, $current_month)->daysInMonth;
    //     $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

    //     $absent_days = $days_in_month - ($total_working_days + $paidleave + $holiday);
    //     if ($absent_days < 0) {
    //         $absent_days = 0;
    //     }

    //     $variables['absent_days'] = $absent_days;

    //     $variables['days_in_month'] = $days_in_month;

    //     $variables['working_days'] = $total_working_days;
    //     foreach ($formulas as $formula) {

    //         $source = $variables[$formula['source']] ?? 0;

    //         $value = is_numeric($formula['value'])
    //             ? $formula['value']
    //             : ($variables[$formula['value']] ?? 0);

    //         switch ($formula['operator']) {
    //             case '/':
    //                 $result = $value ? $source / $value : 0;
    //                 break;

    //             case '*':
    //                 $result = $source * $value;
    //                 break;

    //             case '+':
    //                 $result = $source + $value;
    //                 break;

    //             case '-':
    //                 $result = $source - $value;
    //                 break;

    //             default:
    //                 $result = 0;
    //         }

    //         $variables[$formula['result']] = $result;
    //     }

    //     return round($variables['net_salary'] ?? 0, 2);
    // }
    // private function calculateSalaryFromPolicy($policy, $user, $month, $year, $start_date, $end_date)
    // {
    //     $formulas = json_decode($policy->policy, true);

    //     if (! $formulas) {
    //         return 0;
    //     }

    //     $variables = [];

    //     $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);

    //     $variables['gross'] = $gross_salary;

    //     // $total_working_days = $user->attendances()
    //     //     ->whereIn('status', [
    //     //         \Modules\Attendance\Enums\AttendanceStatus::Present,
    //     //         \Modules\Attendance\Enums\AttendanceStatus::Late,
    //     //         \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
    //     //         \Modules\Attendance\Enums\AttendanceStatus::Weekend,
    //     //     ])
    //     //     ->whereBetween('date', [$start_date, $end_date])
    //     //     ->count();
    //     // $total_working_days = $user->attendances()
    //     //     ->whereIn('status', [
    //     //         \Modules\Attendance\Enums\AttendanceStatus::Present,
    //     //         \Modules\Attendance\Enums\AttendanceStatus::Late,
    //     //         \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
    //     //         \Modules\Attendance\Enums\AttendanceStatus::Weekend,
    //     //     ])
    //     //     ->whereBetween('date', [$start_date, $end_date])
    //     //     ->distinct('date')
    //     //     ->count('date');
    //     $workingDays = userWorkingDays($user, $month, $year, $start_date, $end_date);

    //     $presentDays  = $workingDays['present_count'] ?? 0;
    //     $paidleave    = $workingDays['user_leave'] ?? 0;
    //     $holidaycount = $workingDays['holiday_count'] ?? 0;
    //     $total_working_days = $workingDays['total_working_days'] ?? 0;

    //     // $paidleave = $this->paidLeaveCount($user, $year, $month, $start_date, $end_date);
    //     // $holiday   = $this->holdaydayCount($user, $year, $month, $start_date, $end_date);

    //     $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

    //     $absent_days = $days_in_month - ($total_working_days);

    //     // dd($total_working_days, $paidleave, $holidaycount, $absent_days, $days_in_month);

    //     if ($absent_days < 0) {
    //         $absent_days = 0;
    //     }

    //     $variables['working_days']  = $total_working_days;
    //     $variables['paid_leave']    = $paidleave;
    //     $variables['holiday']       = $holidaycount;
    //     $variables['absent_days']   = $absent_days;
    //     $variables['days_in_month'] = $days_in_month;

    //     foreach ($formulas as $formula) {

    //         $source = $variables[$formula['source']] ?? 0;

    //         $value = is_numeric($formula['value'])
    //             ? $formula['value']
    //             : ($variables[$formula['value']] ?? 0);

    //         switch ($formula['operator']) {
    //             case '/':
    //                 $result = $value ? $source / $value : 0;
    //                 break;
    //             case '*':
    //                 $result = $source * $value;
    //                 break;
    //             case '+':
    //                 $result = $source + $value;
    //                 break;
    //             case '-':
    //                 $result = $source - $value;
    //                 break;
    //             default:
    //                 $result = 0;
    //         }

    //         $variables[$formula['result']] = $result;
    //     }

    //     Log::info("Salary Calculation Variables:", [
    //         'total_working_days' => $total_working_days,
    //         'absent_days'        => $absent_days,
    //         'days_in_month'      => $days_in_month,
    //         'paidleave'          => $paidleave,
    //         'holiday'            => $holidaycount,
    //         'variables'          => $variables,
    //     ]);
    //     // dd($variables);
    //     return round($variables['net_salary'] ?? 0, 2);
    // }

    private function calculateSalaryFromPolicy($policy, $user, $month, $year, $start_date, $end_date)
    {
        $formulas = json_decode($policy->policy, true);

        if (! $formulas) {
            return 0;
        }

        $variables = [];

        // ✅ Gross Salary
        $gross_salary       = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
        $variables['gross'] = $gross_salary;

        // ✅ ✅ ALLOWANCE CALCULATION (YOUR LOGIC)
        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $month,
            'year'                       => $year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $month,
            'year'                       => $year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $total_allowance = $fixed_allowance
             + $percentage_allowance
             + ($monthly_not_fixed['total_allowance'] ?? 0);

        // ✅ Store variables
        $variables['allowance']            = $total_allowance;
        // $variables['gross_plus_allowance'] = $gross_salary + $total_allowance;
        $variables['gross_plus_allowance'] = $gross_salary;

        // ✅ Working Days Calculation
        $workingDays = userWorkingDays($user, $month, $year, $start_date, $end_date);

        $presentDays        = $workingDays['present_count'] ?? 0;
        $paidleave          = $workingDays['user_leave'] ?? 0;
        $holidaycount       = $workingDays['holiday_count'] ?? 0;
        $total_working_days = $workingDays['total_working_days'] ?? 0;

        $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

        $absent_days = $days_in_month - $total_working_days;
        if ($absent_days < 0) {
            $absent_days = 0;
        }

        // ✅ Store attendance variables
        $variables['working_days']  = $total_working_days;
        $variables['paid_leave']    = $paidleave;
        $variables['holiday']       = $holidaycount;
        $variables['absent_days']   = $absent_days;
        $variables['days_in_month'] = $days_in_month;

        // ✅ ✅ FORMULA EXECUTION
        foreach ($formulas as $formula) {

            // 🔥 SOURCE RESOLVER
            switch ($formula['source']) {
                case 'gross':
                    $source = $variables['gross'];
                    break;

                case 'allowance':
                    $source = $variables['allowance'];
                    break;

                case 'gross_plus_allowance':
                    $source = $variables['gross_plus_allowance'];
                    break;

                default:
                    $source = $variables[$formula['source']] ?? 0;
            }

            // 🔥 VALUE RESOLVER
            if (is_numeric($formula['value'])) {
                $value = $formula['value'];
            } else {
                switch ($formula['value']) {
                    case 'gross':
                        $value = $variables['gross'];
                        break;

                    case 'allowance':
                        $value = $variables['allowance'];
                        break;

                    case 'gross_plus_allowance':
                        $value = $variables['gross_plus_allowance'];
                        break;

                    default:
                        $value = $variables[$formula['value']] ?? 0;
                }
            }

            // 🔥 OPERATION
            switch ($formula['operator']) {
                case '/':
                    $result = $value ? $source / $value : 0;
                    break;

                case '*':
                    $result = $source * $value;
                    break;

                case '+':
                    $result = $source + $value;
                    break;

                case '-':
                    $result = $source - $value;
                    break;

                default:
                    $result = 0;
            }

            $variables[$formula['result']] = $result;
        }

        // ✅ Debug log
        Log::info("Salary Calculation Variables:", [
            'gross'         => $gross_salary,
            'allowance'     => $total_allowance,
            'total_salary'  => $variables['gross_plus_allowance'],
            'working_days'  => $total_working_days,
            'absent_days'   => $absent_days,
            'days_in_month' => $days_in_month,
            'variables'     => $variables,
        ]);

        // dd($variables);
        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;

        return round($variables['net_salary'] ?? 0, $roundoff);
    }

    // private function calculateSalaryFromPolicy_EXTRA($policy, $user, $month, $year, $start_date, $end_date, $total_working_days)
    // {
    //     $formulas = json_decode($policy->policy, true);

    //     if (! $formulas) {
    //         return 0;
    //     }

    //     $variables = [];

    //     $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);

    //     $variables['gross'] = $gross_salary;
    //     $days_in_month      = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

    //     $absent_days = $days_in_month - ($total_working_days);

    //     if ($absent_days < 0) {
    //         $absent_days = 0;
    //     }

    //     $variables['working_days']  = $total_working_days;
    //     $variables['paid_leave']    = 0;
    //     $variables['holiday']       = 0;
    //     $variables['absent_days']   = $absent_days;
    //     $variables['days_in_month'] = $days_in_month;

    //     foreach ($formulas as $formula) {

    //         $source = $variables[$formula['source']] ?? 0;

    //         $value = is_numeric($formula['value'])
    //             ? $formula['value']
    //             : ($variables[$formula['value']] ?? 0);

    //         switch ($formula['operator']) {
    //             case '/':
    //                 $result = $value ? $source / $value : 0;
    //                 break;
    //             case '*':
    //                 $result = $source * $value;
    //                 break;
    //             case '+':
    //                 $result = $source + $value;
    //                 break;
    //             case '-':
    //                 $result = $source - $value;
    //                 break;
    //             default:
    //                 $result = 0;
    //         }

    //         $variables[$formula['result']] = $result;
    //     }

    //     Log::info("Salary Calculation Variables:", [
    //         'total_working_days' => $total_working_days,
    //         'absent_days'        => $absent_days,
    //         'days_in_month'      => $days_in_month,
    //         'paidleave'          => 0,
    //         'holiday'            => 0,
    //         'variables'          => $variables,
    //     ]);

    //     return round($variables['net_salary'] ?? 0, 2);
    // }
    private function calculateSalaryFromPolicy_EXTRA($policy, $user, $month, $year, $start_date, $end_date, $total_working_days)
    {
        $formulas = json_decode($policy->policy, true);

        if (! $formulas) {
            return 0;
        }

        $variables = [];

        // ✅ Gross Salary
        $gross_salary       = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
        $variables['gross'] = $gross_salary;

        // ✅ ✅ ALLOWANCE LOGIC (same as main function)
        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $month,
            'year'                       => $year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $month,
            'year'                       => $year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $total_allowance = $fixed_allowance
             + $percentage_allowance
             + ($monthly_not_fixed['total_allowance'] ?? 0);

        // ✅ Variables
        $variables['allowance']            = $total_allowance;
        // $variables['gross_plus_allowance'] = $gross_salary + $total_allowance;
        $variables['gross_plus_allowance'] = $gross_salary;

        // ✅ Days calculation
        $days_in_month = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;

        $absent_days = $days_in_month - $total_working_days;
        if ($absent_days < 0) {
            $absent_days = 0;
        }

        $variables['working_days']  = $total_working_days;
        $variables['paid_leave']    = 0;
        $variables['holiday']       = 0;
        $variables['absent_days']   = $absent_days;
        $variables['days_in_month'] = $days_in_month;

        // ✅ ✅ FORMULA ENGINE
        foreach ($formulas as $formula) {

            // 🔥 SOURCE
            switch ($formula['source']) {
                case 'gross':
                    $source = $variables['gross'];
                    break;

                case 'allowance':
                    $source = $variables['allowance'];
                    break;

                case 'gross_plus_allowance':
                    $source = $variables['gross_plus_allowance'];
                    break;

                default:
                    $source = $variables[$formula['source']] ?? 0;
            }

            // 🔥 VALUE
            if (is_numeric($formula['value'])) {
                $value = $formula['value'];
            } else {
                switch ($formula['value']) {
                    case 'gross':
                        $value = $variables['gross'];
                        break;

                    case 'allowance':
                        $value = $variables['allowance'];
                        break;

                    case 'gross_plus_allowance':
                        $value = $variables['gross_plus_allowance'];
                        break;

                    default:
                        $value = $variables[$formula['value']] ?? 0;
                }
            }

            // 🔥 OPERATION
            switch ($formula['operator']) {
                case '/':
                    $result = $value ? $source / $value : 0;
                    break;

                case '*':
                    $result = $source * $value;
                    break;

                case '+':
                    $result = $source + $value;
                    break;

                case '-':
                    $result = $source - $value;
                    break;

                default:
                    $result = 0;
            }

            $variables[$formula['result']] = $result;
        }

        // ✅ Debug log
        Log::info("Salary Calculation EXTRA:", [
            'gross'         => $gross_salary,
            'allowance'     => $total_allowance,
            'total_salary'  => $variables['gross_plus_allowance'],
            'working_days'  => $total_working_days,
            'absent_days'   => $absent_days,
            'days_in_month' => $days_in_month,
            'variables'     => $variables,
        ]);
        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;

        return round($variables['net_salary'] ?? 0, $roundoff);
    }
}
