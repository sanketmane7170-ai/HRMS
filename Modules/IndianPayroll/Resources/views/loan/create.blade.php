@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">{{ __trans('add_loan_advance') }}</h3></div>

        @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('backend.indian-payroll.loans.store') }}">
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
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __trans('type') }}</label>
                        <select name="loan_type" class="form-control" required>
                            @foreach(\Modules\IndianPayroll\Entities\EmployeeLoan::TYPES as $val => $label)
                            <option value="{{ $val }}" @selected(old('loan_type') == $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __trans('principal_amount') }}</label>
                        <input type="number" step="0.01" name="principal_amount" class="form-control" value="{{ old('principal_amount') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __trans('monthly_emi') }}</label>
                        <input type="number" step="0.01" name="emi_amount" class="form-control" value="{{ old('emi_amount') }}" required>
                        <small class="text-muted">{{ __trans('emi_recovered_until_cleared') }}</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('start_month') }}</label>
                        <select name="start_month" class="form-control" required>
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected(old('start_month', now()->month) == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('start_year') }}</label>
                        <input type="number" name="start_year" class="form-control" value="{{ old('start_year', now()->year) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('disbursed_on') }}</label>
                        <input type="date" name="disbursed_on" class="form-control" value="{{ old('disbursed_on') }}">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __trans('notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <button class="btn btn-primary">{{ __trans('save') }}</button>
                <a href="{{ route('backend.indian-payroll.loans.index') }}" class="btn btn-secondary">{{ __trans('cancel') }}</a>
            </form>
        </div></div>
    </div>
</div>
@endsection
