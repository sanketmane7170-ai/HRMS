<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ getSmallLogo() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%); color: white; padding: 80px 0; }
        .job-card { transition: transform 0.2s; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .job-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .filter-card { background: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px; }
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
                    <li class="nav-item"><a class="nav-link active" href="{{ route('career.index') }}">Jobs</a></li>
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
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Join Our Amazing Team</h1>
            <p class="lead mb-4">Discover exciting career opportunities and grow with us</p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <form action="{{ route('career.index') }}" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search jobs..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-light"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Filters -->
        <div class="filter-card">
            <form action="{{ route('career.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Job Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($jobTypes as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Location</label>
                        <select name="location" class="form-select">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Filter Jobs</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Jobs Grid -->
        <div class="row">
            @forelse($jobs as $job)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card job-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title text-primary">{{ $job->title }}</h5>
                            @if($job->is_featured)
                                <span class="badge bg-warning text-dark">Featured</span>
                            @endif
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-muted mb-1"><i class="fas fa-building me-2"></i>{{ $job->department->name ?? 'N/A' }}</p>
                            <p class="text-muted mb-1"><i class="fas fa-map-marker-alt me-2"></i>{{ $job->location ?: 'Remote' }}</p>
                            <p class="text-muted mb-1">
                                <i class="fas fa-clock me-2"></i>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $job->job_type)) }}</span>
                            </p>
                        </div>
                        
                        <p class="card-text">{{ Str::limit(strip_tags($job->description), 100) }}</p>
                        
                        @if($job->min_salary && $job->max_salary)
                        <p class="text-success fw-bold">
                            <i class="fas fa-dollar-sign me-1"></i>
                            ${{ number_format($job->min_salary) }} - ${{ number_format($job->max_salary) }}
                        </p>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Posted {{ $job->created_at->diffForHumans() }}</small>
                            @if($job->application_deadline)
                                <small class="text-{{ \Carbon\Carbon::parse($job->application_deadline)->isPast() ? 'danger' : 'warning' }}">
                                    Deadline: {{ \Carbon\Carbon::parse($job->application_deadline)->format('M d') }}
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="{{ route('career.job-detail', $job->id) }}" class="btn btn-outline-primary me-md-2 flex-fill">
                                View Details
                            </a>
                            @if($job->is_open)
                                <a href="{{ route('career.apply', $job->id) }}" class="btn btn-primary flex-fill">
                                    Apply Now
                                </a>
                            @else
                                <button class="btn btn-secondary flex-fill" disabled>Closed</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No jobs found</h4>
                    <p class="text-muted">Try adjusting your filters or check back later for new opportunities.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($jobs->hasPages())
        <div class="d-flex justify-content-center">
            {{ $jobs->withQueryString()->links() }}
        </div>
        @endif
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>{{ config('app.name') }} Careers</h5>
                    <p>Join our team and build the future together.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>