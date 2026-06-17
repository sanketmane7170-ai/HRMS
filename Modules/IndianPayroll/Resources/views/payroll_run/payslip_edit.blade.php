@extends('layouts.backend')

@push('css')
<style>
.edit-section { margin-bottom: 28px; }
.edit-section-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748B;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #E2E8F0;
}
.component-row td { vertical-align: middle; }
.amount-input {
    text-align: right;
    font-variant-numeric: tabular-nums;
    max-width: 160px;
}
.manual-tag {
    font-size: 0.7rem;
    background: #EEF2FF;
    color: #4F46E5;
    border-radius: 4px;
    padding: 2px 7px;
    font-weight: 600;
}
.net-pay-summary {
    background: #F8FAFC;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    padding: 20px 24px;
}
.net-pay-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 0.9rem;
    color: #374151;
}
.net-pay-row.total {
    border-top: 1.5px solid #E2E8F0;
    margin-top: 8px;
    padding-top: 12px;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0F172A;
}
.net-pay-row .label { color: #6B7280; }
.net-pay-row .value { font-variant-numeric: tabular-nums; font-weight: 600; }
.net-pay-row.total .value { color: #16A34A; }
#add-deduction-rows .deduction-add-row td { padding-top: 8px; }
.sidebar-sticky-wrap {
    position: sticky;
    top: 80px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
}
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        {{-- Header --}}
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        Edit Payslip &mdash; {{ $payslip->user->name }}
                        <span class="text-muted" style="font-weight:400; font-size:1rem;">
                            {{ \Carbon\Carbon::create($payslip->run->year, $payslip->run->month)->format('F Y') }}
                        </span>
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.payroll-runs.index') }}">Payroll Runs</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.payroll-runs.show', $payslip->run) }}">{{ \Carbon\Carbon::create($payslip->run->year, $payslip->run->month)->format('F Y') }}</a></li>
                        <li class="breadcrumb-item active">Edit Payslip</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.payslips.show', $payslip) }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Payslip
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('backend.indian-payroll.payslips.update', $payslip) }}" id="payslip-edit-form">
            @csrf
            @method('PUT')

            <div class="row">

                {{-- ====== LEFT: Edit Form ====== --}}
                <div class="col-lg-8">

                    {{-- Attendance / Days --}}
                    <div class="card edit-section">
                        <div class="card-body">
                            <p class="edit-section-title"><i class="fa fa-calendar-check mr-1"></i> Attendance Adjustment</p>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label">Paid Days</label>
                                        <input type="number" name="paid_days" class="form-control amount-input"
                                               value="{{ old('paid_days', $payslip->paid_days) }}"
                                               min="0" max="{{ $payslip->days_in_period }}" step="0.5"
                                               data-role="paid-days">
                                        <small class="text-muted">Period: {{ $payslip->days_in_period }} days</small>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label">Loss of Pay Days</label>
                                        <input type="number" name="loss_of_pay_days" class="form-control amount-input"
                                               value="{{ old('loss_of_pay_days', $payslip->loss_of_pay_days) }}"
                                               min="0" max="{{ $payslip->days_in_period }}" step="0.5"
                                               data-role="lop-days">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Earnings --}}
                    @php $earnings = $payslip->components->where('type', 'earning')->sortBy('id'); @endphp
                    <div class="card edit-section">
                        <div class="card-body">
                            <p class="edit-section-title"><i class="fa fa-arrow-up mr-1 text-success"></i> Earnings</p>
                            <table class="table table-sm mb-0" id="earnings-table">
                                <thead>
                                    <tr>
                                        <th style="width:50%">Component</th>
                                        <th style="width:35%" class="text-right">Amount (₹)</th>
                                        <th style="width:15%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($earnings as $comp)
                                    <tr class="component-row">
                                        <td>
                                            <input type="hidden" name="components[{{ $loop->index }}][id]" value="{{ $comp->id }}">
                                            @if($comp->is_manual)
                                                <span class="manual-tag">manual</span>
                                                {{ $comp->label ?? optional($comp->component)->name ?? 'Manual Earning' }}
                                            @else
                                                {{ optional($comp->component)->name ?? $comp->label ?? 'Unknown' }}
                                                @if(optional($comp->component)->code) <small class="text-muted">({{ $comp->component->code }})</small> @endif
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" min="0"
                                                   name="components[{{ $loop->index }}][amount]"
                                                   value="{{ old("components.{$loop->index}.amount", $comp->amount) }}"
                                                   class="form-control form-control-sm amount-input d-inline-block earning-amount"
                                                   data-original="{{ $comp->amount }}">
                                        </td>
                                        <td class="text-right">
                                            @if($comp->is_manual)
                                            <form method="POST"
                                                  action="{{ route('backend.indian-payroll.payslips.deductions.destroy', [$payslip, $comp]) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Remove this earning?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>

                                {{-- Add new earning/bonus rows --}}
                                <tbody id="add-earning-rows">
                                    {{-- JS-injected rows appear here --}}
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="pt-3">
                                            <button type="button" class="btn btn-sm btn-outline-success" id="add-earning-btn">
                                                <i class="fa fa-plus"></i> Add Earning / Bonus
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Deductions --}}
                    @php
                        $deductions = $payslip->components->where('type', 'deduction')->sortBy('id');
                        $earningsOffset = $earnings->count();
                    @endphp
                    <div class="card edit-section">
                        <div class="card-body">
                            <p class="edit-section-title"><i class="fa fa-arrow-down mr-1 text-danger"></i> Deductions</p>
                            <table class="table table-sm mb-0" id="deductions-table">
                                <thead>
                                    <tr>
                                        <th style="width:50%">Component</th>
                                        <th style="width:35%" class="text-right">Amount (₹)</th>
                                        <th style="width:15%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deductions as $i => $comp)
                                    <tr class="component-row">
                                        <td>
                                            <input type="hidden" name="components[{{ $earningsOffset + $i }}][id]" value="{{ $comp->id }}">
                                            @if($comp->is_manual)
                                                <span class="manual-tag">manual</span>
                                                {{ $comp->label ?? optional($comp->component)->name ?? 'Manual Deduction' }}
                                            @else
                                                {{ optional($comp->component)->name ?? $comp->label ?? 'Unknown' }}
                                                @if($comp->component?->code) <small class="text-muted">({{ $comp->component->code }})</small> @endif
                                                @if($comp->component?->is_statutory) <small class="badge badge-secondary">statutory</small> @endif
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" min="0"
                                                   name="components[{{ $earningsOffset + $i }}][amount]"
                                                   value="{{ old("components.".($earningsOffset+$i).".amount", $comp->amount) }}"
                                                   class="form-control form-control-sm amount-input d-inline-block deduction-amount"
                                                   data-original="{{ $comp->amount }}">
                                        </td>
                                        <td class="text-right">
                                            @if($comp->is_manual)
                                            <form method="POST"
                                                  action="{{ route('backend.indian-payroll.payslips.deductions.destroy', [$payslip, $comp]) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Remove this deduction?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>

                                {{-- Add new deduction rows --}}
                                <tbody id="add-deduction-rows">
                                    {{-- JS-injected rows appear here --}}
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="pt-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-deduction-btn">
                                                <i class="fa fa-plus"></i> Add Deduction
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- ====== RIGHT: Live Summary ======
                     All sidebar content lives inside ONE sticky wrapper so the cards
                     scroll together as a single unit — independent sticky siblings
                     would slide past each other and visually overlap on scroll. --}}
                <div class="col-lg-4">
                    <div class="sidebar-sticky-wrap">

                        <div class="card">
                            <div class="card-body">
                                <p class="edit-section-title"><i class="fa fa-calculator mr-1"></i> Live Summary</p>

                                <div class="net-pay-summary">
                                    <div class="net-pay-row">
                                        <span class="label">Gross Earnings</span>
                                        <span class="value text-success" id="summary-gross">₹ {{ number_format($payslip->gross_earnings, 2) }}</span>
                                    </div>
                                    <div class="net-pay-row">
                                        <span class="label">Statutory Deductions</span>
                                        <span class="value text-danger" id="summary-statutory">₹ {{ number_format($payslip->total_statutory_deductions, 2) }}</span>
                                    </div>
                                    <div class="net-pay-row">
                                        <span class="label">Other Deductions</span>
                                        <span class="value text-danger" id="summary-other">₹ {{ number_format($payslip->total_other_deductions, 2) }}</span>
                                    </div>
                                    <div class="net-pay-row total">
                                        <span>Net Pay</span>
                                        <span id="summary-net">₹ {{ number_format($payslip->net_pay, 2) }}</span>
                                    </div>
                                </div>

                                <p class="text-muted small mt-3 mb-0">
                                    <i class="fa fa-info-circle"></i>
                                    Gross, statutory/other split, and net pay are recalculated automatically when you save.
                                    The preview above updates as you type.
                                </p>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-save"></i> Save &amp; Recalculate
                                </button>
                                <a href="{{ route('backend.indian-payroll.payslips.show', $payslip) }}" class="btn btn-outline-secondary btn-block mt-2">
                                    Cancel
                                </a>
                            </div>
                        </div>

                        {{-- Employer contributions (read-only) --}}
                        @php $empContribs = $payslip->components->where('type', 'employer_contribution'); @endphp
                        @if($empContribs->isNotEmpty())
                        <div class="card mt-3">
                            <div class="card-body">
                                <p class="edit-section-title"><i class="fa fa-building mr-1"></i> Employer Contributions (read-only)</p>
                                @foreach($empContribs as $c)
                                <div class="d-flex justify-content-between py-1 small">
                                    <span>{{ optional($c->component)->name ?? 'Unknown' }}</span>
                                    <span class="font-weight-600">₹ {{ number_format($c->amount, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- Available components for JS --}}
@php
    $availableComponentsForJs = $availableComponents->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'code' => $c->code,
        'is_statutory' => $c->is_statutory,
    ]);
    $availableEarningComponentsForJs = $availableEarningComponents->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'code' => $c->code,
    ]);
