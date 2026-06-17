@extends('layouts.backend')
@section('content')
@php($selectedState = $states->firstWhere('id', $stateId))
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Professional Tax</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.statutory-settings.index') }}">Compliance Settings</a></li>
                        <li class="breadcrumb-item active">Professional Tax</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('indianpayroll::statutory_settings.partials._nav')

        <p class="ss-intro">Professional Tax (PT) is levied by individual states. Pick a state, then define the
            monthly tax for each salary band. Payroll applies the band matching each employee's salary.</p>

        {{-- Scope bar: state selector --}}
        <div class="card mb-4"><div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="gap:12px;">
            <div class="ss-scope">
                <i class="fas fa-map-marker-alt text-muted"></i>
                Showing PT slabs for <strong>{{ $selectedState->name ?? '—' }}</strong>
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
                <div class="ss-ico"><i class="fas fa-landmark"></i></div>
                <div><h5>Salary bands for {{ $selectedState->name ?? '—' }}</h5><div class="ss-sub">Monthly professional tax per band</div></div>
            </div>

            <div style="padding:8px 18px 0;">
            @if($slabs->count())
                <table class="table table-sm">
                    <thead><tr><th>Applies to</th><th>Salary from (₹)</th><th>Salary to (₹)</th><th>Monthly PT (₹)</th><th>Effective</th><th class="text-end"></th></tr></thead>
                    <tbody>
                        @foreach ($slabs as $slab)
                        <tr>
                            <td>{{ ucfirst($slab->gender) }}</td>
                            <td>{{ number_format($slab->salary_from, 0) }}</td>
                            <td>{{ $slab->salary_to ? number_format($slab->salary_to, 0) : 'and above' }}</td>
                            <td><strong>{{ number_format($slab->monthly_tax, 2) }}</strong></td>
                            <td>{{ $slab->effective_from->format('d-M-Y') }}</td>
                            <td class="text-end"><a href="{{ route('backend.indian-payroll.professional-tax.destroy', $slab) }}" method="DELETE" class="btn btn-sm btn-link text-danger action-button" data-alert="Delete this PT slab?"><i class="fa fa-trash"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="ss-empty"><i class="fas fa-inbox d-block"></i><p>No PT slabs configured for {{ $selectedState->name ?? 'this state' }} yet. Add the first band below.</p></div>
            @endif
            </div>

            <div class="ss-form" style="border-top:1px solid #f0f0f3;">
                <div class="ss-add-title mb-2">Add a salary band</div>
                <form method="POST" action="{{ route('backend.indian-payroll.professional-tax.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <input type="hidden" name="state_id" value="{{ $stateId }}">
                    <input type="hidden" name="frequency" value="monthly">
                    <div class="col-md-2">
                        <label style="font-size:.74rem;color:#6b7280;">Applies to</label>
                        <select name="gender" class="form-control form-control-sm"><option value="all">All</option><option value="male">Male</option><option value="female">Female</option></select>
                    </div>
                    <div class="col-md-2"><label style="font-size:.74rem;color:#6b7280;">Salary from (₹)</label><input type="number" step="0.01" name="salary_from" class="form-control form-control-sm" placeholder="0" required></div>
                    <div class="col-md-2"><label style="font-size:.74rem;color:#6b7280;">Salary to — blank = above</label><input type="number" step="0.01" name="salary_to" class="form-control form-control-sm" placeholder="e.g. 15000"></div>
                    <div class="col-md-2"><label style="font-size:.74rem;color:#6b7280;">Monthly PT (₹)</label><input type="number" step="0.01" name="monthly_tax" class="form-control form-control-sm" placeholder="200" required></div>
                    <div class="col-md-2"><label style="font-size:.74rem;color:#6b7280;">Effective from</label><input type="date" name="effective_from" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-plus"></i> Add band</button></div>
                </form>
            </div>
        </div></div>
    </div>
</div>
@endsection
