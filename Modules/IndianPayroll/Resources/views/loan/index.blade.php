@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('loans_and_advances') }}</h3></div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.loans.create') }}" class="btn btn-primary">{{ __trans('add_loan_advance') }}</a>
                </div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="card card-table">
            <div class="card-body">
                <table class="table table-hover">
                    <thead><tr>
                        <th>{{ __trans('employee') }}</th>
                        <th>{{ __trans('type') }}</th>
                        <th class="text-end">{{ __trans('principal') }}</th>
                        <th class="text-end">{{ __trans('emi') }}</th>
                        <th class="text-end">{{ __trans('recovered') }}</th>
                        <th class="text-end">{{ __trans('outstanding') }}</th>
                        <th>{{ __trans('status') }}</th>
                        <th>{{ __trans('action') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($loans as $loan)
                        <tr>
                            <td>{{ $loan->user->name ?? 'N/A' }}</td>
                            <td>{{ $loan->typeLabel() }}</td>
                            <td class="text-end">{{ number_format($loan->principal_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($loan->emi_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($loan->recoveredAmount(), 2) }}</td>
                            <td class="text-end">{{ number_format($loan->outstandingBalance(), 2) }}</td>
                            <td><span class="badge badge-{{ $loan->status === 'active' ? 'primary' : ($loan->status === 'closed' ? 'success' : 'secondary') }}">{{ ucfirst($loan->status) }}</span></td>
                            <td>
                                <a href="{{ route('backend.indian-payroll.loans.show', $loan) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                @if($loan->isActive())
                                <form method="POST" action="{{ route('backend.indian-payroll.loans.cancel', $loan) }}" class="d-inline" onsubmit="return confirm('{{ __trans('stop_recovery_confirm') }}')">
                                    @csrf
                                    <button class="btn btn-sm btn-danger" title="{{ __trans('stop_recovery') }}"><i class="fa fa-ban"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted">{{ __trans('no_records_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $loans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
