@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('reimbursements') }}</h3></div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.reimbursements.create') }}" class="btn btn-primary">{{ __trans('add_reimbursement') }}</a>
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
                        <th>{{ __trans('date') }}</th>
                        <th class="text-end">{{ __trans('claim_amount') }}</th>
                        <th class="text-end">{{ __trans('taxable') }}</th>
                        <th>{{ __trans('proof') }}</th>
                        <th>{{ __trans('status') }}</th>
                        <th>{{ __trans('action') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($reimbursements as $r)
                        <tr>
                            <td>{{ $r->user->name ?? 'N/A' }}</td>
                            <td>{{ $r->typeLabel() }}</td>
                            <td>{{ $r->claim_date->format('d-M-Y') }}</td>
                            <td class="text-end">{{ number_format($r->claim_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($r->taxable_amount, 2) }}</td>
                            <td>
                                @if($r->proof_path)
                                <a href="{{ route('backend.indian-payroll.reimbursements.proof', $r) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-file"></i></a>
                                @else <span class="text-muted">—</span> @endif
                            </td>
                            <td><span class="badge badge-{{ $r->status === 'paid' ? 'success' : ($r->status === 'approved' ? 'info' : ($r->status === 'rejected' ? 'danger' : 'warning')) }}">{{ ucfirst($r->status) }}</span></td>
                            <td>
                                @if($r->status === 'pending')
                                <form method="POST" action="{{ route('backend.indian-payroll.reimbursements.approve', $r) }}" class="d-inline-flex gap-1 align-items-center">
                                    @csrf
                                    <input type="number" step="0.01" name="taxable_amount" value="{{ $r->taxable_amount }}" class="form-control form-control-sm" style="width:100px;" title="{{ __trans('taxable_amount') }}">
                                    <button class="btn btn-sm btn-success">{{ __trans('approve') }}</button>
                                </form>
                                <form method="POST" action="{{ route('backend.indian-payroll.reimbursements.reject', $r) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-danger">{{ __trans('reject') }}</button>
                                </form>
                                @elseif($r->run_id)
                                <small class="text-muted">{{ __trans('paid_in') }} {{ optional($r->run)->month ? \Carbon\Carbon::create($r->run->year, $r->run->month, 1)->format('M Y') : '' }}</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted">{{ __trans('no_records_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $reimbursements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
