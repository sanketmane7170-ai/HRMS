@php
$roundoff = getSetting('roundoff') ? getSetting('roundoff') : 0;
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <title>Payslip PDF</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-size: 13px;
        }

        .wrapper {
            padding: 25px 30px;
        }

        .header-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 50%;
            font-size: 14px;
        }

        .header-logo {
            width: 130px;
        }

       

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table th {
            background: #ffffffff;
            padding: 8px;
            border: 1px solid #c3c3c3;
            font-size: 13px;
            text-align: left;
        }

        table td {
            padding: 8px;
            border: 1px solid #c3c3c3;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .top-info {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .footer-note {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #000000ff;
        }

        .employee-section {
            display: table;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .employee-section .left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            font-size: 14px;
        }

        .employee-section .right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
            font-size: 14px;
            font-weight: bold;
        }
    </style>

</head>

<body>

    <div class="wrapper">

        <!-- Header -->
        <div class="header-section">
            <div class="header-left">
                <img src="{{ getLogo() }}" class="header-logo">
            </div>
            <div class="header-right">
                <strong>PaySlip Date :</strong> {{ strtoupper($payslip_date) }}
            </div>
        </div>

        <!-- Employee Info -->
        <div class="employee-section">
            <div class="left">
                <strong>Name :</strong> {{ $user->name }} <br>
                <strong>Designation :</strong> {{ $user->designation->name ?? 'N/A' }}
            </div>

            <div class="right">
                <strong>{{ $setting[0]['value'] }}</strong><br>
                {{ $setting[1]['value'] }}
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="card dark">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-md text-left">
                                <tbody>
                                    <tr class="font-weight-bold">
                                        <th>Earning</th>
                                        <th width="150" class="text-right">Amount</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            @if (getSetting('payroll_calculation') == 'hourly')
                                            Basic Hourly Rate
                                            @else
                                            Basic Salary
                                            @endif
                                        </td>
                                        <td class="text-right">{{ $user->salary->basic }}-{{getSetting('currency')}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @php
                $allowances = Modules\Payroll\Entities\SetAllowanceDeducation::get();
                @endphp
                {{-- @if(!empty($all_fixed_entity['housing_allowance']) || !empty($all_fixed_entity['transportation_allowance']) 
                                            || !empty($all_fixed_entity['other_allowance']) || !empty($all_fixed_entity['tips']))  --}}
                <div class="card dark">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table  table-hover table-md text-left">
                                <tbody>
                                    <tr class="font-weight-bold">
                                        <th>Fixed Allowance</th>
                                        <th width="150">Value</th>
                                    </tr>
                                    @if (!empty($all_fixed_entity['housing_allowance']))
                                    <tr>
                                        <td>Housing Allowance</td>
                                        <td class="text-right">{{ $all_fixed_entity['housing_allowance'] }}</td>
                                    </tr>
                                    @endif
                                    @if (!empty($all_fixed_entity['transportation_allowance']))
                                    <tr>
                                        <td>Transportation Allowance</td>
                                        <td class="text-right">{{ $all_fixed_entity['transportation_allowance'] }}</td>
                                    </tr>
                                    @endif
                                    {{-- @foreach ($allowances as $allowance)
                                                            @php
                                                            $allowanceName = $allowance->name;
                                                            @endphp
                                                            @if($allowance->type==1)
                                                            @if (array_key_exists($allowanceName, $all_fixed_entity))
                                                            <tr>
                                                                <td>{{ $allowanceName }}</td>
                                    <td class="text-right">{{ $all_fixed_entity[$allowanceName] }}</td>
                                    </tr>
                                    @endif
                                    @endif
                                    @endforeach --}}
                                    @if (!empty($all_fixed_entity['other_allowance']))
                                    <tr>
                                        <td>Other Allowance</td>
                                        <td class="text-right">{{ $all_fixed_entity['other_allowance'] }}</td>
                                    </tr>
                                    @endif
                                    @if (!empty($all_fixed_entity['tips']))
                                    <tr>
                                        <td>Tips</td>
                                        <td class="text-right">{{ $all_fixed_entity['tips'] }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- @endif  --}}
                @foreach($user_salary as $result)
                    @php
                        // all fix allowance
                        $finalAllowances = [];
                        $emiData = App\Models\EMIAllowanceData::where('month', $payslip->month_code)
                                    ->where('year', $payslip->year)
                                    ->where('is_paid', 0)
                                    ->whereHas('emiAllowance', function ($q) use ($user) {
                                        $q->where('user_id', $user->id)
                                        ->where('fully_paid', 0);
                                    })
                                    ->with('emiAllowance')
                                    ->get();
                        foreach ($result['all_allowance'] as $allowance) {
                            $finalAllowances[$allowance->title] = [
                                'title' => $allowance->title,
                                'type'  => $allowance->allowance_type,
                                'amount'=> $allowance->amount,
                            ];
                        }
                        foreach ($emiData as $emi) {
                            $title = $emi->emiAllowance->title;
                            if (isset($finalAllowances[$title])) {
                                // If title already exists → ADD amount
                                $finalAllowances[$title]['amount'] += $emi->month_amount;
                            } else {
                                // If title does not exist → Add new row
                                $finalAllowances[$title] = [
                                    'title'  => $title,
                                    'type'   => 'fixed',
                                    'amount' => $emi->month_amount,
                                ];
                            }
                        }
                        // end
                    @endphp
                    @if(count($finalAllowances))
                    <div class="card dark">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table  table-hover table-md text-left">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>@if(request()->getHttpHost()=="cakesocial.momdigital.io") Variable Addition @else Variable Allowance @endif</th>
                                            <th width="150">Type (Fixed/Percentage)</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                        @foreach($finalAllowances as $allowance)
                                        <tr>
                                            <td>{{ $allowance['title'] }}</td>
                                            <td>{{ __trans($allowance['type']) }}</td>
                                            <td class="text-right">{{ $allowance['amount'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    {{--  @if($totalPayrollAllowance)
                    <div class="card dark">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table  table-hover table-md text-left">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>Payroll Allowance</th>
                                            <th width="150" class="text-right">Amount</th>
                                        </tr>
                                        <tr>
                                            <td>User Payroll Allowance</td>
                                            <td class="text-right">{{ $totalPayrollAllowance }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif  --}}
                    @if($totalAirTicketAllowance)
                    <div class="card dark">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table  table-hover table-md text-left">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>Air Ticket Allowance</th>
                                            <th width="150" class="text-right">Amount</th>
                                        </tr>
                                        <tr>
                                            <td>User Air Ticket Allowance</td>
                                            <td class="text-right">{{ $totalAirTicketAllowance }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="card dark">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table  table-hover table-md text-left">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>Advance Salary/Loan Amount</th>
                                            <th width="150" class="text-right">Approved Amount</th>
                                        </tr>
                                        <tr>
                                            <td>Advance Salary/Loan Amount</td>
                                            <td class="text-right">{{ $approvedAdvanceLoanAmount }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card dark">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table  table-hover table-md text-left">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>Fixed Deduction</th>
                                            <th width="150">Value</th>
                                        </tr>
                                        @if (!empty($all_fixed_entity['advance_salary']))
                                        <tr>
                                            <td>Advance Salary</td>
                                            <td class="text-right">{{ $all_fixed_entity['advance_salary'] }}</td>
                                        </tr>
                                        @endif
                                        @if (!empty($all_fixed_entity['loan_deduction']))
                                        <tr>
                                            <td>Loan Deduction</td>
                                            <td class="text-right">{{ $all_fixed_entity['loan_deduction'] }}</td>
                                        </tr>
                                        @endif
                                        @if (!empty($all_fixed_entity['other_deduction']))
                                        <tr>
                                            <td>Other Deduction</td>
                                            <td class="text-right">{{ $all_fixed_entity['other_deduction'] }}</td>
                                        </tr>
                                        @endif
                                        {{-- @foreach ($allowances as $deduction)
                                                                @php
                                                                $deductionName = $deduction->name;
                                                                @endphp
                                                                @if($deduction->type==2)
                                                                @if (array_key_exists($deductionName, $all_fixed_entity))
                                                                <tr>
                                                                    <td>{{ $deductionName }}</td>
                                        <td class="text-right">{{ $all_fixed_entity[$deductionName] }}</td>
                                        </tr>
                                        @endif
                                        @endif
                                        @endforeach --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    {{-- @endif  --}}
                    @php
                        // all fix deduction
                        $finalDeduction = [];
                        $emiDeduData = App\Models\EMIDeductionData::where('month', $payslip->month_code)
                                    ->where('year', $payslip->year)
                                    ->whereHas('emiDeduction', function ($q) use ($user) {
                                        $q->where('user_id', $user->id)
                                        ->where('fully_paid', 0);
                                    })
                                    ->with('emiDeduction')
                                    ->get();
                        foreach ($result['all_deduction'] as $deduction) {

                            $finalDeduction[$deduction->title] = [
                                'title' => $deduction->title,
                                'type'  => $deduction->deduction_type,
                                'amount'=> $deduction->amount,
                            ];
                        }
                        foreach ($emiDeduData as $emi) {
                            $title = $emi->emiDeduction->title;
                            if (isset($finalDeduction[$title])) {
                                // If title already exists → ADD amount
                                $finalDeduction[$title]['amount'] += $emi->month_amount;
                            } else {
                                // If title does not exist → Add new row
                                $finalDeduction[$title] = [
                                    'title'  => $title,
                                    'type'   => 'fixed',
                                    'amount' => $emi->month_amount,
                                ];
                            }
                        }
                        // end
                    @endphp
                    @if(count($finalDeduction))
                    <div class="card dark">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table  table-hover table-md text-left">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>Variable Deductions</th>
                                            <th>Type (Fixed/Percentage)</th>
                                            <th width="150" class="text-right">Amount</th>
                                        </tr>
                                        @foreach($finalDeduction as $deduction)
                                        <tr>
                                            <td>{{ $deduction['title'] }}</td>
                                            <td>{{ __trans($deduction['type']) }}</td>
                                            <td class="text-right">{{ $deduction['amount'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($totalPayrollDeduction)
                    <div class="card dark">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table  table-hover table-md text-left">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>Payroll Deduction</th>
                                            <th width="150" class="text-right">Amount</th>
                                        </tr>
                                        <tr>
                                            <td>User Payroll Deduction</td>
                                            <td class="text-right">{{ $totalPayrollDeduction }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(count($result['all_overtime']))
                    <div class="card dark">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table  table-hover table-md text-left">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>Overtime Title</th>
                                            <th>Rate Per Hour</th>
                                            <th>Overtime Hours</th>
                                            <th width="150" class="text-right">Amount</th>
                                        </tr>
                                        @foreach($result['all_overtime'] as $overtime)
                                        <tr>
                                            <td>{{ __trans($overtime->overtime_type) }}</td>
                                            <td>{{ $overtime->rate_per_hour }}</td>
                                            <td>{{ $overtime->hours }}</td>
                                            <td class="text-right">{{ $overtime->calculated_amount }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
                <div class="card dark">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table  table-hover table-md text-left">
                                <tbody>
                                    <tr class="font-weight-bold">
                                        <th>Total Advance Salary/Loan</th>
                                        <th width="150" class="text-right">Deduction Amount</th>
                                    </tr>
                                    <tr>
                                        <td>Advance Salary/Loan</td>
                                        <td class="text-right">{{ $totaladAdvanceSalary }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card dark">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table  table-hover table-md text-left">
                                <tbody>

                                    <tr class="font-weight-bold">
                                        <th>Total Last Month Expenses</th>
                                        <th width="150">Amount</th>
                                    </tr>

                                    <tr>
                                        <td>Expense</td>
                                        <td class="text-right">{{ $expense }}-{{getSetting('currency')}}</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card dark">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table  table-hover table-md text-left">
                                <tbody>
                                    <tr class="font-weight-bold">
                                        <th>
                                            @if (getSetting('payroll_calculation') == 'hourly')
                                            Total Net Salary(Hourly)
                                            @else
                                            Total Net Salary(Attendance)
                                            @endif
                                        </th>
                                        <th width="150" class="text-right">Total Net Salary</th>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ round(floatval($calculations['attendance_salary'] ), $roundoff)  }} -{{getSetting('currency')}}</strong></td>
                                        <td class="text-right"><strong>{{ number_format(floatval($calculations['net_salary'] ), $roundoff) }} -{{getSetting('currency')}}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="footer-note">
            This is a system-generated payslip and does not require a signature.
        </p>
    </div>

</body>

</html>