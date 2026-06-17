@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">{{ __trans('add_reimbursement') }}</h3></div>

        @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('backend.indian-payroll.reimbursements.store') }}" enctype="multipart/form-data">
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
                        <select name="reimbursement_type" class="form-control" required>
                            @foreach(\Modules\IndianPayroll\Entities\Reimbursement::TYPES as $val => $label)
                            <option value="{{ $val }}" @selected(old('reimbursement_type') == $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('claim_amount') }}</label>
                        <input type="number" step="0.01" name="claim_amount" class="form-control" value="{{ old('claim_amount') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('taxable_amount') }}</label>
                        <input type="number" step="0.01" name="taxable_amount" class="form-control" value="{{ old('taxable_amount', 0) }}">
                        <small class="text-muted">{{ __trans('taxable_portion_hint') }}</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __trans('claim_date') }}</label>
                        <input type="date" name="claim_date" class="form-control" value="{{ old('claim_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __trans('description') }}</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __trans('proof') }}</label>
                        <input type="file" name="proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <button class="btn btn-primary">{{ __trans('save') }}</button>
                <a href="{{ route('backend.indian-payroll.reimbursements.index') }}" class="btn btn-secondary">{{ __trans('cancel') }}</a>
            </form>
        </div></div>
    </div>
</div>
@endsection
