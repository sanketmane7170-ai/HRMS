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

        .section-title {
            background: #002b60;
            /* Dark Blue */
            color: #ffffff;
            padding: 8px 10px;
            font-weight: bold;
            margin-top: 20px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table th {
            background: #dfe3ee;
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
            color: #555;
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

        <!-- Earnings -->
        <div class="section-title">Earning</div>
        <table>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
            <tr>
                <td>{{ getSetting('payroll_calculation') == 'hourly' ? 'Basic Hourly Rate' : 'Basic Salary' }}</td>
                <td class="text-right">{{ $user->salary->basic }} {{ getSetting('currency') }}</td>
            </tr>
        </table>

        <!-- Fixed Allowances -->
        <div class="section-title">Fixed Allowance</div>
        <table>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
            <!-- @foreach($all_fixed_entity as $key => $value)
            @if(!empty($value) && !str_contains($key,'deduction'))
            <tr>
                <td>{{ ucfirst(str_replace('_',' ', $key)) }}</td>
                <td class="text-right">{{ $value }}</td>
            </tr>
            @endif
            @endforeach -->
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
        </table>

        <!-- Variable Allowances -->
        @foreach($user_salary as $result)
        @if(count($result['all_allowance']))
        <div class="section-title">Variable Allowance</div>
        <table>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th class="text-right">Amount</th>
            </tr>
              @foreach($result['all_allowance'] as $allowance)
                <tr>
                    <td>{{ $allowance->title }}</td>
                    @if($allowance->allowance_type == 'fixed')
                    <td>{{ __trans($allowance->allowance_type) }}</td>
                    @else
                    <td>{{ __trans($allowance->allowance_type) }}</td>
                    @endif
                    @if($allowance->allowance_type == 'fixed')
                    <td class="text-right">{{ $allowance->amount }}</td>
                    @else
                    <td class="text-right">{{ $allowance->amount}}%({{ $allowance->percentage_amount }}) </td>
                    @endif
                </tr>
                @endforeach
        </table>
        @endif
        @endforeach

        <!-- Advance Salary -->
        <div class="section-title">Advance Salary / Loan</div>
        <table>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
            <tr>
                <td>Approved Loan/Advance</td>
                <td class="text-right">{{ $approvedAdvanceLoanAmount }}</td>
            </tr>
        </table>

        <!-- Fixed Deductions -->
        <div class="section-title">Fixed Deductions</div>
        <table>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
            @foreach($all_fixed_entity as $key => $value)
            @if(str_contains($key,'deduction') && !empty($value))
            <tr>
                <td>{{ ucfirst(str_replace('_',' ', $key)) }}</td>
                <td class="text-right">{{ $value }}</td>
            </tr>
            @endif
            @endforeach
        </table>

        <!-- Variable Deductions -->
        @foreach($user_salary as $result)
        @if(count($result['all_deduction']))
        <div class="section-title">Variable Deductions</div>
        <table>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th class="text-right">Amount</th>
            </tr>
            @foreach($result['all_deduction'] as $d)
            <tr>
                <td>{{ $d->title }}</td>
                <td>{{ ucfirst($d->deduction_type) }}</td>
                <td class="text-right">
                    @if($d->deduction_type == 'fixed')
                    {{ $d->amount }}
                    @else
                    {{ $d->amount }}% ({{ $d->percentage_amount }})
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        @endif
        @endforeach

        <!-- Overtime -->
        @foreach($user_salary as $result)
        @if(count($result['all_overtime']))
        <div class="section-title">Overtime</div>
        <table>
            <tr>
                <th>Title</th>
                <th>Rate/hr</th>
                <th>Hours</th>
                <th class="text-right">Amount</th>
            </tr>
            @foreach($result['all_overtime'] as $ot)
            <tr>
                <td>{{ $ot->overtime_type }}</td>
                <td>{{ $ot->rate_per_hour }}</td>
                <td>{{ $ot->hours }}</td>
                <td class="text-right">{{ $ot->calculated_amount }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        @endforeach

        <!-- Expense -->
        <div class="section-title">Expense (Last Month)</div>
        <table>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
            <tr>
                <td>Expenses</td>
                <td class="text-right">{{ $expense }} {{ getSetting('currency') }}</td>
            </tr>
        </table>

        <!-- Net Salary -->
        <div class="section-title">Net Salary</div>
        <table>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
            <tr>
                <td><strong>{{ round($calculations['attendance_salary'],2) }} {{ getSetting('currency') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($calculations['net_salary'],2) }} {{ getSetting('currency') }}</strong></td>
            </tr>
        </table>

        <p class="footer-note">
            This is a system-generated payslip and does not require a signature.
        </p>

    </div>

</body>

</html>