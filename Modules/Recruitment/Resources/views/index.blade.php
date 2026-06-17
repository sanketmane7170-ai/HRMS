@extends('layouts.backend')
@section('content')

<!-- Compact Professional Recruitment Dashboard Redesign by Sanket -->
<style>
/* PROFESSIONAL UI CORE SETTINGS */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

#recruitment-dashboard-wrapper {
    background-color: #F8FAFC !important;
    min-height: 100vh;
    font-family: 'Inter', -apple-system, sans-serif !important;
    padding-bottom: 2rem;
    color: #1E293B !important;
}

/* Skeleton Loading Shimmer */
.skeleton {
    background: #E2E8F0;
    background: linear-gradient(110deg, #E2E8F0 8%, #F1F5F9 18%, #E2E8F0 33%);
    border-radius: 5px;
    background-size: 200% 100%;
    animation: 1.5s shine linear infinite;
}

@keyframes shine {
    to { background-position-x: -200%; }
}

.skeleton-text { height: 12px; margin-bottom: 8px; width: 100%; }
.skeleton-title { height: 20px; margin-bottom: 12px; width: 60%; }

/* Card Professionalism */
#recruitment-dashboard-wrapper .card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 12px !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
    margin-bottom: 1rem !important;
    transition: all 0.2s ease-in-out !important;
}

#recruitment-dashboard-wrapper .card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    border-color: #CBD5E1 !important;
}

/* Compact Header */
#recruitment-dashboard-wrapper .page-header {
    background: transparent !important;
    border: none !important;
    margin-bottom: 1.25rem !important;
    padding-top: 0.75rem !important;
}

.dashboard-title {
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: #0F172A !important;
    letter-spacing: -0.01em;
}

/* Compact Stat Widgets - Horizontal Layout */
.stat-card-compact {
    padding: 1rem !important;
    display: flex !important;
    align-items: center !important;
    gap: 1rem !important;
}

.stat-icon-square {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
}

.stat-value-compact {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    color: #0F172A !important;
    line-height: 1.1;
    margin-bottom: 2px;
}

