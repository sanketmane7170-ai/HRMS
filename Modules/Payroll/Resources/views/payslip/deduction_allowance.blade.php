
@php
    if(str_contains(getSetting('currency'), 'AED')){
            $AEDCurrency = '<img src="'. asset("assets/currency/aedb.png") .'" alt="AED" style="width:18px; height:18px; vertical-align:middle;">';
    } else {
        $AEDCurrency = getSetting('currency');
    }
@endphp
@foreach($user_salary as $result)

@if($approvedAdvanceLoanAmount>0)
<table class="">
    <tbody>
        <tr class="font-weight-bold">
            <th>Advance Salary/Loan Amount</th>
            <th width="150" class="text-right">Approved Amount</th>
        </tr>
        <tr>
            <td>Advance Salary/Loan Amount</td>
            <td class="text-right">{{ $approvedAdvanceLoanAmount }} -{!! $AEDCurrency !!}</td>
        </tr>
    </tbody>
</table>
@endif
<table class="">
    <tbody>
        @if (!empty($all_fixed_entity['advance_salary']) || !empty($all_fixed_entity['loan_deduction']) || !empty($all_fixed_entity['other_deduction']) )
        <tr class="font-weight-bold">
            <th>Fixed Deduction</th>
            <th width="150">Value</th>
        </tr>
        @endif
        @if (!empty($all_fixed_entity['advance_salary']))
        <tr>
            <td>Advance Salary</td>
            <td class="text-right">{{ $all_fixed_entity['advance_salary'] }} -{!! $AEDCurrency !!}</td>
        </tr>
        @endif
        @if (!empty($all_fixed_entity['loan_deduction']))
        <tr>
            <td>Loan Deduction</td>
            <td class="text-right">{{ $all_fixed_entity['loan_deduction'] }} -{!! $AEDCurrency !!}</td>
        </tr>
        @endif
        @if (!empty($all_fixed_entity['other_deduction']))
        <tr>
            <td>Other Deduction</td>
            <td class="text-right">{{ $all_fixed_entity['other_deduction'] }} -{!! $AEDCurrency !!}</td>
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
            <td class="text-right">{{ $all_fixed_entity[$deductionName] }} -{{getSetting('currency')}}</td>
        </tr>
        @endif
        @endif
        @endforeach --}}
    </tbody>
</table>
@if(count($result['all_deduction']))
<table class="">
    <tbody>
        <tr class="font-weight-bold">
            <th>Variable Deductions</th>
            <th width="150" >Type (Fixed/Percentage)</th>
            <th class="text-right">Amount</th>
        </tr>
        @foreach($result['all_deduction'] as $deduction)
        <tr>
            <td>{{ $deduction->title }}</td>
            @if($deduction->deduction_type == 'fixed')
            <td>{{ __trans($deduction->deduction_type) }}</td>
            @else
            <td>{{ __trans($deduction->deduction_type) }}</td>
            @endif
            @if($deduction->deduction_type == 'fixed')
            <td class="text-right">{{ $deduction->amount }}-{!! $AEDCurrency !!}</td>
            @else
            <td class="text-right">{{ $deduction->amount}}%({{ $deduction->percentage_amount }}) -{!! $AEDCurrency !!}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
@endif
<!-- @if(count($result['all_overtime']))
<table class="">
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
            <td class="text-right">{{ $overtime->calculated_amount }} -{!! $AEDCurrency !!}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif -->
@endforeach