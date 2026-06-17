<?php
namespace App\Exports;

use App\Models\EmployeeWorkingDay;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings; // Add WithEvents interface
use Maatwebsite\Excel\Concerns\WithMapping;  // Import AfterSheet event
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\CompanyDocument\Entities\CompanyDocument;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserPaySlip;
use Modules\Payroll\Traits\SalaryCalculation;

class SalaryInformationExportSIF implements FromQuery, WithHeadings, WithMapping, WithEvents, WithCustomCsvSettings
{
    use SalaryCalculation;
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $month;
    protected $year;
    protected $company;
    protected $totalFixedIncome;
    protected $totalVariableIncome;
    protected $totalNetIncome;
    protected $current_time;

    public function __construct($month, $year, $company, $current_time)
    {
        $this->month = $month;
        $this->year  = $year;
        if (! $company) {
            $company = auth()->user()->company_document_id;
            if (! $company) {
                $company = auth()->user()->company_document_id;
            }
        }
        $this->company      = $company;
        $this->current_time = $current_time;

        $this->totalFixedIncome    = 0.00;
        $this->totalVariableIncome = 0.00;
        $this->totalNetIncome      = 0.00;
    }

    public function query()
    {

        // $query = UserPaySlip::with('users', 'bank_details', 'user_salary')->where(['month_code' => $this->month, 'year' => $this->year])
        // // ->whereHas('user');
        //     ->whereHas('user', function ($query) {
        //         if (!empty($this->company)) {
        //             $query->where('company_id', $this->company);
        //         }
        //     });

        // // $query = UserPaySlip::with('users', 'bank_details', 'user_salary')->where(['month_code' => $this->month, 'year' => $this->year]);
        // return $query;

        $query = UserPaySlip::with('user', 'bank_details', 'user_salary')
            ->where(['month_code' => $this->month, 'year' => $this->year])
            ->whereHas('user', function ($query) {
                if (! empty($this->company)) {
                    $query->where('company_document_id', $this->company);
                }

                $query->whereHas('workDetail', function ($subQ) {
                    $subQ->where('salary_mode', 'account');
                });
            });
        $query->whereDoesntHave('user.roles', function ($q) {
            $q->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        });
        // dd(
        //     $query->toSql(),
        //     $query->getBindings()
        // );
        Log::info("SIF:-Query count = " . $query->count());

        return $query;
    }

    public function map($userpayslip): array
    {
        Log::info("SIF:-MAP FUNCTION CALLED");
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
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        // $start_date =  $this->year . '-' . sprintf('%02d', $this->month) . '-' . '01';

        // $start_date         =  date("$this->year-$this->month-'01'");
        // $end_date         =  date("$this->year-$this->month-$totalDaysInMonth");

        $fixedIncome            = $salary['total_fixed_income'];
        $this->totalFixedIncome += $fixedIncome;

        $variableIncome             = $salary['total_variable_income'];
        $this->totalVariableIncome += $variableIncome;

        $absentCount  = $salary['absentCount'] ? $salary['absentCount'] : '0';

        $total_net_salary = round((float) $net_salary, 2);
        // $total_net_salary = $net_salary;
        // $total_net_salary = number_format((float) $total_net_salary, 2, '.', '');

        // $this->totalNetIncome += $total_net_salary;
        $this->totalNetIncome = round($this->totalNetIncome + $total_net_salary, 2);
        Log::info("SIF:-Adding salary: " . $total_net_salary);
        Log::info("SIF:-Running totalNetIncome: " . $this->totalNetIncome);

        $roundoff=getSetting('roundoff')? getSetting('roundoff') : 0;

        //Log::error($this->totalVariableIncome);
        $data = [
            "EDR",
            $ministry_of_labor_personal_no,
            (isset($userpayslip->bank_details->routing_number)) ? $userpayslip->bank_details->routing_number : '',
            (isset($userpayslip->bank_details->iba_number)) ? $userpayslip->bank_details->iba_number : '',
            $start_date,
            $end_date,
            (int) $totalDaysInMonth,
            (float) round($total_net_salary, $roundoff),
            // (float) number_format('0', 2, '.', ''),
            "0.00",
            "0",
        ];

        return $data;
    }

    public function headings(): array
    {
        // $headers = [
        //     __trans('record_type'),
        //     __trans('labour_id'),
        //     __trans('routing_number'),
        //     __trans('iban_number'),
        //     __trans('pay_start_date'),
        //     __trans('pay_end_date'),
        //     __trans('number_of_days'),
        //     __trans('fixed_income_amount'),
        //     __trans('variable_income_amount'),
        //     __trans('net_income_amount'),
        //     __trans('days_on_leave'),
        // ];
        // return $headers;
        return [];
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
        Log::info("SIF:-" . 'absent count = ' . $absentCount . ' ' . $start_date . ' ' . $end_date);
        $collection = [
            'total_fixed_income'    => $total_fixed_income,
            'total_variable_income' => $total_variable_income,
            'absentCount'           => $absentCount,
        ];

        return $collection;
    }

