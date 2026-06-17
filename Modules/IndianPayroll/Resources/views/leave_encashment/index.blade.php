@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('leave_encashment') }}</h3></div>
                <div class="col-auto"><a href="{{ route('backend.indian-payroll.leave-encashment.create') }}" class="btn btn-primary">{{ __trans('add_leave_encashment') }}</a></div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="card card-table"><div class="card-body">
            <table class="table table-hover">
                <thead><tr>
                    <th>{{ __trans('employee') }}</th><th>{{ __trans('period') }}</th>
                    <th class="text-end">{{ __trans('days') }}</th><th class="text-end">{{ __trans('rate') }}</th>
                    <th class="text-end">{{ __trans('amount') }}</th><th class="text-end">{{ __trans('taxable') }}</th>
                    <th>{{ __trans('status') }}</th><th>{{ __trans('action') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($entries as $e)
                    <tr>
                        <td>{{ $e->user->name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::create($e->year, $e->month, 1)->format('M Y') }}</td>
                        <td class="text-end">{{ number_format($e->days, 2) }}</td>
                        <td class="text-end">{{ number_format($e->per_day_rate, 2) }}</td>
                        <td class="text-end">{{ number_format($e->amount, 2) }}</td>
                        <td class="text-end">{{ number_format($e->taxable_amount, 2) }}</td>
                        <td><span class="badge badge-{{ $e->status === 'paid' ? 'success' : ($e->status === 'approved' ? 'info' : ($e->status === 'rejected' ? 'danger' : 'warning')) }}">{{ ucfirst($e->status) }}</span></td>
                        <td>
                            @if($e->status === 'pending')
                            <form method="POST" action="{{ route('backend.indian-payroll.leave-encashment.approve', $e) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">{{ __trans('approve') }}</button></form>
                            <form method="POST" action="{{ route('backend.indian-payroll.leave-encashment.reject', $e) }}" class="d-inline">@csrf<button class="btn btn-sm btn-danger">{{ __trans('reject') }}</button></form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">{{ __trans('no_records_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $entries->links() }}
        </div></div>
    </div>
</div>
@endsection
