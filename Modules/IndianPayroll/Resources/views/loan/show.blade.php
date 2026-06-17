@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ $loan->user->name ?? 'N/A' }} — {{ $loan->typeLabel() }}</h3></div>
                <div class="col-auto"><a href="{{ route('backend.indian-payroll.loans.index') }}" class="btn btn-outline-secondary">{{ __trans('back') }}</a></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="card"><div class="card-body"><h6>{{ __trans('principal') }}</h6><h4>{{ number_format($loan->principal_amount, 2) }}</h4></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><h6>{{ __trans('emi') }}</h6><h4>{{ number_format($loan->emi_amount, 2) }}</h4></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><h6>{{ __trans('recovered') }}</h6><h4>{{ number_format($loan->recoveredAmount(), 2) }}</h4></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><h6>{{ __trans('outstanding') }}</h6><h4>{{ number_format($loan->outstandingBalance(), 2) }}</h4></div></div></div>
        </div>

        <div class="card card-table"><div class="card-body">
            <h5>{{ __trans('recovery_history') }}</h5>
            <table class="table table-hover">
                <thead><tr><th>{{ __trans('payroll_run') }}</th><th class="text-end">{{ __trans('amount') }}</th><th>{{ __trans('date') }}</th></tr></thead>
                <tbody>
                    @forelse ($loan->recoveries->sortByDesc('id') as $rec)
                    <tr>
                        <td>{{ optional($rec->run)->month ? \Carbon\Carbon::create($rec->run->year, $rec->run->month, 1)->format('F Y') : '#'.$rec->run_id }}</td>
                        <td class="text-end">{{ number_format($rec->amount, 2) }}</td>
                        <td>{{ $rec->created_at->format('d-M-Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted">{{ __trans('no_recovery_yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>
@endsection
