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

        {{-- Computed annual tax projection — the same figures the Form 16 PDF
             shows, surfaced here so the page isn't empty when the employee has
             made no investment/HRA declarations. --}}
        @if($taxSummary)
        <div class="card"><div class="card-body">
            <h5>{{ __trans('tax_computation_summary') }} <span class="badge badge-primary">{{ ucfirst($taxSummary->regime) }} {{ __trans('regime') }}</span></h5>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tr><td>{{ __trans('gross_taxable_income') }}</td><td class="text-end">{{ number_format($taxSummary->grossTaxableIncome, 2) }}</td></tr>
                        <tr><td>{{ __trans('exemptions_and_deductions') }}</td><td class="text-end">{{ number_format($taxSummary->totalExemptionsAndDeductions, 2) }}</td></tr>
                        <tr><td><strong>{{ __trans('net_taxable_income') }}</strong></td><td class="text-end"><strong>{{ number_format($taxSummary->netTaxableIncome, 2) }}</strong></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tr><td>{{ __trans('tax_before_rebate') }}</td><td class="text-end">{{ number_format($taxSummary->taxBeforeRebate, 2) }}</td></tr>
                        <tr><td>{{ __trans('rebate_87a') }}</td><td class="text-end">-{{ number_format($taxSummary->rebate87A, 2) }}</td></tr>
                        <tr><td>{{ __trans('surcharge') }}</td><td class="text-end">{{ number_format($taxSummary->surcharge, 2) }}</td></tr>
                        <tr><td>{{ __trans('health_and_education_cess') }}</td><td class="text-end">{{ number_format($taxSummary->cess, 2) }}</td></tr>
                        <tr><td><strong>{{ __trans('total_tax_liability') }}</strong></td><td class="text-end"><strong>{{ number_format($taxSummary->annualTaxLiability, 2) }}</strong></td></tr>
                    </table>
                </div>
            </div>
            @if($quarterlyTds)
            <h6 class="mt-3">{{ __trans('quarterly_tds_deposited') }}</h6>
            <table class="table table-sm table-bordered mb-0" style="max-width:640px;">
                <thead><tr><th>Q1 (Apr-Jun)</th><th>Q2 (Jul-Sep)</th><th>Q3 (Oct-Dec)</th><th>Q4 (Jan-Mar)</th><th>{{ __trans('total') }}</th></tr></thead>
                <tr>
                    <td class="text-end">{{ number_format($quarterlyTds[1], 2) }}</td>
                    <td class="text-end">{{ number_format($quarterlyTds[2], 2) }}</td>
                    <td class="text-end">{{ number_format($quarterlyTds[3], 2) }}</td>
                    <td class="text-end">{{ number_format($quarterlyTds[4], 2) }}</td>
                    <td class="text-end"><strong>{{ number_format(array_sum($quarterlyTds), 2) }}</strong></td>
                </tr>
            </table>
            @endif
        </div></div>
        @else
        <div class="alert alert-warning">{{ __trans('tax_summary_unavailable_no_salary_structure') }}</div>
        @endif

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
