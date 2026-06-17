{{-- Professional Final Settlement Document — Author: Sanket --}}
@php
    /**
     * Robust leave balance finder:
     * - Filters out records where leaveType name is null or purely numeric (stale/orphaned IDs)
     * - Uses latest year record if multiple exist for the same type
     */
    $validBalances = $user->leaveBalances
        ->filter(fn($b) => $b->leaveType !== null && !is_numeric(trim($b->leaveType->name ?? '')));

    $findLeave = function(array $names) use ($validBalances) {
        // Try each name in priority order, return latest year record
        foreach ($names as $name) {
            $match = $validBalances
                ->filter(fn($b) => strtolower($b->leaveType->name ?? '') === strtolower($name))
                ->sortByDesc('year')
                ->first();
            if ($match) return $match;
        }
        return null;
    };

    // Annual Leave — try common names in priority order
    $annualLeave    = $findLeave(['Vacation', 'Annual Leave', 'Annual', 'PL']);
    $annualAccrued  = round($annualLeave->earned_balance ?? 0, 2);
    $annualUsed     = round($annualLeave->used_balance ?? 0, 2);
    $annualBalance  = round($annualLeave->remaining_balance ?? 0, 2);

    // Other Leave types
    $extraHours    = $findLeave(['Extra Leave', 'Extra Hours', 'Extra']);
    $phComp        = $findLeave(['PH', 'Public Holiday', 'PH Compensatory']);
    $weeklyOffComp = $findLeave(['Weekly Off', 'DIL Leave', 'Day In Lieu', 'Offset']);
    $offshoreComp  = $findLeave(['Offshore Compensatory', 'Offshore', 'Casual']);

    // Ticket — entitlement vs used
    $airTicketEligible = (int) ($user->workDetail->air_ticket_count ?? 0);
    $airTicketUsed     = $user->airTicketsDetail->count();
    $airTicketBalance  = max(0, $airTicketEligible - $airTicketUsed);

    // Profile photo — local path only (no CDN, avoids CORS in html2pdf)
    $hasLocalPhoto = $user->profile_image && file_exists(public_path('uploads/profile/' . $user->profile_image));
    $profileImgSrc = $hasLocalPhoto ? asset('uploads/profile/' . $user->profile_image) : null;

    // Dynamic company name from portal settings
    $companyName = getSetting('site_title');
@endphp

