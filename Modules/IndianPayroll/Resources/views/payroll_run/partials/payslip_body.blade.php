@php
    if (!function_exists('indianNumberToWords')) {
        function indianNumberToWords($num) {
            $num = (float)$num;
            $decimal = round($num - ($no = floor($num)), 2) * 100;
            $hundred = null;
            $digits_length = strlen($no);
            $i = 0;
            $str = array();
            $words = array(
                0 => '', 1 => 'One', 2 => 'Two',
                3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
                7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
                10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
                13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
                16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
                19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
                40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
                70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
            );
            $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
            while( $i < $digits_length ) {
                $divider = ($i == 2) ? 10 : 100;
                $number = floor($no % $divider);
                $no = floor($no / $divider);
                $i += $divider == 10 ? 1 : 2;
                if ($number) {
                    $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                    $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                    $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
                } else $str[] = null;
            }
            $Rupees = implode('', array_reverse($str));
            $paise = ($decimal > 0) ? ($words[floor($decimal / 10) * 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
            
            $result = '';
            if ($Rupees) {
                $result .= trim($Rupees) . ' Rupees';
            } else {
                $result .= 'Zero Rupees';
            }
            if ($paise) {
                $result .= ' and ' . trim($paise);
            }
            return $result . ' Only';
        }
    }

    $earnings = $payslip->components->filter(fn($c) => $c->type === 'earning' && optional($c->component)->code !== 'LOSS_OF_PAY');
    $deductions = $payslip->components->filter(fn($c) => $c->type === 'deduction' && optional($c->component)->code !== 'LOSS_OF_PAY');
    $employerContributions = $payslip->components->filter(fn($c) => $c->type === 'employer_contribution');
    $lop = $payslip->components->first(fn($c) => optional($c->component)->code === 'LOSS_OF_PAY');

    // gross_earnings is always the prorated/payable figure (sum of earning rows) — LOP is
    // a deduction-type row used only for display below, never summed into gross_earnings.
    $actualGross = $payslip->gross_earnings;
    $profile = $payslip->user->indianPayrollProfile;
    $bank = $profile?->bankDetail;
    $aadhaar = $profile?->aadhaar;
    $maskedAadhaar = $aadhaar ? 'xxxx-xxxx-' . substr($aadhaar, -4) : 'N/A';
    $bankAcc = $bank?->account_number;
    $maskedBankAcc = $bankAcc ? str_repeat('x', max(0, strlen($bankAcc) - 4)) . substr($bankAcc, -4) : 'N/A';
@endphp

<!-- Payslip Main Container -->
<div class="payslip-container">
    
    <!-- Branding & Header -->
    <table class="payslip-header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-logo-section">
                    <span class="company-name">{{ config('app.name', 'WorkPilot') }}</span>
                    <div class="company-sub">{{ __trans('employee_salary_slip') }}</div>
                </div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="payslip-title">{{ __trans('payslip') }}</div>
                <div class="payslip-period">{{ \Carbon\Carbon::create($payslip->run->year, $payslip->run->month, 1)->format('F Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Employee Metadata Grid -->
    <div class="section-title">{{ __trans('employee_details') }}</div>
    <table class="payslip-details-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td class="label-cell" style="width: 25%;">{{ __trans('employee_name') }}:</td>
            <td class="value-cell" style="width: 25%;"><strong>{{ $payslip->user->name }}</strong></td>
            <td class="label-cell" style="width: 25%;">{{ __trans('employee_id') }}:</td>
            <td class="value-cell" style="width: 25%;">{{ $payslip->user->employee_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __trans('designation') }}:</td>
            <td class="value-cell">{{ $payslip->user->designation->name ?? 'N/A' }}</td>
            <td class="label-cell">{{ __trans('department') }}:</td>
            <td class="value-cell">{{ $payslip->user->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __trans('date_of_joining') }}:</td>
            <td class="value-cell">{{ $profile && $profile->date_of_joining ? $profile->date_of_joining->format('d-M-Y') : 'N/A' }}</td>
            <td class="label-cell">{{ __trans('state') }}:</td>
            <td class="value-cell">{{ $profile && $profile->state ? $profile->state->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __trans('pan') }}:</td>
            <td class="value-cell">{{ $profile->pan ?? 'N/A' }}</td>
            <td class="label-cell">{{ __trans('aadhaar') }}:</td>
            <td class="value-cell">{{ $maskedAadhaar }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __trans('uan') }}:</td>
            <td class="value-cell">{{ $profile->uan ?? 'N/A' }}</td>
            <td class="label-cell">{{ __trans('pf_number') }}:</td>
            <td class="value-cell">{{ $profile->pf_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __trans('esi_number') }}:</td>
            <td class="value-cell">{{ $profile->esi_number ?? 'N/A' }}</td>
            <td class="label-cell">{{ __trans('bank_name') }}:</td>
            <td class="value-cell">{{ $bank->bank_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __trans('bank_account') }}:</td>
            <td class="value-cell">{{ $maskedBankAcc }}</td>
            <td class="label-cell">{{ __trans('ifsc') }}:</td>
            <td class="value-cell">{{ $bank->ifsc ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __trans('tax_regime') }}:</td>
            <td class="value-cell"><span class="regime-badge">{{ strtoupper($payslip->tax_regime ?? 'N/A') }}</span></td>
            <td class="label-cell">{{ __trans('status') }}:</td>
            <td class="value-cell"><span class="status-badge status-{{ $payslip->status }}">{{ ucfirst($payslip->status) }}</span></td>
        </tr>
    </table>

    <!-- Attendance Summary -->
    <div class="section-title">{{ __trans('attendance_details') }}</div>
    <table class="payslip-attendance-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; text-align: center;">
        <thead>
            <tr>
                <th style="width: 33.33%;">{{ __trans('total_days') }}</th>
                <th style="width: 33.33%;">{{ __trans('paid_days') }}</th>
                <th style="width: 33.33%;">{{ __trans('loss_of_pay_days') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($payslip->days_in_period) }}</td>
                <td><strong>{{ number_format($payslip->paid_days, 1) }}</strong></td>
                <td style="color: #dc3545;"><strong>{{ number_format($payslip->loss_of_pay_days, 1) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Earnings & Deductions Breakdown -->
    <table class="payslip-breakdown-parent-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <!-- Earnings Block -->
            <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                <table class="payslip-breakdown-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align: left;">{{ __trans('earnings') }}</th>
                            <th style="text-align: right;">{{ __trans('amount') }} (INR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($earnings as $e)
                        <tr>
                            <td>
                                {{ $e->label ?? optional($e->component)->name ?? 'Unknown' }}
                                @if ($e->is_manual)<span style="font-size: 10px; color: #4F46E5;">(one-off)</span>@endif
                            </td>
                            <td class="amount-cell">{{ number_format($e->amount, 2) }}</td>
                        </tr>
                        @endforeach

                        {{-- Loss of Pay reduces earnings — shown here (not in Deductions) so the
                             "Gross Earnings" total below matches the sum of the rows above it. --}}
                        @if ($lop && $lop->amount > 0)
                        <tr>
                            <td style="color: #dc3545;">{{ __trans('less_loss_of_pay') ?? 'Less: Loss of Pay' }}</td>
                            <td class="amount-cell" style="color: #dc3545;">-{{ number_format($lop->amount, 2) }}</td>
                        </tr>
                        @endif

                        {{-- Fill empty lines to balance heights --}}
                        @php
                            $earningsRowCount = count($earnings) + (($lop && $lop->amount > 0) ? 1 : 0);
                            $diff = count($deductions) - $earningsRowCount;
                        @endphp
                        @if ($diff > 0)
                            @for ($i = 0; $i < $diff; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            @endfor
                        @endif

                        <tr class="total-row">
                            <td><strong>{{ __trans('gross_earnings') }}</strong></td>
                            <td class="amount-cell"><strong>{{ number_format($actualGross, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Deductions Block -->
            <td style="width: 50%; vertical-align: top; padding-left: 8px;">
                <table class="payslip-breakdown-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align: left;">{{ __trans('deductions') }}</th>
                            <th style="text-align: right;">{{ __trans('amount') }} (INR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deductions as $d)
                        <tr>
                            <td>
                                {{ $d->label ?? optional($d->component)->name ?? 'Unknown' }}
                                @if ($d->is_manual)<span style="font-size: 10px; color: #4F46E5;">(one-off)</span>@endif
                            </td>
                            <td class="amount-cell">{{ number_format($d->amount, 2) }}</td>
                        </tr>
                        @endforeach

                        {{-- Fill empty lines to balance heights --}}
                        @php
                            $diff = $earningsRowCount - count($deductions);
                        @endphp
                        @if ($diff > 0)
                            @for ($i = 0; $i < $diff; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            @endfor
                        @endif

                        <tr class="total-row">
                            <td><strong>{{ __trans('total_deductions') }}</strong></td>
                            <td class="amount-cell"><strong>{{ number_format($payslip->total_statutory_deductions + $payslip->total_other_deductions, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- Net Pay Highlight Box -->
    <table class="payslip-netpay-box" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="padding: 12px 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: middle;">
                            <div class="netpay-title">{{ __trans('net_payable') }}</div>
                            <div class="netpay-words">{{ indianNumberToWords($payslip->net_pay) }}</div>
                        </td>
                        <td style="text-align: right; vertical-align: middle;">
                            <div class="netpay-amount">₹ {{ number_format($payslip->net_pay, 2) }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Employer Contributions Section (Non-Deducted) -->
    @if ($employerContributions->isNotEmpty())
    <div class="section-title">{{ __trans('employer_statutory_contributions') }} ({{ __trans('not_deducted_from_net_pay') }})</div>
    <table class="payslip-employer-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
        <thead>
            <tr>
                <th style="text-align: left; width: 70%;">{{ __trans('component') }}</th>
                <th style="text-align: right; width: 30%;">{{ __trans('amount') }} (INR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employerContributions as $ec)
            <tr>
                <td>{{ optional($ec->component)->name ?? $ec->label ?? 'Unknown' }}</td>
                <td style="text-align: right;">{{ number_format($ec->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>{{ __trans('total_employer_contributions') }}</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($payslip->total_employer_contributions, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Disclaimer / Digital Signature Signoff -->
    <table class="payslip-footer-table" style="width: 100%; border-collapse: collapse; margin-top: 30px;">
        <tr>
            <td style="width: 60%; vertical-align: bottom; font-size: 11px; color: #777; line-height: 1.4;">
                <p style="margin: 0 0 5px 0;"><strong>Notes:</strong></p>
                <p style="margin: 0;">1. This is a computer-generated statement and does not require a physical signature.</p>
                <p style="margin: 0;">2. For any discrepancies, please contact the HR department within 3 working days.</p>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: bottom;">
                <div class="signature-section" style="display: inline-block; text-align: center;">
                    <div style="font-size: 11px; color: #777; margin-bottom: 45px;">For {{ config('app.name', 'WorkPilot') }}</div>
                    <div style="border-top: 1px dashed #aaa; width: 180px; margin: 0 auto; padding-top: 5px; font-size: 12px; font-weight: bold; color: #555;">
                        {{ __trans('authorized_signatory') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

</div>
