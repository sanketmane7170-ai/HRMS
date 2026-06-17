@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('my_tax_declaration') }} — {{ $financialYear }}</h3></div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="card"><div class="card-body">
            <h5>{{ __trans('tax_regime') }}</h5>
            @if($declaration->isRegimeLocked())
                <p>{{ __trans('your_regime_is_locked') }}: <strong>{{ ucfirst($declaration->regime_choice) }}</strong></p>
            @else
                <form method="POST" action="{{ route('backend.my-indian-payroll.tax-declaration.regime') }}" class="d-flex gap-2">
                    @csrf
                    <select name="regime" class="form-control" style="width:200px;">
                        <option value="new" @selected($declaration->regime_choice === 'new')>{{ __trans('new_regime') }}</option>
                        <option value="old" @selected($declaration->regime_choice === 'old')>{{ __trans('old_regime') }}</option>
                    </select>
                    <button type="submit" class="btn btn-primary">{{ __trans('update_regime') }}</button>
                </form>
            @endif
        </div></div>

        @if($declaration->regime_choice === 'old')
        <div class="card"><div class="card-body">
            <h5>{{ __trans('hra_exemption_details') }}</h5>
            <form method="POST" action="{{ route('backend.my-indian-payroll.tax-declaration.hra.store') }}" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <label>{{ __trans('monthly_rent') }}</label>
                    <input type="number" step="0.01" name="monthly_rent" class="form-control" value="{{ $declaration->hraExemptionInput->monthly_rent ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label>{{ __trans('metro_city') }}</label><br>
                    <input type="checkbox" name="is_metro" value="1" @checked($declaration->hraExemptionInput->is_metro ?? false)>
                </div>
                <div class="col-md-3">
                    <label>{{ __trans('landlord_pan') }}</label>
                    <input type="text" name="landlord_pan" maxlength="10" class="form-control text-uppercase" value="{{ $declaration->hraExemptionInput->landlord_pan ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label>{{ __trans('landlord_name') }}</label>
                    <input type="text" name="landlord_name" class="form-control" value="{{ $declaration->hraExemptionInput->landlord_name ?? '' }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">{{ __trans('save') }}</button>
                </div>
            </form>
        </div></div>

        <div class="card"><div class="card-body">
            <h5>{{ __trans('investment_declarations') }}</h5>
            @foreach ($sections as $code => $section)
                @php($existing = $declaration->investmentDeclarations->firstWhere('section_code', $code))
                <form method="POST" action="{{ route('backend.my-indian-payroll.tax-declaration.investment.store') }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-2">
                    @csrf
                    <input type="hidden" name="section_code" value="{{ $code }}">
                    <div class="col-md-4">
                        <label>{{ $section['label'] }}{{ $section['cap'] ? ' (Cap: '.number_format($section['cap']).')' : '' }}</label>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="declared_amount" class="form-control" value="{{ $existing->declared_amount ?? '' }}" placeholder="{{ __trans('amount') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="file" name="proof" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <span class="badge badge-{{ ($existing->status ?? 'pending') === 'verified' ? 'success' : (($existing->status ?? '') === 'rejected' ? 'danger' : 'warning') }}">{{ $existing->status ?? __trans('not_submitted') }}</span>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-sm btn-outline-primary">{{ __trans('save') }}</button>
                    </div>
                </form>
            @endforeach
        </div></div>
        @endif
    </div>
</div>
@endsection
