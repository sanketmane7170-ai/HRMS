@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">{{ __trans('add_leave_encashment') }}</h3></div>

        @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('backend.indian-payroll.leave-encashment.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __trans('employee') }}</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">{{ __trans('select_employee') }}</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->user_id }}" @selected(old('user_id') == $emp->user_id)>{{ $emp->user->name ?? 'User #'.$emp->user_id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __trans('month') }}</label>
                        <select name="month" class="form-control" required>
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected(old('month', now()->month) == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __trans('year') }}</label>
                        <input type="number" name="year" class="form-control" value="{{ old('year', now()->year) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('days') }}</label>
                        <input type="number" step="0.01" name="days" class="form-control" value="{{ old('days') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('per_day_rate') }}</label>
                        <input type="number" step="0.01" name="per_day_rate" class="form-control" value="{{ old('per_day_rate') }}" required>
                        <small class="text-muted">{{ __trans('encashment_rate_hint') }}</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('taxable_amount') }}</label>
                        <input type="number" step="0.01" name="taxable_amount" class="form-control" value="{{ old('taxable_amount') }}">
                        <small class="text-muted">{{ __trans('blank_defaults_full_taxable') }}</small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __trans('remarks') }}</label>
                        <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                    </div>
                </div>
                <button class="btn btn-primary">{{ __trans('save') }}</button>
                <a href="{{ route('backend.indian-payroll.leave-encashment.index') }}" class="btn btn-secondary">{{ __trans('cancel') }}</a>
            </form>
        </div></div>
    </div>
</div>
@endsection
