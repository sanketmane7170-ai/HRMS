@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('full_and_final_settlements') }}</h3></div>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <table class="table table-hover">
                    <thead><tr><th>{{ __trans('employee') }}</th><th>{{ __trans('last_working_day') }}</th><th>{{ __trans('net_payable') }}</th><th>{{ __trans('status') }}</th><th>{{ __trans('action') }}</th></tr></thead>
                    <tbody>
                        @foreach ($settlements as $settlement)
                        <tr>
                            <td>{{ $settlement->user->name ?? 'N/A' }}</td>
                            <td>{{ $settlement->last_working_day->format('d-M-Y') }}</td>
                            <td>{{ number_format($settlement->net_payable, 2) }}</td>
                            <td><span class="badge badge-{{ $settlement->status === 'approved' ? 'success' : 'warning' }}">{{ $settlement->status }}</span></td>
                            <td><a href="{{ route('backend.indian-payroll.settlements.show', $settlement) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $settlements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
