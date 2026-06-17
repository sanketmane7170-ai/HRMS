@extends('layouts.backend')

@section('title')
    Create Job Offer
@endsection

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper recruitment-create-offer">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create Job Offer</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.offers.index') }}">Offers</a></li>
                        <li class="breadcrumb-item active">Create Offer</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('recruitment.offer-letters.create') }}" class="btn btn-success me-2">
                        <i class="fas fa-file-pdf me-1"></i>Letter Generator
                    </a>
                    <a href="{{ route('recruitment.offers.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Offers
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Welcome Header Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: #1e3a8a;">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                    <i class="fas fa-file-contract fs-3 text-white"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h1 class="h3 fw-bold mb-2 text-white">Generate Job Offer</h1>
                                <p class="mb-0 text-white" style="opacity: 0.9;">Create comprehensive job offers with competitive packages for selected candidates</p>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-white text-dark px-3 py-2 rounded-pill fw-semibold">
                                    <i class="fas fa-clock me-2 text-primary"></i>Draft
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Form -->
            <div class="col-xxl-8 col-lg-7">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white border-bottom">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-file-contract text-primary"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h4 class="card-title mb-1 text-dark">Offer Details</h4>
                                <p class="text-muted mb-0 small">Complete the form below to generate the job offer</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <form action="{{ route('recruitment.offers.store') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            
                            <!-- Candidate Selection -->
                            <div class="mb-4 p-4 bg-white border rounded">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-user text-info"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1 text-dark">Select Candidate</h6>
                                        <small class="text-muted">Choose the candidate for this job offer</small>
                                    </div>
                                </div>
                                <select class="form-select select2 @error('application_id') is-invalid @enderror" 
                                        id="application_id" name="application_id" required>
                                    <option value="">Select Application</option>
                                    @forelse($applications as $application)
                                        @php
                                            $candidateName = $application->user ? $application->user->name : ($application->candidate_name ?? 'Unknown Candidate');
                                            $jobTitle = $application->job ? $application->job->title : 'No Job Title';
                                        @endphp
                                        <option value="{{ $application->id }}" 
                                                data-job="{{ $jobTitle }}"
                                                data-candidate="{{ $candidateName }}"
                                                data-stage="{{ $application->stage }}"
                                                data-email="{{ $application->user ? $application->user->email : $application->candidate_email }}"
                                                {{ old('application_id') == $application->id ? 'selected' : '' }}>
                                            {{ $candidateName }} - {{ $jobTitle }} ({{ ucfirst($application->stage) }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No eligible candidates found</option>
                                    @endforelse
                                </select>
                                @error('application_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Position Details ---->
                            <div class="mb-4 p-4 bg-white border rounded">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-briefcase text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1 text-dark">Position Information</h6>
                                        <small class="text-muted">Job title and department details</small>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Position details will be automatically filled from the selected application.</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Salary Package -->
                            <div class="mb-4 p-4 bg-white border rounded">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-dollar-sign text-success"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1 text-dark">Compensation Package</h6>
                                        <small class="text-muted">Salary and compensation details</small>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Salary Amount <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('salary') is-invalid @enderror" 
                                               id="salary" name="salary" value="{{ old('salary') }}" step="0.01" required>
                                        @error('salary')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Currency</label>
                                        <select class="form-select @error('salary_currency') is-invalid @enderror" id="salary_currency" name="salary_currency">
                                            <option value="USD" {{ old('salary_currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                            <option value="EUR" {{ old('salary_currency') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                            <option value="GBP" {{ old('salary_currency') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                            <option value="AED" {{ old('salary_currency') == 'AED' ? 'selected' : '' }}>AED (د.إ)</option>
                                        </select>
                                        @error('salary_currency')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Salary Period</label>
                                        <select class="form-select @error('salary_period') is-invalid @enderror" id="salary_period" name="salary_period">
                                            <option value="monthly" {{ old('salary_period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="annual" {{ old('salary_period') == 'annual' ? 'selected' : '' }}>Annual</option>
                                            <option value="hourly" {{ old('salary_period') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                        </select>
                                        @error('salary_period')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Details -->
                            <div class="mb-4 p-4 bg-white border rounded">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-calendar text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1 text-dark">Timeline & Dates</h6>
                                        <small class="text-muted">Start date and offer expiry details</small>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Joining Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('joining_date') is-invalid @enderror" 
                                               id="joining_date" name="joining_date" value="{{ old('joining_date') }}" required>
                                        @error('joining_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Response Deadline</label>
                                        <input type="date" class="form-control @error('response_deadline') is-invalid @enderror" 
                                               id="response_deadline" name="response_deadline" value="{{ old('response_deadline') }}">
                                        @error('response_deadline')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Benefits note - will be included in terms and conditions -->
                            <div class="mb-4 p-4 bg-light border rounded">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-info-circle text-info"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1 text-dark">Benefits Information</h6>
                                        <p class="text-muted mb-0 small">Benefits details should be included in the Terms & Conditions section below. Common benefits include health insurance, paid time off, retirement plans, and professional development opportunities.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms & Conditions -->
                            <div class="mb-4 p-4 bg-white border rounded">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <div class="bg-secondary bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-file-contract text-secondary"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1 text-dark">Terms & Conditions</h6>
                                        <small class="text-muted">Employment terms and legal conditions</small>
                                    </div>
                                </div>
                                <textarea class="form-control @error('terms_conditions') is-invalid @enderror" 
                                          id="terms_conditions" name="terms_conditions" rows="5" 
                                          placeholder="Enter employment terms, conditions, probation period, and any special requirements">{{ old('terms_conditions') }}</textarea>
                                @error('terms_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Additional Notes -->
                            <div class="mb-4 p-4 bg-white border rounded">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-sticky-note text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1 text-dark">Additional Notes</h6>
                                        <small class="text-muted">Personal message or additional information</small>
                                    </div>
                                </div>
                                <textarea class="form-control @error('additional_notes') is-invalid @enderror" 
                                          id="additional_notes" name="additional_notes" rows="3" 
                                          placeholder="Add any personalized message, company culture notes, or additional information for the candidate">{{ old('additional_notes') }}</textarea>
                                @error('additional_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Send Options -->
                            <div class="mb-4 p-4 bg-light border rounded">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="send_immediately" name="send_immediately" value="1" 
                                           {{ old('send_immediately') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="send_immediately">
                                        <i class="fas fa-paper-plane text-primary me-2"></i>Send offer immediately after generation
                                    </label>
                                    <small class="form-text text-muted d-block mt-1">
                                        The offer will be automatically sent to the candidate's email address
                                    </small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary px-4" onclick="history.back()">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-file-contract me-1"></i>Generate Offer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-xxl-4 col-lg-5">
                <!-- Offer Tips Card -->
                <div class="card border-0 shadow-sm mb-4 bg-white">
                    <div class="card-header bg-white border-bottom">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-lightbulb text-success"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="card-title mb-1 text-dark">Offer Best Practices</h5>
                                <p class="text-muted mb-0 small">Tips for creating competitive offers</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-chart-line text-success small"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fw-semibold mb-1 text-dark">Market Research</h6>
                                    <small class="text-muted">Review current market salary rates and trends</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-heart text-info small"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fw-semibold mb-1 text-dark">Comprehensive Benefits</h6>
                                    <small class="text-muted">Include health, dental, retirement, and other benefits</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-calendar text-warning small"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fw-semibold mb-1 text-dark">Reasonable Timeline</h6>
                                    <small class="text-muted">Set achievable start dates and decision deadlines</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-file-contract text-primary small"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fw-semibold mb-1 text-dark">Clear Terms</h6>
                                    <small class="text-muted">Provide clear, professional terms and conditions</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Candidate Card -->
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white border-bottom">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-user-check text-primary"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="card-title mb-1 text-dark">Selected Candidate</h5>
                                <p class="text-muted mb-0 small">Candidate details for this offer</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div id="candidate-info">
                            <p class="text-muted">Please select an application first to view candidate details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Enhanced Offer Create Page Styles */
.recruitment-create-offer {
    background-color: #f8fafc !important;
    min-height: 100vh;
}

.recruitment-create-offer .page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2d3748;
}

.recruitment-create-offer .breadcrumb {
    background: none;
    padding: 0;
    font-size: 0.875rem;
}

.recruitment-create-offer .breadcrumb-item a {
    color: #4299e1;
    text-decoration: none;
}

/* Enhanced form controls */
.recruitment-create-offer .form-select,
.recruitment-create-offer .form-control {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 12px 16px;
    font-size: 0.875rem;
    transition: all 0.2s;
    background-color: white;
}

.recruitment-create-offer .form-select:focus,
.recruitment-create-offer .form-control:focus {
    border-color: #1e3a8a;
    box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
    background-color: white;
}

.recruitment-create-offer .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

/* White background for all form sections */
.recruitment-create-offer .card {
    border-radius: 12px !important;
    background-color: white !important;
    border: 1px solid #e5e7eb !important;
    transition: all 0.2s;
}

.recruitment-create-offer .card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

.recruitment-create-offer .card-body {
    background-color: white !important;
}

.recruitment-create-offer .card-header {
    background-color: white !important;
}

/* Form sections styling */
.recruitment-create-offer .mb-4.p-4 {
    background-color: white !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 8px;
    margin-bottom: 1.5rem !important;
}

/* Button styling */
.recruitment-create-offer .btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 12px 24px;
    transition: all 0.2s;
}

.recruitment-create-offer .btn-primary {
    background-color: #1e3a8a;
    border-color: #1e3a8a;
}

.recruitment-create-offer .btn-primary:hover {
    background-color: #1e40af;
    border-color: #1e40af;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.4);
}

.recruitment-create-offer .btn-outline-secondary {
    border-color: #6b7280;
    color: #6b7280;
}

.recruitment-create-offer .btn-outline-secondary:hover {
    background-color: #6b7280;
    border-color: #6b7280;
}

/* Text colors */
.recruitment-create-offer h6,
.recruitment-create-offer .fw-bold {
    color: #111827 !important;
}

.recruitment-create-offer .text-muted {
    color: #6b7280 !important;
}

/* Section icons */
.recruitment-create-offer .rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Content wrapper white background */
.recruitment-create-offer .content {
    background-color: #f8fafc !important;
    padding: 24px;
}

/* Ensure all form elements have white backgrounds */
.recruitment-create-offer select,
.recruitment-create-offer input,
.recruitment-create-offer textarea {
    background-color: white !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true,
            theme: 'bootstrap-5'
        });
    }
    
    // Handle application selection
    const applicationSelectElement = document.getElementById('application_id');
    const candidateInfo = document.getElementById('candidate-info');
    
    if (applicationSelectElement && candidateInfo) {
        console.log('Setting up candidate selection handlers'); // Debug log
        
        // Function to handle selection change
        function handleSelectionChange() {
            const selectedValue = applicationSelectElement.value;
            const selectedOption = applicationSelectElement.options[applicationSelectElement.selectedIndex];
            
            console.log('Selection changed:', selectedValue); // Debug log
            
            if (selectedValue && selectedOption) {
                const job = selectedOption.getAttribute('data-job');
                const candidate = selectedOption.getAttribute('data-candidate');
                const stage = selectedOption.getAttribute('data-stage');
                const email = selectedOption.getAttribute('data-email');
                
                console.log('Data attributes:', { job, candidate, stage, email }); // Debug log
                
                if (job && candidate) {
                    
                    const stageColors = {
                        'interview': 'warning',
                        'offer': 'info', 
                        'hired': 'success',
                        'offer_pending': 'primary',
                        'interview_completed': 'success'
                    };
                    const stageColor = stageColors[stage] || 'secondary';
                    
                    console.log('Updating candidate info for:', candidate); // Debug log
                    
                    candidateInfo.innerHTML = `
                    <div class="candidate-details">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-user text-primary fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-primary mb-1">${candidate}</h6>
                                <small class="text-muted d-block">${email || 'No email available'}</small>
                            </div>
                            <span class="badge bg-${stageColor} text-white">${stage ? stage.replace('_', ' ').toUpperCase() : 'UNKNOWN'}</span>
                        </div>
                        <div class="border-top pt-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <p class="mb-2"><strong class="text-dark">Position:</strong> <span class="text-muted">${job}</span></p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-2"><strong class="text-dark">Application Status:</strong> <span class="badge bg-${stageColor}">${stage ? stage.replace('_', ' ').toUpperCase() : 'Unknown'}</span></p>
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-success alert-sm py-2 px-3 mb-0">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <small><strong>Ready for Offer Generation</strong></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                } else {
                    console.log('Missing job or candidate data'); // Debug log
                    candidateInfo.innerHTML = '<div class="text-center py-4"><div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Unable to load candidate details</div></div>';
                }
            } else {
                console.log('No selection made'); // Debug log
                candidateInfo.innerHTML = '<div class="text-center py-4"><i class="fas fa-user-plus text-muted fs-1 mb-3 d-block"></i><p class="text-muted mb-0">Please select an application to view candidate details</p></div>';
            }
        }
        
        // Set up event listeners
        // For Select2
        if (typeof $.fn.select2 !== 'undefined' && $(applicationSelectElement).hasClass('select2')) {
            $(applicationSelectElement).on('change', handleSelectionChange);
        } else {
            // Fallback for regular select
            applicationSelectElement.addEventListener('change', handleSelectionChange);
        }
        
        // Also trigger on page load if there's a pre-selected value
        if (applicationSelectElement.value) {
            handleSelectionChange();
        }
    }
    
    // Form validation
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
    
    // Auto-calculate response deadline (2 weeks from today)
    const joiningDate = document.getElementById('joining_date');
    const responseDeadline = document.getElementById('response_deadline');
    
    if (joiningDate && responseDeadline) {
        joiningDate.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            if (selectedDate) {
                const deadlineDate = new Date();
                deadlineDate.setDate(deadlineDate.getDate() + 14); // 2 weeks
                responseDeadline.value = deadlineDate.toISOString().split('T')[0];
            }
        });
    }
});
</script>
@endpush