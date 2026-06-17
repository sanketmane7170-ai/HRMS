@extends('layouts.backend')
@section('content')
@php($selectedState = $states->firstWhere('id', $stateId))
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Labour Welfare Fund</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.statutory-settings.index') }}">Compliance Settings</a></li>
                        <li class="breadcrumb-item active">Labour Welfare Fund</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('indianpayroll::statutory_settings.partials._nav')

        <p class="ss-intro">Labour Welfare Fund (LWF) is a state-specific contribution, usually deducted half-yearly.
            Pick a state and define the employee &amp; employer contribution. Payroll uses the latest applicable rule.</p>

        {{-- Scope bar: state selector --}}
        <div class="card mb-4"><div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="gap:12px;">
            <div class="ss-scope">
                <i class="fas fa-map-marker-alt text-muted"></i>
                Showing LWF rules for <strong>{{ $selectedState->name ?? '—' }}</strong>
            </div>
            <form method="GET" class="d-flex align-items-center" style="gap:8px;">
                <label class="mb-0" style="font-size:.82rem; color:#6b7280;">State</label>
                <select name="state_id" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}" @selected($state->id == $stateId)>{{ $state->name }}</option>
                    @endforeach
                </select>
            </form>
        </div></div>

        <div class="card ss-card"><div class="card-body">
            <div class="ss-card-head">
                <div class="ss-ico"><i class="fas fa-hand-holding-heart"></i></div>
                <div><h5>LWF rules for {{ $selectedState->name ?? '—' }}</h5><div class="ss-sub">Employee &amp; employer welfare-fund contribution</div></div>
            </div>

            <div style="padding:8px 18px 0;">
            @if($rules->count())
                <table class="table table-sm">
                    <thead><tr><th>Frequency</th><th>Employee (₹)</th><th>Employer (₹)</th><th>Wage ceiling (₹)</th><th>Effective</th><th class="text-end"></th></tr></thead>
                    <tbody>
                        @foreach ($rules as $rule)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $rule->frequency)) }}</td>
                            <td>{{ number_format($rule->employee_contribution, 2) }}</td>
                            <td>{{ number_format($rule->employer_contribution, 2) }}</td>
                            <td>{{ $rule->wage_ceiling ? number_format($rule->wage_ceiling, 2) : 'No ceiling' }}</td>
                            <td>{{ $rule->effective_from->format('d-M-Y') }}</td>
                            <td class="text-end"><a href="{{ route('backend.indian-payroll.lwf-rules.destroy', $rule) }}" method="DELETE" class="btn btn-sm btn-link text-danger action-button" data-alert="Delete this LWF rule?"><i class="fa fa-trash"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="ss-empty"><i class="fas fa-inbox d-block"></i><p>No LWF rules configured for {{ $selectedState->name ?? 'this state' }} yet. Add the first rule below.</p></div>
            @endif
            </div>

            <div class="ss-form" style="border-top:1px solid #f0f0f3;">
                <div class="ss-add-title mb-2">Add a rule</div>
                <form method="POST" action="{{ route('backend.indian-payroll.lwf-rules.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <input type="hidden" name="state_id" value="{{ $stateId }}">
                    <div class="col-md-2">
                        <label style="font-size:.74rem;color:#6b7280;">Frequency</label>
                        <select name="frequency" class="form-control form-control-sm">
                            <option value="half_yearly">Half-yearly</option>
                            <option value="monthly">Monthly</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                    <div class="col-md-2"><label style="font-size:.74rem;color:#6b7280;">Employee (₹)</label><input type="number" step="0.01" name="employee_contribution" class="form-control form-control-sm" placeholder="0" required></div>
                    <div class="col-md-2"><label style="font-size:.74rem;color:#6b7280;">Employer (₹)</label><input type="number" step="0.01" name="employer_contribution" class="form-control form-control-sm" placeholder="0" required></div>
                    <div class="col-md-3"><label style="font-size:.74rem;color:#6b7280;">Wage ceiling (₹) — optional</label><input type="number" step="0.01" name="wage_ceiling" class="form-control form-control-sm" placeholder="Leave blank for none"></div>
                    <div class="col-md-2"><label style="font-size:.74rem;color:#6b7280;">Effective from</label><input type="date" name="effective_from" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required></div>
                    <div class="col-md-1"><button type="submit" class="btn btn-sm btn-primary w-100" title="Add rule"><i class="fas fa-plus"></i></button></div>
                </form>
            </div>
        </div></div>
    </div>
</div>
@endsection
