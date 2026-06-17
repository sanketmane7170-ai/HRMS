@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ \Carbon\Carbon::create($run->year, $run->month, 1)->format('F Y') }} {{ __trans('payroll_run') }}
                        @php
                            $badgeClass = match($run->status) {
                                'locked'    => 'badge-success',
                                'approved'  => 'badge-info',
                                'computed'  => 'badge-primary',
                                'computing' => 'badge-warning',
                                'failed'    => 'badge-danger',
                                default     => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($run->status) }}</span>
                        @if($run->isComputing())
                            <small class="text-muted ml-2"><i class="fa fa-spinner fa-spin"></i> Computing in background — refresh to see progress.</small>
                        @endif
                    </h3>
                </div>
                <div class="col-auto">
                    @if($run->isEditable())
                        <form method="POST" action="{{ route('backend.indian-payroll.payroll-runs.compute', $run) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">{{ __trans('compute_run') }}</button>
                        </form>
                    @endif
                    @if($run->isComputing())
                        <button class="btn btn-warning" disabled><i class="fa fa-spinner fa-spin"></i> Computing…</button>
                    @endif
                    @if($run->status === 'computed')
                        <form method="POST" action="{{ route('backend.indian-payroll.payroll-runs.approve', $run) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">{{ __trans('approve_run') }}</button>
                        </form>
                    @endif
                    @if($run->status === 'approved')
                        <form method="POST" action="{{ route('backend.indian-payroll.payroll-runs.lock', $run) }}" class="d-inline" onsubmit="return confirm('{{ __trans('lock_run_confirm') }}')">
                            @csrf
                            <button type="submit" class="btn btn-dark">{{ __trans('lock_run') }}</button>
                        </form>
                    @endif
                    @if(in_array($run->status, ['computed', 'approved', 'locked']))
                        <a href="{{ route('backend.indian-payroll.reports.pf', $run) }}" class="btn btn-outline-secondary">{{ __trans('pf_register') }}</a>
                        <a href="{{ route('backend.indian-payroll.reports.esi', $run) }}" class="btn btn-outline-secondary">{{ __trans('esi_register') }}</a>
                        <a href="{{ route('backend.indian-payroll.reports.pt', $run) }}" class="btn btn-outline-secondary">{{ __trans('pt_register') }}</a>
                        <a href="{{ route('backend.indian-payroll.reports.lwf', $run) }}" class="btn btn-outline-secondary">{{ __trans('lwf_register') }}</a>
                        <a href="{{ route('backend.indian-payroll.payroll-runs.salary-register', $run) }}" class="btn btn-outline-dark">{{ __trans('salary_register') }}</a>
                        <a href="{{ route('backend.indian-payroll.payroll-runs.payroll-summary', $run) }}" class="btn btn-outline-dark">{{ __trans('payroll_summary') }}</a>
                        <a href="{{ route('backend.indian-payroll.payroll-runs.department-cost', $run) }}" class="btn btn-outline-dark">{{ __trans('department_cost') }}</a>
                        <a href="{{ route('backend.indian-payroll.payroll-runs.salary-variance', $run) }}" class="btn btn-outline-dark">{{ __trans('salary_variance') }}</a>
                        <a href="{{ route('backend.indian-payroll.payroll-runs.journal-voucher', $run) }}" class="btn btn-outline-dark">{{ __trans('journal_voucher') }}</a>
                        <a href="{{ route('backend.indian-payroll.payroll-runs.pf-ecr', $run) }}" class="btn btn-outline-secondary">{{ __trans('pf_ecr_file') }}</a>
                        <a href="{{ route('backend.indian-payroll.payroll-runs.bank-file', $run) }}" class="btn btn-outline-success">{{ __trans('bank_transfer_file') }}</a>
                    @endif
                </div>
            </div>
        </div>

        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($run->status === 'computing')
            <div class="alert alert-warning">
                <i class="fa fa-spinner fa-spin"></i>
                Payroll compute is running in the background. This page will refresh automatically every 10 seconds.
            </div>
            <script>setTimeout(() => location.reload(), 10000);</script>
        @endif
        @if($run->status === 'failed')
            <div class="alert alert-danger">
                <strong>Compute failed.</strong>
                @if($run->compute_error)
                    <br><small>{{ $run->compute_error }}</small>
                @endif
                <br>You can retry by clicking <em>Compute Run</em> above.
            </div>
        @endif

        <div class="row">
            <div class="col-md-3"><div class="card"><div class="card-body"><h6>{{ __trans('gross') }}</h6><h4>{{ number_format($totals['gross'], 2) }}</h4></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><h6>{{ __trans('statutory_deductions') }}</h6><h4>{{ number_format($totals['statutory_deductions'], 2) }}</h4></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><h6>{{ __trans('employer_contributions') }}</h6><h4>{{ number_format($totals['employer_contributions'], 2) }}</h4></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><h6>{{ __trans('net_pay') }}</h6><h4>{{ number_format($totals['net_pay'], 2) }}</h4></div></div></div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <table class="table table-hover">
                    <thead><tr><th>{{ __trans('employee') }}</th><th>{{ __trans('gross') }}</th><th>{{ __trans('deductions') }}</th><th>{{ __trans('net_pay') }}</th><th>{{ __trans('lop_days') }}</th><th>{{ __trans('action') }}</th></tr></thead>
                    <tbody>
                        @foreach ($payslips as $payslip)
                        <tr>
                            <td>{{ $payslip->user->name ?? 'N/A' }}</td>
                            <td>{{ number_format($payslip->gross_earnings, 2) }}</td>
                            <td>{{ number_format($payslip->gross_earnings - $payslip->net_pay, 2) }}</td>
                            <td>{{ number_format($payslip->net_pay, 2) }}</td>
                            <td>{{ $payslip->loss_of_pay_days }}</td>
                            <td>
                                <a href="{{ route('backend.indian-payroll.payslips.show', $payslip) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                @if($run->isEditable())
                                <a href="{{ route('backend.indian-payroll.payslips.edit', $payslip) }}" class="btn btn-sm btn-warning" title="Edit / Add Deduction"><i class="fa fa-edit"></i></a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $payslips->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
