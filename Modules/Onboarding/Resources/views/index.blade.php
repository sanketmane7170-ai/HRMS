@extends('layouts.backend')

@section('page-title')
    {{ __('Onboarding Dashboard') }}
@endsection

@section('content')
<style>
    /* Dashboard Variables */
    :root {
        --primary-font: 'Inter', sans-serif;
        --card-radius: 12px;
        --transition-speed: 0.3s;
    }

    /* Cards */
    .onboarding-card {
        border-radius: var(--card-radius);
        border: none;
        color: #fff;
        padding: 24px;
        position: relative;
        overflow: hidden;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
        transition: transform var(--transition-speed);
    }
    .onboarding-card:hover { transform: translateY(-5px); }
    
    .card-blue { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
    .card-purple { background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); }
    .card-cyan { background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); }
    .card-violet { background: linear-gradient(135deg, #db2777 0%, #be185d 100%); }
    
    .card-stats-circle {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 700;
        backdrop-filter: blur(4px);
    }
    .stat-label {
        font-size: 12px;
        opacity: 0.95;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .stat-value { font-size: 28px; font-weight: 800; margin-bottom: 0; }
    
    /* Pending Actions */
    .pending-actions-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 32px;
        position: relative;
        height: 100%;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    .hero-title {
        color: #0f172a;
        font-weight: 800;
        font-size: 16px;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    .action-item {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        padding: 16px;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
        z-index: 2;
        position: relative;
    }
    .action-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transform: translateX(5px);
    }
    .action-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        color: #fff;
        font-size: 18px;
    }
    .icon-blue { background: #3b82f6; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); }
    .icon-dark { background: #475569; box-shadow: 0 4px 10px rgba(71, 85, 105, 0.3); }
    
    .hero-image {
        position: absolute;
        right: -20px;
        bottom: -20px;
        height: 200px;
        opacity: 0.15;
        pointer-events: none;
        z-index: 0;
    }

    /* Status Panel */
    .status-panel-card {
        background: #fff;
        border-radius: 16px;
        padding: 30px;
        height: 100%;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    
    /* Table & Graph Cards */
    .content-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        height: 100%;
        overflow: hidden;
    }
    .card-header-custom {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
    }
    .table-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }
    .filter-badge {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-badge:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    /* Table Styles */
    .custom-table th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 24px;
        border-bottom: 1px solid #e2e8f0;
    }
    .custom-table td {
        padding: 16px 24px;
        vertical-align: middle;
        color: #334155;
        font-weight: 500;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-table tr:last-child td { border-bottom: none; }
    
    .percent-pill {
        background: #f0fdf4;
        color: #166534;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
        border: 1px solid #bbf7d0;
    }
    .action-btn-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f8fafc;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        margin: 0 4px;
    }
    .action-btn-circle:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-uppercase font-weight-bold text-dark">Onboarding Summary</h3>
                </div>
                {{-- Removed non-functional Reports button by Sanket --}}
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="onboarding-card card-blue">
                    <div class="stat-label">New Hires</div>
                    <div class="stat-value">Today</div>
                    <div class="card-stats-circle pt-1">{{ $stats['new_hires_today'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="onboarding-card card-purple">
                    <div class="stat-label">New Hires</div>
                    <div class="stat-value">This Month</div>
                    <div class="card-stats-circle pt-1">{{ $stats['new_hires_this_month'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="onboarding-card card-cyan">
                    <div class="stat-label">New Hires</div>
                    <div class="stat-value">Last Month</div>
                    <div class="card-stats-circle pt-1">{{ $stats['new_hires_last_month'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="onboarding-card card-violet">
                    <div class="stat-label">Employees</div>
                    <div class="stat-value">On File</div>
                    <div class="card-stats-circle pt-1">{{ $stats['employees_on_file'] }}</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Left: Pending Actions -->
            <div class="col-md-6 d-flex">
                <div class="pending-actions-card w-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="hero-title">MY PENDING ACTIONS</h5>
                            <p class="text-muted small mb-0">You have {{ $statusCounts['new_hires_in_queue'] + $statusCounts['incomplete_records'] }} tasks pending</p>
                        </div>
                        <span class="badge bg-danger rounded-pill px-3 py-2">Urgent</span>
                    </div>
                    
                    <div class="action-item">
                        <div class="action-icon icon-blue">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 font-weight-bold text-dark">New Hire to Onboard ({{ $statusCounts['new_hires_in_queue'] }})</h6>
                            <a href="{{ route('onboarding.new-hires', ['status' => 'pending']) }}" class="text-primary small font-weight-bold text-decoration-none">Review Queue &rarr;</a>
                        </div>
                    </div>

                    <div class="action-item">
                        <div class="action-icon icon-dark">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 font-weight-bold text-dark">Incomplete Records ({{ $statusCounts['incomplete_records'] }})</h6>
                            <a href="{{ route('onboarding.new-hires', ['status' => 'incomplete']) }}" class="text-primary small font-weight-bold text-decoration-none">Review Records &rarr;</a>
                        </div>
                    </div>

                    <!-- Background Image -->
                    <img src="{{ asset('assets/img/hr-illustration.png') }}" class="hero-image" alt="HR Background">
                </div>
            </div>

            <!-- Right: Status Panel -->
            <div class="col-md-6 d-flex">
                <div class="status-panel-card w-100">
                    <h5 class="hero-title mb-1">ONBOARDING STATUS</h5>
                    <p class="text-muted small mb-4">Overview of candidate progress</p>
                    
                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded" style="border: 1px dashed #cbd5e1;">
                         <div class="mr-3 text-center" style="width: 50px;">
                            <i class="fas fa-user-clock text-primary fa-2x"></i>
                         </div>
                         <div>
                             <h4 class="mb-0 font-weight-bold">{{ $statusCounts['new_hires_in_queue'] }}</h4>
                             <small class="text-muted font-weight-bold text-uppercase">In Queue</small>
                         </div>
                    </div>

                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded" style="border: 1px dashed #cbd5e1;">
                        <div class="mr-3 text-center" style="width: 50px;">
                           <i class="fas fa-hourglass-start text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 font-weight-bold">{{ $statusCounts['incomplete_records'] }}</h4>
                            <small class="text-muted font-weight-bold text-uppercase">In Progress</small>
                        </div>
                   </div>

                   <div class="d-flex align-items-center p-3 bg-light rounded" style="border: 1px dashed #cbd5e1;">
                        <div class="mr-3 text-center" style="width: 50px;">
                        <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 font-weight-bold">{{ $statusCounts['past_due'] }}</h4>
                            <small class="text-muted font-weight-bold text-uppercase">Past Due</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Table -->
            <div class="col-md-7 d-flex">
                <div class="content-card w-100">
                    <div class="card-header-custom">
                        <h5 class="table-title">New Hires This Month</h5>
                        <div class="filter-badge">
                            This Month <i class="fas fa-chevron-down ml-1" style="font-size: 10px;"></i>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Percentage</th>
                                        <th class="text-center">View</th>
                                        <th class="text-center">Docs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentHires as $hire)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('assets/img/profiles/avatar-02.jpg') }}" class="rounded-circle mr-3" width="32" height="32" alt="">
                                                <div class="font-weight-bold text-dark">{{ $hire->full_name }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $hire->division->name ?? 'General' }}</td>
                                        <td>
                                            <span class="percent-pill">{{ $hire->progress_percent }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="action-btn-circle"><i class="fas fa-eye"></i></a>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="action-btn-circle"><i class="fas fa-file-alt"></i></a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-5 text-center text-muted">
                                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486777.png" width="64" class="mb-3 opacity-50" style="opacity: 0.5;">
                                            <p class="mb-0">No new hires found for this month.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($recentHires->count() > 0)
                    <div class="text-center py-3 border-top">
                        <a href="{{ route('onboarding.new-hires') }}" class="text-primary font-weight-bold small">View All Records</a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Graph -->
            <div class="col-md-5 d-flex">
                <div class="content-card w-100">
                    <div class="card-header-custom">
                        <h5 class="table-title">GRAPH REPORTS</h5>
                        <div class="filter-badge">Monthly</div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                        <h6 class="text-center text-muted font-weight-bold mb-4 small text-uppercase">Offers vs Hires</h6>
                        <div style="height: 220px; width: 100%;">
                             <canvas id="hiresChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        const ctx = document.getElementById('hiresChart').getContext('2d');
        const gradientBlue = ctx.createLinearGradient(0, 0, 0, 300);
        gradientBlue.addColorStop(0, '#3b82f6');
        gradientBlue.addColorStop(1, '#2563eb');

        const gradientGreen = ctx.createLinearGradient(0, 0, 0, 300);
        gradientGreen.addColorStop(0, '#10b981');
        gradientGreen.addColorStop(1, '#059669');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'Hired',
                        data: @json($chartData['hired']),
                        backgroundColor: gradientBlue,
                        borderRadius: 4,
                        barPercentage: 0.5,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Joined',
                        data: @json($chartData['joined']),
                        backgroundColor: gradientGreen,
                        borderRadius: 4,
                        barPercentage: 0.5,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            font: { size: 11, family: "'Inter', sans-serif" }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { size: 10 }, color: '#94a3b8' },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#94a3b8' },
                        border: { display: false }
                    }
                }
            }
        });
    });
</script>
@endsection