    public function getTotalNetSalary($user, $month, $year, $start_date, $end_date)
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

        $total_net_salary = (($attendanceBaseSalary + $monthly_fixed_advance_loan) + $overtime_amount + $monthly_expense) - ($total_deduction + $monthly_fixed_advance_salary);
        // return number_format((float) $total_net_salary, 2, '.', '');
        return round((float) $total_net_salary, 2);
    }

    public function registerEvents(): array
    {
        $setting                   = Setting::whereIn('key', ['employer_unique_id', 'bank_code', 'employer_reference_number'])->get();
        $employer_id               = '';
        $bank_code                 = '';
        $employer_reference_number = '';
        $current_date              = date('Y-m-d');
        // $current_time              = date('Hi');
        $current_time = $this->current_time;
        $salary_month = sprintf('%02d', $this->month) . $this->year;
        $edr          = UserPaySlip::with('user', 'bank_details', 'user_salary')
            ->where(['month_code' => $this->month, 'year' => $this->year])
            ->whereHas('user', function ($query) {
                if (! empty($this->company)) {
                    $query->where('company_document_id', $this->company);
                }

                $query->whereHas('workDetail', function ($subQ) {
                    $subQ->where('salary_mode', 'account');
                });
                $query->whereDoesntHave('user.roles', function ($q) {
                    $q->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                });
            })->whereHas('user')->count();
        $edr = str_pad($edr, 2, '0', STR_PAD_LEFT);
        //Log::info("SIF:-".$edr); Log::error($edr)

        Log::info("SIF:-totalFixedIncome" . $this->totalFixedIncome);
        Log::info("SIF:-totalVariableIncome" . $this->totalVariableIncome);
        foreach ($setting as $data) {
            if ($data->key == 'employer_unique_id') {
                $employer_id = $data->value;
            }
            if ($data->key == 'bank_code') {
                $bank_code = $data->value;
            }
            if ($data->key == 'employer_reference_number') {
                $employer_reference_number = $data->value;
            }
        }

        if (auth()->check() && auth()->user()->hasRole('admin')) {
            if (! empty($this->company)) {
                $companydocument = CompanyDocument::Where("id", $this->company)->first();
            } else {
                $companydocument = CompanyDocument::first();
            }
        } else {
            $companydocument = CompanyDocument::Where("id", $this->company)->first();
        }
        $months = [
            1  => 'JAN',
            2  => 'FEB',
            3  => 'MAR',
            4  => 'APR',
            5  => 'MAY',
            6  => 'JUN',
            7  => 'JUL',
            8  => 'AUG',
            9  => 'SEP',
            10 => 'OCT',
            11 => 'NOV',
            12 => 'DEC',
        ];
        $monthName = $months[(int) $this->month];

         $roundoff=getSetting('roundoff')? getSetting('roundoff') : 0;

        if (! empty($companydocument)) {
            // $employer_id               = $companydocument->license_number;
            $mol_code       = $companydocument->mol_code;
            $short_name     = $companydocument->short_name;
            $routing_number = $companydocument->routing_number;
            // if ($companydocument->sif_scr_employer_refrence == 0) {
            //     $employer_reference_number = $monthName;
            // }
        }
        return [
            AfterSheet::class => function (AfterSheet $event) use (
                $mol_code,
                $routing_number,
                $current_date,
                $current_time,
                $salary_month,
                $edr,
                $short_name,
                $roundoff
            ) {
                Log::info("SIF:-totalNetIncome before SCR append: " . $this->totalNetIncome);

                $total_salary_amount = $this->totalNetIncome;
                // // Header row
                // $header2 = [
                //     __trans('record_type'),
                //     __trans('MOL_companynumber'),
                //     __trans('routing_bank_code'),
                //     __trans('file_creation_date'),
                //     __trans('file_creation_time'),
                //     __trans('salary_month'),
                //     __trans('EDR_count'),
                //     __trans('total_salary'),
                //     __trans('payment_currency'),
                //     __trans('employer_reference'),
                // ];

                // $event->sheet->append($header2);

                // Get the last inserted row number (header row index)
                // $headerRowIndex = $event->sheet->getDelegate()->getHighestRow();

                // Apply bold styling to the header row
                // $event->sheet->getStyle("A{$headerRowIndex}:J{$headerRowIndex}")->getFont()->setBold(true);

                // Append data row

                $event->sheet->append([
                    'SCR',
                    $mol_code,
                    $routing_number,
                    $current_date,
                    $current_time,
                    $salary_month,
                    $edr,
                    sprintf('%.2f', $total_salary_amount, $roundoff),

                    'AED',
                    // $short_name,
                    "Salary",
                ]);
                Log::info("SIF:-SCR row appended with total: " . $total_salary_amount);

            },
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter'              => ',',
            'enclosure'              => '', // <-- VERY IMPORTANT: No quotes
            'line_ending'            => PHP_EOL,
            'use_bom'                => false,
            'include_separator_line' => false,
            'excel_compatibility'    => false,
            'escape_character'       => '\\',
        ];
    }

    public function collection()
    {
        //
    }
}
