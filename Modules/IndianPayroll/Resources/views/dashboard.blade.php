@extends('layouts.backend')
@section('content')
@php
    use Carbon\Carbon;
    $cur = fn($n) => '₹' . number_format((float) $n, 0);
    $statusMeta = [
        'draft'     => ['Draft', 'secondary', 'fa-pen'],
        'computing' => ['Computing…', 'warning', 'fa-spinner'],
        'computed'  => ['Computed', 'info', 'fa-calculator'],
        'failed'    => ['Failed', 'danger', 'fa-triangle-exclamation'],
        'approved'  => ['Approved', 'success', 'fa-circle-check'],
        'locked'    => ['Locked', 'dark', 'fa-lock'],
    ];
    $sm = fn($s) => $statusMeta[$s] ?? [ucfirst((string) $s), 'secondary', 'fa-circle'];

    // Derive deductions from the reliable identity: net = gross - deductions.
    $totalDeductions = max($agg->gross - $agg->net, 0);
    $grossSplit = [
        ['Net pay (take-home)', $agg->net, '#16a34a'],
        ['Deductions (PF, ESI, PT, TDS…)', $totalDeductions, '#dc2626'],
    ];
    $grossTotal = max($agg->gross, 1);
@endphp

@push('css')
<style>
    .ip-dash .card { border:1px solid #e9eaee; border-radius:13px; }
    .ip-kpi { padding:18px 20px; display:flex; align-items:flex-start; gap:14px; height:100%; }
    .ip-kpi .ic { width:46px;height:46px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0; }
    .ip-kpi .l { font-size:.78rem; color:#8b909a; text-transform:uppercase; letter-spacing:.03em; font-weight:600; }
    .ip-kpi .v { font-size:1.55rem; font-weight:700; color:#111827; line-height:1.15; margin-top:2px; }
    .ip-kpi .s { font-size:.78rem; color:#9ca3af; margin-top:2px; }
    .k-indigo .ic{background:#eef2ff;color:#4f46e5;} .k-green .ic{background:#ecfdf5;color:#059669;}
    .k-amber .ic{background:#fffbeb;color:#d97706;} .k-blue .ic{background:#eff6ff;color:#2563eb;}

    .ip-panel-head { padding:16px 20px; border-bottom:1px solid #f1f1f4; display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .ip-panel-head h6 { margin:0; font-weight:600; font-size:.98rem; }
    .ip-panel-head .sub { font-size:.76rem; color:#9ca3af; }
    .ip-panel-body { padding:18px 20px; }

    /* segmented bar */
    .ip-seg { display:flex; height:14px; border-radius:8px; overflow:hidden; background:#f3f4f6; }
    .ip-seg span { display:block; height:100%; }
    .ip-legend { display:flex; flex-direction:column; gap:10px; margin-top:16px; }
    .ip-legend .row-i { display:flex; align-items:center; justify-content:space-between; font-size:.86rem; }
    .ip-legend .dot { width:9px;height:9px;border-radius:50%;display:inline-block;margin-right:8px; }
    .ip-legend .amt { font-weight:600; color:#111827; }
    .ip-cost-foot { margin-top:16px; padding-top:14px; border-top:1px dashed #e5e7eb; display:flex; flex-wrap:wrap; gap:14px 26px; }
    .ip-cost-foot .k { font-size:.72rem; color:#9ca3af; text-transform:uppercase; letter-spacing:.03em; display:block; }
    .ip-cost-foot .v { font-size:1.05rem; font-weight:700; color:#111827; }

    /* trend bars */
    .ip-trend { display:flex; align-items:flex-end; gap:10px; height:150px; padding-top:8px; }
    .ip-trend .col-b { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:6px; height:100%; }
    .ip-trend .bar { width:100%; max-width:42px; background:linear-gradient(180deg,#818cf8,#4f46e5); border-radius:6px 6px 0 0; min-height:4px; transition:height .3s; }
    .ip-trend .bl { font-size:.74rem; color:#9ca3af; }
    .ip-trend .bv { font-size:.68rem; color:#6b7280; font-weight:600; }

    /* action items */
    .ip-action { display:flex; align-items:center; gap:13px; padding:13px 16px; border:1px solid #eef0f3; border-radius:10px; text-decoration:none; transition:.15s; }
    .ip-action:hover { border-color:#c7d2fe; background:#f8f9ff; }
    .ip-action .ic { width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .ip-action .t { font-weight:600; font-size:.88rem; color:#111827; }
    .ip-action .d { font-size:.76rem; color:#9ca3af; }
    .ip-action .cnt { margin-left:auto; font-weight:700; font-size:1.1rem; }
    .a-ok .ic{background:#ecfdf5;color:#059669;} .a-ok .cnt{color:#059669;}
    .a-warn .ic{background:#fff7ed;color:#ea580c;} .a-warn .cnt{color:#ea580c;}

    .ip-quick { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; }
    .ip-quick a { display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 10px; border:1px solid #eef0f3; border-radius:11px; text-decoration:none; color:#374151; text-align:center; transition:.15s; }
    .ip-quick a:hover { border-color:#c7d2fe; background:#f8f9ff; color:#4f46e5; }
    .ip-quick i { font-size:1.2rem; color:#6366f1; }
    .ip-quick span { font-size:.8rem; font-weight:500; }

    .ip-runs td, .ip-runs th { vertical-align:middle; }
    .ip-empty { text-align:center; padding:30px; color:#9ca3af; }
</style>
@endpush

<div class="page-wrapper ip-dash">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Payroll Dashboard</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('indian_payroll') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.payroll-runs.index') }}" class="btn btn-primary"><i class="fas fa-play"></i> Go to Payroll Runs</a>
                </div>
            </div>
        </div>

        {{-- ── KPI cards ───────────────────────────────────────────── --}}
        <div class="row g-3 mb-1">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100"><div class="ip-kpi k-indigo">
                    <div class="ic"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="l">Active Employees</div>
                        <div class="v">{{ $activeEmployees }}</div>
                        <div class="s">{{ $totalProfiles }} total profiles · {{ $withStructure }} with salary structure</div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100"><div class="ip-kpi k-blue">
                    <div class="ic"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="l">Latest Pay Run</div>
                        <div class="v">{{ $latestRun ? Carbon::create($latestRun->year, $latestRun->month, 1)->format('M Y') : '—' }}</div>
                        <div class="s">
                            @if($latestRun)
                                @php($m = $sm($latestRun->status))
                                <span class="badge bg-{{ $m[1] }} text-white"><i class="fas {{ $m[2] }}"></i> {{ $m[0] }}</span>
                                · {{ $agg->count }} payslips
                            @else No runs yet @endif
                        </div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100"><div class="ip-kpi k-green">
                    <div class="ic"><i class="fas fa-sack-dollar"></i></div>
                    <div>
                        <div class="l">Net Pay (latest run)</div>
                        <div class="v">{{ $cur($agg->net) }}</div>
                        <div class="s">Take-home paid to employees</div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100"><div class="ip-kpi k-amber">
                    <div class="ic"><i class="fas fa-building-columns"></i></div>
                    <div>
                        <div class="l">Total Employer Cost</div>
                        <div class="v">{{ $cur($employerCost) }}</div>
                        <div class="s">Gross + employer contributions</div>
                    </div>
                </div></div>
            </div>
        </div>

        <div class="row g-3">
            {{-- ── Cost breakdown ──────────────────────────────────── --}}
            <div class="col-12 col-xl-7">
                <div class="card h-100">
                    <div class="ip-panel-head">
                        <div><h6>Latest Run Cost Breakdown</h6>
                            <div class="sub">{{ $latestRun ? Carbon::create($latestRun->year, $latestRun->month, 1)->format('F Y') : 'No data' }}</div>
                        </div>
                        @if($latestRun)<a href="{{ route('backend.indian-payroll.payroll-runs.show', $latestRun) }}" class="btn btn-sm btn-outline-primary">View run</a>@endif
                    </div>
                    <div class="ip-panel-body">
                        @if($latestRun && $agg->gross > 0)
                            <div class="d-flex justify-content-between mb-2" style="font-size:.82rem;color:#6b7280;">
                                <span>Gross earnings</span><span class="amt" style="font-weight:700;color:#111827;">{{ $cur($agg->gross) }}</span>
                            </div>
                            <div class="ip-seg">
                                @foreach($grossSplit as $seg)
                                    <span style="width:{{ ($seg[1] / $grossTotal) * 100 }}%;background:{{ $seg[2] }};" title="{{ $seg[0] }}"></span>
                                @endforeach
                            </div>
                            <div class="ip-legend">
                                @foreach($grossSplit as $seg)
                                <div class="row-i">
                                    <span><span class="dot" style="background:{{ $seg[2] }}"></span>{{ $seg[0] }}</span>
                                    <span class="amt">{{ $cur($seg[1]) }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="ip-cost-foot">
                                <div><span class="k">Employer contributions</span><span class="v">{{ $cur($agg->employer) }}</span></div>
                                <div><span class="k">Total employer cost</span><span class="v">{{ $cur($employerCost) }}</span></div>
                                <div><span class="k">Avg net / employee</span><span class="v">{{ $cur($agg->count ? $agg->net / $agg->count : 0) }}</span></div>
                            </div>
                        @else
                            <div class="ip-empty"><i class="fas fa-calculator fa-2x d-block mb-2"></i>No computed payroll yet. Create and compute a run to see the cost breakdown.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Net pay trend ───────────────────────────────────── --}}
            <div class="col-12 col-xl-5">
                <div class="card h-100">
                    <div class="ip-panel-head"><div><h6>Net Pay Trend</h6><div class="sub">Last {{ $trend->count() }} run(s)</div></div></div>
                    <div class="ip-panel-body">
                        @if($trend->count())
                            <div class="ip-trend">
                                @foreach($trend as $t)
                                <div class="col-b" title="{{ $cur($t->net) }}">
                                    <div class="bv">{{ $t->net >= 100000 ? round($t->net/100000,1).'L' : round($t->net/1000).'k' }}</div>
                                    <div class="bar" style="height:{{ max(($t->net / $trendMax) * 100, 3) }}%;"></div>
                                    <div class="bl">{{ $t->label }}</div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="ip-empty"><i class="fas fa-chart-column fa-2x d-block mb-2"></i>No runs to chart yet.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-0">
            {{-- ── Needs attention ─────────────────────────────────── --}}
            <div class="col-12 col-xl-5">
                <div class="card h-100">
                    <div class="ip-panel-head"><div><h6>Needs Attention</h6><div class="sub">FY {{ $financialYear }}</div></div></div>
                    <div class="ip-panel-body d-flex flex-column" style="gap:11px;">
                        <a href="{{ route('backend.indian-payroll.employee-salary-structures.index') }}" class="ip-action {{ $missingStructure ? 'a-warn' : 'a-ok' }}">
                            <span class="ic"><i class="fas {{ $missingStructure ? 'fa-user-xmark' : 'fa-user-check' }}"></i></span>
                            <span><span class="t d-block">Employees without salary structure</span><span class="d">Can't be included in payroll until assigned</span></span>
                            <span class="cnt">{{ $missingStructure }}</span>
                        </a>
                        <a href="{{ route('backend.indian-payroll.tax-declarations.index') }}" class="ip-action {{ $pendingDeclarations ? 'a-warn' : 'a-ok' }}">
                            <span class="ic"><i class="fas fa-file-signature"></i></span>
                            <span><span class="t d-block">Pending tax declarations</span><span class="d">Employees yet to declare for {{ $financialYear }}</span></span>
                            <span class="cnt">{{ $pendingDeclarations }}</span>
                        </a>
                        <a href="{{ route('backend.indian-payroll.settlements.index') }}" class="ip-action {{ $pendingSettlements ? 'a-warn' : 'a-ok' }}">
                            <span class="ic"><i class="fas fa-handshake"></i></span>
                            <span><span class="t d-block">Settlements awaiting approval</span><span class="d">Full &amp; final settlements not yet approved</span></span>
                            <span class="cnt">{{ $pendingSettlements }}</span>
                        </a>
                        <div class="ip-action {{ $payslipCoverage >= 100 ? 'a-ok' : 'a-warn' }}" style="cursor:default;">
                            <span class="ic"><i class="fas fa-list-check"></i></span>
                            <span><span class="t d-block">Latest run coverage</span><span class="d">{{ $agg->count }} of {{ $activeEmployees }} active employees paid</span></span>
                            <span class="cnt">{{ $payslipCoverage }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Quick links ─────────────────────────────────────── --}}
            <div class="col-12 col-xl-7">
                <div class="card h-100">
                    <div class="ip-panel-head"><div><h6>Quick Access</h6></div></div>
                    <div class="ip-panel-body">
                        <div class="ip-quick">
                            <a href="{{ route('backend.indian-payroll.employee-profiles.index') }}"><i class="fas fa-id-card"></i><span>Employee Profiles</span></a>
                            <a href="{{ route('backend.indian-payroll.salary-components.index') }}"><i class="fas fa-puzzle-piece"></i><span>Salary Components</span></a>
                            <a href="{{ route('backend.indian-payroll.salary-templates.index') }}"><i class="fas fa-sitemap"></i><span>CTC Templates</span></a>
                            <a href="{{ route('backend.indian-payroll.employee-salary-structures.index') }}"><i class="fas fa-money-check-dollar"></i><span>Salary Structures</span></a>
                            <a href="{{ route('backend.indian-payroll.statutory-settings.index') }}"><i class="fas fa-scale-balanced"></i><span>Compliance Settings</span></a>
                            <a href="{{ route('backend.indian-payroll.payroll-runs.index') }}"><i class="fas fa-gears"></i><span>Payroll Runs</span></a>
                            <a href="{{ route('backend.indian-payroll.tax-declarations.index') }}"><i class="fas fa-file-signature"></i><span>Tax Declarations</span></a>
                            <a href="{{ route('backend.indian-payroll.settlements.index') }}"><i class="fas fa-handshake"></i><span>Settlements</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Recent runs ─────────────────────────────────────────── --}}
        <div class="row g-3 mt-0">
            <div class="col-12">
                <div class="card">
                    <div class="ip-panel-head"><div><h6>Recent Payroll Runs</h6></div>
                        <a href="{{ route('backend.indian-payroll.payroll-runs.index') }}" class="btn btn-sm btn-outline-primary">All runs</a>
                    </div>
                    <div class="ip-panel-body p-0">
                        <div class="table-responsive">
                        <table class="table table-hover ip-runs mb-0">
                            <thead><tr><th class="ps-4">Period</th><th>Status</th><th>Payslips</th><th>Net Pay</th><th class="text-end pe-4">Action</th></tr></thead>
                            <tbody>
                                @forelse ($recentRuns as $run)
                                @php($m = $sm($run->status))
                                <tr>
                                    <td class="ps-4"><strong>{{ Carbon::create($run->year, $run->month, 1)->format('F Y') }}</strong></td>
                                    <td><span class="badge bg-{{ $m[1] }} text-white"><i class="fas {{ $m[2] }}"></i> {{ $m[0] }}</span></td>
                                    <td>{{ $run->payslips()->count() }}</td>
                                    <td>{{ $cur($run->payslips()->sum('net_pay')) }}</td>
                                    <td class="text-end pe-4"><a href="{{ route('backend.indian-payroll.payroll-runs.show', $run) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i> View</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="ip-empty"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No payroll runs yet.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