@endphp
<script>
const AVAILABLE_COMPONENTS = @json($availableComponentsForJs);
const AVAILABLE_EARNING_COMPONENTS = @json($availableEarningComponentsForJs);

// ── Live net pay preview ──────────────────────────────────────────
function recalculatePreview() {
    let gross = 0;
    document.querySelectorAll('.earning-amount').forEach(el => {
        gross += parseFloat(el.value) || 0;
    });

    let totalDed = 0;
    document.querySelectorAll('.deduction-amount').forEach(el => {
        totalDed += parseFloat(el.value) || 0;
    });

    const net = Math.max(0, gross - totalDed);

    const fmt = v => '₹ ' + v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('summary-gross').textContent = fmt(gross);
    // Note: statutory vs other split shown accurately only after server recalculates.
    const otherEl = document.getElementById('summary-other');
    // We know new manual deductions add to "other", but for the preview we just update total.
    document.getElementById('summary-net').textContent = fmt(net);
}

document.addEventListener('input', function(e) {
    if (e.target.matches('.earning-amount, .deduction-amount')) recalculatePreview();
});

// ── Add deduction row ─────────────────────────────────────────────
let newRowIndex = 0;

document.getElementById('add-deduction-btn').addEventListener('click', function() {
    const idx = newRowIndex++;
    const tbody = document.getElementById('add-deduction-rows');

    const hasComponents = AVAILABLE_COMPONENTS.length > 0;

    const selectOptions = AVAILABLE_COMPONENTS.map(c =>
        `<option value="${c.id}">${c.name}${c.code ? ' (' + c.code + ')' : ''}${c.is_statutory ? ' [statutory]' : ''}</option>`
    ).join('');

    const row = document.createElement('tr');
    row.className = 'deduction-add-row';
    row.innerHTML = `
        <td>
            <div class="mb-1">
                <select name="new_deductions[${idx}][salary_component_id]"
                        class="form-control form-control-sm"
                        style="max-width:220px;">
                    <option value="">— Custom / free-text —</option>
                    ${selectOptions}
                </select>
            </div>
            <input type="text" name="new_deductions[${idx}][label]"
                   class="form-control form-control-sm"
                   placeholder="Custom label (if not in list above)"
                   style="max-width:220px;">
        </td>
        <td class="text-right">
            <input type="number" step="0.01" min="0"
                   name="new_deductions[${idx}][amount]"
                   class="form-control form-control-sm amount-input d-inline-block deduction-amount"
                   placeholder="0.00">
        </td>
        <td class="text-right">
            <button type="button" class="btn btn-sm btn-outline-danger remove-new-row">
                <i class="fa fa-times"></i>
            </button>
        </td>`;

    tbody.appendChild(row);

    row.querySelector('.remove-new-row').addEventListener('click', function() {
        row.remove();
        recalculatePreview();
    });
});

