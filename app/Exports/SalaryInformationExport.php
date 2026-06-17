<?php

namespace App\Exports;

use App\Models\Setting;
use App\Models\UserDocument;
use Exception;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; // Add WithEvents interface
use Maatwebsite\Excel\Events\AfterSheet;    // Import AfterSheet event
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\CompanyDocument\Entities\CompanyDocument;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserPaySlip;
use Modules\Payroll\Traits\SalaryCalculation;
use App\Models\EmployeeWorkingDay;

class SalaryInformationExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithCustomCsvSettings
{
    use SalaryCalculation;
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $month;
    protected $year;
    protected $totalFixedIncome;
    protected $totalVariableIncome;
    protected $companyDocumentId;
    protected $columns = [];
    protected $scrColumns = [];
    protected $showDataHeaders;
    protected $showScrHeaders;



    public function __construct($month, $year, $companyDocumentId, $columns = [], $scrColumns = [], $showDataHeaders = 1, $showScrHeaders = 1)
    {
        $this->month = $month;
        $this->year = $year;
        $this->companyDocumentId = $companyDocumentId;
        // $this->columns = $columns;
        $this->columns = collect($columns)->sortBy('index')->values()->toArray();
        $this->scrColumns = collect($scrColumns)->sortBy('index')->values()->toArray();
        $this->showDataHeaders = $showDataHeaders;
        $this->showScrHeaders = $showScrHeaders;
    }

