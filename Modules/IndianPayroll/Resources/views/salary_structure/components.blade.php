@extends('layouts.backend')
@section('content')
@php
    $groups = [
        'earning'                => ['Earnings', 'fa-arrow-trend-up', 'sc-earning'],
        'deduction'              => ['Deductions', 'fa-arrow-trend-down', 'sc-deduction'],
        'employer_contribution'  => ['Employer Contributions', 'fa-building', 'sc-employer'],
    ];
    $byType = $components->groupBy('type');
    $total = $components->count();
@endphp

@push('css')
<style>
    .sc-chips { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:18px; }
    .sc-chip { display:flex; align-items:center; gap:10px; padding:10px 16px; background:#fff;
        border:1px solid #e5e7eb; border-radius:10px; min-width:130px; }
    .sc-chip .n { font-size:1.3rem; font-weight:700; line-height:1; color:#111827; }
    .sc-chip .l { font-size:.74rem; color:#9ca3af; text-transform:uppercase; letter-spacing:.03em; }
    .sc-chip .ic { width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.9rem; }
    .sc-chip.t-all .ic{background:#eef2ff;color:#4f46e5;}
    .sc-chip.t-earning .ic{background:#ecfdf5;color:#059669;}
    .sc-chip.t-deduction .ic{background:#fef2f2;color:#dc2626;}
    .sc-chip.t-employer .ic{background:#eff6ff;color:#2563eb;}

    .sc-toolbar { display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; margin-bottom:18px; }
    .sc-search { position:relative; flex:1 1 260px; max-width:360px; }
    .sc-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9ca3af; }
    .sc-search input { padding-left:34px; border-radius:9px; }

    .sc-section { margin-bottom:26px; }
    .sc-section-head { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
    .sc-section-head .ic { width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.82rem; }
    .sc-earning .ic{background:#ecfdf5;color:#059669;}
    .sc-deduction .ic{background:#fef2f2;color:#dc2626;}
    .sc-employer .ic{background:#eff6ff;color:#2563eb;}
    .sc-section-head h6 { margin:0; font-weight:600; font-size:.98rem; }
    .sc-section-head .cnt { font-size:.78rem; color:#9ca3af; background:#f3f4f6; padding:2px 9px; border-radius:20px; }

    .sc-tile { background:#fff; border:1px solid #e5e7eb; border-radius:11px; padding:14px; height:100%;
        display:flex; flex-direction:column; gap:9px; transition:box-shadow .15s, border-color .15s; }
    .sc-tile:hover { border-color:#c7d2fe; box-shadow:0 3px 10px rgba(17,24,39,.06); }
    .sc-tile.is-inactive { opacity:.62; }
    .sc-tile-top { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; }
    .sc-code { font-family:ui-monospace,Menlo,monospace; font-size:.7rem; color:#6b7280; background:#f3f4f6;
        padding:2px 7px; border-radius:5px; word-break:break-all; }
    .sc-name { font-weight:600; font-size:.92rem; color:#111827; line-height:1.25; }
    .sc-badges { display:flex; flex-wrap:wrap; gap:5px; margin-top:auto; }
    .sc-pill { font-size:.68rem; font-weight:600; padding:2px 8px; border-radius:20px; line-height:1.5; white-space:nowrap; }
    .p-tax { background:#fffbeb; color:#b45309; }
    .p-notax { background:#f3f4f6; color:#6b7280; }
    .p-pf { background:#eff6ff; color:#1d4ed8; }
    .p-sys { background:#f5f3ff; color:#6d28d9; }
    .p-ctc { background:#ecfeff; color:#0e7490; }
    .p-inactive { background:#fef2f2; color:#b91c1c; }

    .sc-actions { display:flex; gap:6px; }
    .sc-actions .btn { padding:3px 9px; }
    .sc-lock { font-size:.72rem; color:#9ca3af; display:flex; align-items:center; gap:5px; }

    .sc-empty { text-align:center; padding:26px; color:#9ca3af; border:1px dashed #e5e7eb; border-radius:10px; }
    .sc-noresults { display:none; text-align:center; padding:30px; color:#9ca3af; }
</style>
@endpush

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('salary_components') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('indian_payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('salary_components') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.salary-components.create') }}" class="btn btn-primary edit-button">
                        <i class="fas fa-plus"></i> {{ __trans('add_component') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Summary counts --}}
        <div class="sc-chips">
            <div class="sc-chip t-all"><div class="ic"><i class="fas fa-layer-group"></i></div><div><div class="n">{{ $total }}</div><div class="l">Total</div></div></div>
            <div class="sc-chip t-earning"><div class="ic"><i class="fas fa-arrow-trend-up"></i></div><div><div class="n">{{ $byType->get('earning')?->count() ?? 0 }}</div><div class="l">Earnings</div></div></div>
            <div class="sc-chip t-deduction"><div class="ic"><i class="fas fa-arrow-trend-down"></i></div><div><div class="n">{{ $byType->get('deduction')?->count() ?? 0 }}</div><div class="l">Deductions</div></div></div>
            <div class="sc-chip t-employer"><div class="ic"><i class="fas fa-building"></i></div><div><div class="n">{{ $byType->get('employer_contribution')?->count() ?? 0 }}</div><div class="l">Employer</div></div></div>
        </div>

        {{-- Search --}}
        <div class="sc-toolbar">
            <div class="sc-search">
                <i class="fas fa-search"></i>
                <input type="text" id="scSearch" class="form-control" placeholder="Search by name or code…" autocomplete="off">
            </div>
            <div class="text-muted" style="font-size:.8rem;"><i class="fas fa-lock"></i> System components are managed by the payroll engine and can't be edited.</div>
        </div>

        @if($total === 0)
            <div class="sc-empty"><i class="fas fa-inbox fa-2x d-block mb-2"></i><p class="mb-0">No salary components yet. Click “Add Component” to create one.</p></div>
        @endif

        @foreach($groups as $type => $meta)
            @php($items = $byType->get($type, collect()))
            @if($items->count())
            <div class="sc-section {{ $meta[2] }}" data-section="{{ $type }}">
                <div class="sc-section-head">
                    <div class="ic"><i class="fas {{ $meta[1] }}"></i></div>
                    <h6>{{ $meta[0] }}</h6>
                    <span class="cnt">{{ $items->count() }}</span>
                </div>
                <div class="row g-3">
                    @foreach($items as $component)
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-3 sc-tile-col"
                         data-search="{{ strtolower($component->code.' '.$component->name) }}">
                        <div class="sc-tile {{ $component->is_active ? '' : 'is-inactive' }}">
                            <div class="sc-tile-top">
                                <span class="sc-code">{{ $component->code }}</span>
                                @if($component->is_statutory)
                                    <span class="sc-lock"><i class="fas fa-lock"></i></span>
                                @endif
                            </div>
                            <div class="sc-name">{{ $component->name }}</div>
                            <div class="sc-badges">
                                @if($component->is_taxable)
                                    <span class="sc-pill p-tax">Taxable</span>
                                @else
                                    <span class="sc-pill p-notax">Non-taxable</span>
                                @endif
                                @if($component->considered_for_pf_wage)<span class="sc-pill p-pf">PF wage</span>@endif
                                @if($component->is_part_of_ctc)<span class="sc-pill p-ctc">In CTC</span>@endif
                                @if($component->is_statutory)<span class="sc-pill p-sys">System</span>@endif
                                @if(!$component->is_active)<span class="sc-pill p-inactive">Inactive</span>@endif
                            </div>
                            @if(!$component->is_statutory)
                            <div class="sc-actions">
                                <a href="{{ route('backend.indian-payroll.salary-components.edit', $component) }}" class="btn btn-sm btn-outline-primary edit-button"><i class="fa fa-edit"></i> Edit</a>
                                <a href="{{ route('backend.indian-payroll.salary-components.destroy', $component) }}" method="DELETE" class="btn btn-sm btn-outline-danger action-button" data-alert="Delete the “{{ $component->name }}” component?"><i class="fa fa-trash"></i></a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

        <div class="sc-noresults" id="scNoResults"><i class="fas fa-search fa-2x d-block mb-2"></i>No components match your search.</div>
    </div>
</div>
<div id="editModal" class="modal"></div>

@push('scripts')
<script>
(function () {
    var input = document.getElementById('scSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        var anyVisible = false;
        document.querySelectorAll('.sc-section').forEach(function (section) {
            var sectionHas = false;
            section.querySelectorAll('.sc-tile-col').forEach(function (col) {
                var match = !q || (col.getAttribute('data-search') || '').indexOf(q) !== -1;
                col.style.display = match ? '' : 'none';
                if (match) { sectionHas = true; anyVisible = true; }
            });
            section.style.display = sectionHas ? '' : 'none';
        });
        document.getElementById('scNoResults').style.display = anyVisible ? 'none' : 'block';
    });
})();
</script>
@endpush
@endsection
