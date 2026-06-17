@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('indian_payroll') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('indian_payroll') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card"><div class="card-body">
                    <h6>{{ __trans('active_employees') }}</h6>
                    <h3>{{ $employeeCount }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card"><div class="card-body">
                    <h6>{{ __trans('latest_run') }}</h6>
                    <h4>{{ $latestRun ? \Carbon\Carbon::create($latestRun->year, $latestRun->month, 1)->format('M Y') : __trans('none') }}</h4>
                    <span class="badge badge-info">{{ $latestRun?->status }}</span>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card"><div class="card-body">
                    <h6>{{ __trans('latest_run_net_pay') }}</h6>
                    <h3>{{ number_format($latestRunNetPay, 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card"><div class="card-body">
                    <a href="{{ route('backend.indian-payroll.payroll-runs.index') }}" class="btn btn-primary w-100">{{ __trans('go_to_payroll_runs') }}</a>
                    <a href="{{ route('backend.indian-payroll.employee-profiles.index') }}" class="btn btn-outline-primary w-100 mt-2">{{ __trans('manage_employee_profiles') }}</a>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <h5>{{ __trans('recent_payroll_runs') }}</h5>
                        <table class="table table-hover">
                            <thead><tr><th>{{ __trans('period') }}</th><th>{{ __trans('status') }}</th><th>{{ __trans('action') }}</th></tr></thead>
                            <tbody>
                                @foreach ($recentRuns as $run)
                                <tr>
                                    <td>{{ \Carbon\Carbon::create($run->year, $run->month, 1)->format('F Y') }}</td>
                                    <td><span class="badge badge-info">{{ $run->status }}</span></td>
                                    <td><a href="{{ route('backend.indian-payroll.payroll-runs.show', $run) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
