@extends('layouts.backend')

@section('title')
    Schedule Interview
@endsection

@section('content')
<div class="interview-page-wrapper" style="margin-left: 260px !important; width: calc(100% - 260px) !important; position: relative !important;">
    <div class="container-fluid px-4 py-4">
        
        <!-- Header Section -->
        <div class="page-header-section mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Schedule Interview</h1>
                    <div class="breadcrumb-nav">
                        <a href="{{ route('backend.dashboard') }}">Dashboard</a>
                        <span class="separator">/</span>
                        <a href="{{ route('recruitment.interviews.index') }}">Interviews</a>
                        <span class="separator">/</span>
                        <span class="current">Schedule New</span>
                    </div>
                </div>
                <a href="{{ route('recruitment.interviews.index') }}" class="btn-back">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('recruitment.interviews.store') }}" method="POST" class="interview-form">
            @csrf
            
            <div class="row g-4">
                <!-- Main Content -->
                <div class="col-lg-8">
                    
                    <!-- Candidate Selection -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon candidate-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M10 10C12.7614 10 15 7.76142 15 5C15 2.23858 12.7614 0 10 0C7.23858 0 5 2.23858 5 5C5 7.76142 7.23858 10 10 10Z" fill="currentColor"/>
                                    <path d="M10 12C5.58172 12 2 15.5817 2 20H18C18 15.5817 14.4183 12 10 12Z" fill="currentColor"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Candidate Selection</h3>
                                <p class="section-subtitle">Choose the candidate for this interview</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="form-field">
                                <label class="field-label">Candidate Application <span class="required">*</span></label>
                                <select class="field-input select2 @error('application_id') is-invalid @enderror" 
                                        id="application_id" name="application_id" required>
                                    <option value="">Select a candidate...</option>
                                    @forelse($applications as $application)
                                        @php 
                                            $selectedApp = old('application_id', request('application_id')) == $application->id;
                                        @endphp
                                        <option value="{{ $application->id }}" {{ $selectedApp ? 'selected' : '' }}>
                                            {{ $application->candidate_name ?? ($application->user->name ?? 'N/A') }} - {{ $application->job->title ?? 'N/A' }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No applications available</option>
                                    @endforelse
                                </select>
                                @error('application_id')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                                @if(!$applications->isEmpty())
                                    <span class="field-hint">{{ $applications->count() }} application(s) available for scheduling</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Interview Details -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon details-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <rect x="3" y="4" width="14" height="12" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="M7 2V6M13 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M3 8H17" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Interview Details</h3>
                                <p class="section-subtitle">Configure interview format and location</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-field">
                                        <label class="field-label">Interview Type <span class="required">*</span></label>
                                        <select class="field-input @error('type') is-invalid @enderror" id="type" name="type" required>
                                            <option value="">Select type...</option>
                                            <option value="phone" {{ old('type') == 'phone' ? 'selected' : '' }}>📞 Phone Interview</option>
                                            <option value="video" {{ old('type') == 'video' ? 'selected' : '' }}>🎥 Video Call</option>
                                            <option value="in_person" {{ old('type') == 'in_person' ? 'selected' : '' }}>🏢 In Person</option>
                                        </select>
                                        @error('type')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-field">
                                        <label class="field-label" for="location">Location/Link</label>
                                        <input type="text" class="field-input @error('location') is-invalid @enderror" 
                                               id="location" name="location" value="{{ old('location') }}" 
                                               placeholder="Meeting room, address, or video link">
                                        @error('location')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon schedule-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="M10 6V10L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Schedule</h3>
                                <p class="section-subtitle">Set date, time, and duration</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-field">
                                        <label class="field-label">Date <span class="required">*</span></label>
                                        <input type="date" class="field-input @error('interview_date') is-invalid @enderror" 
                                               id="interview_date" name="interview_date" value="{{ old('interview_date') }}" required>
                                        @error('interview_date')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-field">
                                        <label class="field-label">Time <span class="required">*</span></label>
                                        <input type="time" class="field-input @error('interview_time') is-invalid @enderror" 
                                               id="interview_time" name="interview_time" value="{{ old('interview_time') }}" required>
                                        @error('interview_time')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-field">
                                        <label class="field-label">Duration (minutes)</label>
                                        <input type="number" class="field-input @error('duration') is-invalid @enderror" 
                                               id="duration" name="duration" value="{{ old('duration', 60) }}" min="15" step="15" placeholder="60">
                                        @error('duration')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
                        </div>
                    </div>

                    <!-- Interviewer -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon interviewer-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M10 10C12.7614 10 15 7.76142 15 5C15 2.23858 12.7614 0 10 0C7.23858 0 5 2.23858 5 5C5 7.76142 7.23858 10 10 10Z" fill="currentColor"/>
                                    <path d="M10 12C5.58172 12 2 15.5817 2 20H18C18 15.5817 14.4183 12 10 12Z" fill="currentColor"/>
                                    <circle cx="15" cy="15" r="4" fill="#10b981"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Interviewer</h3>
                                <p class="section-subtitle">Assign interviewer for this session</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="form-field">
                                <label class="field-label">Select Interviewer <span class="required">*</span></label>
                                <select class="field-input select2 @error('interviewer_id') is-invalid @enderror" 
                                        id="interviewer_id" name="interviewer_id" required>
                                    <option value="">Choose interviewer...</option>
                                    @foreach($interviewers as $interviewer)
                                        <option value="{{ $interviewer->id }}" {{ old('interviewer_id') == $interviewer->id ? 'selected' : '' }}>
                                            {{ $interviewer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('interviewer_id')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon notes-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <rect x="4" y="2" width="12" height="16" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="M7 6H13M7 10H13M7 14H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Additional Notes</h3>
                                <p class="section-subtitle">Agenda, instructions, or preparation notes</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="form-field">
                                <textarea class="field-input field-textarea @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="5" 
                                          placeholder="Enter interview agenda, questions, special instructions, or any other relevant notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                                <span class="field-hint">These notes will be visible to the interviewer and HR team</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <button type="button" class="btn-secondary-action" onclick="history.back()">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary-action">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M13.5 4.5L6 12L2.5 8.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Schedule Interview
                        </button>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sidebar-card">
                        <div class="sidebar-header">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 7V17C3 17.5304 3.21071 18.0391 3.58579 18.4142C3.96086 18.7893 4.46957 19 5 19H15C15.5304 19 16.0391 18.7893 16.4142 18.4142C16.7893 18.0391 17 17.5304 17 17V7L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                <path d="M7 19V10H13V19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h4>Quick Tips</h4>
                        </div>
                        <ul class="tips-list">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="7" fill="#10b981"/>
                                    <path d="M5 8L7 10L11 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Schedule at least 24 hours in advance</span>
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="7" fill="#10b981"/>
                                    <path d="M5 8L7 10L11 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Send calendar invites to all participants</span>
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="7" fill="#10b981"/>
                                    <path d="M5 8L7 10L11 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Prepare interview questions beforehand</span>
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="7" fill="#10b981"/>
                                    <path d="M5 8L7 10L11 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Test video/audio before the call</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Complete UI Redesign - Author: Sanket */
/* Clean white background design */

.interview-page-wrapper {
    background: #ffffff !important;
    min-height: 100vh;
}

/* Header */
.page-header-section {
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 32px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 8px 0;
    letter-spacing: -0.5px;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.breadcrumb-nav a {
    color: #6366f1;
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumb-nav a:hover {
    color: #4f46e5;
}

.breadcrumb-nav .separator {
    color: #9ca3af;
}

.breadcrumb-nav .current {
    color: #6b7280;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    color: #374151;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #111827;
}

/* Form Sections */
.form-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}

.section-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.candidate-icon {
    background: #eff6ff;
    color: #3b82f6;
}

.details-icon {
    background: #f0fdf4;
    color: #10b981;
}

.schedule-icon {
    background: #fef3c7;
    color: #f59e0b;
}

.interviewer-icon {
    background: #ede9fe;
    color: #8b5cf6;
}

.notes-icon {
    background: #f3f4f6;
    color: #6b7280;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px 0;
}

.section-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.section-body {
    padding: 24px;
}

/* Form Fields */
.form-field {
    margin-bottom: 0;
}

.field-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.required {
    color: #ef4444;
}

.field-input {
    width: 100%;
    padding: 12px 16px;
    font-size: 15px;
    color: #111827;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    transition: all 0.2s;
}

.field-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.field-input::placeholder {
    color: #9ca3af;
}

.field-textarea {
    resize: vertical;
    min-height: 120px;
    font-family: inherit;
    line-height: 1.6;
}

select.field-input {
    cursor: pointer;
}

.field-error {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    color: #ef4444;
}

.field-hint {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    color: #6b7280;
}

/* Action Buttons */
.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 0 0 20px 0;
}

.btn-secondary-action {
    padding: 12px 24px;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    color: #374151;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary-action:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-primary-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: #6366f1;
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary-action:hover {
    background: #4f46e5;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

/* Sidebar */
.sidebar-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
}

.sidebar-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f3f4f6;
}

.sidebar-header svg {
    color: #6366f1;
}

.sidebar-header h4 {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.tips-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-list li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
}

.tips-list li:last-child {
    margin-bottom: 0;
}

.tips-list svg {
    flex-shrink: 0;
    margin-top: 2px;
}

/* Select2 Overrides */
.select2-container .select2-selection--single {
    height: 48px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
}

.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 46px !important;
    padding-left: 16px !important;
    color: #111827 !important;
}

.select2-container--open .select2-selection--single {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
}

/* Responsive */
@media (max-width: 991px) {
    .page-title {
        font-size: 24px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-secondary-action,
    .btn-primary-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Author: Sanket - Interview scheduling functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        $('#application_id, #interviewer_id').select2({
            width: '100%'
        });
    }
    
    // Dynamic location field
    const typeSelect = document.getElementById('type');
    const locationInput = document.getElementById('location');
    const locationLabel = document.querySelector('label[for="location"]');
    
    if (typeSelect && locationInput) {
        typeSelect.addEventListener('change', function() {
            switch(this.value) {
                case 'video':
                    locationLabel.innerHTML = 'Video Call Link <span class="required">*</span>';
                    locationInput.placeholder = 'Enter Zoom, Teams, or Meet link';
                    locationInput.required = true;
                    break;
                case 'phone':
                    locationLabel.innerHTML = 'Phone Number <span class="required">*</span>';
                    locationInput.placeholder = 'Enter phone number';
                    locationInput.required = true;
                    break;
                case 'in_person':
                    locationLabel.innerHTML = 'Meeting Location <span class="required">*</span>';
                    locationInput.placeholder = 'Enter meeting room or address';
                    locationInput.required = true;
                    break;
                default:
                    locationLabel.innerHTML = 'Location/Link';
                    locationInput.placeholder = 'Enter location or link';
                    locationInput.required = false;
            }
        });
    }
    
    // Combine date and time
    const dateInput = document.getElementById('interview_date');
    const timeInput = document.getElementById('interview_time');
    const scheduledAtInput = document.getElementById('scheduled_at');
    
    function updateScheduledAt() {
        if (dateInput.value && timeInput.value) {
            scheduledAtInput.value = dateInput.value + ' ' + timeInput.value;
        }
    }
    
    if (dateInput && timeInput) {
        dateInput.addEventListener('change', updateScheduledAt);
        timeInput.addEventListener('change', updateScheduledAt);
        updateScheduledAt();
    }
    
    // Set minimum date to today
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    }
    
    // Form validation
    const form = document.querySelector('.interview-form');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
});
</script>
@endsection