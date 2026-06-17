@extends('layouts.backend')

@section('title', 'Probation Reviews')

@section('title', 'Probation Reviews Management')

@push('css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .page-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
    }

    /* Header & Breadcrumb */
    .header-section {
        background: var(--primary-gradient);
        padding: 40px 0 100px;
        color: white;
    }

    .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.6); }
    .breadcrumb-item.active { color: white; opacity: 0.9; }
    .breadcrumb-item a { color: white; opacity: 0.7; transition: var(--transition); }
    .breadcrumb-item a:hover { opacity: 1; text-decoration: none; }

    /* Stats Section */
    .stats-container {
        margin-top: -60px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-card:hover { transform: translateY(-5px); }
    
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .icon-blue { background: #eff6ff; color: #3b82f6; }
    .icon-orange { background: #fff7ed; color: #f97316; }
    .icon-green { background: #f0fdf4; color: #22c55e; }

    .stat-val { font-size: 28px; font-weight: 800; color: #1e293b; line-height: 1; }
    .stat-label { font-size: 14px; color: #64748b; font-weight: 500; margin-top: 4px; }

    /* Main Review Card */
    .review-card {
        background: white;
        border-radius: 20px;
        border: none;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .review-card .card-header {
        background: white;
        border-bottom: 1px solid #f1f5f9;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title { font-weight: 700; color: #0f172a; margin-bottom: 0; }

    /* Table Styles */
    .custom-table-container { padding: 0; }
    
    .custom-table { margin-bottom: 0; }
    .custom-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 18px 24px;
        border: none;
    }

    .custom-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: var(--transition); }
    .custom-table tbody tr:hover { background-color: #f9fafb; }
    .custom-table tbody td { padding: 18px 24px; vertical-align: middle; border: none; }

    /* Avatar & Name Group */
    .emp-group { display: flex; align-items: center; gap: 14px; }
    .emp-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .emp-name { font-weight: 600; color: #1e293b; font-size: 14px; display: block; }
    .emp-role { font-size: 12px; color: #64748b; display: block; }

    /* Badges & Text */
    .dept-badge {
        background: #f1f5f9;
        color: #475569;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .date-text { font-size: 14px; color: #334155; font-weight: 500; }
    .overdue-text { color: #ef4444; font-weight: 700; }
    .upcoming-text { color: #3b82f6; font-weight: 700; }

    /* Action Button */
    .btn-start-review {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.3);
    }

    .btn-start-review:hover {
        transform: scale(1.05);
        color: white;
        box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.4);
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="header-section">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="fw-bold mb-1">Probation Reviews</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('onboarding.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Probation Queue</li>
                        </ol>
                    </nav>
                </div>
                <div class="text-end opacity-75">
                    <div class="small fw-bold">{{ date('F j, Y') }}</div>
                    <div class="small">Manager Control Panel</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content container-fluid">
        @php
            $overdueCount = $reviews->filter(fn($r) => $r->scheduled_date < now())->count();
            $upcomingCount = $reviews->filter(fn($r) => $r->scheduled_date >= now() && $r->scheduled_date <= now()->addDays(7))->count();
        @endphp

        <!-- Stats Section -->
        <div class="row stats-container">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-blue">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="stat-val">{{ $reviews->count() }}</div>
                        <div class="stat-label">Total Pending Reviews</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-orange">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="stat-val">{{ $overdueCount }}</div>
                        <div class="stat-label">Overdue Reviews</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-green">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="stat-val">{{ $upcomingCount }}</div>
                        <div class="stat-label">Due This Week</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="row">
            <div class="col-md-12">
                <div class="card review-card">
                    <div class="card-header">
                        <h5 class="card-title">Employee Review Queue</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-light border"><i class="fas fa-filter me-1"></i> Filter</button>
                            <button class="btn btn-sm btn-light border"><i class="fas fa-download me-1"></i> Export</button>
                        </div>
                    </div>
                    <div class="card-body custom-table-container">
                        @if(session('success'))
                            <div class="alert alert-success mx-4 mt-3 alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>Employee Details</th>
                                        <th>Department</th>
                                        <th>Joining Date</th>
                                        <th>Review Schedule</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reviews as $review)
                                    @php
                                        $isOverdue = $review->scheduled_date < now();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="emp-group">
                                                <img src="{{ $review->employee->profile_image_url ?? asset('assets/backend/img/profiles/avatar-01.jpg') }}" class="emp-avatar" alt="Avatar">
                                                <div>
                                                    <span class="emp-name">{{ $review->employee->name }}</span>
                                                    <span class="emp-role text-uppercase">{{ $review->employee->designation->name ?? 'Staff' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="dept-badge">{{ $review->employee->department->name ?? 'General' }}</span>
                                        </td>
                                        <td>
                                            <div class="date-text">
                                                <i class="far fa-calendar-check me-2 text-muted"></i>
                                                {{ $review->employee->workDetail?->joining_date ? $review->employee->workDetail?->joining_date->format('d M, Y') : '--' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-text {{ $isOverdue ? 'overdue-text' : 'upcoming-text' }}">
                                                <i class="fas fa-clock me-2"></i>
                                                {{ $review->scheduled_date->format('d M, Y') }}
                                                @if($isOverdue)
                                                    <span class="ms-1 tiny" style="font-size: 10px;">(OVERDUE)</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('onboarding.probation.edit', $review->id) }}" class="btn-start-review">
                                                <span>Begin Review</span>
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="py-4">
                                                <div class="mb-4">
                                                    <i class="fas fa-clipboard-list fa-4x text-light" style="opacity: 0.5;"></i>
                                                </div>
                                                <h5 class="text-muted">All reviews are up to date!</h5>
                                                <p class="text-muted small">When an employee's probation period is nearing end, they will appear here.</p>
                                            </div>
                                        </td>
                                    </tr>
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
