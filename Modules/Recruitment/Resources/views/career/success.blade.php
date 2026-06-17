<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Success - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ getSmallLogo() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section { 
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%); 
            color: white; 
            padding: 80px 0; 
        }
        .success-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }
        .success-icon {
            font-size: 4rem;
            color: #059669;
            margin-bottom: 1rem;
        }
        .application-details {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }
        .detail-value {
            color: #2d3748;
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
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Application Submitted Successfully!</h1>
            <p class="lead mb-4">Thank you for your interest in joining {{ config('app.name') }}</p>
        </div>
    </section>

    <!-- Success Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="success-card text-center">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    
                    <h2 class="mb-3">Your application has been received!</h2>
                    <p class="text-muted mb-4">
                        We have successfully received your application for the <strong>{{ $application->job->title }}</strong> position. 
                        Our recruitment team will review your application and get back to you soon.
                    </p>

                    <!-- Application Details -->
                    <div class="application-details">
                        <h5 class="mb-3">Application Details</h5>
                        <div class="detail-row">
                            <span class="detail-label">Application ID:</span>
                            <span class="detail-value">#{{ $application->id }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Position:</span>
                            <span class="detail-value">{{ $application->job->title }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Department:</span>
                            <span class="detail-value">{{ $application->job->department->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Submitted:</span>
                            <span class="detail-value">{{ $application->applied_at->format('F d, Y \a\t g:i A') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value"><span class="badge bg-primary">{{ ucfirst($application->stage) }}</span></span>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>What happens next?</h6>
                        <ul class="list-unstyled mb-0 text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Our HR team will review your application within 2-3 business days</li>
                            <li><i class="fas fa-check text-success me-2"></i>If you're a good fit, we'll contact you for a preliminary interview</li>
                            <li><i class="fas fa-check text-success me-2"></i>You can track your application status using your Application ID</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('career.track-application') }}" class="btn btn-primary me-md-2">
                            <i class="fas fa-search me-2"></i>Track Application
                        </a>
                        <a href="{{ route('career.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-briefcase me-2"></i>View More Jobs
                        </a>
                    </div>

                    <!-- Contact Information -->
                    <div class="mt-4 pt-3 border-top">
                        <p class="text-muted mb-0">
                            <small>
                                Have questions? Contact our HR team at 
                                <a href="mailto:careers@momdigital.com">careers@momdigital.com</a>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
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