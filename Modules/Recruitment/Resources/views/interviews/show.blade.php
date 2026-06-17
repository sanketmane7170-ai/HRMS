@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper recruitment-page">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('interview_details') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.dashboard') }}">{{ __trans('recruitment') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.interviews.index') }}">{{ __trans('interviews') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('details') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('recruitment.interviews.index') }}" class="btn btn-outline-primary">
                        <i class="fa fa-arrow-left mr-1"></i> {{ __trans('back_to_interviews') }}
                    </a>
                    @if(auth()->user() && (auth()->user()->hasRole(['admin', 'hr', 'HR Manager']) || auth()->user()->can('Manage Interviews')))
                        <a href="{{ route('recruitment.interviews.edit', $interview->id) }}" class="btn btn-primary">
                            <i class="fa fa-edit mr-1"></i> {{ __trans('edit_interview') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Interview Details -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __trans('interview_information') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>{{ __trans('candidate') }}:</strong></label>
                                    <p>{{ $interview->application->user->name ?? $interview->application->candidate_name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>{{ __trans('job_title') }}:</strong></label>
                                    <p>{{ optional($interview->application)->job->title ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>{{ __trans('interviewer') }}:</strong></label>
                                    <p>{{ $interview->interviewer->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>{{ __trans('scheduled_date') }}:</strong></label>
                                    <p>{{ $interview->scheduled_at ? $interview->scheduled_at->format('M d, Y H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>{{ __trans('type') }}:</strong></label>
                                    <p>
                                        @php
                                            $typeClass = ['phone' => 'badge-info', 'video' => 'badge-primary', 'in_person' => 'badge-success'][$interview->type ?? 'phone'] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $typeClass }}">{{ ucfirst(str_replace('_', ' ', $interview->type ?? 'phone')) }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>{{ __trans('status') }}:</strong></label>
                                    <p>
                                        @php
                                            $statusClass = ['scheduled' => 'badge-warning', 'completed' => 'badge-success', 'cancelled' => 'badge-danger'][$interview->status] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($interview->status) }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>{{ __trans('duration') }}:</strong></label>
                                    <p>{{ $interview->duration_minutes }} minutes</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Round:</strong></label>
                                    <p>
                                        <span class="badge badge-primary">{{ $interview->round_label }}</span>
                                        @if($interview->score)
                                            <span class="badge badge-info ml-2">Score: {{ $interview->score }}/10</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($interview->location)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>{{ __trans('location') }}:</strong></label>
                                    <p>{{ $interview->location }}</p>
                                </div>
                            </div>
                            @endif
                            @if($interview->meeting_link)
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><strong>{{ __trans('meeting_link') }}:</strong></label>
                                    <p><a href="{{ $interview->meeting_link }}" target="_blank">{{ $interview->meeting_link }}</a></p>
                                </div>
                            </div>
                            @endif
                            @if($interview->agenda)
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><strong>{{ __trans('agenda') }}:</strong></label>
                                    <p>{{ $interview->agenda }}</p>
                                </div>
                            </div>
                            @endif
                            @if($interview->preparation_notes)
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><strong>{{ __trans('preparation_notes') }}:</strong></label>
                                    <p>{{ $interview->preparation_notes }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Multi-Round Interview System -->
                @php
                    $allRounds = $interview->getAllRounds();
                    $canScheduleNext = \Modules\Recruitment\Entities\Interview::canScheduleNextRound($interview->application_id);
                    $nextRoundNumber = \Modules\Recruitment\Entities\Interview::getNextRoundNumber($interview->application_id);
                @endphp
                
                @if($allRounds->count() > 1 || $canScheduleNext)
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="fa fa-layer-group mr-2"></i>Interview Rounds
                            </h4>
                            @if($canScheduleNext && auth()->user() && (auth()->user()->hasRole(['admin', 'hr', 'HR Manager']) || auth()->user()->can('Manage Interviews')))
                                <!-- Debug info -->
                                <div class="mb-2 small text-muted">
                                    Debug: App ID={{ $interview->application_id }}, Next Round={{ $nextRoundNumber }}, Can Schedule={{ $canScheduleNext ? 'true' : 'false' }}
                                </div>
                                <button class="btn btn-success btn-sm" onclick="scheduleNextRound({{ $interview->application_id }}, {{ $nextRoundNumber }}); return false;">
                                    <i class="fa fa-plus mr-1"></i>Schedule Round {{ $nextRoundNumber }}
                                </button>
                                <!-- Test button -->
                                <button class="btn btn-warning btn-sm ml-2" onclick="console.log('Test button clicked'); alert('Test button works!'); return false;">
                                    <i class="fa fa-bug mr-1"></i>Test
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($allRounds as $round)
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ $round->round_label }}</h6>
                                            <span class="badge {{ ['scheduled' => 'badge-warning', 'completed' => 'badge-success', 'cancelled' => 'badge-danger'][$round->status] ?? 'badge-secondary' }}">
                                                {{ ucfirst($round->status) }}
                                            </span>
                                        </div>
                                        <p class="card-text small mb-1">
                                            <strong>Date:</strong> {{ $round->scheduled_at ? $round->scheduled_at->format('M d, Y H:i') : 'N/A' }}
                                        </p>
                                        <p class="card-text small mb-1">
                                            <strong>Interviewer:</strong> {{ $round->interviewer->name ?? 'N/A' }}
                                        </p>
                                        <p class="card-text small mb-1">
                                            <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $round->type)) }}
                                        </p>
                                        @if($round->status === 'completed')
                                            @if($round->score)
                                                <p class="card-text small mb-1">
                                                    <strong>Score:</strong> <span class="badge badge-info">{{ $round->score }}/10</span>
                                                </p>
                                            @endif
                                            @if($round->overall_rating)
                                                <p class="card-text small mb-1">
                                                    <strong>Rating:</strong> <span class="badge badge-info">{{ $round->overall_rating }}/10</span>
                                                </p>
                                            @endif
                                            @if($round->feedback && $round->feedback->recommendation)
                                                <p class="card-text small mb-0">
                                                    <strong>Result:</strong> 
                                                    <span class="badge {{ ['hire' => 'badge-success', 'reject' => 'badge-danger', 'next_round' => 'badge-primary'][$round->feedback->recommendation] ?? 'badge-secondary' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $round->feedback->recommendation)) }}
                                                    </span>
                                                </p>
                                            @endif
                                        @endif
                                        @if($round->id === $interview->id)
                                            <div class="mt-2">
                                                <a href="{{ route('recruitment.interviews.show', $round->id) }}" class="btn btn-primary btn-xs">Current</a>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <a href="{{ route('recruitment.interviews.show', $round->id) }}" class="btn btn-outline-primary btn-xs">View Details</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Interview Completion Status -->
                @if($interview->status === 'completed' && $interview->feedback)
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-check-circle mr-2"></i>Interview Evaluation
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Interview Completed:</strong>
                                    <span class="badge badge-success ml-2">Yes</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Interview Score:</strong>
                                    <span class="h5 text-primary ml-2">
                                        {{ $interview->feedback->overall_rating ?? 'N/A' }}/10
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>Recommendation:</strong>
                                    <br>
                                    @php
                                        $recClass = [
                                            'highly_recommended' => 'badge-success',
                                            'recommended' => 'badge-info', 
                                            'maybe' => 'badge-warning',
                                            'not_recommended' => 'badge-danger'
                                        ][$interview->feedback->recommendation ?? ''] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $recClass }} mt-1">
                                        {{ ucfirst(str_replace('_', ' ', $interview->feedback->recommendation ?? 'Not Set')) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Recommended By:</strong>
                                    <br>{{ $interview->interviewer->name ?? 'N/A' }}
                                </div>
                                <div class="mb-3">
                                    <strong>Completed At:</strong>
                                    <br>{{ $interview->feedback->completed_at ? $interview->feedback->completed_at->format('M d, Y H:i') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                        
                        @if($interview->feedback->interviewer_observations)
                        <div class="mt-3">
                            <strong>Interview Feedback:</strong>
                            <div class="bg-light p-3 rounded mt-2">
                                <p class="mb-0 text-dark">{{ $interview->feedback->interviewer_observations }}</p>
                            </div>
                        </div>
                        @endif

                        @if($interview->feedback->follow_up_actions)
                        <div class="mt-3">
                            <strong>Next Steps:</strong>
                            <div class="bg-light p-3 rounded mt-2">
                                <p class="mb-0 text-dark">{{ $interview->feedback->follow_up_actions }}</p>
                            </div>
                        </div>
                        @endif

                        @if($interview->feedback->positive_highlights)
                        <div class="mt-3">
                            <strong>Positive Highlights:</strong>
                            <div class="bg-success bg-opacity-10 p-3 rounded mt-2">
                                <p class="mb-0 text-success">{{ $interview->feedback->positive_highlights }}</p>
                            </div>
                        </div>
                        @endif

                        @if($interview->feedback->concerns_raised)
                        <div class="mt-3">
                            <strong>Concerns Raised:</strong>
                            <div class="bg-warning bg-opacity-10 p-3 rounded mt-2">
                                <p class="mb-0 text-warning">{{ $interview->feedback->concerns_raised }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @elseif($interview->status === 'scheduled')
                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-clock mr-2"></i>Interview Status
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="mb-3">
                                <strong>Interview Completed:</strong>
                                <span class="badge badge-warning ml-2">Pending</span>
                            </div>
                            <p class="text-muted">Interview is scheduled but not yet completed. Evaluation details will appear here once the interview is marked as complete.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __trans('actions') }}</h4>
                    </div>
                    <div class="card-body">
                        @if($interview->status === 'scheduled' && auth()->user() && (auth()->user()->hasRole(['admin', 'hr', 'HR Manager']) || auth()->user()->can('Manage Interviews')))
                            <button type="button" class="btn btn-success btn-block mb-2" onclick="showCompleteModal()">
                                <i class="fa fa-check mr-1"></i> {{ __trans('complete_interview') }}
                            </button>
                            <button type="button" class="btn btn-warning btn-block mb-2" onclick="showRescheduleModal()">
                                <i class="fa fa-calendar mr-1"></i> {{ __trans('reschedule_interview') }}
                            </button>
                            <button type="button" class="btn btn-danger btn-block" onclick="showCancelModal()">
                                <i class="fa fa-times mr-1"></i> {{ __trans('cancel_interview') }}
                            </button>
                        @endif
                    </div>
                </div>
                
                @if($interview->application)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __trans('application_details') }}</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>{{ __trans('application_date') }}:</strong><br>
                        {{ $interview->application->applied_on ? $interview->application->applied_on->format('M d, Y') : 'N/A' }}</p>
                        
                        <p><strong>{{ __trans('current_stage') }}:</strong><br>
                        <span class="badge badge-info">{{ ucfirst($interview->application->current_stage ?? 'applied') }}</span></p>
                        
                        @if($interview->application->notes)
                        <p><strong>{{ __trans('notes') }}:</strong><br>
                        {{ $interview->application->notes }}</p>
                        @endif
                        
                        <a href="{{ route('recruitment.applications.show', $interview->application->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-eye mr-1"></i> {{ __trans('view_application') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Complete Interview Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="completeModalLabel">
                    <i class="fa fa-check mr-2"></i>Complete Interview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="completeForm" action="{{ route('recruitment.interviews.complete', $interview->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="score" class="form-label">Interview Score (1-10)</label>
                                <input type="number" name="score" id="score" class="form-control" min="1" max="10" required>
                                <div class="form-text">Rate the candidate's performance</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="recommendation" class="form-label">Recommendation</label>
                                <select name="recommendation" id="recommendation" class="form-control" required>
                                    <option value="">Select recommendation</option>
                                    <option value="hire">Hire</option>
                                    <option value="reject">Reject</option>
                                    <option value="second_interview">Second Interview</option>
                                    <option value="on_hold">Put On Hold</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="feedback" class="form-label">Interview Feedback</label>
                                <textarea name="feedback" id="feedback" class="form-control" rows="4" required placeholder="Provide detailed feedback about the interview..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="next_steps" class="form-label">Next Steps</label>
                                <textarea name="next_steps" id="next_steps" class="form-control" rows="3" placeholder="What should happen next?"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check mr-1"></i>Complete Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reschedule Interview Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="rescheduleModalLabel">
                    <i class="fa fa-calendar mr-2"></i>Reschedule Interview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rescheduleForm" action="{{ route('recruitment.interviews.reschedule', $interview->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Current Schedule:</strong> {{ $interview->scheduled_at ? $interview->scheduled_at->format('M d, Y H:i') : 'N/A' }}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="new_date" class="form-label">New Date</label>
                                <input type="date" name="new_date" id="new_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="new_time" class="form-label">New Time</label>
                                <input type="time" name="new_time" id="new_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="reschedule_reason" class="form-label">Reason for Rescheduling</label>
                                <textarea name="reschedule_reason" id="reschedule_reason" class="form-control" rows="3" required placeholder="Please provide a reason for rescheduling..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" name="notify_candidate" id="notify_candidate" class="form-check-input" checked>
                                <label class="form-check-label" for="notify_candidate">
                                    Send notification to candidate
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-calendar mr-1"></i>Reschedule Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Interview Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelModalLabel">
                    <i class="fa fa-exclamation-triangle mr-2"></i>Cancel Interview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelForm" action="{{ route('recruitment.interviews.cancel', $interview->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action will cancel the interview and cannot be undone.
                    </div>
                    <div class="mb-3">
                        <p><strong>Interview Details:</strong></p>
                        <ul>
                            <li><strong>Candidate:</strong> {{ $interview->application->user->name ?? $interview->application->candidate_name ?? 'Unknown' }}</li>
                            <li><strong>Date:</strong> {{ $interview->scheduled_at ? $interview->scheduled_at->format('M d, Y H:i') : 'N/A' }}</li>
                            <li><strong>Interviewer:</strong> {{ $interview->interviewer->name ?? 'N/A' }}</li>
                        </ul>
                    </div>
                    <div class="form-group mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="4" required placeholder="Please provide a reason for cancelling this interview..."></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="notify_candidate_cancel" id="notify_candidate_cancel" class="form-check-input" checked>
                        <label class="form-check-label" for="notify_candidate_cancel">
                            Send cancellation notification to candidate
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Interview</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-times mr-1"></i>Cancel Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Ensure jQuery is loaded and ready
$(document).ready(function() {
    // Initialize any jQuery-dependent features here
});

function showCompleteModal() {
    $(document).ready(function() {
        $('#completeModal').modal('show');
    });
}

function showRescheduleModal() {
    $(document).ready(function() {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('new_date').min = today;
        $('#rescheduleModal').modal('show');
    });
}

function showCancelModal() {
    $(document).ready(function() {
        $('#cancelModal').modal('show');
    });
}

// Handle form submissions
$(document).ready(function() {
    // Complete interview form
    $('#completeForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#completeModal').modal('hide');
                    showAlert('Interview completed successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(response.message || 'Failed to complete interview', 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error completing interview';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert(errorMessage, 'error');
            }
        });
    });
    
    // Reschedule interview form
    $('#rescheduleForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#rescheduleModal').modal('hide');
                    showAlert('Interview rescheduled successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(response.message || 'Failed to reschedule interview', 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error rescheduling interview';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert(errorMessage, 'error');
            }
        });
    });
    
    // Cancel interview form
    // Author: Sanket - Enhanced error handling for interview cancellation
    $('#cancelForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate cancellation reason length (must be 10+ characters)
        const reason = $('#cancellation_reason').val().trim();
        if (reason.length < 10) {
            showAlert('Cancellation reason must be at least 10 characters long', 'error');
            return;
        }
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#cancelModal').modal('hide');
                    showAlert('Interview cancelled successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(response.message || 'Failed to cancel interview', 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error cancelling interview';
                
                // Handle validation errors (422)
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showAlert(errorMessage, 'error');
            }
        });
    });
});

function scheduleNextRound(applicationId, nextRoundNumber) {
    if (!confirm(`Are you sure you want to schedule Round ${nextRoundNumber} for this candidate?`)) {
        return;
    }

    // Find the button that was clicked
    let button = null;
    const buttons = document.querySelectorAll('button');
    for (let btn of buttons) {
        if (btn.innerHTML.includes('Schedule Round')) {
            button = btn;
            break;
        }
    }
    
    if (!button) {
        alert('Error: Could not find button');
        return;
    }

    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Scheduling...';
    button.disabled = true;

    // Prepare the data
    const requestData = {
        _token: '{{ csrf_token() }}',
        application_id: applicationId,
        round: nextRoundNumber,
        interviewer_id: {{ $interview->interviewer_id ?? 'null' }},
        scheduled_at: new Date(Date.now() + 24*60*60*1000).toISOString().slice(0, 19),
        type: 'video',
        location: 'Video Call - Link will be provided',
        duration_minutes: 60
    };
    
    $.ajax({
        url: '{{ route("recruitment.interviews.schedule-next-round", $interview->id) }}',
        type: 'POST',
        data: requestData,
        timeout: 10000,
        success: function(response) {
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                showAlert(response.message || `Round ${nextRoundNumber} has been scheduled successfully!`, 'success');
                setTimeout(() => {
                    if (response.interview_id) {
                        window.location.href = `{{ url('recruitment/interviews') }}/${response.interview_id}`;
                    } else {
                        location.reload();
                    }
                }, 1500);
            } else {
                showAlert(response.message || 'Failed to schedule next round', 'error');
            }
        },
        error: function(xhr, status, error) {
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
            
            let errorMessage = 'Error scheduling next round';
            
            if (xhr.status === 0) {
                errorMessage = 'Network error - please check your connection';
            } else if (xhr.status === 422) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            showAlert(errorMessage, 'error');
        }
    });
}
</script>
@endpush

@endsection