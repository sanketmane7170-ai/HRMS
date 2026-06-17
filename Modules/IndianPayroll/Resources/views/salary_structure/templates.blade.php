@extends('layouts.backend')
@section('content')
@php
    $earningComponents = \Modules\IndianPayroll\Entities\SalaryComponent::where('type', 'earning')
        ->where('is_active', true)->orderBy('display_order')->get();

    // calculation_type => [label, pill-class]
    $calcMeta = [
        'flat'                 => ['Flat amount', 'c-flat'],
        'percentage_of_basic'  => ['% of Basic', 'c-basic'],
        'percentage_of_ctc'    => ['% of CTC', 'c-ctc'],
        'remainder_of_ctc'     => ['Balance of CTC', 'c-bal'],
    ];
    $calcVal = function ($tc) {
        if ($tc->calculation_type === 'flat')  return '₹' . number_format($tc->value, 2);
        if ($tc->calculation_type === 'remainder_of_ctc') return 'Auto (remaining)';
        return rtrim(rtrim(number_format($tc->value, 2), '0'), '.') . '%';
    };
@endphp

@push('css')
<style>
    .ct-intro { color:#6b7280; font-size:.9rem; margin:-2px 0 20px; max-width:760px; }

    .ct-card { border:1px solid #e7e8ee; border-radius:14px; margin-bottom:22px; overflow:hidden; background:#fff; }
    .ct-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; padding:18px 22px; border-bottom:1px solid #f1f1f4; }
    .ct-head .tt { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .ct-head h5 { margin:0; font-size:1.08rem; font-weight:700; color:#111827; }
    .ct-head .desc { color:#9097a1; font-size:.84rem; margin-top:5px; }
    .ct-head .badge-pill { font-size:.7rem; font-weight:700; padding:4px 10px; border-radius:20px; }
    .ct-active { background:#ecfdf5; color:#047857; }
    .ct-inactive { background:#f3f4f6; color:#6b7280; }
    .ct-head .acts { display:flex; gap:8px; flex-shrink:0; }
    .ct-head .acts .btn { padding:6px 11px; border-radius:8px; }

    .ct-table { width:100%; margin:0; }
    .ct-table th { font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#9ca3af; font-weight:600;
        padding:12px 22px; border-bottom:1px solid #f1f1f4; background:#fafafb; }
    .ct-table td { padding:13px 22px; border-bottom:1px solid #f5f5f7; vertical-align:middle; font-size:.9rem; }
    .ct-table tr:last-child td { border-bottom:1px solid #f1f1f4; }
    .ct-comp-name { font-weight:600; color:#111827; }
    .ct-calc-pill { font-size:.72rem; font-weight:600; padding:3px 10px; border-radius:20px; white-space:nowrap; }
    .c-flat{background:#eff6ff;color:#1d4ed8;} .c-basic{background:#fffbeb;color:#b45309;}
    .c-ctc{background:#eef2ff;color:#4f46e5;} .c-bal{background:#f0fdf4;color:#15803d;}
    .ct-value { font-weight:700; color:#111827; }
    .ct-remove { color:#d1d5db; border:0; background:none; transition:.15s; }
    .ct-remove:hover { color:#dc2626; }

    .ct-empty-row { text-align:center; color:#9ca3af; padding:26px !important; font-size:.88rem; }

    .ct-add { padding:16px 22px; background:#fafbff; border-top:1px solid #f1f1f4; }
    .ct-add-title { font-size:.74rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; margin-bottom:10px; }
    .ct-add label { font-size:.74rem; color:#6b7280; margin-bottom:3px; }

    .ct-empty { text-align:center; padding:50px 20px; border:1px dashed #d1d5db; border-radius:14px; color:#9ca3af; background:#fff; }
    .ct-empty i { font-size:2.2rem; margin-bottom:12px; opacity:.5; }
    .ct-empty h6 { color:#374151; font-weight:600; margin-bottom:4px; }

    .ct-table-wrap { width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
    @media (max-width:575.98px) {
        .ct-head, .ct-add { padding-left:16px; padding-right:16px; }
        .ct-table th, .ct-table td { padding-left:16px; padding-right:16px; white-space:nowrap; }
        .ct-head h5 { font-size:1rem; }
    }
</style>
@endpush

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('ctc_structure_templates') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('indian_payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('ctc_structure_templates') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.salary-templates.create') }}" class="btn btn-primary edit-button">
                        <i class="fas fa-plus"></i> {{ __trans('add_template') }}
                    </a>
                </div>
            </div>
        </div>

        <p class="ct-intro">
            A CTC template defines how an employee's annual CTC is split into salary components (Basic, HRA, allowances…).
            Assign a template to an employee to auto-generate their salary structure. Add a <strong>Balance of CTC</strong>
            component so any leftover amount is absorbed automatically.
        </p>

        @forelse ($templates as $template)
        <div class="ct-card">
            <div class="ct-head">
                <div>
                    <div class="tt">
                        <h5>{{ $template->name }}</h5>
                        <span class="badge-pill {{ $template->is_active ? 'ct-active' : 'ct-inactive' }}">
                            <i class="fas {{ $template->is_active ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                            {{ $template->is_active ? __trans('active') : __trans('inactive') }}
                        </span>
                    </div>
                    @if($template->description)<div class="desc">{{ $template->description }}</div>@endif
                </div>
                <div class="acts">
                    <a href="{{ route('backend.indian-payroll.salary-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary edit-button" title="Edit template"><i class="fa fa-pen"></i></a>
                    <a href="{{ route('backend.indian-payroll.salary-templates.destroy', $template) }}" method="DELETE" class="btn btn-sm btn-outline-danger action-button" data-alert="{{ __trans('delete_template_confirm') }}" title="Delete template"><i class="fa fa-trash"></i></a>
                </div>
            </div>

            <div class="ct-table-wrap">
            <table class="ct-table">
                <thead><tr>
                    <th>{{ __trans('component') }}</th>
                    <th>{{ __trans('calculation') }}</th>
                    <th>{{ __trans('value') }}</th>
                    <th class="text-end" style="width:60px;"></th>
                </tr></thead>
                <tbody>
                    @forelse ($template->components as $tc)
                    @php($meta = $calcMeta[$tc->calculation_type] ?? [str_replace('_',' ',$tc->calculation_type),'c-ctc'])
                    <tr>
                        <td class="ct-comp-name">{{ $tc->component->name }}</td>
                        <td><span class="ct-calc-pill {{ $meta[1] }}">{{ $meta[0] }}</span></td>
                        <td class="ct-value">{{ $calcVal($tc) }}</td>
                        <td class="text-end">
                            <a href="{{ route('backend.indian-payroll.salary-templates.components.remove', [$template, $tc]) }}" method="DELETE" class="ct-remove action-button" data-alert="{{ __trans('remove_component_confirm') }}" title="Remove component"><i class="fa fa-times-circle"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="ct-empty-row"><i class="fas fa-layer-group"></i> No components yet — add the first one below.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            <div class="ct-add">
                <div class="ct-add-title"><i class="fas fa-plus-circle"></i> Add component</div>
                <form method="POST" action="{{ route('backend.indian-payroll.salary-templates.components.add', $template) }}" class="row g-2 align-items-end ct-add-form">
                    @csrf
                    <div class="col-12 col-md-4">
                        <label>Component</label>
                        <select name="salary_component_id" class="form-control" required>
                            <option value="">{{ __trans('select_component') }}</option>
                            @foreach ($earningComponents as $sc)
                                <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-7 col-md-3">
                        <label>Calculation</label>
                        <select name="calculation_type" class="form-control ct-calc-select" required>
                            <option value="flat">{{ __trans('flat_amount') }}</option>
                            <option value="percentage_of_basic">{{ __trans('percentage_of_basic') }}</option>
                            <option value="percentage_of_ctc">{{ __trans('percentage_of_ctc') }}</option>
                            <option value="remainder_of_ctc">{{ __trans('remainder_of_ctc') }}</option>
                        </select>
                    </div>
                    <div class="col-5 col-md-3 ct-value-wrap">
                        <label class="ct-value-label">Amount (₹)</label>
                        <input type="number" step="0.01" name="value" class="form-control ct-value-input" placeholder="0.00">
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> {{ __trans('add') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="ct-empty">
            <i class="fas fa-sitemap d-block"></i>
            <h6>No CTC templates yet</h6>
            <p class="mb-3">Create a template to define how CTC is broken into salary components.</p>
            <a href="{{ route('backend.indian-payroll.salary-templates.create') }}" class="btn btn-primary edit-button"><i class="fas fa-plus"></i> {{ __trans('add_template') }}</a>
        </div>
        @endforelse
    </div>
</div>
<div id="editModal" class="modal"></div>

@push('scripts')
<script>
(function () {
    // For each "add component" form, adapt the value field to the calculation type.
    function syncValueField(form) {
        var calc = form.querySelector('.ct-calc-select');
        var wrap = form.querySelector('.ct-value-wrap');
        var input = form.querySelector('.ct-value-input');
        var label = form.querySelector('.ct-value-label');
        if (!calc || !wrap || !input) return;
        var v = calc.value;
        if (v === 'remainder_of_ctc') {
            wrap.style.display = 'none';
            input.removeAttribute('required'); input.value = '';
        } else {
            wrap.style.display = '';
            input.setAttribute('required', 'required');
            if (v === 'flat') { label.textContent = 'Amount (₹)'; input.placeholder = '0.00'; }
            else { label.textContent = 'Percentage (%)'; input.placeholder = 'e.g. 40'; }
        }
    }
    document.querySelectorAll('.ct-add-form').forEach(function (form) {
        syncValueField(form);
        form.querySelector('.ct-calc-select').addEventListener('change', function () { syncValueField(form); });
    });
})();
</script>
@endpush
@endsection
