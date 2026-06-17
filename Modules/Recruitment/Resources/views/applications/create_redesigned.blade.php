@extends('layouts.backend')

@section('title')
    Create Application
@endsection

@section('content')
<!-- Clean White Background Page -->
<div class="bg-white min-vh-100">
    <div class="container-fluid py-4">
        <!-- Clean Header -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="bg-white p-4 rounded-4 shadow-sm border">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">
                        <div>
                            <h1 class="display-6 fw-bold text-dark mb-3">Create New Application</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 fs-6">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('backend.dashboard') }}" class="text-decoration-none text-primary fw-medium">
                                            <i class="fas fa-home me-1"></i>Dashboard
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('recruitment.applications.index') }}" class="text-decoration-none text-primary fw-medium">Applications</a>
                                    </li>
                                    <li class="breadcrumb-item active text-secondary fw-medium">Create Application</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <span class="badge bg-light text-dark border px-4 py-3 rounded-pill fs-6 fw-semibold">
                                <i class="fas fa-edit me-2 text-primary"></i>Draft Mode
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Application Form -->
        <div class="row g-4">
            <!-- Form Content -->
            <div class="col-xxl-8 col-lg-7">
                <form method="POST" action="{{ route('recruitment.applications.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    
                    <!-- Welcome Section -->
                    <div class="bg-white rounded-4 shadow-sm border mb-4 overflow-hidden">
                        <div class="bg-gradient-primary text-white p-4">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                        <i class="fas fa-user-plus fs-3 text-white"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h2 class="h3 fw-bold mb-2 text-white">Apply for Your Dream Job</h2>
                                    <p class="mb-0 text-white-50 fs-6">Complete the application form below and take the first step towards your career goals</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Selection -->
                    <div class="bg-white rounded-4 shadow-sm border mb-4">
                        <div class="p-4 border-bottom border-light">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-briefcase text-info fs-5"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3 class="h4 fw-bold text-dark mb-1">Select Position</h3>
                                    <p class="text-muted mb-0 fs-6">Choose the job position you want to apply for</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="mb-3">
                                <label for="job_id" class="form-label fw-bold text-dark mb-3">Available Positions <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg border-2" id="job_id" name="job_id" required 
                                        style="border-color: #e9ecef; border-radius: 12px; padding: 16px 20px; font-size: 1rem;">
                                    <option value="">Select a position...</option>
                                    @foreach($jobs as $job)
                                        <option value="{{ $job->id }}">{{ $job->title }} - {{ $job->department->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback fs-6">Please select a job position.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="bg-white rounded-4 shadow-sm border mb-4">
                        <div class="p-4 border-bottom border-light">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-user text-success fs-5"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3 class="h4 fw-bold text-dark mb-1">Personal Information</h3>
                                    <p class="text-muted mb-0 fs-6">Tell us about yourself</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold text-dark">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg border-2" id="name" name="name" 
                                           placeholder="Enter your full name" required
                                           style="border-color: #e9ecef; border-radius: 12px; padding: 16px 20px;">
                                    <div class="invalid-feedback fs-6">Please provide your full name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-bold text-dark">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-lg border-2" id="email" name="email" 
                                           placeholder="your.email@example.com" required
                                           style="border-color: #e9ecef; border-radius: 12px; padding: 16px 20px;">
                                    <div class="invalid-feedback fs-6">Please provide a valid email address.</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="phone" class="form-label fw-bold text-dark">Phone Number</label>
                                    <input type="tel" class="form-control form-control-lg border-2" id="phone" name="phone" 
                                           placeholder="+1 (555) 123-4567"
                                           style="border-color: #e9ecef; border-radius: 12px; padding: 16px 20px;">
                                </div>
                                <div class="col-md-4">
                                    <label for="expected_salary" class="form-label fw-bold text-dark">Expected Salary</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text border-2 fw-bold" 
                                              style="border-color: #e9ecef; border-radius: 12px 0 0 12px; background: #f8f9fa;">$</span>
                                        <input type="number" class="form-control border-2" id="expected_salary" name="expected_salary" 
                                               placeholder="50000"
                                               style="border-color: #e9ecef; border-radius: 0 12px 12px 0; padding: 16px 20px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="available_from" class="form-label fw-bold text-dark">Available From</label>
                                    <input type="date" class="form-control form-control-lg border-2" id="available_from" name="available_from"
                                           style="border-color: #e9ecef; border-radius: 12px; padding: 16px 20px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Section -->
                    <div class="bg-white rounded-4 shadow-sm border mb-4">
                        <div class="p-4 border-bottom border-light">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-upload text-warning fs-5"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3 class="h4 fw-bold text-dark mb-1">Documents & Portfolio</h3>
                                    <p class="text-muted mb-0 fs-6">Upload your resume and additional documents</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="resume" class="form-label fw-bold text-dark mb-3">Resume/CV <span class="text-danger">*</span></label>
                                    <div class="upload-zone border-2 border-dashed rounded-4 p-5 text-center bg-light bg-opacity-25" 
                                         style="border-color: #dee2e6 !important; cursor: pointer; transition: all 0.3s ease;">
                                        <div class="upload-content">
                                            <div class="mb-4">
                                                <div class="bg-primary bg-opacity-10 rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                                     style="width: 100px; height: 100px;">
                                                    <i class="fas fa-cloud-upload-alt text-primary" style="font-size: 2.5rem;"></i>
                                                </div>
                                            </div>
                                            <h4 class="h5 fw-bold text-dark mb-2">Drop your resume here or click to browse</h4>
                                            <p class="text-muted mb-0 fs-6">Supported formats: PDF, DOC, DOCX (Max: 5MB)</p>
                                            <input type="file" class="form-control d-none" id="resume" name="resume" 
                                                   accept=".pdf,.doc,.docx" required>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback fs-6">Please upload your resume.</div>
                                </div>
                                <div class="col-12">
                                    <label for="cover_letter" class="form-label fw-bold text-dark mb-3">Cover Letter</label>
                                    <textarea class="form-control border-2" id="cover_letter" name="cover_letter" rows="8" 
                                              placeholder="Tell us why you're the perfect fit for this position..."
                                              style="border-color: #e9ecef; border-radius: 12px; padding: 20px; resize: vertical; font-size: 1rem;"></textarea>
                                    <div class="d-flex justify-content-between mt-3">
                                        <small class="text-muted fw-medium">
                                            <i class="fas fa-info-circle me-1 text-info"></i>
                                            Optional but highly recommended
                                        </small>
                                        <small class="text-muted fw-bold">
                                            <span id="charCount">0</span>/1000 characters
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="bg-white rounded-4 shadow-sm border">
                        <div class="p-4">
                            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end">
                                <a href="{{ route('recruitment.applications.index') }}" 
                                   class="btn btn-light btn-lg border-2 px-5 fw-bold"
                                   style="border-color: #dee2e6; border-radius: 12px;">
                                    <i class="fas fa-arrow-left me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold"
                                        style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); 
                                               border: none; border-radius: 12px;">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar Information -->
            <div class="col-xxl-4 col-lg-5">
                <!-- Application Guidelines -->
                <div class="bg-white rounded-4 shadow-sm border mb-4">
                    <div class="p-4 border-bottom border-light">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-lightbulb text-success fs-5"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h3 class="h5 fw-bold text-dark mb-0">Application Guidelines</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-success rounded-circle text-white me-3 flex-shrink-0 d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px;">
                                    <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark">Complete All Required Fields</p>
                                    <small class="text-muted">Ensure all mandatory information is provided accurately</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="bg-success rounded-circle text-white me-3 flex-shrink-0 d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px;">
                                    <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark">Upload Professional Resume</p>
                                    <small class="text-muted">Use an updated, well-formatted resume in PDF format</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="bg-success rounded-circle text-white me-3 flex-shrink-0 d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px;">
                                    <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark">Write Compelling Cover Letter</p>
                                    <small class="text-muted">Highlight your relevant skills and experience</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="bg-success rounded-circle text-white me-3 flex-shrink-0 d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px;">
                                    <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark">Review Before Submitting</p>
                                    <small class="text-muted">Double-check all information for accuracy</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Requirements -->
                <div class="bg-white rounded-4 shadow-sm border mb-4">
                    <div class="p-4 border-bottom border-light">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-file-alt text-warning fs-5"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h3 class="h5 fw-bold text-dark mb-0">Document Requirements</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="bg-light rounded-3 p-3 mb-4">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="fas fa-file-pdf text-danger me-2"></i>Resume/CV Requirements
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2"><i class="fas fa-dot-circle text-primary me-2"></i>PDF, DOC, or DOCX format</li>
                                <li class="mb-2"><i class="fas fa-dot-circle text-primary me-2"></i>Maximum file size: 5MB</li>
                                <li class="mb-2"><i class="fas fa-dot-circle text-primary me-2"></i>Include complete contact information</li>
                                <li class="mb-0"><i class="fas fa-dot-circle text-primary me-2"></i>List relevant work experience</li>
                            </ul>
                        </div>
                        <div class="alert alert-info border-0 rounded-3 mb-0" style="background: rgba(13, 110, 253, 0.1);">
                            <div class="d-flex">
                                <i class="fas fa-info-circle text-info me-2 mt-1"></i>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark small">Important Note</p>
                                    <small class="text-muted">
                                        Make sure your documents are up-to-date and clearly showcase your qualifications for the position.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Process -->
                <div class="bg-white rounded-4 shadow-sm border">
                    <div class="p-4 border-bottom border-light">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-route text-info fs-5"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h3 class="h5 fw-bold text-dark mb-0">What Happens Next?</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle text-white me-3 flex-shrink-0 fw-bold d-flex align-items-center justify-content-center" 
                                     style="width: 48px; height: 48px; font-size: 1.2rem;">1</div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Application Review</h6>
                                    <small class="text-muted">HR team reviews your application within 3-5 business days</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-info rounded-circle text-white me-3 flex-shrink-0 fw-bold d-flex align-items-center justify-content-center" 
                                     style="width: 48px; height: 48px; font-size: 1.2rem;">2</div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Interview Process</h6>
                                    <small class="text-muted">Multiple rounds including technical and behavioral interviews</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle text-white me-3 flex-shrink-0 fw-bold d-flex align-items-center justify-content-center" 
                                     style="width: 48px; height: 48px; font-size: 1.2rem;">3</div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Final Decision</h6>
                                    <small class="text-muted">Job offer or constructive feedback provided</small>
                                </div>
                            </div>
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
/* Clean White Background Styles */
body {
    background-color: #ffffff !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
}

/* Form Controls */
.form-control:focus, .form-select:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15) !important;
}

