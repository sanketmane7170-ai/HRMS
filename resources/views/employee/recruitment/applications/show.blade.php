@extends('layouts.backend')

@section('title', 'Application Details')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
@php
    $stageDates = [];
    // Process logs from oldest to newest to get the first time each stage was reached
    foreach($application->logs->reverse() as $log) {
        if ($log->new_stage && !isset($stageDates[$log->new_stage])) {
            $stageDates[$log->new_stage] = $log->created_at;
        }
    }
    // Fallback for 'applied' if not in logs
    if (!isset($stageDates['applied'])) {
        $stageDates['applied'] = $application->applied_on ?? $application->created_at;
    }
@endphp
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-dark fw-bold">Application Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.employee.applications.index')}}">My Applications</a></li>
                        <li class="breadcrumb-item active text-muted">Application Details</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.employee.applications.index') }}" class="btn btn-light border rounded-0 shadow-none px-4" style="border-color: #e2e8f0 !important;">
                        <i class="fas fa-arrow-left me-1"></i> Back to Applications
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <!-- Application Overview Card -->
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0 text-dark fw-bold">{{ $application->job->title }}</h4>
                            <span class="badge bg-{{ 
                                $application->current_stage == 'applied' ? 'warning' : 
                                ($application->current_stage == 'screening' ? 'info' :
                                ($application->current_stage == 'shortlisted' ? 'info' : 
                                ($application->current_stage == 'interview' ? 'primary' : 
                                ($application->current_stage == 'offer' ? 'success' :
                                ($application->current_stage == 'hired' ? 'success' : 'danger'))))) 
                            }} text-white rounded-0 px-3 py-2">
                                {{ ucfirst(str_replace('_', ' ', $application->current_stage)) }}
                            </span>
                        </div>
                        
                        <!-- Application Progress Bar -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small fw-bold">Application Progress</span>
                                <span class="text-dark small fw-bold">
                                    @php
                                        $progress = 0;
                                        switch($application->current_stage) {
                                            case 'applied': $progress = 20; break;
                                            case 'screening': $progress = 40; break;
                                            case 'shortlisted': $progress = 60; break;
                                            case 'interview': $progress = 80; break;
                                            case 'offer': $progress = 90; break;
                                            case 'hired': $progress = 100; break;
                                            case 'rejected': $progress = 0; break;
                                        }
                                    @endphp
                                    {{ $application->current_stage == 'rejected' ? 'Not Selected' : $progress . '%' }}
                                </span>
                            </div>
                            <div class="progress rounded-0" style="height: 10px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-{{ $application->current_stage == 'rejected' ? 'danger' : ($progress == 100 ? 'success' : 'primary') }} shadow-none" 
                                     style="width: {{ $application->current_stage == 'rejected' ? '100' : $progress }}%"
                                     role="progressbar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="text-muted small d-block mb-1">Department</label>
                                    <p class="text-dark fw-bold mb-0"><i class="fas fa-building text-primary me-2"></i>{{ $application->job->department->name ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small d-block mb-1">Job Type</label>
                                    <p class="text-dark fw-bold mb-0"><i class="fas fa-briefcase text-primary me-2"></i>{{ ucfirst(str_replace('_', ' ', $application->job->hiring_type)) }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="text-muted small d-block mb-1">Applied On</label>
                                    <p class="text-dark fw-bold mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>{{ $application->applied_on ? \Carbon\Carbon::parse($application->applied_on)->format('M d, Y \a\t g:i A') : $application->created_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small d-block mb-1">Last Updated</label>
                                    <p class="text-dark fw-bold mb-0"><i class="fas fa-clock text-primary me-2"></i>{{ $application->updated_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        @if($application->notes)
                        <div class="mb-4">
                            <h5 class="text-dark fw-bold mb-3">Cover Letter / Notes</h5>
                            <div class="p-3 bg-light border rounded-0 text-dark" style="border-color: #f1f5f9 !important; white-space: pre-line;">
                                {!! nl2br(e($application->notes)) !!}
                            </div>
                        </div>
                        @endif


                    </div>
                </div>

                <!-- Interview Information -->
                @if($application->interviews && $application->interviews->count() > 0)
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <h5 class="card-title mb-0 text-dark fw-bold">Interview Schedule</h5>
                    </div>
                    <div class="card-body">
                        @foreach($application->interviews as $interview)
                        <div class="p-3 mb-3 bg-light border rounded-0 {{ $loop->last ? 'mb-0' : '' }}" style="border-color: #f1f5f9 !important;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-dark fw-bold mb-0">{{ $interview->title ?? 'Interview Round ' . $loop->iteration }}</h6>
                                <span class="badge bg-{{ 
                                    $interview->status == 'scheduled' ? 'primary' : 
                                    ($interview->status == 'rescheduled' ? 'warning' :
                                    ($interview->status == 'completed' ? 'success' : 
                                    ($interview->status == 'cancelled' ? 'danger' : 'secondary'))) 
                                }} text-white rounded-0">
                                    {{ ucfirst($interview->status) }}
                                </span>
                            </div>
                            <div class="row small mt-3">
                                <div class="col-md-4">
                                    <p class="text-dark mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i>{{ Carbon\Carbon::parse($interview->scheduled_at)->format('M d, Y') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-dark mb-1"><i class="fas fa-clock text-primary me-2"></i>{{ Carbon\Carbon::parse($interview->scheduled_at)->format('g:i A') }}</p>
                                </div>
                                @if($interview->location)
                                <div class="col-md-4">
                                    <p class="text-dark mb-1"><i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $interview->location }}</p>
                                </div>
                                @endif
                            </div>
                            @if($interview->notes)
                            <div class="mt-3 p-2 border-top border-light">
                                <p class="text-muted small mb-0">{{ $interview->notes }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <!-- Application Status Timeline -->
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <h5 class="card-title mb-0 text-dark fw-bold">Application Status</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="timeline-container px-4 py-4">
                            <!-- Application Submitted -->
                            <div class="timeline-box {{ in_array($application->current_stage, ['applied', 'screening', 'shortlisted', 'interview', 'offer', 'hired']) ? 'active' : '' }}">
                                <div class="timeline-point"></div>
                                <div class="timeline-info">
                                    <h6 class="text-dark fw-bold mb-1">Application Submitted</h6>
                                    <p class="text-muted small mb-0">{{ $application->applied_on ? \Carbon\Carbon::parse($application->applied_on)->format('M d, Y g:i A') : $application->created_at->format('M d, Y g:i A') }}</p>
                                    @if($application->current_stage == 'applied')
                                        <p class="text-primary small mt-1 fw-bold"><i class="fas fa-hourglass-half me-1"></i> Under Initial Review</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Screening -->
                            <div class="timeline-box {{ in_array($application->current_stage, ['screening', 'shortlisted', 'interview', 'offer', 'hired']) ? 'active' : '' }}">
                                <div class="timeline-point"></div>
                                <div class="timeline-info">
                                    <h6 class="text-dark fw-bold mb-1">Screening</h6>
                                    @if(isset($stageDates['screening']))
                                        <p class="text-muted small mb-0">{{ $stageDates['screening']->format('M d, Y g:i A') }}</p>
                                    @else
                                        <p class="text-muted small mb-0">Initial review by HR</p>
                                    @endif
                                    
                                    @if($application->current_stage == 'screening')
                                        <p class="text-primary small mt-1 fw-bold"><i class="fas fa-search me-1"></i> Screening in Progress</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Shortlisted -->
                            <div class="timeline-box {{ in_array($application->current_stage, ['shortlisted', 'interview', 'offer', 'hired']) ? 'active' : '' }}">
                                <div class="timeline-point"></div>
                                <div class="timeline-info">
                                    <h6 class="text-dark fw-bold mb-1">Shortlisted</h6>
                                    @if(isset($stageDates['shortlisted']))
                                        <p class="text-muted small mb-0">{{ $stageDates['shortlisted']->format('M d, Y g:i A') }}</p>
                                    @else
                                        <p class="text-muted small mb-0">Qualified for next steps</p>
                                    @endif

                                    @if($application->current_stage == 'shortlisted')
                                        <p class="text-success small mt-1 fw-bold"><i class="fas fa-star me-1"></i> You've been shortlisted!</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Interview -->
                            <div class="timeline-box {{ in_array($application->current_stage, ['interview', 'offer', 'hired']) ? 'active' : '' }}">
                                <div class="timeline-point"></div>
                                <div class="timeline-info">
                                    <h6 class="text-dark fw-bold mb-1">Interview Round</h6>
                                    @if(isset($stageDates['interview']))
                                        <p class="text-muted small mb-0">{{ $stageDates['interview']->format('M d, Y g:i A') }}</p>
                                    @else
                                        <p class="text-muted small mb-0">Technical or HR discussion</p>
                                    @endif

                                    @if($application->current_stage == 'interview')
                                        <p class="text-primary small mt-1 fw-bold"><i class="fas fa-calendar-check me-1"></i> Interview Phase</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Offer -->
                            <div class="timeline-box {{ in_array($application->current_stage, ['offer', 'hired']) ? 'active' : '' }}">
                                <div class="timeline-point"></div>
                                <div class="timeline-info">
                                    <h6 class="text-dark fw-bold mb-1">Job Offer</h6>
                                    @if(isset($stageDates['offer']))
                                        <p class="text-muted small mb-0">{{ $stageDates['offer']->format('M d, Y g:i A') }}</p>
                                    @else
                                        <p class="text-muted small mb-0">Final decision made</p>
                                    @endif

                                    @if($application->current_stage == 'offer')
                                        <p class="text-success small mt-1 fw-bold"><i class="fas fa-award me-1"></i> Offer Extended!</p>
                                    @endif
                                </div>
                            </div>

                            @if($application->current_stage == 'rejected')
                            <div class="timeline-box active rejected">
                                <div class="timeline-point bg-danger border-danger"></div>
                                <div class="timeline-info">
                                    <h6 class="text-danger fw-bold mb-1">Application Closed</h6>
                                    <p class="text-muted small mb-0">Not selected this time</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Offer Details Section -->
                @if($application->offers->isNotEmpty())
                @php $latestOffer = $application->offers->first(); @endphp
                <div class="card bg-white border border-success shadow-none rounded-0 mb-4" style="border-width: 2px !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <h5 class="card-title mb-0 text-success fw-bold">
                            <i class="fas fa-gift me-2"></i>Job Offer Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 text-center pb-3 border-bottom border-light">
                            <h2 class="text-dark fw-bold mb-1">{{ number_format($latestOffer->salary, 2) }} {{ $latestOffer->currency ?? 'USD' }}</h2>
                            <p class="text-muted small uppercase fw-bold mb-0">Annual Salary Package</p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="text-muted small d-block mb-1">Joining Date</label>
                                <p class="text-dark fw-bold mb-0 small">{{ $latestOffer->joining_date ? \Carbon\Carbon::parse($latestOffer->joining_date)->format('M d, Y') : 'TBD' }}</p>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small d-block mb-1">Status</label>
                                @if($latestOffer->status === 'pending')
                                    <span class="badge bg-warning text-white rounded-0 btn-sm p-1 px-2">Pending</span>
                                @elseif($latestOffer->status === 'sent')
                                    <span class="badge bg-info text-white rounded-0 btn-sm p-1 px-2">Sent</span>
                                @elseif($latestOffer->status === 'accepted')
                                    <span class="badge bg-success text-white rounded-0 btn-sm p-1 px-2">Accepted</span>
                                @else
                                    <span class="badge bg-danger text-white rounded-0 btn-sm p-1 px-2">{{ ucfirst($latestOffer->status ?: 'Declined') }}</span>
                                @endif
                            </div>
                        </div>

                        @if($latestOffer->status === 'pending' || $latestOffer->status === 'sent')
                        <div class="mt-4 pt-3 border-top border-light">
                            <button type="button" class="btn btn-success rounded-0 w-100 mb-2 shadow-none" onclick="respondToOffer({{ $latestOffer->id }}, 'accepted')">
                                <i class="fas fa-check me-2"></i> Accept Offer
                            </button>
                            <button type="button" class="btn btn-outline-danger rounded-0 w-100 shadow-none" onclick="respondToOffer({{ $latestOffer->id }}, 'declined')">
                                <i class="fas fa-times me-2"></i> Decline Offer
                            </button>
                        </div>
                        @else
                        <div class="mt-4 alert {{ $latestOffer->status === 'accepted' ? 'alert-success' : 'alert-secondary' }} rounded-0 mb-0 py-2 border-0">
                            <p class="mb-0 small text-center fw-bold">
                                <i class="fas {{ $latestOffer->status === 'accepted' ? 'fa-check-circle' : 'fa-info-circle' }} me-2"></i>
                                You have {{ $latestOffer->status }} this offer.
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Job Information Card -->
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <h5 class="card-title mb-0 text-dark fw-bold">Job Information</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-dark fw-bold mb-2">{{ $application->job->title }}</h6>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-building me-2"></i> {{ $application->job->department->name ?? 'N/A' }} | 
                            <i class="fas fa-map-marker-alt ms-2 me-2"></i> HQ Office
                        </p>
                        <a href="{{ route('backend.employee.jobs.show', $application->job->id) }}" class="btn btn-outline-primary btn-sm rounded-0 w-100 shadow-none border">
                            <i class="fas fa-external-link-alt me-2"></i> Full Job Description
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Timeline Style */
.timeline-container {
    position: relative;
}
.timeline-box {
    position: relative;
    padding-left: 35px;
    margin-bottom: 25px;
    border-left: 2px solid #e2e8f0;
}
.timeline-box:last-child {
    margin-bottom: 0;
}
.timeline-point {
    position: absolute;
    left: -8px;
    top: 5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #e2e8f0;
    z-index: 10;
}
.timeline-box.active {
    border-left-color: #2b6cb0;
}
.timeline-box.active .timeline-point {
    border-color: #2b6cb0;
    background: #2b6cb0;
    box-shadow: 0 0 0 4px rgba(43, 108, 176, 0.1);
}
.timeline-box.rejected {
    border-left-color: #e53e3e;
}

/* Card Improvements */
.card-header {
    padding: 1.25rem 1.5rem;
}
.card-body {
    padding: 1.5rem;
    color: #1a202c;
}
.breadcrumb-item.active {
    color: #718096 !important;
}
.badge {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.alert-success { background-color: #f0fff4; color: #22543d; }
.alert-secondary { background-color: #f7fafc; color: #2d3748; }
</style>


<script>
function respondToOffer(offerId, response) {
    if (!confirm('Are you sure you want to ' + response + ' this offer?')) {
        return;
    }

    // Show loading state
    const buttons = document.querySelectorAll('button[onclick*="' + offerId + '"]');
    buttons.forEach(button => {
        button.disabled = true;
        if (button.textContent.includes(response === 'accepted' ? 'Accept' : 'Decline')) {
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
        }
    });

    // Make AJAX request
    fetch('/employee/applications/' + offerId + '/respond', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: response
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => { window.location.reload(); }, 1500);
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Failed to process offer response.', 'error');
        buttons.forEach(button => {
            button.disabled = false;
            if (button.textContent.includes('Processing')) {
                if (response === 'accepted') {
                    button.innerHTML = '<i class="fas fa-check me-2"></i> Accept Offer';
                } else {
                    button.innerHTML = '<i class="fas fa-times me-2"></i> Decline Offer';
                }
            }
        });
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = 'alert alert-' + (type === 'error' ? 'danger' : type) + ' alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; border-radius: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);';
    notification.innerHTML = 
        '<div class="d-flex align-items-center">' +
            '<i class="fas fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + ' me-2"></i>' +
            '<span>' + message + '</span>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" style="padding: 1rem 1rem;"></button>' +
        '</div>';
    document.body.appendChild(notification);
    setTimeout(() => { if (notification.parentNode) notification.remove(); }, 5000);
}
</script>
@endsection
