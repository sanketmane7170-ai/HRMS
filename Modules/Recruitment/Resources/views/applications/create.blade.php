@extends('layouts.backend')

@section('title')
    Create Application
@endsection

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper" style="background-color: #ffffff !important;">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title fw-bold text-dark">New Job Application</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.applications.index') }}">Applications</a></li>
                        <li class="breadcrumb-item active">New Application</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-12">
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                        <div>
                            <strong>Submission Error:</strong> {{ session('error') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <div>
                            <strong>Validation Errors:</strong>
                            <ul class="mb-0 mt-2 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @endif

                <!-- Form Card -->
                <div class="card border-0 shadow-sm" style="background-color: #ffffff !important; border: 1px solid #eef2f7 !important;">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('recruitment.applications.store') }}" enctype="multipart/form-data" id="applicationForm" style="background-color: #ffffff !important;">
                            @csrf
                            
                            <!-- Application Summary Header -->
                            <div class="d-flex align-items-center mb-5 pb-3 border-bottom">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="fas fa-file-contract text-primary fs-3"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold text-dark mb-0">Candidate Details</h4>
                                    <p class="text-muted small mb-0">Provide candidate information and job preferences.</p>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Target Position Section -->
                                <div class="col-12 mb-5">
                                    <label for="job_id" class="form-label fw-bold text-dark">Target Job Position <span class="text-danger">*</span></label>
                                    <select name="job_id" id="job_id" class="form-select form-select-lg border @error('job_id') is-invalid @enderror" required style="background-color: #ffffff !important;">
                                        <option value="">Choose a position...</option>
                                        @foreach($jobs as $job)
                                            <option value="{{ $job->id }}" {{ old('job_id') == $job->id ? 'selected' : '' }}>
                                                {{ $job->title }} ({{ $job->department->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('job_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Personal Info Section -->
                                <div class="col-12 mb-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-1">
                                                <input type="text" name="candidate_name" id="candidate_name" value="{{ old('candidate_name') }}" class="form-control @error('candidate_name') is-invalid @enderror" placeholder="Full Name" required style="background-color: #ffffff !important; height: 60px;">
                                                <label for="candidate_name" class="text-muted">Full Name *</label>
                                            </div>
                                            @error('candidate_name')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-1">
                                                <input type="email" name="candidate_email" id="candidate_email" value="{{ old('candidate_email') }}" class="form-control @error('candidate_email') is-invalid @enderror" placeholder="Email Address" required style="background-color: #ffffff !important; height: 60px;">
                                                <label for="candidate_email" class="text-muted">Email Address *</label>
                                            </div>
                                            @error('candidate_email')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="tel" name="candidate_phone" id="candidate_phone" value="{{ old('candidate_phone') }}" class="form-control" placeholder="Phone Number" style="background-color: #ffffff !important; height: 60px;">
                                                <label for="candidate_phone" class="text-muted">Phone Number</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url') }}" class="form-control" placeholder="LinkedIn URL" style="background-color: #ffffff !important; height: 60px;">
                                                <label for="linkedin_url" class="text-muted">LinkedIn Profile URL</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Professional Details Section -->
                                <div class="col-12 mb-5 pt-3">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="expected_salary" class="form-label fw-semibold text-dark mb-2">Expected Annual Salary ($)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted fw-bold">$</span>
                                                <input type="number" name="expected_salary" id="expected_salary" value="{{ old('expected_salary') }}" class="form-control border-start-0" placeholder="e.g. 50000" style="background-color: #ffffff !important;">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="availability_date" class="form-label fw-semibold text-dark mb-2">Notice Period Ends / Availability Date</label>
                                            <input type="date" name="availability_date" id="availability_date" value="{{ old('availability_date') }}" class="form-control" style="background-color: #ffffff !important;">
                                        </div>
                                    </div>
                                </div>

                                <!-- Resume Section -->
                                <div class="col-12 mb-5">
                                    <label class="form-label fw-bold text-dark mb-3">Submission Documents</label>
                                    <div class="p-4 border rounded-3 text-center transition-all shadow-sm" style="background-color: #fcfdfe !important; border-style: dashed !important; border-width: 2px !important;" id="uploadArea">
                                        <div class="mb-3">
                                            <i class="fas fa-file-pdf fs-1 text-primary mb-2"></i>
                                            <h6 class="fw-bold mb-1">Click to browse or Drag & Drop Resume</h6>
                                            <p class="text-muted small mb-0">Accepted: PDF, DOC, DOCX (Max 5MB)</p>
                                        </div>
                                        <input type="file" name="resume" id="resume" class="form-control border-0 bg-transparent text-center mx-auto" style="max-width: 300px;" accept=".pdf,.doc,.docx" required>
                                        <div class="invalid-feedback">Please upload a resume file.</div>
                                    </div>
                                </div>

                                <!-- Cover Letter -->
                                <div class="col-12 mb-5">
                                    <label for="cover_letter" class="form-label fw-semibold text-dark mb-2">Cover Letter / Additional Notes</label>
                                    <textarea name="cover_letter" id="cover_letter" rows="8" class="form-control border p-3" placeholder="Tell us about yourself or add any specific notes..." style="background-color: #ffffff !important;">{{ old('cover_letter') }}</textarea>
                                    <div class="text-end mt-2">
                                        <small class="text-muted"><span id="charCounter">0</span> / 2000 characters</small>
                                    </div>
                                </div>

                                <!-- Footer Actions -->
                                <div class="col-12 d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-center mt-4 pt-5 border-top">
                                    <a href="{{ route('recruitment.applications.index') }}" class="btn btn-link text-muted text-decoration-none fw-bold order-2 order-sm-1">
                                        <i class="fas fa-arrow-left me-2"></i> Back to Listing
                                    </a>
                                    <div class="d-flex gap-3 order-1 order-sm-2 w-100 w-sm-auto">
                                        <button type="reset" class="btn btn-light border px-4 py-3 fw-bold flex-grow-1">Reset Form</button>
                                        <button type="submit" class="btn btn-primary px-5 py-3 fw-bold flex-grow-1 shadow-sm fs-5">
                                            Submit Application
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Strictly Professional Design by Sanket */
    :root {
        --primary-brand: #007bff;
        --border-light: #e9ecef;
    }

    body {
        background-color: #ffffff !important;
        color: #333 !important;
    }

    .page-wrapper {
        background-color: #ffffff !important;
    }

    .card {
        background-color: #ffffff !important;
    }

    .form-control, .form-select {
        border-color: #dee2e6 !important;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-brand) !important;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.1) !important;
    }

    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        color: var(--primary-brand);
    }

    #uploadArea {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    #uploadArea:hover {
        background-color: #f1f4f9 !important;
        border-color: var(--primary-brand) !important;
    }

    .btn-primary {
        background-color: var(--primary-brand);
        border: none;
    }

    .btn-primary:hover {
        background-color: #0069d9;
        transform: translateY(-2px);
    }

    .breadcrumb-item a {
        color: var(--primary-brand);
        text-decoration: none;
    }

    /* Fixed UI for headers to avoid dark colors */
    .text-primary {
        color: var(--primary-brand) !important;
    }

    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const coverLetter = document.getElementById('cover_letter');
        const charCounter = document.getElementById('charCounter');
        
        // Update character counter
        coverLetter.addEventListener('input', function() {
            const current = this.value.length;
            charCounter.textContent = current;
            if (current > 1900) {
                charCounter.classList.add('text-danger');
            } else {
                charCounter.classList.remove('text-danger');
            }
        });

        // Optional: click upload area triggers file input
        document.getElementById('uploadArea').addEventListener('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                document.getElementById('resume').click();
            }
        });
    });
</script>
@endpush