<?php
namespace App\Exports;

use App\Models\EMIAllowance;
use App\Models\EMIAllowanceData;
use App\Models\EMIDeduction;
use App\Models\EMIDeductionData;
use App\Models\EmployeeWorkingDay;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserPaySlip;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Traits\SalaryCalculation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UsersPaySlipExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    use SalaryCalculation;
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $month;
    protected $year;
    protected $company;
    protected $dynamicAllowanceTitles = [];
    protected $dynamicDeductionTitles = [];

    public function __construct($month, $year, $company)
    {
        $this->month   = $month;
        $this->year    = $year;
        $this->company = $company;

        $userAllowanceTitles = UserSalaryAllowance::where('month_code', $month)
            ->where('year', $year)
            ->pluck('title')
            ->unique()
            ->toArray();

        $baseAllowances = SetAllowanceDeducation::where('type', 1)
            ->pluck('name')
            ->toArray();

        $baseDeductions = SetAllowanceDeducation::where('type', 2)
            ->pluck('name')
            ->toArray();

        $emiAllowanceTitles = EMIAllowanceData::where('month', $month)
            ->where('year', $year)
            ->where('is_paid', 0)
            ->with('emiAllowance')
            ->get()
            ->pluck('emiAllowance.title')
            ->unique()
            ->toArray();

        $emiDeductionTitles = EMIDeductionData::where('month', $month)
            ->where('year', $year)
            ->where('is_paid', 0)
            ->with('emiDeduction')
            ->get()
            ->pluck('emiDeduction.title')
            ->unique()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Merge Titles
        |--------------------------------------------------------------------------
        */
        // $this->dynamicAllowanceTitles = array_unique(
        //     array_merge($baseAllowances, $emiAllowanceTitles,$userAllowanceTitles)
        // );
        $allTitles = array_merge($baseAllowances, $emiAllowanceTitles, $userAllowanceTitles);

// Normalize (trim + lowercase key)
        $normalized = [];

        foreach ($allTitles as $title) {
            $key              = strtolower(trim($title)); // unique key
            $normalized[$key] = trim($title);             // keep original value
        }

        $this->dynamicAllowanceTitles = array_values($normalized);

        $this->dynamicDeductionTitles = array_unique(
            array_merge($baseDeductions, $emiDeductionTitles)
        );
    }

    public function query()
    {
                                                                            // $query = UserPaySlip::with('users', 'bank_details', 'user_salary')->where(['month_code' => $this->month, 'year' => $this->year]);
        $query = UserPaySlip::with(['user', 'bank_details', 'user_salary']) // Eager load relations
            ->where([
                'month_code' => $this->month,
                'year'       => $this->year,
            ])
            ->whereHas('user', function ($q) {
                $q->where('status', User::STATUS_ACTIVE);
                $q->where('settlement_status', 0);
                if ($this->company > 0) {
                    $q->where('company_document_id', $this->company);
                }
            })
            ->whereDoesntHave('user.roles', function ($q) {
                $q->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });

        return $query;
    }

    public function map($userpayslip): array
    {
        $salary = $this->allSalaryCalculations($userpayslip->users, $userpayslip->month_code, $userpayslip->year, $userpayslip->start_date, $userpayslip->end_date);
        $user   = $userpayslip->users;

        $month_name = date('F', strtotime(date('Y') . '-' . $userpayslip->month_code));
        $month_year = $month_name . ' ' . $userpayslip->year;
        $data       = [
            // $month_year,
            $userpayslip->users->employee_id,
            $userpayslip->users->name,
            $userpayslip->user_salary->basic,
            $salary['housing_allowance'],
            $salary['transportation_allowance'],
            $salary['functional_allowance'],
            $salary['other_allowance'],
            $salary['tips'],
        ];

        // Get dynamic allowances and deductions
        $allowances = SetAllowanceDeducation::where('type', 1)->select('name', 'amount')->get();
        $deductions = SetAllowanceDeducation::where('type', 2)->select('name', 'amount')->get();

        $userAllowances = UserSalaryAllowance::where('user_id', $user->id)
            ->where('month_code', $userpayslip->month_code)
            ->where('year', $userpayslip->year)
            ->get();
        // Append allowance data to $data
        // all fix allowance
        $finalAllowances = [];
        $emiData         = EMIAllowanceData::where('month', $userpayslip->month_code)
            ->where('year', $userpayslip->year)
            ->where('is_paid', 0)
            ->whereHas('emiAllowance', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('fully_paid', 0);
            })
            ->with('emiAllowance')
            ->get();
        foreach ($userAllowances as $allowance) {
            $amount = $allowance->allowance_type == 'fixed'
                ? $allowance->amount
                : $allowance->percentage_amount;

            $finalAllowances[$allowance->title] = [
                'title'  => $allowance->title,
                'amount' => $amount,
                'remark' => $allowance->remark ?? '',
            ];
        }
        foreach ($emiData as $emi) {
            $title  = $emi->emiAllowance->title;
            $remark = $emi->emiAllowance->remark ?? '';
            if (isset($finalAllowances[$title])) {
                $finalAllowances[$title]['amount'] += $emi->month_amount;
                // Get existing remark
                $existingRemark  = $finalAllowances[$title]['remark'] ?? '';
                $emiRemark       = $emi->emiAllowance->remark ?? '';
                if ($existingRemark && $emiRemark) {
                    $finalAllowances[$title]['remark'] = $existingRemark . ', ' . $emiRemark;
                } elseif ($emiRemark) {
                    $finalAllowances[$title]['remark'] = $emiRemark;
                }
            } else {
                $finalAllowances[$title] = [
                    'title'  => $title,
                    'amount' => $emi->month_amount,
                    'remark' => $remark,
                ];
            }
        }
        foreach ($this->dynamicAllowanceTitles as $title) {
            $amount = $finalAllowances[$title]['amount'] ?? 0;
            $remark = $finalAllowances[$title]['remark'] ?? '';

            $data[] = $amount;
            $data[] = $remark;
        }
        // end
        // foreach ($allowances as $allowanceName) {
        //     $allowancesAmount = UserSalaryAllowance::where([
        //         ['title', '=', $allowanceName->name],
        //         ['user_id', '=', $user->id],
        //         ['month_code', '=', $userpayslip->month_code],
        //         ['year', '=', $userpayslip->year],
        //     ])->select('title', 'amount','allowance_type','percentage_amount')->first();
        //     if($allowancesAmount){
        //         if($allowancesAmount->allowance_type =='fixed'){
        //             $data[] = $allowancesAmount->amount;
        //         } else {
        //             $data[] = $allowancesAmount->percentage_amount;
        //         }
        //     } else {
        //         $data[] = 0;
        //     }
        // }

        $data = array_merge($data, [
            // $salary['total_monthly_allowance'],
            $salary['total_allowance'],
            $salary['gross'],
            $salary['total_overtime'],
            $salary['advance_salary'],
            $salary['loan_deduction'],
            $salary['other_deduction'],
        ]);

        // Append deduction data to $data
        $userDeductions = UserDeduction::where('user_id', $user->id)
            ->where('month_code', $userpayslip->month_code)
            ->where('year', $userpayslip->year)
            ->get();
        $finalDeduction = [];
        $emidaduData    = EMIDeductionData::where('month', $userpayslip->month_code)
            ->where('year', $userpayslip->year)
            ->where('is_paid', 0)
            ->whereHas('emiDeduction', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('fully_paid', 0);
            })
            ->with('emiDeduction')
            ->get();
        foreach ($userDeductions as $deduction) {
            $amount = $deduction->deduction_type == 'fixed'
                ? $deduction->amount
                : $deduction->percentage_amount;
            $finalDeduction[$deduction->title] = [
                'title'  => $deduction->title,
                'amount' => $amount,
                'remark' => $deduction->remark ?? '',
            ];
        }
        foreach ($emidaduData as $emi) {
            $title = $emi->emiDeduction->title;
            if (isset($finalDeduction[$title])) {
                $finalDeduction[$title]['amount'] += $emi->month_amount;
                // Get existing remark
                $existingRemark  = $finalDeduction[$title]['remark'] ?? '';
                $emiRemark       = $emi->emiDeduction->remark ?? '';
                if ($existingRemark && $emiRemark) {
                    $finalDeduction[$title]['remark'] = $existingRemark . ', ' . $emiRemark;
                } elseif ($emiRemark) {
                    $finalDeduction[$title]['remark'] = $emiRemark;
                }
            } else {
                $finalDeduction[$title] = [
                    'title'  => $title,
                    'amount' => $emi->month_amount,
                    'remark' => $emi->emiDeduction->remark ?? '',
                ];
            }
        }
        foreach ($this->dynamicDeductionTitles as $title) {
            $amount = $finalDeduction[$title]['amount'] ?? 0;
            $remark = $finalDeduction[$title]['remark'] ?? '';

            $data[] = $amount;
            $data[] = $remark;
        }
        // foreach ($deductions as $deductionName) {
        //     $deductionsAmount = UserDeduction::where([
        //         ['title', '=', $deductionName->name],
        //         ['user_id', '=', $user->id],
        //         ['month_code', '=', $userpayslip->month_code],
        //         ['year', '=', $userpayslip->year],
        //     ])->select('title', 'amount')->first();
        //     $data[] = isset($deductionsAmount) ? $deductionsAmount->amount : 0;
        // }

        $data = array_merge($data, [
            // $salary['total_monthly_deduction'],
            $salary['total_deduction'],
            $salary['net'],
            $salary['total_net'],
            $userpayslip->status,
            (isset($userpayslip->bank_details->account_number)) ? $userpayslip->bank_details->account_number : '',
            (isset($userpayslip->bank_details->bank_name)) ? $userpayslip->bank_details->bank_name : '',
            (isset($userpayslip->bank_details->iba_number)) ? $userpayslip->bank_details->iba_number : '',
            (isset($userpayslip->bank_details->swift_code)) ? $userpayslip->bank_details->swift_code : '',
        ]);

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastColumnLetter = $sheet->getHighestDataColumn();
                $lastColumnIndex  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastColumnLetter);

                // Force fixed narrow column widths and disable auto-sizing
                foreach (range('A', $lastColumnLetter) as $col) {
                    $dimension = $sheet->getColumnDimension($col);
                    $dimension->setAutoSize(false);
                    $dimension->setWidth(17); // force wrap
                }

                // start total
                // Auto-height for header row (row 4)
                $sheet->getRowDimension(4)->setRowHeight(-1);

                $lastColumnLetter = $sheet->getHighestDataColumn();
                $lastRow          = $sheet->getHighestRow();

                // TOTAL row position
                $totalRow = $lastRow + 1;

                // Add TOTAL label in column A
                $sheet->setCellValue("A{$totalRow}", 'TOTAL');

                $headerRow = 4;

                // Get the highest column that has any data on that row
                $highestColumn      = $sheet->getHighestDataColumn($headerRow);
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                $endColumn = null;

                for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
                    $colLetter   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $headerValue = trim((string) $sheet->getCellByColumnAndRow($colIndex, $headerRow)->getValue());

                    if (strcasecmp($headerValue, 'Total Net Salary') === 0) {
                        $endColumn = $colLetter;
                        break;
                    }
                }

                $currencyColumns = [];
                $startColIndex   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('C');
                $endColIndex     = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($endColumn);

                for ($i = $startColIndex; $i <= $endColIndex; $i++) {
                    $currencyColumns[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                }

                $sheet->getStyle("A1:{$lastColumnLetter}4")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                // ✅ Header styling (row 4)
                $headerRange = 'A4:' . $lastColumnLetter . '4';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font'      => ['bold' => true],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill'      => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFC0C0C0'], // light gray
                    ],
                    'borders'   => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                foreach ($currencyColumns as $colLetter) {
                    $formula = "=SUM({$colLetter}5:{$colLetter}{$lastRow})";
                    // $sheet->setCellValue("{$colLetter}{$totalRow}", $formula);
                    $sum = 0;
                    for ($row = 5; $row <= $lastRow; $row++) {
                        $value = $sheet->getCell("{$colLetter}{$row}")->getCalculatedValue();
                        $sum   += floatval($value);
                    }
                    $sheet->setCellValue("{$colLetter}{$totalRow}", $sum);

                    // Format as number with 2 decimals
                    $sheet->getStyle("{$colLetter}{$totalRow}")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

                    // Bold totals
                    $sheet->getStyle("{$colLetter}{$totalRow}")->applyFromArray([
                        'font'      => ['bold' => true],
                        'fill'      => [
                            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFECECEC'],
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        ],
                    ]);
                }
                //end formula

                // Style the TOTAL row
                $totalRange = "A{$totalRow}:{$lastColumnLetter}{$totalRow}";
                $sheet->getStyle($totalRange)->applyFromArray([
                    'font'    => ['bold' => true],
                    'fill'    => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFFE599'], // light yellow
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }

    public function headings(): array
    {
                                                                                          // Get allowances and deductions
        $allowances = SetAllowanceDeducation::where('type', 1)->pluck('name')->toArray(); // Type 1 for allowances
        $deductions = SetAllowanceDeducation::where('type', 2)->pluck('name')->toArray(); // Type 2 for deductions

        $company_name = env('APP_NAME');
        $month_year   = date('F Y', strtotime($this->year . '-' . $this->month));

        $headers = [
            [$company_name],
            [$month_year],
            [],
            [
                __trans('emp_id'),
                __trans('name'),
                __trans('basic_salary'),
                __trans('housing_allowance'),
                __trans('transportation_allowance'),
                __trans('functional_allowance'),
                __trans('other_allowance'),
                __trans('tips'),
            ],
        ];

        // Append allowance headers dynamically
        // foreach ($allowances as $allowance) {
        //     $headers[3][] = __trans($allowance);
        // }
        foreach ($this->dynamicAllowanceTitles as $title) {
            $headers[3][] = __trans($title);
            $headers[3][] = __trans('Remark');
        }
        $headers[3] = array_merge($headers[3], [
            // __trans('total_fix_month_allowance'),
            __trans('total_allowance'),
            __trans('gross_salary'),
            __trans('total_overtime'),
            __trans('advance_salary'),
            __trans('loan_deduction'),
            __trans('other_deduction'),
        ]);
        // Append deduction headers dynamically
        // foreach ($deductions as $deduction) {
        //     $headers[3][] = __trans($deduction);
        // }
        foreach ($this->dynamicDeductionTitles as $title) {
            $headers[3][] = __trans($title);
            $headers[3][] = __trans('Remark');
        }

        $headers[3] = array_merge($headers[3], [
            // __trans('total_fix_monthly_deduction'),
            __trans('total_deduction'),
            __trans('net_salary (Attendance)'),
            __trans('total_net_salary'),
            __trans('status'),
            __trans('account_number'),
            __trans('bank_name'),
            __trans('iban_number'),
            __trans('swift_code'),
        ]);

        return $headers;
    }

    private function allSalaryCalculations($user, $month, $year, $start_date, $end_date)
    {
        $gross_salary     = 0;
        $net_salary       = 0;
        $total_net_salary = 0;

        /* Amount Mismatch In Export CSV So OverRide $user Again  */

        $user = User::with(['attendances' => function ($query) use ($month, $year) {
            $query->whereMonth('date', $month)->whereYear('date', $year);
        }])->where('id', $user->id)->first();

        $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);

        $working_days = 0;
        $working_days = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $user->id])->value('total_working_days');

        if (getSetting('attendance_base_payroll') == 'true') {
            $net_salary       = $this->getNetSalaryAsPerAttendance($user, $month, $year, $start_date, $end_date);
            $total_net_salary = $this->getTotalNetSalary($user, $month, $year, $start_date, $end_date);
        } else {
            $net_salary       = $this->getNetSalaryAsPerAttendance_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
            $total_net_salary = $this->getTotalNetSalary_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
        }

        $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $basic_salary    = $user->salary ? $user->salary->basic : 0;
        $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
        // $overtime_amount = UserOvertime::where('user_id', $user->id)
        //     ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
        //     ->sum('calculated_amount');

        $fixed_allowance          = isset($user->salary) ? json_decode($user->salary->fixed_allowances, true) : 0;
        $fixed_deduction          = isset($user->salary) ? json_decode($user->salary->fixed_deductions, true) : 0;
        $housing_allowance        = isset($fixed_allowance['housing_allowance']) ? $fixed_allowance['housing_allowance'] : 0;
        $transportation_allowance = isset($fixed_allowance['transportation_allowance']) ? $fixed_allowance['transportation_allowance'] : 0;
        $other_allowance          = isset($fixed_allowance['other_allowance']) ? $fixed_allowance['other_allowance'] : 0;
        $functional_allowance     = isset($fixed_allowance['functional_allowance']) ? $fixed_allowance['functional_allowance'] : 0;
        $tips                     = isset($fixed_allowance['tips']) ? $fixed_allowance['tips'] : 0;

        $advance_salary  = isset($fixed_deduction['advance_salary']) ? $fixed_deduction['advance_salary'] : 0;
        $loan_deduction  = isset($fixed_deduction['loan_deduction']) ? $fixed_deduction['loan_deduction'] : 0;
        $other_deduction = isset($fixed_deduction['other_deduction']) ? $fixed_deduction['other_deduction'] : 0;

        $total_monthly_deduction = $monthly_fixed['total_deduction']; // + $monthly_not_fixed['total_deduction'];
        $total_monthly_allowance = $monthly_fixed['total_allowance']; // + $monthly_not_fixed['total_allowance'];

        // $totalallowa = 0;
        // $totaldedu = 0;

        // $allowances = SetAllowanceDeducation::get();
        // $allowanceData = [];
        // $deducationData = [];
        // foreach ($allowances as $allowance) {
        //     $allowanceName = $allowance?->name;
        //     if ($allowance?->type == 1) {
        //         $allowancesAmount = UserSalaryAllowance::where([
        //             ['title', '=', $allowanceName],
        //             ['user_id', '=', $user->id],
        //             ['month_code', '=', $month],
        //             ['year', '=', $year],
        //         ])->select('title', 'amount')->first();
        //         $alloAmount = isset($allowancesAmount) ? $allowancesAmount->amount : 0;
        //         $totalallowa = $totalallowa + $alloAmount;
        //         $total_monthly_allowance = ($total_monthly_allowance + $totalallowa);
        //     }
        //     if ($allowance?->type == 2) {
        //         $deductionsAmount = UserDeduction::where([
        //             ['title', '=', $allowanceName],
        //             ['user_id', '=', $user->id],
        //             ['month_code', '=', $month],
        //             ['year', '=', $year],
        //         ])->select('title', 'amount')->first();
        //         $deduamount = isset($deductionsAmount) ? $deductionsAmount->amount : 0;
        //         $totaldedu = $totaldedu + $deduamount;
        //     }
        // }
        $totalUserAllowance = $this->getTotalUserAllowance($user, $month, $year, $start_date, $end_date);
        $totalUserAllowance = $totalUserAllowance + (float) $housing_allowance + (float) $transportation_allowance + (float) $functional_allowance + (float) $other_allowance + (float) $tips;

        $totalUserDeduction = $this->getTotalUserDeduction($user, $month, $year, $start_date, $end_date);
        $totalUserDeduction = $totalUserDeduction + (float) $advance_salary + (float) $loan_deduction + (float) $other_deduction;

        $total_deduction = $totalUserDeduction;
        $total_allowance = $totalUserAllowance;

        $collection = array_merge(
            [
                'gross'                    => $gross_salary,
                'housing_allowance'        => $housing_allowance,
                'transportation_allowance' => $transportation_allowance,
                'functional_allowance'     => $functional_allowance,
                'other_allowance'          => $other_allowance,
                'tips'                     => $tips,
                'total_monthly_allowance'  => $total_monthly_allowance,
                'total_allowance'          => $total_allowance,
                'total_overtime'           => $overtime_amount,
                'advance_salary'           => $advance_salary,
                'loan_deduction'           => $loan_deduction,
                'other_deduction'          => $other_deduction,
                'total_monthly_deduction'  => $total_monthly_deduction,
                'total_deduction'          => toNumeric($total_deduction),
                'net'                      => $net_salary,
                'total_net'                => toNumeric($total_net_salary),
            ],
            // $allowanceData,  // Add allowance data
            //$deducationData,  // Add deduction data
        );
        return $collection;
    }
}
function toNumeric($value)
{
    return is_numeric($value) ? (float) $value : 0;
}
