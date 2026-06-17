@extends('layouts.backend')

@section('content')
<style>
/* Complete Custom Styling - Override Everything (Sanket) */
.app-details-page * {
    box-sizing: border-box;
}

.app-details-page {
    background: #F9FAFB !important;
    min-height: 100vh;
}

.app-details-header {
    background: #FFFFFF !important;
    border-bottom: 1px solid #E5E7EB !important;
    padding: 1.5rem 0 !important;
    margin-bottom: 2rem !important;
}

.app-details-breadcrumb {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
}

.app-details-breadcrumb li {
    color: #6B7280;
}

.app-details-breadcrumb li + li:before {
    content: "/";
    padding: 0 0.5rem;
    color: #D1D5DB;
}

.app-details-breadcrumb a {
    color: #6B7280;
    text-decoration: none;
}

.app-details-breadcrumb a:hover {
    color: #2563EB;
}

.app-details-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827 !important;
    margin: 0;
}

.app-details-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.5rem;
    border: 1px solid #2563EB;
    background: #FFFFFF;
    color: #2563EB !important;
    text-decoration: none;
    transition: all 0.2s;
}

.app-details-btn:hover {
    background: #2563EB;
    color: #FFFFFF !important;
}

.app-details-btn-primary {
    background: #2563EB;
    color: #FFFFFF !important;
    border-color: #2563EB;
}

.app-details-btn-primary:hover {
    background: #1D4ED8;
    border-color: #1D4ED8;
}

.app-details-card {
    background: #FFFFFF !important;
    border: 1px solid #E5E7EB !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
    margin-bottom: 1.5rem;
}

.app-details-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #F3F4F6;
}

.app-details-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.app-details-card-body {
    padding: 1.5rem;
}

.app-details-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #EFF6FF !important;
    color: #2563EB !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.app-details-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827 !important;
    margin: 0 0 0.5rem 0;
}

.app-details-email {
    color: #6B7280 !important;
    margin: 0 0 0.75rem 0;
    font-size: 0.9375rem;
}

.app-details-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.app-details-badge-success {
    background: #D1FAE5 !important;
    color: #065F46 !important;
}

.app-details-badge-danger {
    background: #FEE2E2 !important;
    color: #991B1B !important;
}

.app-details-badge-info {
    background: #DBEAFE !important;
    color: #1E40AF !important;
}

.app-details-badge-warning {
    background: #FEF3C7 !important;
    color: #92400E !important;
}

.app-details-badge-primary {
    background: #DBEAFE !important;
    color: #1E40AF !important;
}

.app-details-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.app-details-info-item label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.app-details-info-item p {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.app-details-timeline {
    position: relative;
}

.app-details-timeline-item {
    position: relative;
    display: flex;
    gap: 1rem;
    padding-bottom: 1.5rem;
}

.app-details-timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 36px;
    bottom: 0;
    width: 2px;
    background: #E5E7EB;
}

.app-details-timeline-marker {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
    font-size: 0.875rem;
}

.app-details-timeline-marker-primary {
    background: #EFF6FF !important;
    color: #2563EB !important;
}

.app-details-timeline-marker-success {
    background: #D1FAE5 !important;
    color: #059669 !important;
}

.app-details-timeline-marker-warning {
    background: #FEF3C7 !important;
    color: #D97706 !important;
}

.app-details-timeline-marker-danger {
    background: #FEE2E2 !important;
    color: #DC2626 !important;
}

.app-details-timeline-marker-info {
    background: #DBEAFE !important;
    color: #0284C7 !important;
}

.app-details-timeline-content {
    flex: 1;
    padding-top: 4px;
}

.app-details-timeline-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.app-details-timeline-meta {
    font-size: 0.8125rem;
    color: #6B7280;
}

.app-details-timeline-meta span + span:before {
    content: "•";
    margin: 0 0.5rem;
    color: #D1D5DB;
}

.app-details-sidebar {
    position: sticky;
    top: 20px;
}

.app-details-action-btn {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    margin-bottom: 0.75rem;
    font-size: 0.9375rem;
    font-weight: 500;
    border-radius: 0.5rem;
    border: 1px solid;
    background: #FFFFFF;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
}

.app-details-action-btn i {
    margin-right: 0.75rem;
    width: 16px;
    text-align: center;
}

.app-details-action-btn-primary {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
}

.app-details-action-btn-primary:hover {
    background: #1D4ED8 !important;
    border-color: #1D4ED8 !important;
}

.app-details-action-btn-outline {
    border-color: #D1D5DB;
    color: #374151;
}

.app-details-action-btn-outline:hover {
    background: #F9FAFB;
    border-color: #9CA3AF;
}

