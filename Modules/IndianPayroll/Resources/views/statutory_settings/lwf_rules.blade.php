@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('lwf_rules') }}</h3></div>
                <div class="col-auto">
                    <form method="GET" class="d-flex">
                        <select name="state_id" class="form-control" onchange="this.form.submit()">
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}" @selected($state->id == $stateId)>{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="card"><div class="card-body">
            <table class="table table-sm">
                <thead><tr><th>{{ __trans('frequency') }}</th><th>{{ __trans('employee_contribution') }}</th><th>{{ __trans('employer_contribution') }}</th><th>{{ __trans('wage_ceiling') }}</th><th>{{ __trans('effective_from') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach ($rules as $rule)
                    <tr>
                        <td>{{ str_replace('_', ' ', $rule->frequency) }}</td>
                        <td>{{ number_format($rule->employee_contribution, 2) }}</td>
                        <td>{{ number_format($rule->employer_contribution, 2) }}</td>
                        <td>{{ $rule->wage_ceiling ? number_format($rule->wage_ceiling, 2) : __trans('none') }}</td>
                        <td>{{ $rule->effective_from->format('d-M-Y') }}</td>
                        <td><a href="{{ route('backend.indian-payroll.lwf-rules.destroy', $rule) }}" method="DELETE" class="btn btn-sm btn-link text-danger action-button"><i class="fa fa-trash"></i></a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <form method="POST" action="{{ route('backend.indian-payroll.lwf-rules.store') }}" class="row g-2">
                @csrf
                <input type="hidden" name="state_id" value="{{ $stateId }}">
                <div class="col-md-2">
                    <select name="frequency" class="form-control">
                        <option value="half_yearly">{{ __trans('half_yearly') }}</option>
                        <option value="monthly">{{ __trans('monthly') }}</option>
                        <option value="annual">{{ __trans('annual') }}</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="number" step="0.01" name="employee_contribution" class="form-control" placeholder="{{ __trans('employee') }}" required></div>
                <div class="col-md-2"><input type="number" step="0.01" name="employer_contribution" class="form-control" placeholder="{{ __trans('employer') }}" required></div>
                <div class="col-md-2"><input type="number" step="0.01" name="wage_ceiling" class="form-control" placeholder="{{ __trans('wage_ceiling_optional') }}"></div>
                <div class="col-md-2"><input type="date" name="effective_from" class="form-control" required></div>
                <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">{{ __trans('add') }}</button></div>
            </form>
        </div></div>
    </div>
</div>
@endsection
