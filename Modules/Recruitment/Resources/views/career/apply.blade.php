<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for {{ $job->title }} - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ getSmallLogo() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section { 
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%); 
            color: white; 
            padding: 60px 0; 
        }
        .application-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        .job-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        .btn-primary {
            background: #3b82f6;
            border-color: #3b82f6;
            padding: 12px 30px;
        }
        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }
        .salary-range {
            color: #059669;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('career.index') }}">{{ config('app.name') }} Careers</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.index') }}">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.about') }}">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.benefits') }}">Benefits</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.faq') }}">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.track-application') }}">Track Application</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-5 fw-bold mb-3">Apply for {{ $job->title }}</h1>
                    <p class="lead">{{ $job->department->name }} Department</p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="{{ route('career.index') }}" class="text-white">Careers</a></li>
                            <li class="breadcrumb-item active text-white-50">Apply</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Application Form Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Job Details Sidebar -->
                <div class="col-lg-4">
                    <div class="job-details sticky-top">
                        <h4 class="h5 fw-bold mb-3">{{ $job->title }}</h4>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Department</small>
                            <span class="fw-medium">{{ $job->department->name }}</span>
                        </div>

                        @if($job->location)
                        <div class="mb-3">
                            <small class="text-muted d-block">Location</small>
                            <span class="fw-medium"><i class="fas fa-map-marker-alt me-1"></i> {{ $job->location }}</span>
                        </div>
                        @endif

                        <div class="mb-3">
                            <small class="text-muted d-block">Employment Type</small>
                            <span class="fw-medium">{{ ucfirst(str_replace('_', ' ', $job->job_type)) }}</span>
                        </div>

                        @if($job->experience_level)
                        <div class="mb-3">
                            <small class="text-muted d-block">Experience Level</small>
                            <span class="fw-medium">{{ ucfirst($job->experience_level) }} Level</span>
                        </div>
                        @endif

                        @if($job->min_salary && $job->max_salary)
                        <div class="mb-3">
                            <small class="text-muted d-block">Salary Range</small>
                            <span class="salary-range">${{ number_format($job->min_salary) }} - ${{ number_format($job->max_salary) }}</span>
                        </div>
                        @endif

                        <hr class="my-3">
                        
                        <!-- Job Description -->
                        <div class="mb-3">
                            <h6 class="fw-bold mb-2">About This Role</h6>
                            <p class="small text-muted">{{ \Illuminate\Support\Str::limit($job->description, 200) }}</p>
                        </div>

                        <!-- Requirements -->
                        @if($job->requirements && is_array($job->requirements) && count($job->requirements) > 0)
                        <div class="mb-3">
                            <h6 class="fw-bold mb-2" style="color: #1e40af;">Requirements</h6>
                            <ul class="small text-muted ps-3">
                                @foreach(array_slice($job->requirements, 0, 3) as $requirement)
                                    <li>{{ $requirement }}</li>
                                @endforeach
                                @if(count($job->requirements) > 3)
                                    <li><em>...and {{ count($job->requirements) - 3 }} more</em></li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        <!-- Skills Required -->
                        @if($job->skills && is_array($job->skills) && count($job->skills) > 0)
                        <div class="mb-3">
                            <h6 class="fw-bold mb-2" style="color: #059669;">Skills Required</h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach(array_slice($job->skills, 0, 5) as $skill)
                                    <span class="badge bg-light text-dark border small">{{ $skill }}</span>
                                @endforeach
                                @if(count($job->skills) > 5)
                                    <span class="badge bg-secondary small">+{{ count($job->skills) - 5 }} more</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($job->remote_work)
                        <div class="mb-3">
                            <span class="badge bg-success"><i class="fas fa-home me-1"></i> Remote Work Available</span>
                        </div>
                        @endif

                        @if($job->application_deadline)
                        <div class="mb-3">
                            <small class="text-muted d-block">Application Deadline</small>
                            <span class="fw-medium text-danger">{{ $job->application_deadline->format('F d, Y') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Application Form -->
                <div class="col-lg-8">
                    <div class="application-form">
                        <h3 class="h4 fw-bold mb-4">Submit Your Application</h3>

                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('career.submit-application', $job->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Personal Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="fw-bold text-secondary mb-3">Personal Information</h5>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="candidate_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('candidate_name') is-invalid @enderror" 
                                               id="candidate_name" name="candidate_name" value="{{ old('candidate_name') }}" required>
                                        @error('candidate_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="candidate_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('candidate_email') is-invalid @enderror" 
                                               id="candidate_email" name="candidate_email" value="{{ old('candidate_email') }}" required>
                                        @error('candidate_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="candidate_phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control @error('candidate_phone') is-invalid @enderror" 
                                               id="candidate_phone" name="candidate_phone" value="{{ old('candidate_phone') }}">
                                        @error('candidate_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="fw-bold text-secondary mb-3">Professional Information</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="current_position" class="form-label">Current Position</label>
                                        <input type="text" class="form-control @error('current_position') is-invalid @enderror" 
                                               id="current_position" name="current_position" value="{{ old('current_position') }}" 
                                               placeholder="e.g. Senior Software Developer">
                                        @error('current_position')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="current_company" class="form-label">Current Company</label>
                                        <input type="text" class="form-control @error('current_company') is-invalid @enderror" 
                                               id="current_company" name="current_company" value="{{ old('current_company') }}" 
                                               placeholder="e.g. Tech Corp Inc">
                                        @error('current_company')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="years_experience" class="form-label">Years of Experience</label>
                                        <input type="number" class="form-control @error('years_experience') is-invalid @enderror" 
                                               id="years_experience" name="years_experience" value="{{ old('years_experience') }}" 
                                               min="0" max="50" placeholder="e.g. 5">
                                        @error('years_experience')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="expected_salary" class="form-label">Expected Salary</label>
                                        <input type="number" class="form-control @error('expected_salary') is-invalid @enderror" 
                                               id="expected_salary" name="expected_salary" value="{{ old('expected_salary') }}" 
                                               placeholder="Annual salary expectation">
                                        @error('expected_salary')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Documents -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="fw-bold text-secondary mb-3">Documents</h5>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="resume" class="form-label">Resume/CV <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('resume') is-invalid @enderror" 
                                               id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                        <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX (Max: 5MB)</small>
                                        @error('resume')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="mb-4">
                                <h5 class="fw-bold text-secondary mb-3">Additional Information</h5>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="cover_letter" class="form-label">Cover Letter / Why are you interested? <span class="text-danger">*</span></label>
                                            <textarea class="form-control @error('cover_letter') is-invalid @enderror" 
                                                      id="cover_letter" name="cover_letter" rows="4" required
                                                      placeholder="Tell us why you're excited about this opportunity...">{{ old('cover_letter') }}</textarea>
                                            @error('cover_letter')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="linkedin_url" class="form-label">LinkedIn Profile</label>
                                            <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" 
                                                   id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url') }}" 
                                                   placeholder="https://linkedin.com/in/yourprofile">
                                            @error('linkedin_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="portfolio_url" class="form-label">Portfolio Website</label>
                                            <input type="url" class="form-control @error('portfolio_url') is-invalid @enderror" 
                                                   id="portfolio_url" name="portfolio_url" value="{{ old('portfolio_url') }}" 
                                                   placeholder="https://yourportfolio.com">
                                            @error('portfolio_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="notice_period" class="form-label">Notice Period (days)</label>
                                            <input type="number" class="form-control @error('notice_period') is-invalid @enderror" 
                                                   id="notice_period" name="notice_period" value="{{ old('notice_period') }}" 
                                                   min="0" max="365" placeholder="e.g. 30">
                                            @error('notice_period')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="availability_date" class="form-label">Available From</label>
                                            <input type="date" class="form-control @error('availability_date') is-invalid @enderror" 
                                                   id="availability_date" name="availability_date" value="{{ old('availability_date') }}">
                                            @error('availability_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <div class="form-check mt-4">
                                                <input type="checkbox" class="form-check-input @error('willing_to_relocate') is-invalid @enderror" 
                                                       id="willing_to_relocate" name="willing_to_relocate" value="1" {{ old('willing_to_relocate') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="willing_to_relocate">
                                                    Willing to Relocate
                                                </label>
                                                @error('willing_to_relocate')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Consent & Authorization -->
                            <div class="mb-4">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input @error('authorization_to_work') is-invalid @enderror" 
                                           id="authorization_to_work" name="authorization_to_work" value="1" {{ old('authorization_to_work') ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="authorization_to_work">
                                        I am authorized to work in this location <span class="text-danger">*</span>
                                    </label>
                                    @error('authorization_to_work')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input @error('terms_accepted') is-invalid @enderror" 
                                           id="terms_accepted" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="terms_accepted">
                                        I accept the terms and conditions and consent to data processing <span class="text-danger">*</span>
                                    </label>
                                    @error('terms_accepted')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex justify-content-end gap-3">
                                <a href="{{ route('career.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Jobs
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 {{ config('app.name') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('career.index') }}" class="text-white-50 me-3">Careers</a>
                    <a href="#" class="text-white-50 me-3">Privacy Policy</a>
                    <a href="#" class="text-white-50">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload validation
        document.getElementById('resume').addEventListener('change', function(e) {
            validateFile(e.target, 5);
        });
        
        document.getElementById('cover_letter').addEventListener('change', function(e) {
            validateFile(e.target, 5);
        });
        
        function validateFile(input, maxSizeMB) {
            const file = input.files[0];
            if (file) {
                const maxSize = maxSizeMB * 1024 * 1024; // Convert to bytes
                if (file.size > maxSize) {
                    alert(`File size must be less than ${maxSizeMB}MB`);
                    input.value = '';
                }
            }
        }
    </script>
</body>
</html>