.stat-label-compact {
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    color: #64748B !important;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

/* Section Styling */
.section-heading {
    font-size: 0.875rem !important;
    font-weight: 700 !important;
    color: #475569 !important;
    margin-bottom: 0.75rem !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
}

.section-heading i { color: #3B82F6; }

/* Table High Density */
.table-compact th {
    background: #F8FAFC !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    color: #64748B !important;
    padding: 0.75rem 1rem !important;
    text-transform: uppercase;
    border-bottom: 1px solid #E2E8F0 !important;
}

.table-compact td {
    padding: 0.75rem 1rem !important;
    font-size: 0.875rem !important;
    vertical-align: middle !important;
    color: #1E293B !important;
}

.fw-600 { 
    font-weight: 600 !important; 
    color: #0F172A !important;
}

/* Quick Action Tiles - Compact */
.action-tile {
    text-decoration: none !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    padding: 0.875rem 1.25rem !important;
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 10px !important;
    color: #1E293B !important;
    font-weight: 600 !important;
    font-size: 0.875rem !important;
    transition: all 0.2s !important;
}

.action-tile:hover {
    background: #F1F5F9 !important;
    border-color: #3B82F6 !important;
    transform: translateX(4px) !important;
    color: #2563EB !important;
}

.action-tile i {
    font-size: 1rem;
    color: #3B82F6;
    width: 20px;
    text-align: center;
}

/* Status Badges */
.badge-soft-success { background: #DCFCE7; color: #166534; }
.badge-soft-warning { background: #FEF3C7; color: #92400E; }
.badge-soft-info { background: #E0F2FE; color: #075985; }

</style>

<div id="recruitment-dashboard-wrapper" class="page-wrapper">
    <div class="content container-fluid">
        <!-- Compact Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="dashboard-title">Recruitment Overview</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Admin</a></li>
                            <li class="breadcrumb-item active">Recruitment</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <a href="{{ route('recruitment.jobs.create') }}" class="btn btn-sm btn-primary px-3">
                            <i class="fas fa-plus me-1"></i> Post Job
                        </a>
                        <a href="{{ route('career.index') }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> Career Site
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horizontal Stats Grid -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card-compact">
                    <div class="stat-icon-square" style="background: #EFF6FF; color: #2563EB;">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value-compact" id="activeJobs">{{ $stats['activeJobs'] ?? 0 }}</div>
                        <div class="stat-label-compact">Active Jobs</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card-compact">
                    <div class="stat-icon-square" style="background: #ECFDF5; color: #059669;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value-compact" id="totalApplications">{{ $stats['totalApplications'] ?? 0 }}</div>
                        <div class="stat-label-compact">Applications</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card-compact">
                    <div class="stat-icon-square" style="background: #FFFBEB; color: #D97706;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value-compact" id="upcomingInterviews">{{ $stats['upcomingInterviews'] ?? 0 }}</div>
                        <div class="stat-label-compact">Interviews Today</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card-compact">
                    <div class="stat-icon-square" style="background: #FDF2F8; color: #DB2777;">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value-compact" id="pendingOffers">{{ $stats['pendingOffers'] ?? 0 }}</div>
                        <div class="stat-label-compact">Offers Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row g-3">
            <!-- Recent Applications -->
            <div class="col-lg-8">
                <div class="section-heading"><i class="fas fa-clock"></i> Recent Applications</div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive" id="recentApplicationsTable">
                            <!-- High-Density Skeleton Loader -->
                            <div class="p-3">
                                <div class="skeleton mb-3" style="height: 12px; width: 30%;"></div>
                                @for ($i = 0; $i < 4; $i++)
                                <div class="d-flex gap-3 mb-3">
                                    <div class="skeleton" style="height: 12px; flex: 2;"></div>
                                    <div class="skeleton" style="height: 12px; flex: 2;"></div>
                                    <div class="skeleton" style="height: 12px; flex: 1;"></div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end py-2">
                        <a href="{{ route('recruitment.applications.index') }}" class="btn btn-link btn-sm text-decoration-none p-0">View All <i class="fas fa-chevron-right ms-1"></i></a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Interviews -->
            <div class="col-lg-4">
                <div class="section-heading"><i class="fas fa-calendar-check"></i> Interviews Today</div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive" id="upcomingInterviewsTable">
                            <!-- High-Density Skeleton Loader -->
                            <div class="p-3">
                                <div class="skeleton mb-3" style="height: 12px; width: 40%;"></div>
                                @for ($i = 0; $i < 3; $i++)
                                <div class="d-flex gap-3 mb-3">
                                    <div class="skeleton" style="height: 12px; flex: 2;"></div>
                                    <div class="skeleton" style="height: 12px; flex: 1;"></div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end py-2">
                        <a href="{{ route('recruitment.interviews.index') }}" class="btn btn-link btn-sm text-decoration-none p-0">Full Schedule <i class="fas fa-chevron-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Board -->
        <div class="section-heading mt-4"><i class="fas fa-th-large"></i> Command Board</div>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('recruitment.jobs.index') }}" class="action-tile">
                    <i class="fas fa-cog"></i> Manage Jobs
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('recruitment.applications.index') }}" class="action-tile">
                    <i class="fas fa-user-tie"></i> Review Candidates
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('recruitment.interviews.index') }}" class="action-tile">
                    <i class="fas fa-calendar-plus"></i> Interview Board
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('recruitment.offers.index') }}" class="action-tile">
                    <i class="fas fa-file-contract"></i> Offer Management
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Dashboard initialized
        loadDashboardStats();
        loadRecentApplications();
        loadUpcomingInterviews();

        function loadDashboardStats() {
            $.ajax({
                url: "{{ route('recruitment.stats') }}",
                method: 'GET',
                success: function(data) {
                    $('#activeJobs').text(data.activeJobs || 0);
                    $('#totalApplications').text(data.totalApplications || 0);
                    $('#upcomingInterviews').text(data.upcomingInterviews || 0);
                    $('#pendingOffers').text(data.pendingOffers || 0);
                },
                error: function(xhr) {
                    console.error('Stats load failed');
                }
            });
        }

        function loadRecentApplications() {
            $.ajax({
                url: "{{ route('recruitment.recent-applications') }}",
                method: 'GET',
                success: function(applications) {
                    let html = `
                    <table class="table table-compact mb-0">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Job Title</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>`;
                    
                    if (applications.length > 0) {
                        applications.forEach(app => {
                            let stageClass = 'badge-soft-info';
                            if (app.stage === 'hired') stageClass = 'badge-soft-success';
                            if (app.stage === 'rejected') stageClass = 'badge-soft-warning';

                            let candidateName = app.user ? app.user.name : (app.candidate_name || 'Unknown Candidate');

                            html += `
                            <tr>
                                <td class="fw-600">${candidateName}</td>
                                <td class="text-muted">${app.job ? app.job.title : 'N/A'}</td>
                                <td><span class="badge ${stageClass}">${app.stage || 'Applied'}</span></td>
                            </tr>`;
                        });
                    } else {
                        html += `
                        <tr>
                            <td colspan="3" class="text-center text-muted p-4">
                                <div class="py-2">No recent applications found</div>
                            </td>
                        </tr>`;
                    }
                    
                    html += `</tbody></table>`;
                    $('#recentApplicationsTable').html(html);
                },
                error: function() {
                    $('#recentApplicationsTable').html('<div class="p-4 text-center text-danger">Failed to load applications</div>');
                }
            });
        }

        function loadUpcomingInterviews() {
            $.ajax({
                url: "{{ route('recruitment.upcoming-interviews') }}",
                method: 'GET',
                success: function(interviews) {
                    let html = `
                    <table class="table table-compact mb-0">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>`;
                    
                    if (interviews.length > 0) {
                        interviews.forEach(interview => {
                            let time = new Date(interview.scheduled_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            let candidateName = (interview.application && interview.application.user) 
                                ? interview.application.user.name 
                                : (interview.application ? interview.application.candidate_name : 'Unknown');

                            html += `
                            <tr>
                                <td class="fw-600">${candidateName}</td>
                                <td><span class="badge badge-soft-info">${time}</span></td>
                            </tr>`;
                        });
                    } else {
                        html += `
                        <tr>
                            <td colspan="2" class="text-center text-muted p-4">
                                <div class="py-2">No interviews scheduled today</div>
                            </td>
                        </tr>`;
                    }
                    
                    html += `</tbody></table>`;
                    $('#upcomingInterviewsTable').html(html);
                },
                error: function() {
                    $('#upcomingInterviewsTable').html('<div class="p-4 text-center text-danger">Failed to load interviews</div>');
                }
            });
        }
    });
</script>
@endpush
