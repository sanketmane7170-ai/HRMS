{{-- Shared section navigation for all Statutory / Compliance setting pages --}}
@once
@push('css')
<style>
    /* ── Statutory settings redesign ───────────────────────────────── */
    .ss-intro { color:#6b7280; font-size:.9rem; margin:-4px 0 18px; max-width:760px; }
    .ss-nav .card-body { overflow-x:auto; }
    .ss-tabs { flex-wrap:nowrap; gap:8px; }
    .ss-tab {
        flex:1 1 0; min-width:150px; display:flex; flex-direction:column;
        padding:12px 16px; border-radius:10px; border:1px solid #e5e7eb;
        background:#fff; color:#374151; text-decoration:none; transition:all .15s;
    }
    .ss-tab i { font-size:1.05rem; margin-bottom:6px; color:#9ca3af; }
    .ss-tab span { font-weight:600; font-size:.92rem; line-height:1.1; }
    .ss-tab small { color:#9ca3af; font-size:.74rem; margin-top:2px; }
    .ss-tab:hover { border-color:#c7d2fe; background:#f5f7ff; }
    .ss-tab.active { border-color:#4f46e5; background:#eef2ff; color:#3730a3; box-shadow:0 1px 3px rgba(79,70,229,.12); }
    .ss-tab.active i, .ss-tab.active small { color:#6366f1; }

    .ss-card { border:1px solid #e5e7eb; border-radius:12px; height:100%; }
    .ss-card .card-body { padding:0; }
    .ss-card-head {
        display:flex; align-items:center; gap:10px; padding:16px 18px;
        border-bottom:1px solid #f0f0f3;
    }
    .ss-card-head .ss-ico {
        width:38px; height:38px; border-radius:9px; display:flex; align-items:center;
        justify-content:center; background:#eef2ff; color:#4f46e5; flex-shrink:0;
    }
    .ss-card-head h5 { margin:0; font-size:1rem; font-weight:600; }
    .ss-card-head .ss-sub { font-size:.76rem; color:#9ca3af; }

    /* status banner */
    .ss-status { padding:14px 18px; }
    .ss-status .badge { font-size:.72rem; padding:5px 9px; border-radius:6px; font-weight:600; }
    .ss-status.is-set { background:#f0fdf4; border-bottom:1px solid #dcfce7; }
    .ss-status.not-set { background:#fff7ed; border-bottom:1px solid #fed7aa; }
    .ss-kv { display:flex; flex-wrap:wrap; gap:6px 22px; margin-top:10px; }
    .ss-kv div { font-size:.82rem; }
    .ss-kv .k { color:#9ca3af; display:block; font-size:.7rem; text-transform:uppercase; letter-spacing:.03em; }
    .ss-kv .v { color:#111827; font-weight:600; }

    .ss-form { padding:16px 18px; }
    .ss-form-title { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; margin-bottom:4px; }
    .ss-form-help { font-size:.78rem; color:#9ca3af; margin-bottom:14px; }
    .ss-form .form-group { margin-bottom:12px; }
    .ss-form label { font-size:.8rem; font-weight:500; color:#374151; margin-bottom:3px; }

    .ss-history summary { cursor:pointer; font-size:.8rem; color:#6366f1; padding:10px 18px; border-top:1px solid #f0f0f3; }
    .ss-history summary:hover { color:#4338ca; }
    .ss-history table { font-size:.8rem; margin:0; }
    .ss-history .ss-current-row { background:#f5f7ff; }

    .ss-empty { text-align:center; padding:34px 18px; color:#9ca3af; }
    .ss-empty i { font-size:1.8rem; margin-bottom:8px; opacity:.5; }
    .ss-empty p { margin:0; font-size:.88rem; }

    .ss-section-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }
    .ss-add-title { font-size:.82rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
    .ss-scope { font-size:.82rem; color:#374151; }
    .ss-scope strong { color:#4f46e5; }
</style>
@endpush
@endonce
@php($current = $activeLink ?? '')
<div class="ss-nav card mb-4">
    <div class="card-body py-2">
        <nav class="nav ss-tabs">
            <a class="ss-tab @if($current === 'indian-payroll.statutory-settings') active @endif"
               href="{{ route('backend.indian-payroll.statutory-settings.index') }}">
                <i class="fas fa-piggy-bank"></i>
                <span>Contributions</span>
                <small>PF · ESI · Gratuity</small>
            </a>
            <a class="ss-tab @if($current === 'indian-payroll.tax-slabs') active @endif"
               href="{{ route('backend.indian-payroll.tax-slabs.index') }}">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Income Tax</span>
                <small>TDS slabs &amp; surcharge</small>
            </a>
            <a class="ss-tab @if($current === 'indian-payroll.professional-tax') active @endif"
               href="{{ route('backend.indian-payroll.professional-tax.index') }}">
                <i class="fas fa-landmark"></i>
                <span>Professional Tax</span>
                <small>Per-state PT slabs</small>
            </a>
            <a class="ss-tab @if($current === 'indian-payroll.lwf-rules') active @endif"
               href="{{ route('backend.indian-payroll.lwf-rules.index') }}">
                <i class="fas fa-hand-holding-heart"></i>
                <span>Labour Welfare Fund</span>
                <small>Per-state LWF</small>
            </a>
        </nav>
    </div>
</div>
