@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('statutory_settings') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('indian_payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('statutory_settings') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.tax-slabs.index') }}" class="btn btn-outline-primary">{{ __trans('income_tax_slabs') }}</a>
                    <a href="{{ route('backend.indian-payroll.professional-tax.index') }}" class="btn btn-outline-primary">{{ __trans('professional_tax') }}</a>
                    <a href="{{ route('backend.indian-payroll.lwf-rules.index') }}" class="btn btn-outline-primary">{{ __trans('lwf_rules') }}</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card"><div class="card-body">
                    <h5>{{ __trans('provident_fund_epf') }}</h5>
                    @php($current = $pfSettings->first())
                    @if($current)
                    <p>{{ __trans('employee') }}: {{ $current->employee_rate }}% | {{ __trans('employer') }}: {{ $current->employer_rate }}% ({{ __trans('eps') }} {{ $current->eps_rate }}%)<br>
                    {{ __trans('wage_ceiling') }}: {{ number_format($current->wage_ceiling, 2) }}</p>
                    @endif
                    <form method="POST" action="{{ route('backend.indian-payroll.statutory-settings.pf.store') }}">
                        @csrf
                        <div class="form-group"><label>{{ __trans('effective_from') }}</label><input type="date" name="effective_from" class="form-control" required></div>
                        <div class="form-group"><label>{{ __trans('employee_rate') }} %</label><input type="number" step="0.01" name="employee_rate" class="form-control" value="12.00" required></div>
                        <div class="form-group"><label>{{ __trans('employer_rate') }} %</label><input type="number" step="0.01" name="employer_rate" class="form-control" value="12.00" required></div>
                        <div class="form-group"><label>{{ __trans('eps_rate') }} %</label><input type="number" step="0.01" name="eps_rate" class="form-control" value="8.33" required></div>
                        <div class="form-group"><label>{{ __trans('wage_ceiling') }}</label><input type="number" step="0.01" name="wage_ceiling" class="form-control" value="15000.00" required></div>
                        <div class="form-group"><label>{{ __trans('eps_wage_ceiling') }}</label><input type="number" step="0.01" name="eps_wage_ceiling" class="form-control" value="15000.00" required></div>
                        <div class="form-group"><label>{{ __trans('admin_charges_rate') }} %</label><input type="number" step="0.01" name="admin_charges_rate" class="form-control" value="0.50" required></div>
                        <button type="submit" class="btn btn-sm btn-primary">{{ __trans('add_new_effective_dated_rate') }}</button>
                    </form>
                </div></div>
            </div>

            <div class="col-md-4">
                <div class="card"><div class="card-body">
                    <h5>{{ __trans('esi') }}</h5>
                    @php($current = $esiSettings->first())
                    @if($current)
                    <p>{{ __trans('employee') }}: {{ $current->employee_rate }}% | {{ __trans('employer') }}: {{ $current->employer_rate }}%<br>
                    {{ __trans('wage_threshold') }}: {{ number_format($current->wage_threshold, 2) }}</p>
                    @endif
                    <form method="POST" action="{{ route('backend.indian-payroll.statutory-settings.esi.store') }}">
                        @csrf
                        <div class="form-group"><label>{{ __trans('effective_from') }}</label><input type="date" name="effective_from" class="form-control" required></div>
                        <div class="form-group"><label>{{ __trans('employee_rate') }} %</label><input type="number" step="0.01" name="employee_rate" class="form-control" value="0.75" required></div>
                        <div class="form-group"><label>{{ __trans('employer_rate') }} %</label><input type="number" step="0.01" name="employer_rate" class="form-control" value="3.25" required></div>
                        <div class="form-group"><label>{{ __trans('wage_threshold') }}</label><input type="number" step="0.01" name="wage_threshold" class="form-control" value="21000.00" required></div>
                        <div class="form-group"><label>{{ __trans('wage_threshold_disabled') }}</label><input type="number" step="0.01" name="wage_threshold_disabled" class="form-control" value="25000.00" required></div>
                        <button type="submit" class="btn btn-sm btn-primary">{{ __trans('add_new_effective_dated_rate') }}</button>
                    </form>
                </div></div>
            </div>

            <div class="col-md-4">
                <div class="card"><div class="card-body">
                    <h5>{{ __trans('gratuity') }}</h5>
                    @php($current = $gratuitySettings->first())
                    @if($current)
                    <p>{{ __trans('exemption_ceiling') }}: {{ number_format($current->exemption_ceiling, 2) }}<br>
                    {{ __trans('formula') }}: {{ $current->days_per_year_first_slab }}/{{ $current->divisor_days_per_month }}, {{ __trans('min_years') }}: {{ $current->minimum_vesting_years }}</p>
                    @endif
                    <form method="POST" action="{{ route('backend.indian-payroll.statutory-settings.gratuity.store') }}">
                        @csrf
                        <div class="form-group"><label>{{ __trans('effective_from') }}</label><input type="date" name="effective_from" class="form-control" required></div>
                        <div class="form-group"><label>{{ __trans('exemption_ceiling') }}</label><input type="number" step="0.01" name="exemption_ceiling" class="form-control" value="2000000.00" required></div>
                        <div class="form-group"><label>{{ __trans('days_per_year_first_slab') }}</label><input type="number" name="days_per_year_first_slab" class="form-control" value="15" required></div>
                        <div class="form-group"><label>{{ __trans('divisor_days_per_month') }}</label><input type="number" name="divisor_days_per_month" class="form-control" value="26" required></div>
                        <div class="form-group"><label>{{ __trans('minimum_vesting_years') }}</label><input type="number" name="minimum_vesting_years" class="form-control" value="5" required></div>
                        <button type="submit" class="btn btn-sm btn-primary">{{ __trans('add_new_effective_dated_rate') }}</button>
                    </form>
                </div></div>
            </div>
        </div>
    </div>
</div>
@endsection
