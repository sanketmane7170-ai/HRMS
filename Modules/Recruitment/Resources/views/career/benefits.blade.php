<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benefits - {{ config('app.name') }} Careers</title>
    <link rel="shortcut icon" href="{{ getSmallLogo() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%); color: white; padding: 80px 0; }
        .benefit-card { padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; text-align: center; }
        .benefit-icon { font-size: 3rem; color: #1e40af; margin-bottom: 20px; }
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
                    <li class="nav-item"><a class="nav-link active" href="{{ route('career.benefits') }}">Benefits</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.faq') }}">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.track-application') }}">Track Application</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Employee Benefits</h1>
            <p class="lead mb-4">We care about our employees and offer comprehensive benefits</p>
        </div>
    </section>

    <!-- Benefits Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h4>Health Insurance</h4>
                    <p>Comprehensive health coverage for you and your family</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4>Flexible Hours</h4>
                    <p>Work-life balance with flexible working hours</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4>Learning & Development</h4>
                    <p>Continuous learning opportunities and skill development</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h4>Remote Work</h4>
                    <p>Option to work from home when needed</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <h4>Retirement Plan</h4>
                    <p>Secure your future with our retirement savings plan</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h4>Paid Time Off</h4>
                    <p>Generous vacation and sick leave policies</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>