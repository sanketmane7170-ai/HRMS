<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Application - {{ config('app.name') }} Careers</title>
    <link rel="shortcut icon" href="{{ getSmallLogo() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%); color: white; padding: 80px 0; }
        .track-card { padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
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
                    <li class="nav-item"><a class="nav-link active" href="{{ route('career.track-application') }}">Track Application</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Track Your Application</h1>
            <p class="lead mb-4">Enter your application details to check the status</p>
        </div>
    </section>

    <!-- Track Application Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="track-card">
                    <form method="POST" action="{{ route('career.track-application') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="application_id" class="form-label">Application ID</label>
                            <input type="text" class="form-control" id="application_id" name="application_id" required placeholder="Enter your application ID">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email address">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Track Application
                        </button>
                    </form>
                    
                    @if(session('error'))
                        <div class="alert alert-danger mt-4">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger mt-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li><i class="fas fa-exclamation-circle me-2"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            You received your application ID via email after submitting your application.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>