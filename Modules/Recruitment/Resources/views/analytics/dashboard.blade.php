@extends('layouts.backend')

@section('content')
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header border-0 pb-0 mb-5">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title text-dark font-weight-bold mb-1">{{ __trans('recruitment_insights') }}</h4>
                    <p class="text-muted small mb-0">{{ __trans('monitor_your_hiring_performance_and_metrics') }}</p>
                </div>
                <div class="col-auto">
                    <div class="d-flex align-items-center gap-3">
                        <form action="{{ route('recruitment.analytics.index') }}" method="GET" id="analytics-filter-form" class="m-0">
                            <select name="period" class="form-select form-select-sm border-0 bg-light shadow-none fw-semibold" onchange="this.form.submit()" style="border-radius: 8px; padding-right: 35px;">
                                <option value="7_days" {{ request('period') == '7_days' ? 'selected' : '' }}>{{ __trans('last_7_days') }}</option>
                                <option value="30_days" {{ request('period') == '30_days' || !request('period') ? 'selected' : '' }}>{{ __trans('last_30_days') }}</option>
                                <option value="90_days" {{ request('period') == '90_days' ? 'selected' : '' }}>{{ __trans('last_90_days') }}</option>
                                <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>{{ __trans('this_year') }}</option>
                            </select>
                        </form>
                        <button type="button" class="btn btn-sm btn-dark px-3 fw-bold shadow-none" style="border-radius: 8px;">
                            <i class="fas fa-download me-2"></i> {{ __trans('export') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        @php
            $funnel = $metrics['funnel']['funnel_data'] ?? [];
            $timeToHire = $metrics['time_to_hire'] ?? [];
            $costAnalysis = $metrics['cost_per_hire'] ?? [];
            
            $stats = [
                ['title' => 'total_applications', 'value' => $funnel['applied']['count'] ?? 0, 'icon' => 'fa-users', 'color' => '#4e73df', 'bg' => 'rgba(78, 115, 223, 0.08)'],
                ['title' => 'active_pipeline', 'value' => ($funnel['screening']['count'] ?? 0) + ($funnel['interview']['count'] ?? 0), 'icon' => 'fa-layer-group', 'color' => '#1cc88a', 'bg' => 'rgba(28, 200, 138, 0.08)'],
                ['title' => 'time_to_hire', 'value' => ($timeToHire['average_days'] ?? 0) . 'd', 'icon' => 'fa-bolt', 'color' => '#36b9cc', 'bg' => 'rgba(54, 185, 204, 0.08)'],
                ['title' => 'cost_per_hire', 'value' => '$' . number_format($costAnalysis['cost_per_hire'] ?? 0), 'icon' => 'fa-chart-pie', 'color' => '#f6c23e', 'bg' => 'rgba(246, 194, 62, 0.08)']
            ];
        @endphp

        <!-- Premium Stats Cards -->
        <div class="row mb-5">
            @foreach($stats as $stat)
            <div class="col-xl-3 col-sm-6 col-12 mb-4">
                <div class="card border border-light shadow-none h-100" style="border-radius: 16px; background-color: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background-color: {{ $stat['bg'] }}; color: {{ $stat['color'] }};">
                                <i class="fas {{ $stat['icon'] }} fa-lg"></i>
                            </div>
                            <span class="text-muted small fw-bold text-uppercase ls-1">{{ __trans($stat['title']) }}</span>
                        </div>
                        <h2 class="m-0 fw-black text-dark tracking-tight">{{ $stat['value'] }}</h2>
                        <div class="mt-3 d-flex align-items-center">
                            <span class="text-success small fw-bold me-2"><i class="fas fa-arrow-up me-1"></i> 12%</span>
                            <span class="text-muted smaller">{{ __trans('vs_last_period') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row mb-5">
            <!-- Main Chart Area -->
            <div class="col-xl-8 col-lg-7">
                <div class="card border border-light shadow-none mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 py-4 px-4 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 fw-black text-dark text-uppercase ls-1">{{ __trans('hiring_velocity') }}</h6>
                        <div class="d-flex gap-2">
                             <span class="badge bg-light text-dark border-0 px-3 py-2 fw-semibold" style="border-radius: 8px;">{{ __trans('candidates') }}</span>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="chart-area" style="height: 380px;">
                            <canvas id="velocityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Distribution -->
            <div class="col-xl-4 col-lg-5">
                <div class="card border border-light shadow-none mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 py-4 px-4">
                        <h6 class="m-0 fw-black text-dark text-uppercase ls-1">{{ __trans('department_mix') }}</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="chart-pie mb-4" style="height: 300px;">
                            <canvas id="mixChart"></canvas>
                        </div>
                        <div id="mix-legend" class="mt-4 pt-2 border-top">
                            <!-- Dynamic Legend -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Source Analytics -->
            <div class="col-md-6 mb-4">
                <div class="card border border-light shadow-none h-100" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 py-4 px-4">
                        <h6 class="m-0 fw-black text-dark text-uppercase ls-1">{{ __trans('top_performing_sources') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light-subtle text-muted smaller text-uppercase fw-bold ls-1">
                                    <tr>
                                        <th class="ps-4 py-3 border-0">{{ __trans('channel') }}</th>
                                        <th class="text-center py-3 border-0">{{ __trans('volume') }}</th>
                                        <th class="text-end pe-4 py-3 border-0">{{ __trans('quality_score') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($metrics['source_effectiveness'] ?? [] as $source)
                                    <tr class="border-bottom-0">
                                        <td class="ps-4 py-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs bg-dark-subtle text-dark rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-globe-americas small"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ $source['source'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center fw-medium text-dark">{{ $source['count'] }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-inline-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 6px; border-radius: 3px; background-color: #f1f3f9;">
                                                    <div class="progress-bar bg-primary" style="width: {{ $source['hire_rate'] }}%"></div>
                                                </div>
                                                <span class="fw-bold text-dark smaller">{{ $source['hire_rate'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">{{ __trans('insufficient_data') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stages Velocity -->
            <div class="col-md-6 mb-4">
                <div class="card border border-light shadow-none h-100" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 py-4 px-4">
                        <h6 class="m-0 fw-black text-dark text-uppercase ls-1">{{ __trans('pipeline_cycle_time') }}</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        @forelse($metrics['time_to_hire']['stage_metrics'] ?? [] as $stage => $days)
                        <div class="mb-5 last-child-mb-0">
                            <div class="d-flex justify-content-between align-items-end mb-3">
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase ls-1 d-block mb-1">{{ ucfirst(str_replace('_', ' ', $stage)) }}</span>
                                    <h5 class="m-0 fw-black text-dark">{{ $days }} <small class="fw-normal text-muted smaller">{{ __trans('business_days') }}</small></h5>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $days > 5 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }} border-0 px-3 py-2 fw-bold" style="border-radius: 8px;">
                                        {{ $days > 5 ? __trans('neutral') : __trans('efficient') }}
                                    </span>
                                </div>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 5px; background-color: #f8f9fa;">
                                <div class="progress-bar bg-dark shadow-none" role="progressbar" style="width: {{ min(($days / 15) * 100, 100) }}%; border-radius: 5px;"></div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">{{ __trans('waiting_for_metrics') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
    
    .page-wrapper.bg-white { 
        background-color: #fff !important; 
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .fw-black { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .ls-1 { letter-spacing: 0.05em; }
    .smaller { font-size: 0.75rem; }
    .text-dark { color: #111827 !important; }
    .text-muted { color: #6b7280 !important; }
    .border-light { border-color: #f3f4f6 !important; }
    .bg-light { background-color: #f9fafb !important; }
    .bg-light-subtle { background-color: #f9fafb !important; }
    .bg-dark-subtle { background-color: #f3f4f6 !important; }
    .last-child-mb-0:last-child { margin-bottom: 0 !important; }
    
    .btn-dark { background-color: #111827; border-color: #111827; }
    .btn-dark:hover { background-color: #1f2937; border-color: #1f2937; }
    
    .form-select-sm { font-size: 0.8rem; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.color = '#9ca3af';

        // Velocity Line Chart
        const velCtx = document.getElementById('velocityChart').getContext('2d');
        const velGradient = velCtx.createLinearGradient(0, 0, 0, 380);
        velGradient.addColorStop(0, 'rgba(17, 24, 39, 0.05)');
        velGradient.addColorStop(1, 'rgba(17, 24, 39, 0)');

        new Chart(velCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($metrics['trends']['labels'] ?? []) !!},
                datasets: [{
                    label: "{{ __trans('applications') }}",
                    fill: true,
                    backgroundColor: velGradient,
                    borderColor: "#111827",
                    borderWidth: 2.5,
                    pointBackgroundColor: "#fff",
                    pointBorderColor: "#111827",
                    pointBorderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: "#111827",
                    pointHoverBorderColor: "#fff",
                    pointHoverBorderWidth: 3,
                    tension: 0.4,
                    data: {!! json_encode($metrics['trends']['data'] ?? []) !!},
                }]
            },
            options: {
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        displayColors: false,
                        cornerRadius: 8,
                        titleFont: { weight: 'bold', size: 13 },
                        bodyFont: { size: 13 }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: '#f3f4f6', drawBorder: false },
                        ticks: { padding: 10 }
                    },
                    x: {
                        border: { display: false },
                        grid: { display: false }
                    }
                }
            }
        });

        // Mix Doughnut Chart
        const mixCtx = document.getElementById('mixChart').getContext('2d');
        const mixChart = new Chart(mixCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($metrics['department_distribution']['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($metrics['department_distribution']['data'] ?? []) !!},
                    backgroundColor: ['#111827', '#4b5563', '#9ca3af', '#d1d5db', '#e5e7eb', '#f3f4f6'],
                    borderWidth: 4,
                    borderColor: '#fff',
                    hoverOffset: 12
                }],
            },
            options: {
                maintainAspectRatio: false,
                cutout: '82%',
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Custom Legend Generation
        const legendContainer = document.getElementById('mix-legend');
        const data = mixChart.data.datasets[0].data;
        const labels = mixChart.data.labels;
        const colors = mixChart.data.datasets[0].backgroundColor;

        labels.forEach((label, i) => {
            const item = document.createElement('div');
            item.className = 'd-flex align-items-center justify-content-between mb-3';
            item.innerHTML = `
                <div class="d-flex align-items-center">
                    <div style="width: 10px; height: 10px; border-radius: 3px; background-color: ${colors[i]}; margin-right: 12px;"></div>
                    <span class="small fw-semibold text-dark">${label}</span>
                </div>
                <span class="smaller fw-bold text-muted">${data[i]}</span>
            `;
            legendContainer.appendChild(item);
        });
    });
</script>
@endpush
@endsection
