@php
    $show_fixed_allowances = false;
    if(str_contains(getSetting('currency'), 'AED')){
            $AEDCurrency = '<img src="'. asset("assets/currency/aedb.png") .'" alt="AED" style="width:18px; height:18px; vertical-align:middle;">';
    } else {
        $AEDCurrency = getSetting('currency');
    }
@endphp

{{-- Check if any fixed allowance is present --}}
@foreach ($allowances as $allowance)
    @php $allowanceName = $allowance->name; @endphp
    @if ($allowance->type == 1 && array_key_exists($allowanceName, $all_fixed_entity))
        @php $show_fixed_allowances = true; @endphp
        @break
    @endif
@endforeach

@if($show_fixed_allowances)
<table class="">
    <tbody>
        <tr class="font-weight-bold">
            <th>Fixed Allowance</th>
            <th width="150">Value</th>
        </tr>

        {{-- @foreach ($allowances as $allowance)
            @php $allowanceName = $allowance->name; @endphp
            @if ($allowance->type == 1 && array_key_exists($allowanceName, $all_fixed_entity))
            <tr>
                <td>{{ $allowanceName }}</td>
                <td class="text-right">{{ $all_fixed_entity[$allowanceName] }} {{ getSetting('currency') }}</td>
            </tr>
            @endif
        @endforeach --}}

        {{-- Tips Row --}}
        @if (!empty($all_fixed_entity['tips']))
        <tr>
            <td>Tips</td>
            <td class="text-right">{{ $all_fixed_entity['tips'] }} {!! $AEDCurrency !!}</td>
        </tr>
        @endif
    </tbody>
</table>
@endif

@if($expense)
<table class="">
    <tbody>

        <tr class="font-weight-bold">
            <th>Total Last Month Expenses</th>
            <th width="150">Amount</th>
        </tr>

        <tr>
            <td>Expense</td>
            <td class="text-right">
                {{ $expense }}-{!! $AEDCurrency !!}</td>
        </tr>

    </tbody>
</table>
@endif
@foreach($user_salary as $result)
@if(count($result['all_allowance']))
<table class="">
    <tbody>
        <tr class="font-weight-bold">
            <th>@if(request()->getHttpHost()=="cakesocial.momdigital.io") Variable Addition @else Variable
                Allowance @endif</th>
            <th width="150">Type (Fixed/Percentage)</th>
            <th  class="text-right">Amount</th>
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
            <td class="text-right">{{ $allowance->amount }} -{!! $AEDCurrency !!}</td>
            @else
            <td class="text-right">{{ $allowance->amount}}%({{ $allowance->percentage_amount }}) -{!! $AEDCurrency !!}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(count($result['all_overtime']))
<div class="table-responsive">
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
</div>
@endif
@endforeach