// ── Add earning/bonus row ─────────────────────────────────────────
let newEarningRowIndex = 0;

document.getElementById('add-earning-btn').addEventListener('click', function() {
    const idx = newEarningRowIndex++;
    const tbody = document.getElementById('add-earning-rows');

    const selectOptions = AVAILABLE_EARNING_COMPONENTS.map(c =>
        `<option value="${c.id}">${c.name}${c.code ? ' (' + c.code + ')' : ''}</option>`
    ).join('');

    const row = document.createElement('tr');
    row.className = 'earning-add-row';
    row.innerHTML = `
        <td>
            <div class="mb-1">
                <select name="new_earnings[${idx}][salary_component_id]"
                        class="form-control form-control-sm"
                        style="max-width:220px;">
                    <option value="">— Custom / free-text —</option>
                    ${selectOptions}
                </select>
            </div>
            <input type="text" name="new_earnings[${idx}][label]"
                   class="form-control form-control-sm"
                   placeholder="Custom label (if not in list above)"
                   style="max-width:220px;">
        </td>
        <td class="text-right">
            <input type="number" step="0.01" min="0"
                   name="new_earnings[${idx}][amount]"
                   class="form-control form-control-sm amount-input d-inline-block earning-amount"
                   placeholder="0.00">
        </td>
        <td class="text-right">
            <button type="button" class="btn btn-sm btn-outline-danger remove-new-row">
                <i class="fa fa-times"></i>
            </button>
        </td>`;

    tbody.appendChild(row);

    row.querySelector('.remove-new-row').addEventListener('click', function() {
        row.remove();
        recalculatePreview();
    });
});
</script>
@endsection
