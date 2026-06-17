<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - {{ config('app.name') }} Careers</title>
    <link rel="shortcut icon" href="{{ getSmallLogo() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%); color: white; padding: 80px 0; }
        .feature-card { padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
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
                    <li class="nav-item"><a class="nav-link active" href="{{ route('career.about') }}">About Us</a></li>
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
            <h1 class="display-4 fw-bold mb-4">About {{ config('app.name') }}</h1>
            <p class="lead mb-4">Learn more about our company, mission, and values</p>
        </div>
    </section>

    <!-- Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="feature-card">
                    <h2 class="mb-4">Our Company</h2>
                    <p class="mb-4">{{ config('app.name') }} is a leading technology company dedicated to providing innovative HR management solutions. We help businesses streamline their human resources processes and create better workplace experiences.</p>
                    
                    <h3 class="mb-3">Our Mission</h3>
                    <p class="mb-4">To revolutionize the way companies manage their human resources through cutting-edge technology and user-centric design.</p>
                    
                    <h3 class="mb-3">Our Values</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Innovation and Excellence</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Customer-First Approach</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Integrity and Transparency</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Continuous Learning</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Team Collaboration</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>