.form-control:hover:not(:focus), .form-select:hover:not(:focus) {
    border-color: #adb5bd !important;
}

/* Upload Zone */
.upload-zone {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-zone:hover {
    background-color: rgba(13, 110, 253, 0.05) !important;
    border-color: #0d6efd !important;
    transform: translateY(-2px);
}

.upload-zone:hover .upload-content i {
    transform: scale(1.05);
    color: #0a58ca !important;
}

/* Button Animations */
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.btn-primary:hover {
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3) !important;
}

.btn-light:hover {
    background-color: #f8f9fa !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}

/* Character Counter */
#charCount {
    font-weight: 700;
    transition: color 0.3s ease;
}

/* Card Hover Effects */
.shadow-sm {
    transition: all 0.3s ease;
}

.shadow-sm:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .display-6 {
        font-size: 2rem !important;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem !important;
        font-size: 1rem !important;
    }
}

/* Animations */
.rounded-4 {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* File Upload Styling */
.upload-zone input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter
    const coverLetter = document.getElementById('cover_letter');
    const charCount = document.getElementById('charCount');
    
    if (coverLetter && charCount) {
        coverLetter.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            if (count > 1000) {
                charCount.style.color = '#dc3545';
                this.value = this.value.substring(0, 1000);
                charCount.textContent = '1000';
            } else if (count > 800) {
                charCount.style.color = '#fd7e14';
            } else {
                charCount.style.color = '#6c757d';
            }
        });
    }
    
    // File upload
    const uploadZone = document.querySelector('.upload-zone');
    const fileInput = document.getElementById('resume');
    
    if (uploadZone && fileInput) {
        uploadZone.addEventListener('click', () => fileInput.click());
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const uploadContent = uploadZone.querySelector('.upload-content');
                
                uploadContent.innerHTML = `
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                             style="width: 100px; height: 100px;">
                            <i class="fas fa-file-check text-success" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    <h4 class="h5 fw-bold text-success mb-2">File Selected: ${file.name}</h4>
                    <p class="text-muted mb-0 fs-6">Size: ${(file.size / (1024 * 1024)).toFixed(2)} MB</p>
                    <small class="text-muted mt-2 d-block">Click to change file</small>
                `;
                
                uploadZone.style.borderColor = '#198754';
                uploadZone.style.backgroundColor = 'rgba(25, 135, 84, 0.05)';
            }
        });
        
        // Drag & drop
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#0d6efd';
            this.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
        });
        
        uploadZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#dee2e6';
            this.style.backgroundColor = '';
        });
        
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
            this.style.borderColor = '#dee2e6';
            this.style.backgroundColor = '';
        });
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
});
</script>
@endpush