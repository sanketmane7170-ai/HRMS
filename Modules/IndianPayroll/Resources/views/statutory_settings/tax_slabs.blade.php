@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Income Tax Slabs</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.statutory-settings.index') }}">Compliance Settings</a></li>
                        <li class="breadcrumb-item active">Income Tax</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('indianpayroll::statutory_settings.partials._nav')

        {{-- Scope bar: which financial year these slabs apply to --}}
        <div class="card mb-4"><div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="gap:12px;">
            <div class="ss-scope">
                <i class="fas fa-calendar-alt text-muted"></i>
                Showing TDS slabs for financial year <strong>{{ $financialYear }}</strong>
            </div>
            <form method="GET" class="d-flex align-items-center" style="gap:8px;">
                <label class="mb-0" style="font-size:.82rem; color:#6b7280;">Change year</label>
                <select name="financial_year" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
                    @foreach ($financialYears as $fy)
                        <option value="{{ $fy }}" @selected($fy == $financialYear)>{{ $fy }}</option>
                    @endforeach
                    <option value="{{ $financialYear }}" @selected(!$financialYears->contains($financialYear))>{{ $financialYear }} ({{ __trans('current') }})</option>
                </select>
            </form>
        </div></div>

        <div class="row">
            @foreach (['new' => ['New Tax Regime', $newRegimeSlabs], 'old' => ['Old Tax Regime', $oldRegimeSlabs]] as $regime => $data)
            <div class="col-lg-6 mb-4">
                <div class="card ss-card"><div class="card-body">
                    <div class="ss-card-head">
                        <div class="ss-ico"><i class="fas fa-layer-group"></i></div>
                        <div><h5>{{ $data[0] }}</h5><div class="ss-sub">Income brackets &amp; tax rate</div></div>
                    </div>

                    <div style="padding:8px 18px 0;">
                    @if($data[1]->count())
                        <table class="table table-sm">
                            <thead><tr><th>From (₹)</th><th>To (₹)</th><th>Rate</th><th class="text-end"></th></tr></thead>
                            <tbody>
                                @foreach ($data[1] as $slab)
                                <tr>
                                    <td>{{ number_format($slab->slab_from, 0) }}</td>
                                    <td>{{ $slab->slab_to ? number_format($slab->slab_to, 0) : 'and above' }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $slab->rate }}%</span></td>
                                    <td class="text-end"><a href="{{ route('backend.indian-payroll.tax-slabs.destroy', $slab) }}" method="DELETE" class="btn btn-sm btn-link text-danger action-button" data-alert="Delete this tax slab?"><i class="fa fa-trash"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="ss-empty"><i class="fas fa-inbox d-block"></i><p>No slabs added for this regime / year yet.</p></div>
                    @endif
                    </div>

                    <div class="ss-form" style="border-top:1px solid #f0f0f3;">
                        <div class="ss-add-title mb-2">Add a slab</div>
                        @include('indianpayroll::statutory_settings.partials.tax_slab_form', ['regime' => $regime])
                    </div>
                </div></div>
            </div>
            @endforeach
        </div>

        <div class="card ss-card"><div class="card-body">
            <div class="ss-card-head">
                <div class="ss-ico"><i class="fas fa-percent"></i></div>
                <div><h5>Surcharge Slabs</h5><div class="ss-sub">Additional surcharge on high incomes</div></div>
            </div>
            <div style="padding:8px 18px 18px;">
            @if($surchargeSlabs->count())
                <table class="table table-sm mb-2">
                    <thead><tr><th>Regime</th><th>From (₹)</th><th>To (₹)</th><th>Surcharge</th></tr></thead>
                    <tbody>
                        @foreach ($surchargeSlabs as $slab)
                        <tr>
                            <td>{{ ucfirst($slab->regime) }}</td>
                            <td>{{ number_format($slab->income_from, 0) }}</td>
                            <td>{{ $slab->income_to ? number_format($slab->income_to, 0) : 'and above' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $slab->surcharge_rate }}%</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="ss-empty"><i class="fas fa-inbox d-block"></i><p>No surcharge slabs configured for this year.</p></div>
            @endif
                <p class="text-muted mb-0" style="font-size:.78rem;"><i class="fas fa-info-circle"></i> Surcharge slabs are seeded with statutory defaults and not edited here.</p>
            </div>
        </div></div>
    </div>
</div>
@endsection