.app-details-action-btn-danger {
    border-color: #FCA5A5;
    color: #DC2626;
}

.app-details-action-btn-danger:hover {
    background: #FEF2F2;
    border-color: #DC2626;
}

.app-details-stat-box {
    text-align: center;
    padding: 1.25rem;
    border-radius: 0.75rem;
}

.app-details-stat-box-success {
    background: #D1FAE5 !important;
}

.app-details-stat-box-primary {
    background: #DBEAFE !important;
}

.app-details-stat-box-warning {
    background: #FEF3C7 !important;
}

.app-details-stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.app-details-stat-value-success {
    color: #059669 !important;
}

.app-details-stat-value-primary {
    color: #2563EB !important;
}

.app-details-stat-value-warning {
    color: #D97706 !important;
}

.app-details-stat-label {
    font-size: 0.8125rem;
    color: #6B7280;
    font-weight: 500;
}

@media (max-width: 991px) {
    .app-details-sidebar {
        position: static;
    }
    .app-details-info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="app-details-page">
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Header -->
            <div class="app-details-header">
                <div class="row align-items-center">
                    <div class="col">
                        <ul class="app-details-breadcrumb">
                            <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                            <li><a href="{{ route('recruitment.applications.index') }}">Applications</a></li>
                            <li>Application Details</li>
                        </ul>
                        <h1 class="app-details-title">Application Details</h1>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('recruitment.applications.index') }}" class="app-details-btn">
                            <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>Back to Applications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Candidate Card -->
                    <div class="app-details-card">
                        <div class="app-details-card-body">
                            <div class="d-flex align-items-start mb-4">
                                <div class="app-details-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div style="flex: 1; margin-left: 1rem;">
                                    <h2 class="app-details-name">
                                        {{ $application->user ? (is_array($application->user->name) ? implode(', ', $application->user->name) : $application->user->name) : (is_array($application->candidate_name) ? implode(', ', $application->candidate_name) : ($application->candidate_name ?? 'N/A')) }}
                                    </h2>
                                    <p class="app-details-email">
                                        <i class="far fa-envelope" style="margin-right: 0.5rem;"></i>
                                        {{ $application->user ? (is_array($application->user->email) ? implode(', ', $application->user->email) : $application->user->email) : (is_array($application->candidate_email) ? implode(', ', $application->candidate_email) : ($application->candidate_email ?? 'N/A')) }}
                                    </p>
                                    @php 
                                        $stageVal = is_array($application->stage) ? implode(', ', $application->stage) : ($application->stage ?? 'unknown');
                                        $badgeClass = match($stageVal) {
                                            'hired' => 'app-details-badge-success',
                                            'rejected' => 'app-details-badge-danger',
                                            'offer' => 'app-details-badge-info',
                                            'interview' => 'app-details-badge-warning',
                                            default => 'app-details-badge-primary'
                                        };
                                    @endphp
                                    <span class="app-details-badge {{ $badgeClass }}">{{ ucfirst($stageVal) }}</span>
                                </div>
                                @if($application->resume_url)
                                <a href="{{ route('recruitment.applications.download-resume', $application->id) }}" class="app-details-btn">
                                    <i class="fas fa-download" style="margin-right: 0.5rem;"></i>Resume
                                </a>
                                @endif
                            </div>

                            <div class="app-details-info-grid">
                                <div class="app-details-info-item">
                                    <label>Job Title</label>
                                    <p>{{ $application->job ? (is_array($application->job->title) ? implode(', ', $application->job->title) : $application->job->title) : 'N/A' }}</p>
                                </div>
                                <div class="app-details-info-item">
                                    <label>Applied Date</label>
                                    <p>{{ $application->applied_on && !is_array($application->applied_on) ? $application->applied_on->format('M d, Y') : 'N/A' }}</p>
                                </div>
                                @if($application->job)
                                <div class="app-details-info-item">
                                    <label>Job Type</label>
                                    <p>{{ ucfirst(str_replace('_', ' ', is_array($application->job->job_type) ? implode(', ', $application->job->job_type) : ($application->job->job_type ?? 'N/A'))) }}</p>
                                </div>
                                <div class="app-details-info-item">
                                    <label>Location</label>
                                    <p>{{ is_array($application->job->location) ? implode(', ', $application->job->location) : ($application->job->location ?? 'N/A') }}</p>
                                </div>
                                @endif
                            </div>

                            @if($application->notes)
                            <div style="margin-top: 1.5rem; padding: 1rem; background: #F9FAFB; border-radius: 0.5rem; border: 1px solid #E5E7EB;">
                                <label style="font-size: 0.75rem; font-weight: 600; color: #6B7280; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">
                                    <i class="fas fa-sticky-note" style="margin-right: 0.5rem;"></i>Notes
                                </label>
                                <p style="margin: 0; color: #374151;">{{ is_array($application->notes) ? implode(', ', $application->notes) : $application->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Job Details -->
                    @if($application->job && ($application->job->description || $application->job->requirements))
                    <div class="app-details-card">
                        <div class="app-details-card-header">
                            <h3 class="app-details-card-title">
                                <i class="fas fa-briefcase" style="margin-right: 0.5rem; color: #2563EB;"></i>Job Details
                            </h3>
                        </div>
                        <div class="app-details-card-body">
                            @if($application->job->description)
                            <div style="margin-bottom: 1.5rem;">
                                <label style="font-size: 0.75rem; font-weight: 600; color: #6B7280; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Description</label>
                                <div style="color: #374151; line-height: 1.6;">{!! nl2br(e(is_array($application->job->description) ? implode(', ', $application->job->description) : $application->job->description)) !!}</div>
                            </div>
                            @endif
                            
                            @if($application->job->requirements)
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #6B7280; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Requirements</label>
                                @if(is_array($application->job->requirements))
                                <ul style="margin: 0; padding-left: 1.5rem; color: #374151;">
                                    @foreach($application->job->requirements as $requirement)
                                    <li style="margin-bottom: 0.5rem;">{{ $requirement }}</li>
                                    @endforeach
                                </ul>
                                @else
                                <div style="color: #374151; line-height: 1.6;">{!! nl2br(e($application->job->requirements)) !!}</div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Timeline -->
                    @if($application->logs && $application->logs->count() > 0)
                    <div class="app-details-card">
                        <div class="app-details-card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="app-details-card-title">
                                    <i class="fas fa-history" style="margin-right: 0.5rem; color: #2563EB;"></i>Activity Timeline
                                </h3>
                                <span class="app-details-badge app-details-badge-primary">{{ $application->logs->count() }} Events</span>
                            </div>
                        </div>
                        <div class="app-details-card-body">
                            <div class="app-details-timeline">
                                @foreach($application->logs as $index => $log)
                                @php
                                    $icon = 'fa-circle-dot';
                                    $markerClass = 'app-details-timeline-marker-primary';
                                    
                                    if (str_contains($log->action, 'note')) {
                                        $icon = 'fa-sticky-note';
                                        $markerClass = 'app-details-timeline-marker-warning';
                                    } elseif (str_contains($log->action, 'interview')) {
                                        $icon = 'fa-calendar-check';
                                        $markerClass = 'app-details-timeline-marker-success';
                                    } elseif (str_contains($log->action, 'offer')) {
                                        $icon = 'fa-file-contract';
                                        $markerClass = 'app-details-timeline-marker-info';
                                    } elseif (str_contains($log->action, 'hire')) {
                                        $icon = 'fa-user-check';
                                        $markerClass = 'app-details-timeline-marker-success';
                                    } elseif (str_contains($log->action, 'reject')) {
                                        $icon = 'fa-times-circle';
                                        $markerClass = 'app-details-timeline-marker-danger';
                                    }
                                @endphp
                                
                                <div class="app-details-timeline-item">
                                    <div class="app-details-timeline-marker {{ $markerClass }}">
                                        <i class="fas {{ $icon }}"></i>
                                    </div>
                                    <div class="app-details-timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h4 class="app-details-timeline-title">
                                                {{ is_array($log->description) ? implode(', ', $log->description) : ($log->description ?? 'No description') }}
                                            </h4>
                                            @if($index === 0)
                                            <span class="app-details-badge app-details-badge-success" style="font-size: 0.75rem; padding: 0.25rem 0.75rem;">Latest</span>
                                            @endif
                                        </div>
                                        <div class="app-details-timeline-meta">
                                            <span><i class="fas fa-user" style="margin-right: 0.25rem;"></i>{{ $log->changedBy ? (is_array($log->changedBy->name) ? implode(', ', $log->changedBy->name) : $log->changedBy->name) : 'System' }}</span>
                                            <span>
                                                <i class="far fa-clock" style="margin-right: 0.25rem;"></i>
                                                @if($log->created_at && !is_array($log->created_at) && $log->created_at->year > 0)
                                                    {{ $log->created_at->format('M d, Y \a\t H:i') }}
                                                @else
                                                    <span style="color: #DC2626;">Invalid Date</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="app-details-sidebar">
                        <!-- Actions -->
                        <div class="app-details-card">
                            <div class="app-details-card-header">
                                <h3 class="app-details-card-title">
                                    <i class="fas fa-bolt" style="margin-right: 0.5rem; color: #2563EB;"></i>Quick Actions
                                </h3>
                            </div>
                            <div class="app-details-card-body" style="padding: 1rem;">
                                @php 
                                    $isRejected = (is_array($application->stage) ? in_array('rejected', $application->stage) : $application->stage == 'rejected');
                                    $isHired = (is_array($application->stage) ? in_array('hired', $application->stage) : $application->stage == 'hired');
                                @endphp

                                @if(!$isRejected && !$isHired)
                                <button type="button" class="app-details-action-btn app-details-action-btn-primary" onclick="openStageModal()">
                                    <i class="fas fa-exchange-alt"></i>Update Stage
                                </button>
                                
                                <button type="button" class="app-details-action-btn app-details-action-btn-outline" onclick="openNotesModal()">
                                    <i class="fas fa-sticky-note"></i>Add Note
                                </button>
                                
                                <a href="{{ route('recruitment.interviews.create', ['application_id' => $application->id]) }}" class="app-details-action-btn app-details-action-btn-outline" style="text-decoration: none;">
                                    <i class="fas fa-calendar-plus"></i>Schedule Interview
                                </a>
                                
                                <hr style="margin: 1rem 0; border-color: #E5E7EB;">
                                
                                <button type="button" class="app-details-action-btn app-details-action-btn-danger" onclick="confirmReject()">
                                    <i class="fas fa-user-times"></i>Reject Candidate
                                </button>
                                @elseif($isHired)
                                <div class="alert alert-success mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle fs-4 me-3"></i>
                                        <div>
                                            <strong class="d-block">Candidate Hired!</strong>
                                            <small>The hiring process is complete.</small>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-danger mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                                        <div>
                                            <strong class="d-block">Application Rejected</strong>
                                            <small>No further actions available.</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="app-details-card">
                            <div class="app-details-card-header">
                                <h3 class="app-details-card-title">
                                    <i class="fas fa-chart-bar" style="margin-right: 0.5rem; color: #2563EB;"></i>Statistics
                                </h3>
                            </div>
                            <div class="app-details-card-body" style="padding: 1rem;">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="app-details-stat-box app-details-stat-box-success">
                                            <div class="app-details-stat-value app-details-stat-value-success">{{ $application->interviews->count() }}</div>
                                            <div class="app-details-stat-label">Interviews</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="app-details-stat-box app-details-stat-box-primary">
                                            <div class="app-details-stat-value app-details-stat-value-primary">{{ $application->logs->count() }}</div>
                                            <div class="app-details-stat-label">Activities</div>
                                        </div>
                                    </div>
                                    @if($application->score)
                                    <div class="col-12">
                                        <div class="app-details-stat-box app-details-stat-box-warning">
                                            <div class="app-details-stat-value app-details-stat-value-warning">{{ number_format($application->score, 1) }}/100</div>
                                            <div class="app-details-stat-label">Overall Score</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals (keeping existing structure) -->
<div class="modal fade" id="stageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Application Stage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stageForm" method="POST" action="{{ route('recruitment.applications.move-stage', $application->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="stage" class="form-label">Select New Stage</label>
                        <select class="form-select" id="stage" name="stage" required>
                            <option value="">Choose Stage</option>
                            @php $currentStage = is_array($application->stage) ? implode(', ', $application->stage) : $application->stage; @endphp
                            <option value="applied" {{ $currentStage == 'applied' ? 'selected' : '' }}>Applied</option>
                            <option value="screening" {{ $currentStage == 'screening' ? 'selected' : '' }}>Screening</option>
                            <option value="shortlisted" {{ $currentStage == 'shortlisted' ? 'selected' : '' }}>Shortlisted</option>
                            <option value="interview" {{ $currentStage == 'interview' ? 'selected' : '' }}>Interview</option>
                            <option value="offer" {{ $currentStage == 'offer' ? 'selected' : '' }}>Offer</option>
                            <option value="hired" {{ $currentStage == 'hired' ? 'selected' : '' }}>Hired</option>
                            <option value="rejected" {{ $currentStage == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="stage_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="stage_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="notesForm" method="POST" action="{{ route('recruitment.applications.add-notes', $application->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="application_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="application_notes" name="notes" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Notes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openStageModal() {
    var modal = new bootstrap.Modal(document.getElementById('stageModal'));
    modal.show();
}

function openNotesModal() {
    var modal = new bootstrap.Modal(document.getElementById('notesModal'));
    modal.show();
}

@if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        // Handle other validation errors if needed
    });
@endif

function confirmReject() {
    if (confirm('Are you sure you want to reject this candidate? This action cannot be undone.')) {
        document.getElementById('stage').value = 'rejected';
        document.getElementById('stageForm').submit();
    }
}
</script>
@endsection