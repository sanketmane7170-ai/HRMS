@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('payroll_runs') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('indian_payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('payroll_runs') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRunModal"><i class="fas fa-plus"></i> {{ __trans('new_run') }}</button>
                </div>
            </div>
        </div>

        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="card card-table">
            <div class="card-body">
                <table class="table table-hover">
                    <thead><tr><th>{{ __trans('period') }}</th><th>{{ __trans('status') }}</th><th>{{ __trans('created_by') }}</th><th>{{ __trans('action') }}</th></tr></thead>
                    <tbody>
                        @foreach ($runs as $run)
                        <tr>
                            <td>{{ \Carbon\Carbon::create($run->year, $run->month, 1)->format('F Y') }}</td>
                            <td><span class="badge badge-{{ $run->status === 'locked' ? 'success' : ($run->status === 'approved' ? 'info' : 'warning') }}">{{ $run->status }}</span></td>
                            <td>{{ $run->creator->name ?? '-' }}</td>
                            <td><a href="{{ route('backend.indian-payroll.payroll-runs.show', $run) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $runs->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createRunModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('backend.indian-payroll.payroll-runs.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">{{ __trans('new_payroll_run') }}</h5></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __trans('month') }}</label>
                        <select name="month" class="form-control" required>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == now()->month)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __trans('year') }}</label>
                        <input type="number" name="year" class="form-control" value="{{ now()->year }}" required>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">{{ __trans('create') }}</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
