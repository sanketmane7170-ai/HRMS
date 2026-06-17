<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $job->title }} - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ getSmallLogo() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section { 
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%); 
            color: white; 
            padding: 80px 0; 
        }
        .job-detail-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .job-meta {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .job-meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .job-meta-item:last-child {
            margin-bottom: 0;
        }
        .job-meta-item i {
            width: 20px;
            color: #3b82f6;
            margin-right: 0.5rem;
        }
        .salary-range {
            color: #059669;
            font-weight: 600;
        }
        .btn-apply {
            background: #3b82f6;
            border-color: #3b82f6;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-apply:hover {
            background: #2563eb;
            border-color: #2563eb;
        }
        .related-jobs {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
        }
        .job-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .job-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
            color: inherit;
        }
        .requirements-list, .benefits-list {
            padding-left: 0;
            list-style: none;
        }
        .requirements-list li, .benefits-list li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .requirements-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #059669;
            font-weight: bold;
        }
        .benefits-list li:before {
            content: '★';
            position: absolute;
            left: 0;
            color: #f59e0b;
            font-weight: bold;
        }
        .sticky-apply {
            position: sticky;
            top: 20px;
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
                    <h1 class="display-5 fw-bold mb-3">{{ $job->title }}</h1>
                    <p class="lead">{{ $job->department->name }} Department</p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="{{ route('career.index') }}" class="text-white">Careers</a></li>
                            <li class="breadcrumb-item active text-white-50">Job Details</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Job Details Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="job-detail-card mb-4">
                        <div class="card-body p-4">
                            <!-- Job Meta Information -->
                            <div class="job-meta">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="job-meta-item">
                                            <i class="fas fa-building"></i>
                                            <span><strong>Department:</strong> {{ $job->department->name }}</span>
                                        </div>
                                        @if($job->location)
                                        <div class="job-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><strong>Location:</strong> {{ $job->location }}</span>
                                        </div>
                                        @endif
                                        <div class="job-meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $job->job_type ?? $job->hiring_type ?? 'Full Time')) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @if($job->experience_level)
                                        <div class="job-meta-item">
                                            <i class="fas fa-user-graduate"></i>
                                            <span><strong>Experience:</strong> {{ ucfirst($job->experience_level) }} Level</span>
                                        </div>
                                        @endif
                                        @if($job->min_salary && $job->max_salary)
                                        <div class="job-meta-item">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span class="salary-range"><strong>Salary:</strong> ${{ number_format($job->min_salary) }} - ${{ number_format($job->max_salary) }}</span>
                                        </div>
                                        @endif
                                        @if($job->positions_available > 1)
                                        <div class="job-meta-item">
                                            <i class="fas fa-users"></i>
                                            <span><strong>Openings:</strong> {{ $job->positions_available }} positions</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($job->remote_work || $job->application_deadline || $job->is_featured)
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    @if($job->remote_work)
                                        <span class="badge bg-success"><i class="fas fa-home me-1"></i> Remote Work Available</span>
                                    @endif
                                    @if($job->is_featured)
                                        <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i> Featured Position</span>
                                    @endif
                                    @if($job->application_deadline)
                                        <span class="badge bg-danger"><i class="fas fa-calendar me-1"></i> Apply by {{ $job->application_deadline->format('M d, Y') }}</span>
                                    @endif
                                </div>
                                @endif
                            </div>

                            <!-- Job Description -->
                            <div class="mb-4">
                                <h3 class="h4 fw-bold mb-3">About This Role</h3>
                                <div class="text-muted">{!! nl2br(e($job->description)) !!}</div>
                            </div>

                            <!-- Requirements -->
                            @if($job->requirements && is_array($job->requirements) && count($job->requirements) > 0)
                            <div class="mb-4">
                                <h3 class="h4 fw-bold mb-3" style="color: #1e40af;">Requirements</h3>
                                <ul class="requirements-list">
                                    @foreach($job->requirements as $requirement)
                                        <li>{{ $requirement }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @elseif($job->requirements && !is_array($job->requirements))
                            <div class="mb-4">
                                <h3 class="h4 fw-bold mb-3" style="color: #1e40af;">Requirements</h3>
                                <div class="text-muted">{!! nl2br(e($job->requirements)) !!}</div>
                            </div>
                            @endif

                            <!-- Responsibilities -->
                            @if($job->responsibilities)
                            <div class="mb-4">
                                <h3 class="h4 fw-bold mb-3" style="color: #7c3aed;">Responsibilities</h3>
                                <div class="text-muted">{!! nl2br(e($job->responsibilities)) !!}</div>
                            </div>
                            @endif

                            <!-- Skills Required -->
                            @if($job->skills && is_array($job->skills) && count($job->skills) > 0)
                            <div class="mb-4">
                                <h3 class="h4 fw-bold mb-3" style="color: #059669;">Skills Required</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($job->skills as $skill)
                                        <span class="badge bg-primary rounded-pill">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Benefits -->
                            @if($job->benefits)
                            <div class="mb-4">
                                <h3 class="h4 fw-bold mb-3">What We Offer</h3>
                                <div class="text-muted">{!! nl2br(e($job->benefits)) !!}</div>
                            </div>
                            @endif

                            <!-- Application CTA -->
                            <div class="text-center py-4">
                                <h4 class="mb-3">Ready to Join Our Team?</h4>
                                <p class="text-muted mb-4">We'd love to hear from you and learn more about your experience and passion.</p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="{{ route('career.apply', $job->id) }}" class="btn btn-primary btn-apply">
                                        <i class="fas fa-paper-plane me-2"></i>Apply Now
                                    </a>
                                    <a href="{{ route('career.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>View All Jobs
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sticky-apply">
                        <!-- Quick Apply Card -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <h5 class="fw-bold mb-3">Interested in This Position?</h5>
                                <p class="text-muted small mb-3">Join our team and make an impact</p>
                                <a href="{{ route('career.apply', $job->id) }}" class="btn btn-primary btn-apply w-100 mb-2">
                                    <i class="fas fa-paper-plane me-2"></i>Apply for This Job
                                </a>
                                <small class="text-muted d-block">
                                    Application takes 5-10 minutes
                                </small>
                            </div>
                        </div>

                        <!-- Company Info -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3">About {{ config('app.name') }}</h5>
                                <p class="text-muted small mb-3">
                                    We're a forward-thinking technology company focused on delivering innovative solutions that make a real difference.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('career.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-info-circle me-1"></i>More Jobs
                                    </a>
                                    <a href="{{ route('career.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-briefcase me-1"></i>View All Openings
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Related Jobs -->
                        @if($relatedJobs && count($relatedJobs) > 0)
                        <div class="related-jobs">
                            <h5 class="fw-bold mb-3">Similar Opportunities</h5>
                            @foreach($relatedJobs as $relatedJob)
                                <a href="{{ route('career.job-detail', $relatedJob->id) }}" class="job-card mb-2">
                                    <h6 class="fw-bold mb-1">{{ $relatedJob->title }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>{{ $relatedJob->department->name }}
                                        @if($relatedJob->location)
                                            <i class="fas fa-map-marker-alt ms-2 me-1"></i>{{ $relatedJob->location }}
                                        @endif
                                    </small>
                                </a>
                            @endforeach
                            <a href="{{ route('career.index') }}" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                View All Jobs
                            </a>
                        </div>
                        @endif
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
                    <a href="#" class="text-white-50 me-3">About</a>
                    <a href="#" class="text-white-50">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>