@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $declaration->user->name }} — {{ $declaration->financial_year }}</h3>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.forms.form16', [$declaration->user_id, $declaration->financial_year]) }}" class="btn btn-outline-primary">{{ __trans('download_form16') }}</a>
                </div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        @if($declaration->hraExemptionInput)
        <div class="card"><div class="card-body">
            <h5>{{ __trans('hra_details') }}</h5>
            <p>{{ __trans('monthly_rent') }}: {{ number_format($declaration->hraExemptionInput->monthly_rent, 2) }} | {{ __trans('metro') }}: {{ $declaration->hraExemptionInput->is_metro ? __trans('yes') : __trans('no') }} | {{ __trans('landlord_pan') }}: {{ $declaration->hraExemptionInput->landlord_pan ?? '-' }}</p>
        </div></div>
        @endif

        <div class="card card-table">
            <div class="card-body">
                <h5>{{ __trans('investment_declarations') }}</h5>
                <table class="table table-hover">
                    <thead><tr><th>{{ __trans('section') }}</th><th>{{ __trans('declared') }}</th><th>{{ __trans('proof') }}</th><th>{{ __trans('status') }}</th><th>{{ __trans('verify') }}</th></tr></thead>
                    <tbody>
                        @foreach ($declaration->investmentDeclarations as $inv)
                        <tr>
                            <td>{{ config('indianpayroll.investment_sections.'.$inv->section_code.'.label', $inv->section_code) }}</td>
                            <td>{{ number_format($inv->declared_amount, 2) }}</td>
                            <td>
                                @if($inv->proof_path)
                                    <a href="{{ route('backend.indian-payroll.tax-declarations.investment.proof', $inv) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-file"></i> {{ __trans('view_proof') }}
                                    </a>
                                @else
                                    <span class="text-danger">{{ __trans('no_proof') }}</span>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $inv->status === 'verified' ? 'success' : ($inv->status === 'rejected' ? 'danger' : 'warning') }}">{{ $inv->status }}</span></td>
                            <td>
                                @if($inv->status === 'pending')
                                <form method="POST" action="{{ route('backend.indian-payroll.tax-declarations.verify', $inv) }}" class="d-flex gap-1">
                                    @csrf
                                    <input type="number" step="0.01" name="verified_amount" class="form-control form-control-sm" value="{{ $inv->declared_amount }}" style="width:120px;">
                                    <button type="submit" name="status" value="verified" class="btn btn-sm btn-success">{{ __trans('verify') }}</button>
                                    <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger">{{ __trans('reject') }}</button>
                                </form>
                                @else
                                    {{ number_format($inv->verified_amount ?? 0, 2) }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
