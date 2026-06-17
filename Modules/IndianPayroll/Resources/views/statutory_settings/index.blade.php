@extends('layouts.backend')
@section('content')
@php
    use Illuminate\Support\Carbon;
    $pf = $pfSettings->first();
    $esi = $esiSettings->first();
    $grat = $gratuitySettings->first();
    $fmtDate = fn($d) => $d ? Carbon::parse($d)->format('d M Y') : '—';
@endphp
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Compliance Settings</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('indian_payroll') }}</a></li>
                        <li class="breadcrumb-item active">Compliance Settings</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('indianpayroll::statutory_settings.partials._nav')

        <p class="ss-intro">
            Configure the statutory contributions used when payroll is processed. Each block shows what is
            <strong>currently active</strong>. Saving does not overwrite the old values — it adds a new version that
            takes effect from the date you choose, and payroll always uses the latest applicable version.
        </p>

        <div class="row">
            {{-- ───────────────── Provident Fund (EPF) ───────────────── --}}
            <div class="col-lg-4 mb-4">
                <div class="card ss-card"><div class="card-body">
                    <div class="ss-card-head">
                        <div class="ss-ico"><i class="fas fa-piggy-bank"></i></div>
                        <div>
                            <h5>Provident Fund (EPF)</h5>
                            <div class="ss-sub">Employee &amp; employer PF contribution</div>
                        </div>
                    </div>

                    @if($pf)
                    <div class="ss-status is-set">
                        <span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Active</span>
                        <span class="text-muted" style="font-size:.8rem;"> since {{ $fmtDate($pf->effective_from) }}</span>
                        <div class="ss-kv">
                            <div><span class="k">Employee</span><span class="v">{{ $pf->employee_rate }}%</span></div>
                            <div><span class="k">Employer</span><span class="v">{{ $pf->employer_rate }}%</span></div>
                            <div><span class="k">EPS</span><span class="v">{{ $pf->eps_rate }}%</span></div>
                            <div><span class="k">Wage ceiling</span><span class="v">₹{{ number_format($pf->wage_ceiling, 0) }}</span></div>
                            <div><span class="k">Admin charges</span><span class="v">{{ $pf->admin_charges_rate }}%</span></div>
                        </div>
                    </div>
                    @else
                    <div class="ss-status not-set">
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Not configured yet</span>
                        <div class="text-muted mt-1" style="font-size:.8rem;">Add the first version below to start deducting PF.</div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('backend.indian-payroll.statutory-settings.pf.store') }}" class="ss-form">
                        @csrf
                        <div class="ss-form-title">{{ $pf ? 'Update rates' : 'Set up PF' }}</div>
                        <div class="ss-form-help">{{ $pf ? 'Saving adds a new version effective from the date below.' : 'These are the standard EPF defaults — adjust if needed.' }}</div>
                        <div class="form-group"><label>Effective from</label><input type="date" name="effective_from" class="form-control" value="{{ now()->toDateString() }}" required></div>
                        <div class="row">
                            <div class="col-6 form-group"><label>Employee %</label><input type="number" step="0.01" name="employee_rate" class="form-control" value="{{ $pf->employee_rate ?? '12.00' }}" required></div>
                            <div class="col-6 form-group"><label>Employer %</label><input type="number" step="0.01" name="employer_rate" class="form-control" value="{{ $pf->employer_rate ?? '12.00' }}" required></div>
                            <div class="col-6 form-group"><label>EPS %</label><input type="number" step="0.01" name="eps_rate" class="form-control" value="{{ $pf->eps_rate ?? '8.33' }}" required></div>
                            <div class="col-6 form-group"><label>Admin charges %</label><input type="number" step="0.01" name="admin_charges_rate" class="form-control" value="{{ $pf->admin_charges_rate ?? '0.50' }}" required></div>
                            <div class="col-6 form-group"><label>Wage ceiling ₹</label><input type="number" step="0.01" name="wage_ceiling" class="form-control" value="{{ $pf->wage_ceiling ?? '15000.00' }}" required></div>
                            <div class="col-6 form-group"><label>EPS wage ceiling ₹</label><input type="number" step="0.01" name="eps_wage_ceiling" class="form-control" value="{{ $pf->eps_wage_ceiling ?? '15000.00' }}" required></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block w-100"><i class="fas fa-save"></i> {{ $pf ? 'Save new version' : 'Save PF settings' }}</button>
                    </form>

                    @if($pfSettings->count())
                    <details class="ss-history">
                        <summary>Version history ({{ $pfSettings->count() }})</summary>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Effective</th><th>Emp%</th><th>Empr%</th><th>Ceiling</th></tr></thead>
                            <tbody>
                            @foreach($pfSettings as $row)
                                <tr class="{{ $loop->first ? 'ss-current-row' : '' }}">
                                    <td>{{ $fmtDate($row->effective_from) }} @if($loop->first)<span class="badge bg-success text-white">current</span>@endif</td>
                                    <td>{{ $row->employee_rate }}</td><td>{{ $row->employer_rate }}</td><td>{{ number_format($row->wage_ceiling,0) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </details>
                    @endif
                </div></div>
            </div>

            {{-- ───────────────── ESI ───────────────── --}}
            <div class="col-lg-4 mb-4">
                <div class="card ss-card"><div class="card-body">
                    <div class="ss-card-head">
                        <div class="ss-ico"><i class="fas fa-notes-medical"></i></div>
                        <div>
                            <h5>Employee State Insurance (ESI)</h5>
                            <div class="ss-sub">Medical/insurance contribution</div>
                        </div>
                    </div>

                    @if($esi)
                    <div class="ss-status is-set">
                        <span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Active</span>
                        <span class="text-muted" style="font-size:.8rem;"> since {{ $fmtDate($esi->effective_from) }}</span>
                        <div class="ss-kv">
                            <div><span class="k">Employee</span><span class="v">{{ $esi->employee_rate }}%</span></div>
                            <div><span class="k">Employer</span><span class="v">{{ $esi->employer_rate }}%</span></div>
                            <div><span class="k">Wage threshold</span><span class="v">₹{{ number_format($esi->wage_threshold, 0) }}</span></div>
                        </div>
                    </div>
                    @else
                    <div class="ss-status not-set">
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Not configured yet</span>
                        <div class="text-muted mt-1" style="font-size:.8rem;">Add the first version below to enable ESI.</div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('backend.indian-payroll.statutory-settings.esi.store') }}" class="ss-form">
                        @csrf
                        <div class="ss-form-title">{{ $esi ? 'Update rates' : 'Set up ESI' }}</div>
                        <div class="ss-form-help">{{ $esi ? 'Saving adds a new version effective from the date below.' : 'Standard ESI defaults — adjust if needed.' }}</div>
                        <div class="form-group"><label>Effective from</label><input type="date" name="effective_from" class="form-control" value="{{ now()->toDateString() }}" required></div>
                        <div class="row">
                            <div class="col-6 form-group"><label>Employee %</label><input type="number" step="0.01" name="employee_rate" class="form-control" value="{{ $esi->employee_rate ?? '0.75' }}" required></div>
                            <div class="col-6 form-group"><label>Employer %</label><input type="number" step="0.01" name="employer_rate" class="form-control" value="{{ $esi->employer_rate ?? '3.25' }}" required></div>
                            <div class="col-6 form-group"><label>Wage threshold ₹</label><input type="number" step="0.01" name="wage_threshold" class="form-control" value="{{ $esi->wage_threshold ?? '21000.00' }}" required></div>
                            <div class="col-6 form-group"><label>Threshold (disabled) ₹</label><input type="number" step="0.01" name="wage_threshold_disabled" class="form-control" value="{{ $esi->wage_threshold_disabled ?? '25000.00' }}" required></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block w-100"><i class="fas fa-save"></i> {{ $esi ? 'Save new version' : 'Save ESI settings' }}</button>
                    </form>

                    @if($esiSettings->count())
                    <details class="ss-history">
                        <summary>Version history ({{ $esiSettings->count() }})</summary>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Effective</th><th>Emp%</th><th>Empr%</th><th>Threshold</th></tr></thead>
                            <tbody>
                            @foreach($esiSettings as $row)
                                <tr class="{{ $loop->first ? 'ss-current-row' : '' }}">
                                    <td>{{ $fmtDate($row->effective_from) }} @if($loop->first)<span class="badge bg-success text-white">current</span>@endif</td>
                                    <td>{{ $row->employee_rate }}</td><td>{{ $row->employer_rate }}</td><td>{{ number_format($row->wage_threshold,0) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </details>
                    @endif
                </div></div>
            </div>

            {{-- ───────────────── Gratuity ───────────────── --}}
            <div class="col-lg-4 mb-4">
                <div class="card ss-card"><div class="card-body">
                    <div class="ss-card-head">
                        <div class="ss-ico"><i class="fas fa-gift"></i></div>
                        <div>
                            <h5>Gratuity</h5>
                            <div class="ss-sub">End-of-service gratuity formula</div>
                        </div>
                    </div>

                    @if($grat)
                    <div class="ss-status is-set">
                        <span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Active</span>
                        <span class="text-muted" style="font-size:.8rem;"> since {{ $fmtDate($grat->effective_from) }}</span>
                        <div class="ss-kv">
                            <div><span class="k">Exemption ceiling</span><span class="v">₹{{ number_format($grat->exemption_ceiling, 0) }}</span></div>
                            <div><span class="k">Formula</span><span class="v">{{ $grat->days_per_year_first_slab }}/{{ $grat->divisor_days_per_month }} days</span></div>
                            <div><span class="k">Min. years</span><span class="v">{{ $grat->minimum_vesting_years }}</span></div>
                        </div>
                    </div>
                    @else
                    <div class="ss-status not-set">
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Not configured yet</span>
                        <div class="text-muted mt-1" style="font-size:.8rem;">Add the first version below to enable gratuity.</div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('backend.indian-payroll.statutory-settings.gratuity.store') }}" class="ss-form">
                        @csrf
                        <div class="ss-form-title">{{ $grat ? 'Update formula' : 'Set up gratuity' }}</div>
                        <div class="ss-form-help">{{ $grat ? 'Saving adds a new version effective from the date below.' : 'Standard Payment of Gratuity Act defaults.' }}</div>
                        <div class="form-group"><label>Effective from</label><input type="date" name="effective_from" class="form-control" value="{{ now()->toDateString() }}" required></div>
                        <div class="form-group"><label>Exemption ceiling ₹</label><input type="number" step="0.01" name="exemption_ceiling" class="form-control" value="{{ $grat->exemption_ceiling ?? '2000000.00' }}" required></div>
                        <div class="row">
                            <div class="col-6 form-group"><label>Days / year</label><input type="number" name="days_per_year_first_slab" class="form-control" value="{{ $grat->days_per_year_first_slab ?? '15' }}" required></div>
                            <div class="col-6 form-group"><label>Divisor days</label><input type="number" name="divisor_days_per_month" class="form-control" value="{{ $grat->divisor_days_per_month ?? '26' }}" required></div>
                            <div class="col-6 form-group"><label>Min. vesting years</label><input type="number" name="minimum_vesting_years" class="form-control" value="{{ $grat->minimum_vesting_years ?? '5' }}" required></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block w-100"><i class="fas fa-save"></i> {{ $grat ? 'Save new version' : 'Save gratuity settings' }}</button>
                    </form>

                    @if($gratuitySettings->count())
                    <details class="ss-history">
                        <summary>Version history ({{ $gratuitySettings->count() }})</summary>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Effective</th><th>Ceiling</th><th>Formula</th><th>Min yrs</th></tr></thead>
                            <tbody>
                            @foreach($gratuitySettings as $row)
                                <tr class="{{ $loop->first ? 'ss-current-row' : '' }}">
                                    <td>{{ $fmtDate($row->effective_from) }} @if($loop->first)<span class="badge bg-success text-white">current</span>@endif</td>
                                    <td>{{ number_format($row->exemption_ceiling,0) }}</td><td>{{ $row->days_per_year_first_slab }}/{{ $row->divisor_days_per_month }}</td><td>{{ $row->minimum_vesting_years }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </details>
                    @endif
                </div></div>
            </div>
        </div>
    </div>
</div>
@endsection