{{-- Wrapper: hidden by default; shown only during PDF generation --}}
<div id="professionalSettlementLayout"
     style="display:none; background:#fff; color:#000;
            font-family:Arial,Helvetica,sans-serif; font-size:8.5pt;
            padding:10px 14px; width:740px; margin:auto;
            box-sizing:border-box; line-height:1.25;
            page-break-inside:avoid;">

    {{-- ===== HEADER ===== --}}
    <div style="margin-bottom:4px;">
        <div style="font-size:12pt; font-weight:bold;">{{ $companyName }}</div>
        <div style="font-size:11pt; font-weight:bold; margin-top:1px;">FINAL SETTLEMENT DETAILS - RESIGNED</div>
    </div>

    {{-- ===== EMPLOYEE PROFILE (photo beside profile table) ===== --}}
    <table style="width:100%; border-collapse:collapse; border:1px solid #aaa;" cellpadding="0" cellspacing="0">
        <tr>
            {{-- Profile rows (8 data rows + 1 header = 9 rows total) --}}
            <td style="vertical-align:top;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td colspan="4" style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border-bottom:1px solid #aaa;">EMPLOYEE PROFILE</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; width:14%; text-align:right; white-space:nowrap;">Name :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5; width:28%;">{{ $user->name }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; width:18%; text-align:right; white-space:nowrap;">Employee ID :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ $user->employee_id }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Main Dept :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ $user->division->name ?? 'N/A' }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Sub Department :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ $user->department->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Department :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ $user->department->name ?? 'N/A' }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Point Of Hire :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">N/A</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Designation :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ $user->designation->name ?? 'N/A' }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Class :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">N/A</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Grade :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ $user->designation->grade ?? 'N/A' }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Provision Type :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">N/A</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Nationality :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ $user->profile->country->name ?? 'N/A' }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Sponsor Name :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ ($user->profile->visa_type == 'company_sponsored') ? $companyName : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Join Date :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ optional($user->workDetail->joining_date)->format('d/m/Y') ?? 'N/A' }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Leave Entitled :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">ANNUAL LEAVE - 30 DAYS</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Group Join Date :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ optional($user->workDetail->joining_date)->format('d/m/Y') ?? 'N/A' }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Gratuity Entitled :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">LABOUR LAW</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Basic Salary :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ number_format($user->salary->basic ?? 0, 2) }}</td>
                        <td style="padding:2px 5px; font-weight:bold; border:1px solid #e5e5e5; text-align:right; white-space:nowrap;">Gross Salary :</td>
                        <td style="padding:2px 5px; border:1px solid #e5e5e5;">{{ number_format($gross_value ?? 0, 2) }}</td>
                    </tr>
                </table>
            </td>

            {{-- Photo cell: fixed 72px width, no flexbox --}}
            <td style="width:72px; vertical-align:top; padding:3px 0 0 4px;">
                @if($profileImgSrc)
                    <img src="{{ $profileImgSrc }}"
                         style="width:68px; height:88px; object-fit:cover; border:1px solid #ccc; display:block;">
                @else
                    {{-- No flexbox: use display:table for vertical centering --}}
                    <div style="width:68px; height:88px; border:1px solid #ccc; background:#eee; display:table;">
                        <div style="display:table-cell; vertical-align:middle; text-align:center; font-size:7pt; color:#999;">No<br>Photo</div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ===== ROW 2: FINAL SETTLEMENT DETAILS (left 48%) + LEAVE DETAILS (right 52%) ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-top:4px;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:48%; vertical-align:top; border:1px solid #aaa;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td colspan="2" style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border-bottom:1px solid #aaa;">FINAL SETTLEMENT DETAILS</td></tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #eee; width:44%;">Status :</td>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">RESIGNED</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #eee;">Settlement Date :</td>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">{{ date('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #eee;">Last Working Date :</td>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">
                            {{ $offboard?->departure_date ? \Carbon\Carbon::parse($offboard->departure_date)->format('d/m/Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #eee;">Length of Service :</td>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;" id="pdf_service_duration">N/A</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #eee;">Reason :</td>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">{{ $offboard?->departure_reason?->name ?? 'Final Settlement' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; vertical-align:top;">Remarks :</td>
                        <td style="padding:2px 5px;">{{ $offboard?->remarks ?? '' }}</td>
                    </tr>
                </table>
            </td>

            <td style="width:52%; vertical-align:top; border:1px solid #aaa; border-left:none;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td colspan="2" style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border-bottom:1px solid #aaa;">LEAVE DETAILS</td></tr>
                    <tr>
                        <td style="padding:3px 5px; font-weight:bold; background:#e2e2e2; border-bottom:1px solid #ccc;">ANNUAL LEAVE</td>
                        <td style="padding:3px 5px; font-weight:bold; background:#e2e2e2; border-bottom:1px solid #ccc; text-align:right;">Balance</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">Annual leave accrued</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">{{ number_format($annualAccrued, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">Annual leave used</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">{{ number_format($annualUsed, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #ccc;">Annual leave balance</td>
                        <td style="padding:2px 5px; font-weight:bold; text-align:right; border-bottom:1px solid #ccc;">{{ number_format($annualBalance, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:3px 5px; font-weight:bold; background:#e2e2e2; border-bottom:1px solid #ccc;">OTHER LEAVE</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">Extra Hours</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">{{ number_format($extraHours->remaining_balance ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">PH Compensatory</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">{{ number_format($phComp->remaining_balance ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">Weekly Off Compensatory</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">{{ number_format($weeklyOffComp->remaining_balance ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px;">Offshore Compensatory</td>
                        <td style="padding:2px 5px; text-align:right;">{{ number_format($offshoreComp->remaining_balance ?? 0, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== ROW 3: FIXED ALLOWANCE (55%) + TICKET DETAILS (45%) ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-top:4px;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:55%; vertical-align:top; border:1px solid #aaa;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td colspan="3" style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border-bottom:1px solid #aaa;">FIXED ALLOWANCE / DEDUCTIONS</td></tr>
                    <tr style="background:#e2e2e2;">
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #ccc; width:50%;">Payment type</td>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #ccc; text-align:right; width:30%;">Amount</td>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #ccc; text-align:right; width:20%;">%</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">HOUSING</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">{{ number_format($user->salary->hra ?? 0, 2) }}</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">0.00</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px;">TRANSPORT</td>
                        <td style="padding:2px 5px; text-align:right;">{{ number_format($user->salary->travel_allowance ?? 0, 2) }}</td>
                        <td style="padding:2px 5px; text-align:right;">0.00</td>
                    </tr>
                </table>
            </td>
            <td style="width:45%; vertical-align:top; border:1px solid #aaa; border-left:none;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td colspan="4" style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border-bottom:1px solid #aaa;">TICKET DETAILS</td></tr>
                    <tr style="background:#e2e2e2;">
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #ccc; width:35%;">Ticket Type</td>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #ccc; text-align:right;">Eligible</td>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #ccc; text-align:right;">Used :</td>
                        <td style="padding:2px 5px; font-weight:bold; border-bottom:1px solid #ccc; text-align:right;">{{ $airTicketUsed }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px; border-bottom:1px solid #eee;">Annual</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">{{ $airTicketEligible }}</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">Balance</td>
                        <td style="padding:2px 5px; text-align:right; border-bottom:1px solid #eee;">{{ $airTicketBalance }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px;">Given :</td>
                        <td colspan="3" style="padding:2px 5px; text-align:right;">0.00</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== LEAVE APPROVAL ===== --}}
    <table style="width:55%; border-collapse:collapse; margin-top:4px;" cellpadding="0" cellspacing="0">
        <tr><td colspan="3" style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border:1px solid #aaa;">LEAVE APPROVAL</td></tr>
        <tr style="background:#e2e2e2;">
            <td style="padding:2px 5px; font-weight:bold; border:1px solid #aaa; width:35%;">Leave</td>
            <td style="padding:2px 5px; font-weight:bold; border:1px solid #aaa; width:30%;"># Days &nbsp; Balance In</td>
            <td style="padding:2px 5px; font-weight:bold; border:1px solid #aaa;">Leave Type</td>
        </tr>
        <tr>
            <td style="padding:2px 5px; border:1px solid #ddd;">Weekly Off Clearance</td>
            <td style="padding:2px 5px; border:1px solid #ddd;">{{ number_format($weeklyOffComp->remaining_balance ?? 0, 2) }} Days</td>
            <td style="padding:2px 5px; border:1px solid #ddd;">COMPENSATORY LEAVE</td>
        </tr>
        <tr>
            <td style="padding:2px 5px; border:1px solid #ddd;">Paid Leave</td>
            <td style="padding:2px 5px; border:1px solid #ddd;">{{ number_format($annualBalance, 2) }} Days</td>
            <td style="padding:2px 5px; border:1px solid #ddd;">ANNUAL LEAVE</td>
        </tr>
        <tr>
            <td style="padding:2px 5px; border:1px solid #ddd;">Public Holiday Clearance</td>
            <td style="padding:2px 5px; border:1px solid #ddd;">{{ number_format($phComp->remaining_balance ?? 0, 2) }} Days</td>
            <td style="padding:2px 5px; border:1px solid #ddd;">PH CLEARANCE</td>
        </tr>
    </table>

    {{-- ===== FINAL SETTLEMENT PAYMENTS ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-top:4px;" cellpadding="0" cellspacing="0">
        <tr><td colspan="4" style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border:1px solid #aaa;">FINAL SETTLEMENT PAYMENTS</td></tr>
        <tr style="background:#e2e2e2;">
            <td style="padding:2px 5px; font-weight:bold; border:1px solid #aaa; width:22%;">Settlements</td>
            <td style="padding:2px 5px; font-weight:bold; border:1px solid #aaa; width:16%;">Payment Code</td>
            <td style="padding:2px 5px; font-weight:bold; border:1px solid #aaa; width:44%;">Narration</td>
            <td style="padding:2px 5px; font-weight:bold; border:1px solid #aaa; text-align:right; width:18%;">Amount</td>
        </tr>
        <tbody id="pdf_payment_body">
            <tr id="pdf_row_salary">
                <td style="padding:2px 5px; border:1px solid #ddd;">SALARY (IF ANY)</td>
                <td style="padding:2px 5px; border:1px solid #ddd;">BASIC PAY</td>
                <td style="padding:2px 5px; border:1px solid #ddd;" id="pdf_narration_salary">BASIC PAY For —</td>
                <td style="padding:2px 5px; border:1px solid #ddd; text-align:right;" id="pdf_val_basic">0.00</td>
            </tr>
            <tr id="pdf_row_housing">
                <td style="padding:2px 5px; border:1px solid #ddd;">ALLOWANCES</td>
                <td style="padding:2px 5px; border:1px solid #ddd;">HOUSING</td>
                <td style="padding:2px 5px; border:1px solid #ddd;">HOUSING</td>
                <td style="padding:2px 5px; border:1px solid #ddd; text-align:right;" id="pdf_val_housing">{{ number_format($user->salary->hra ?? 0, 2) }}</td>
            </tr>
            <tr id="pdf_row_transport">
                <td style="padding:2px 5px; border:1px solid #ddd;">ALLOWANCES</td>
                <td style="padding:2px 5px; border:1px solid #ddd;">TRANSPORT</td>
                <td style="padding:2px 5px; border:1px solid #ddd;">TRANSPORT</td>
                <td style="padding:2px 5px; border:1px solid #ddd; text-align:right;" id="pdf_val_transport">{{ number_format($user->salary->travel_allowance ?? 0, 2) }}</td>
            </tr>
            <tr id="pdf_row_leave">
                <td style="padding:2px 5px; border:1px solid #ddd;">LEAVE SALARY</td>
                <td style="padding:2px 5px; border:1px solid #ddd;">LEAVE SALARY</td>
                <td style="padding:2px 5px; border:1px solid #ddd;" id="pdf_narration_leave">LEAVE SALARY For {{ number_format($annualBalance, 2) }} Days</td>
                <td style="padding:2px 5px; border:1px solid #ddd; text-align:right;" id="pdf_val_leave">0.00</td>
            </tr>
            <tr id="pdf_row_gratuity">
                <td style="padding:2px 5px; border:1px solid #ddd;">SERVICE BENEFITS (GRATUITY)</td>
                <td style="padding:2px 5px; border:1px solid #ddd;">GRATUITY</td>
                <td style="padding:2px 5px; border:1px solid #ddd;" id="pdf_narration_gratuity">GRATUITY (Days — Adv 0.00)</td>
                <td style="padding:2px 5px; border:1px solid #ddd; text-align:right;" id="pdf_val_gratuity">0.00</td>
            </tr>
        </tbody>
        <tr>
            <td colspan="3" style="padding:3px 5px; border:1px solid #ddd; text-align:right; font-weight:bold;">Total Amount</td>
            <td style="padding:3px 5px; border:1px solid #ddd; text-align:right; font-weight:bold;" id="pdf_val_total">0.00</td>
        </tr>
    </table>

    {{-- ===== BANK + GRAND TOTAL ===== --}}
    <table style="width:100%; border-collapse:collapse; border:1px solid #ddd; border-top:none;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:3px 5px; font-weight:bold; width:22%; border-right:1px solid #ddd;">{{ $user->bankDetail->bank_name ?? 'N/A' }}</td>
            <td style="padding:3px 5px; width:40%; border-right:1px solid #ddd;">{{ $user->bankDetail->iba_number ?? 'N/A' }}</td>
            <td style="padding:3px 5px; font-weight:bold; text-align:right; border-right:1px solid #ddd; width:20%;">Grand Total</td>
            <td style="padding:3px 5px; font-weight:bold; text-align:right; width:18%;" id="pdf_val_grand_total">0.00</td>
        </tr>
    </table>

    {{-- ===== EMPLOYEES ACCEPTANCE ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-top:4px; border:1px solid #aaa;" cellpadding="0" cellspacing="0">
        <tr><td style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border-bottom:1px solid #aaa;">EMPLOYEES ACCEPTANCE</td></tr>
        <tr>
            <td style="padding:5px; font-size:8pt; line-height:1.4;">
                I confirm that, I have received the above in full and final settlement of all the dues from the company for my service for the period from joining date to last day of service. I also confirm that, I do not have any financial or other claim on the company.
            </td>
        </tr>
    </table>

    {{-- ===== APPROVAL DETAILS ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-top:4px; border:1px solid #aaa;" cellpadding="0" cellspacing="0">
        <tr><td colspan="4" style="background:#ccc; font-weight:bold; padding:3px 5px; font-size:8pt; border-bottom:1px solid #aaa;">APPROVAL DETAILS</td></tr>
        <tr>
            <td style="padding:20px 5px 3px 5px; width:38%;">Chief Human Resources Officer :</td>
            <td style="padding:20px 5px 3px 5px; width:24%; border-bottom:1px solid #333;">&nbsp;</td>
            <td style="padding:20px 5px 3px 5px; width:10%;">Date :</td>
            <td style="padding:20px 5px 3px 5px; width:28%; border-bottom:1px solid #333;">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding:16px 5px 3px 5px;">Finance Department :</td>
            <td style="padding:16px 5px 3px 5px; border-bottom:1px solid #333;">&nbsp;</td>
            <td style="padding:16px 5px 3px 5px;">Date :</td>
            <td style="padding:16px 5px 3px 5px; border-bottom:1px solid #333;">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding:16px 5px 8px 5px;">Employee's Name &amp; Signature :</td>
            <td style="padding:16px 5px 8px 5px; border-bottom:1px solid #333;">&nbsp;</td>
            <td style="padding:16px 5px 8px 5px;">Date :</td>
            <td style="padding:16px 5px 8px 5px; border-bottom:1px solid #333;">&nbsp;</td>
        </tr>
    </table>

    {{-- ===== FOOTER ===== --}}
    <table style="width:100%; margin-top:6px; font-size:7.5pt; color:#555;" cellpadding="0" cellspacing="0">
        <tr>
            <td>Printed On : {{ date('d/m/Y') }} at {{ date('H:i:s') }}</td>
            <td style="text-align:center;">Printed By : HRM</td>
            <td style="text-align:right;">Page 1 of 1</td>
        </tr>
    </table>

</div>