    public function query()
    {
        // $query = UserPaySlip::with('users', 'bank_details', 'user_salary')->where(['month_code' => $this->month, 'year' => $this->year]);
        // return $query;
        // $query = UserPaySlip::with('users', 'bank_details', 'user_salary')
        //     ->where(['month_code' => $this->month, 'year' => $this->year])
        //     ->whereHas('users.workDetail', function ($q) {
        //         $q->where('salary_mode', 'account');
        //     });

        // return $query;
        // $query = UserPaySlip::with('users', 'bank_details', 'user_salary')
        //     ->where(['month_code' => $this->month, 'year' => $this->year])
        //     ->whereHas('users', function ($q) {
        //         $q->whereHas('workDetail', function ($subQ) {
        //             $subQ->where('salary_mode', 'account');
        //         });
        //     });
        // return UserPaySlip::query()
        //     ->where('month_code', $this->month)
        //     ->where('year', $this->year)
        //     ->when($this->companyDocumentId, function ($query) {
        //         $query->whereHas('user', function ($q) {
        //             $q->where('company_document_id', $this->companyDocumentId);
        //         });
        //     });
        return UserPaySlip::query()
            ->where('month_code', $this->month)
            ->where('year', $this->year)
            ->when($this->companyDocumentId, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('company_document_id', $this->companyDocumentId);
                });
            })
            ->whereHas('user', function ($q) {
                $q->whereHas('workDetail', function ($subQ) {
                    $subQ->where('salary_mode', 'account');
                });
            });


        // return $query;
    }

    public function map($userpayslip): array
    {
        $salary           = $this->allSalaryCalculations($userpayslip->users, $userpayslip->month_code, $userpayslip->year,$userpayslip->start_date, $userpayslip->end_date);
        // $start_date       = $this->year . '-' . $this->month . '-' . '01';
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        // $end_date         = date("$this->year-$this->month-$totalDaysInMonth");
        $month_name       = date('F', strtotime(date('Y') . '-' . $userpayslip->month_code));
        $month_year       = $month_name . ' ' . $userpayslip->year;

        $start_date = $userpayslip->start_date ?? date('Y-m-01', strtotime("$userpayslip->year-$userpayslip->month_code-01"));
        $end_date   = $userpayslip->end_date   ?? date('Y-m-t', strtotime("$userpayslip->year-$userpayslip->month_code-01"));

        // $overtime_amount = UserOvertime::where('user_id', $userpayslip->users->id)->where(['month_code' => $userpayslip->month_code, 'year' => $userpayslip->year])->sum('calculated_amount');
                $overtime_amount = UserOvertime::where('user_id', $userpayslip->users->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');

        $employee_document = UserDocument::select('serial_number','ministry_of_labor_personal_no')->where(['user_id' => $userpayslip->users->id,'type'=> 'labor_card_no',])->first();
        $labor_card = $employee_document->ministry_of_labor_personal_no ?? 'Not exist';

        $fixedIncome = $salary['total_fixed_income'];
        $this->totalFixedIncome += $fixedIncome;

        $variableIncome = $salary['total_variable_income'];
        $this->totalVariableIncome += $variableIncome;

        $absentCount = $salary['absentCount'] ?? '0';

        $housing_allowance = $salary['housing_allowance']??'0';
        $transportation_allowance = $salary['transportation_allowance']??'0';
        $other_allowance =  $salary['other_allowance']??'0';

        // return [
        //     "'EDR",
        //     "'{$labor_card}",
        //     "'" . ($userpayslip->bank_details->routing_number ?? ''),
        //     "'" . ($userpayslip->bank_details->iba_number ?? ''),
        //     "'{$start_date}",
        //     "'{$end_date}",
        //     "'{$totalDaysInMonth}",
        //     "'" . number_format($fixedIncome, 2),
        //     "'" . number_format($variableIncome, 2),
        //     "'{$absentCount}",
        // ];
        // $data = [
        //     "\t" ."EDR",
        //     "\t" .$labor_card,
        //     "\t" .(isset($userpayslip->bank_details->routing_number)) ? $userpayslip->bank_details->routing_number : '',
        //     "\t" .(isset($userpayslip->bank_details->iba_number)) ? $userpayslip->bank_details->iba_number : '',
        //     $start_date,
        //     $end_date,
        //     "\t" .$totalDaysInMonth,
        //     "\t" .number_format($fixedIncome, 2),
        //     "\t" .number_format($variableIncome, 2),
        //     "\t" .$absentCount
        // ];
        //   $data = [
        //     "\t" ."EDR",
        //     "\t" .$labor_card,
        //     "\t" .(isset($userpayslip->bank_details->routing_number)) ? $userpayslip->bank_details->routing_number : '',
        //     "\t" .(isset($userpayslip->bank_details->iba_number)) ? $userpayslip->bank_details->iba_number : '',
        //     $start_date,
        //     $end_date,
        //     "\t" .$totalDaysInMonth,
        //     "\t" .number_format($fixedIncome, 2),
        //     "\t" .number_format($variableIncome, 2),
        //     "\t" .$absentCount
        // ];
        // return $data;
        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;

        $dataMap = [
            'no_value' => "\t0",
            'record_type' => "\t" . "EDR",
            'labour_id' => "\t" . $labor_card,
            'emp_name' => "\t" . $userpayslip->users->name,
            'routing_number' => "\t" . ($userpayslip->bank_details->routing_number ?? ''),
            'account_number' => "\t" . ($userpayslip->bank_details->account_number ?? ''),
            'iban_number' => "\t" . ($userpayslip->bank_details->iba_number ?? ''),
            'pay_start_date' => $start_date,
            'pay_end_date' => $end_date,
            'number_of_days' => "\t" . $totalDaysInMonth,
            'fixed_income_amount' => "\t" . isset($fixedIncome) && $fixedIncome > 0  ?  number_format($fixedIncome, 2, '.', '') : "0.00",
            'variable_income_amount' => "\t" . isset($variableIncome) && $variableIncome > 0  ?  number_format($variableIncome, 2, '.', '') : "0.00",
            'days_on_leave' => "\t" . $absentCount,
            // Additional optional columns — you can customize their values if needed
            'housing_allowance' => "\t" . isset($housing_allowance) && $housing_allowance > 0  ?  number_format($housing_allowance, 2, '.', '') : "0.00",
            'conveyance_allowance' => "\t0.00",
            'medical_allowance' => "\t0.00",
            'annual_passage_allowance' => "\t0.00",
            'overtime_allowance' => "\t" . isset($overtime_amount) && $overtime_amount > 0  ?  number_format($overtime_amount, 2, '.', '') : "0.00",
            'other_allowance' => "\t" . isset($other_allowance) && $other_allowance > 0  ? number_format($other_allowance, 2, '.', ''):"0.00",
            'leave_encashment' => "\t0.00",
            'employee_code' => "\t" . $userpayslip->users->employee_id,
            'total_salary' => "\t" . round($salary['net_salary'], $roundoff),
            'gross_salary' => "\t" . round($salary['gross_salary'], 2),
            'total_deduction' => "\t" . round($salary['total_deduction'], 2),

        ];
        // dd( $dataMap );
        $row = [];
        // foreach ($this->columns as $col) {
        //     $row[] = $dataMap[$col] ?? '';  // fallback empty if missing
        // }
        foreach ($this->columns as $col) {
            $field = $col['field'];
            $row[] = $dataMap[$field] ?? '';
        }

        return $row;
    }

    public function headings(): array
    {
        // $headers = [
        //     __trans('#'),
        //     __trans('labour_id'),
        //     __trans('routing_number'),
        //     __trans('iban_number'),
        //     __trans('pay_start_date'),
        //     __trans('pay_end_date'),
        //     __trans('number_of_days'),
        //     __trans('fixed_income_amount'),
        //     __trans('variable_income_amount'),
        //     __trans('days_on_leave'),
        //     __trans('housing_allowance'),
        //     __trans('conveyance_allowance'),
        //     __trans('medical_allowance'),
        //     __trans('annual_passage_allowance'),
        //     __trans('overtime_allowance'),
        //     __trans('other_allowance'),
        //     __trans('leave_encashment'),
        // ];
        // return $headers;
        if (!$this->showDataHeaders) {
            return [];
        }
        $translations = [
            'record_type' => __trans('record_type'),
            'labour_id' => __trans('labour_id'),
            'routing_number' => __trans('routing_number'),
            'account_number' => __trans('account_number'),
            'iban_number' => __trans('iban_number'),
            'pay_start_date' => __trans('pay_start_date'),
            'pay_end_date' => __trans('pay_end_date'),
            'number_of_days' => __trans('number_of_days'),
            'fixed_income_amount' => __trans('fixed_income_amount'),
            'variable_income_amount' => __trans('variable_income_amount'),
            'days_on_leave' => __trans('days_on_leave'),
            'housing_allowance' => __trans('housing_allowance'),
            'conveyance_allowance' => __trans('conveyance_allowance'),
            'medical_allowance' => __trans('medical_allowance'),
            'annual_passage_allowance' => __trans('annual_passage_allowance'),
            'overtime_allowance' => __trans('overtime_allowance'),
            'other_allowance' => __trans('other_allowance'),
            'leave_encashment' => __trans('leave_encashment'),
            'employee_code' => __trans('employee_code'),
            'total_salary' => __trans('total_salary'),
            'gross_salary' => __trans('gross_salary'),
            'total_deduction' => __trans('total_deduction'),
        ];

        $headers = [];
        // foreach ($this->columns as $col) {
        //     $headers[] = $translations[$col] ?? ucfirst(str_replace('_', ' ', $col));
        // }
        foreach ($this->columns as $col) {
            $headers[] = $col['name'];
        }
        return $headers;
    }

    // private function allSalaryCalculations($user, $month, $year)
    // {
    //     $total_fixed_income    = 0.00;
    //     $total_variable_income = 0.00;

    //     $basic_salary       = $user->salary ? $user->salary->basic : 0;
    //     $monthly_fixed      = $this->monthlyfixedExpensesCalculation($user, $month, $year);
    //     $total_fixed_income = $basic_salary + $monthly_fixed['total_allowance'];

    //     $overtime_amount       = UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
    //     $monthly_not_fixed     = $this->monthlynotfixedExpensesCalculation($user, $month, $year);
    //     $total_variable_income = $overtime_amount + $monthly_not_fixed['total_allowance'];

    //     $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    //     $formattedMonth   = sprintf("%02d", $month); // Format month with leading zeros
    //     $start_date       = $year . '-' . $formattedMonth . '-' . '01';
    //     $end_date         = date("$year-$formattedMonth-$totalDaysInMonth");

    //     $absentCount = $user->attendances->where('status', AttendanceStatus::Absent)->whereBetween('date', [$start_date, $end_date])->count();
    //     Log::info('absent count = ' . $absentCount . ' ' . $start_date . ' ' . $end_date);
    //     $collection = [
    //         'total_fixed_income'    => $total_fixed_income,
    //         'total_variable_income' => $total_variable_income,
    //         'absentCount'           => $absentCount,
    //     ];

    //     return $collection;
    // }
    private function allSalaryCalculations($user, $month, $year,$start_date,$end_date)
    {
        $total_fixed_income    = 0.00;
        $total_variable_income = 0.00;

        // Basic salary
        $basic_salary = $user->salary ? $user->salary->basic : 0;

        // Fixed Allowances (stored as JSON)
        $fixed_allowances = [];
        if ($user->salary && !empty($user->salary->fixed_allowances)) {
            $fixed_allowances = json_decode($user->salary->fixed_allowances, true) ?? [];
        }

        // Sum all fixed allowances
        $fixed_allowance_total = 0;
        foreach ($fixed_allowances as $key => $value) {
            $fixed_allowance_total += floatval($value);
        }

        // Monthly Fixed (from existing calculation)
        $monthly_fixed = $this->monthlyfixedExpensesCalculation($user, $month, $year,$start_date,$end_date);

        // Total Fixed Income: Basic + JSON Allowances + Monthly Fixed
        $total_fixed_income = $basic_salary + $fixed_allowance_total + $monthly_fixed['total_allowance'];

        // Variable Income
        // $overtime_amount = UserOvertime::where('user_id', $user->id)
        //     ->where(['month_code' => $month, 'year' => $year])
        //     ->sum('calculated_amount');
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year,$start_date,$end_date);
        $total_variable_income = $overtime_amount + $monthly_not_fixed['total_allowance'];

        // Days calculations
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $formattedMonth = sprintf("%02d", $month);
        $start_date = $year . '-' . $formattedMonth . '-' . '01';
        $end_date = date("$year-$formattedMonth-$totalDaysInMonth");

        if (getSetting('attendance_base_payroll') == 'true') {
            $net_salary = $this->getTotalNetSalary($user, $month, $year, $start_date, $end_date);
        } else {
            $working_days = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $user->id])->value('total_working_days');
            $net_salary = $this->getTotalNetSalary_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
        }
        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        $net_salary = round($net_salary, $roundoff);

        $absentCount = $user->attendances
            ->where('status', AttendanceStatus::Absent)
            ->whereBetween('date', [$start_date, $end_date])
            ->count();
        Log::info('absent count = ' . $absentCount . ' ' . $start_date . ' ' . $end_date);
        $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
        $gross_salary = round($gross_salary, 2);

        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && !empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
        $fixed_entity_deduction = array_sum($fixed_entity_deduction);

        $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;
        $total_deduction = round($total_deduction, 2);

        // Build collection (including dynamic allowances)
        $collection = [
            'total_fixed_income'    => $total_fixed_income,
            'total_variable_income' => $total_variable_income,
            'absentCount'           => $absentCount,
            'net_salary'            => $net_salary,
            'gross_salary'          => $gross_salary,
            'total_deduction'       => $total_deduction,
        ];

        // Add decoded fixed allowances to collection
        foreach ($fixed_allowances as $key => $value) {
            $collection[$key] = $value;
        }

        return $collection;
    }

    public function registerEvents(): array
    {
        $setting = Setting::whereIn('key', ['employer_unique_id', 'bank_code', 'employer_reference_number'])->get();
        $employer_id = '';
        $bank_code = '';
        $employer_reference_number = '';
        $current_date = date('Y-m-d');
        $current_time = date('Hi');
        $salary_month = $this->month . $this->year;
        // $edr = UserPaySlip::where(['month_code' => $this->month, 'year' => $this->year])
        //     ->when($this->companyDocumentId, function ($query) {
        //         $query->whereHas('user', function ($q) {
        //             $q->where('company_document_id', $this->companyDocumentId);
        //         });

        //     })->count();
        $edr = UserPaySlip::where([
            'month_code' => $this->month,
            'year' => $this->year
        ])
            ->when($this->companyDocumentId, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('company_document_id', $this->companyDocumentId);
                });
            })
            ->whereHas('user', function ($q) {
                $q->whereHas('workDetail', function ($subQ) {
                    $subQ->where('salary_mode', 'account');
                });
            })
            ->count();


        Log::info($this->totalFixedIncome);
        Log::info($this->totalVariableIncome);

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
        if (strlen($salary_month) === 5) {
            // Single digit month (e.g. 2 for February)
            $month = substr($salary_month, 0, 1);
            $year = substr($salary_month, 1, 4);
        } elseif (strlen($salary_month) === 6) {
            // Double digit month (e.g. 11 for November)
            $month = substr($salary_month, 0, 2);
            $year = substr($salary_month, 2, 4);
        } else {
            // Invalid format
            throw new Exception("Invalid salary month format: $salary_month");
        }

        // Convert month to two digits
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);

        // Build date
        $formatted_date = "$year-$month-01";

        // Get month name
        $salary_month = date('F', strtotime($formatted_date));


        $companydocument = CompanyDocument::where('id', $this->companyDocumentId)->first();

        if (! empty($companydocument)) {
            $mol_code               = $companydocument->mol_code;
            $routing_number                 = $companydocument->routing_number;
            $employer_reference_number = $companydocument->employer_reference;
        }


        $scrDataMap = [
            'no_value' =>"\t0",
            'record_type' => "\t" . 'SCR',
            'mol_company_number' => "\t" . ($mol_code ?? ''),
            'routing_bank_code' => "\t" . ($routing_number ?? ''),
            'file_creation_date' => "\t" . $current_date,
            'file_creation_time' => "\t" . $current_time,
            'salary_month' => "\t" . date('F', strtotime($salary_month)),
            'edr_count' => "\t" . $edr,
            'total_salary' => "\t" . $this->calculateTotalSalary(),

            'payment_currency' => "\t" . 'AED',
            'employer_reference' => "\t" . ($employer_reference_number ?? ''),
        ];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($scrDataMap) {
                // Insert SCR Headers if enabled
                if ($this->showScrHeaders) {
                    $scrHeaderTranslations = [
                        'record_type' => 'Record Type',
                        'mol_company_number' => 'MOL Company Number',
                        'routing_bank_code' => 'Routing Bank Code',
                        'file_creation_date' => 'File Creation Date',
                        'file_creation_time' => 'File Creation Time',
                        'salary_month' => 'Salary Month',
                        'edr_count' => 'EDR Count',
                        'total_salary' => 'Total Salary',
                        'payment_currency' => 'Payment Currency',
                        'employer_reference' => 'Employer Reference',
                    ];

                    $scrHeaderRow = [];
                    foreach ($this->scrColumns as $col) {
                        $scrHeaderRow[] = $col['name'];
                        // $scrHeaderRow[] = $scrHeaderTranslations[$col] ?? ucfirst(str_replace('_', ' ', $col));
                    }

                    $event->sheet->append($scrHeaderRow);
                }

                // Insert SCR Data Row
                // $scrDataRow = array_map(function ($col) use ($scrDataMap) {
                //     return $scrDataMap[$col] ?? '';
                // }, $this->scrColumns);

                // $event->sheet->append($scrDataRow);
                $scrDataRow = [];
                foreach ($this->scrColumns as $col) {
                    $field = $col['field'];                 // field = "routing_bank_code"
                    $scrDataRow[] = $scrDataMap[$field] ?? ''; // map to SCR data
                }

                $event->sheet->append($scrDataRow);
            },
        ];
    }

    private function calculateTotalSalary()
    {
        $total_fixed_income = UserPaySlip::where([
            'month_code' => $this->month,
            'year' => $this->year
        ])
            ->when($this->companyDocumentId, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('company_document_id', $this->companyDocumentId);
                });
            })
            ->whereHas('user', function ($q) {
                $q->whereHas('workDetail', function ($subQ) {
                    $subQ->where('salary_mode', 'account');
                });
            })
            ->get()
            ->sum(function ($payslip) {
                return $this->allSalaryCalculations($payslip->users, $payslip->month_code, $payslip->year,$payslip->start_date,$payslip->end_date)['total_fixed_income'];
            });

        $total_variable_income = UserPaySlip::where([
            'month_code' => $this->month,
            'year' => $this->year
        ])
            ->when($this->companyDocumentId, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('company_document_id', $this->companyDocumentId);
                });
            })
            ->whereHas('user', function ($q) {
                $q->whereHas('workDetail', function ($subQ) {
                    $subQ->where('salary_mode', 'account');
                });
            })
            ->get()
            ->sum(function ($payslip) {
                return $this->allSalaryCalculations($payslip->users, $payslip->month_code, $payslip->year,$payslip->start_date, $payslip->end_date)['total_variable_income'];
            });
        $roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
        return number_format($total_fixed_income + $total_variable_income, $roundoff, '.', '');
    }


    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ",", // Set the delimiter to tab for .sif files
            'enclosure' => '',  // Disable double quotes
        ];
    }

    public function collection()
    {
        //
    }
}
