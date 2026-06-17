@extends('onboarding::portal.layout')

@section('title', 'Employee Onboarding Software | MOM DIGITAL LLC')

@section('styles')
<style>
    /* --- Personio Clone Core Variables --- */
    :root {
        --color-bg-cream: #FAF9F6;
        --color-bg-white: #FFFFFF;
        --color-text-main: #050505;
        --color-text-sub: #5E5E5E;
        --color-accent-orange: #FFC062;
        --color-accent-yellow: #FFD43B;
        --color-footer-bg: #1A1A1A;
        --radius-pill: 50px;
        --radius-section: 40px;
    }

    body {
        background-color: var(--color-bg-cream);
        color: var(--color-text-main);
        font-family: 'Inter', sans-serif;
    }

    /* --- Typography Overrides --- */
    h1, h2, h3, h4 {
        color: var(--color-text-main);
        letter-spacing: -0.01em;
    }

    .display-hero {
        font-size: 4.5rem;
        font-weight: 800;
        line-height: 1.05;
        margin-bottom: 2rem;
        text-align: center;
    }

    .display-section {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }

    .text-body-lg {
        font-size: 1.25rem;
        line-height: 1.6;
        color: var(--color-text-sub);
    }

    /* --- Utilities --- */
    .bg-cream { background-color: var(--color-bg-cream); }
    .bg-white-rounded { 
        background-color: var(--color-bg-white); 
        border-radius: var(--radius-section);
        padding: 5rem 2rem;
        margin: 4rem 1rem;
    }

    /* --- Hero Section Refined --- */
    .hero-container {
        padding: 8rem 0 6rem;
        margin-bottom: 2rem;
        /* Warm subtle gradient burst behind center */
        background: radial-gradient(circle at center top, rgba(255, 192, 98, 0.15) 0%, transparent 50%);
    }

    .hero-content {
        max-width: 900px;
        margin: 0 auto;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Pill Buttons */
    .btn-personio {
        background-color: var(--color-text-main);
        color: white;
        padding: 1rem 2.5rem;
        border-radius: var(--radius-pill);
        font-weight: 700;
        transition: all 0.3s ease;
        border: 2px solid var(--color-text-main);
    }

    .btn-personio:hover {
        background-color: #333;
        border-color: #333;
        color: white;
        transform: translateY(-2px);
    }

    .btn-personio-outline {
        background-color: transparent;
        color: var(--color-text-main);
        padding: 1rem 2.5rem;
        border-radius: var(--radius-pill);
        font-weight: 700;
        transition: all 0.3s ease;
        border: 2px solid #E5E5E5;
    }

    .btn-personio-outline:hover {
        border-color: var(--color-text-main);
    }

    /* --- Sticky Scroll CSS UI Mockups --- */
    .sticky-scroll-wrapper {
        position: relative;
    }

    .sticky-feature-row {
        display: flex;
        gap: 5rem;
        position: relative;
    }

    .feature-text-col {
        flex: 1;
        padding-top: 100px;
    }

    .feature-visual-col {
        flex: 1;
        position: sticky;
        top: 150px;
        height: 500px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .feature-text-block {
        min-height: 600px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        opacity: 0.2;
        transition: opacity 0.5s ease;
        padding-left: 2rem;
        border-left: 4px solid transparent;
    }

    .feature-text-block.active {
        opacity: 1;
        border-left-color: var(--color-text-main);
    }

    /* CSS UI Mockup Container (Updated for Images) */
    .ui-mockup-wrapper {
        width: 100%;
        /* Remove fixed height to allow image aspect ratio */
        height: auto; 
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 40px 80px -20px rgba(0,0,0,0.15);
        opacity: 0;
        transition: all 0.5s ease;
        transform: translateY(20px);
        position: absolute;
        top: 0;
        left: 0;
        /* Border handled by image or wrapper */
    }

    .ui-mockup-wrapper img {
        width: 100%;
        height: auto;
        display: block;
    }

    .ui-mockup-wrapper.active {
        opacity: 1;
        transform: translateY(0);
    }

    /* --- Footer Refined (Dark) --- */
    .personio-footer {
        background-color: #1A1A1A !important;
        color: white !important;
        padding: 6rem 2rem;
        margin: 4rem 1rem 2rem;
        border-radius: var(--radius-section);
        position: relative;
        z-index: 10;
    }

    .personio-footer h2, .personio-footer h6, .personio-footer h1, .personio-footer p, .personio-footer li {
        color: white !important;
    }

    .footer-heading {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }
    
    .personio-footer ul li {
        color: rgba(255, 255, 255, 0.7) !important;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        cursor: pointer;
        transition: color 0.2s;
    }

    .personio-footer ul li:hover {
        color: white !important;
    }

    /* Hide Default Layout Footer */
    body > footer.footer {
        display: none !important;
    }

    /* Mobile */
    @media (max-width: 991px) {
        .display-hero { font-size: 3rem; }
        .hero-content { align-items: stretch; text-align: left; }
        .sticky-feature-row { flex-direction: column-reverse; }
        .feature-visual-col { height: 350px; position: relative; top: 0; }
        .feature-text-block { min-height: auto; padding: 3rem 0; opacity: 1; border: none; }
        .ui-mockup-wrapper { position: relative; opacity: 1; transform: none; display: none; }
        .ui-mockup-wrapper.active { display: block; }
        .personio-footer { padding: 3rem 1rem; }
    }
</style>
@endsection

@section('content')

<!-- 1. Hero Section (Centered & Warm) -->
<section class="hero-container">
    <div class="container">
        <div class="hero-content">
            
            <h1 class="display-hero">
                Welcome to the <br>
                <span style="background: linear-gradient(120deg, #FFC062 0%, #FFD43B 100%); padding: 0 15px; border-radius: 12px;">{{ getSetting('site_title') }}</span> Team!
            </h1>
            
            <p class="text-body-lg mb-5" style="max-width: 700px; margin-bottom: 3rem;">
                We are thrilled to have you join us. This portal is your personal guide to a smooth start. Let's get you settled in so you can enjoy your journey with us.
            </p>

            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center w-100">
                <a href="{{ route('portal.login') }}" class="btn btn-personio">
                    Start Onboarding
                </a>
                <a href="#schedule" class="btn btn-personio-outline">
                    View Schedule
                </a>
            </div>

            <!-- Removed Hero Visual Image as per user request -->

        </div>
    </div>
</section>

<!-- 2. Sticky Scroll Feature Section (Refined UI Mockups) -->
<section class="bg-white-rounded">
    <div class="container sticky-scroll-wrapper">
        <div class="text-center mb-5">
            <h2 class="display-section">Your Journey Starts Here</h2>
            <p class="text-body-lg text-muted">Three simple steps to help you settle in and succeed.</p>
        </div>

        <div class="sticky-feature-row">
            <!-- Left: Scrolling Text -->
            <div class="feature-text-col">
                <div class="feature-text-block active" data-target="ui-1">
                    <h3 class="font-weight-bold mb-3">1. Get Ready</h3>
                    <p class="text-body-lg text-muted">
                        Complete your profile, sign your contract, and upload necessary documents before day one.
                    </p>
                </div>
                <div class="feature-text-block" data-target="ui-2">
                    <h3 class="font-weight-bold mb-3">2. First Day</h3>
                    <p class="text-body-lg text-muted">
                        Meet your team, pick up your equipment, and get access to all the tools you need.
                    </p>
                </div>
                <div class="feature-text-block" data-target="ui-3">
                    <h3 class="font-weight-bold mb-3">3. Settling In</h3>
                    <p class="text-body-lg text-muted">
                        Set your initial goals, receive feedback, and start making an impact with confidence.
                    </p>
                </div>
            </div>

            <!-- Right: CSS UI Mocks -->
            <div class="feature-visual-col">
                <!-- UI 1: Contract -->
                <div id="ui-1" class="ui-mockup-wrapper active">
                    <img src="{{ asset('assets/img/personio/feature-contracts.avif') }}" alt="Contract Management">
                </div>

                <!-- UI 2: Tasks -->
                <div id="ui-2" class="ui-mockup-wrapper">
                    <img src="{{ asset('assets/img/personio/feature-tasks.png') }}" alt="Task Management">
                </div>

                <!-- UI 3: Feedback -->
                <div id="ui-3" class="ui-mockup-wrapper">
                    <img src="{{ asset('assets/img/personio/feature-feedback.png') }}" alt="Feedback Loops">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Footer (Dark Rounded) -->
<footer class="personio-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mb-5 mb-lg-0">
                <h2 class="footer-heading">{{ getSetting('site_title') }}</h2>
                <div class="d-flex gap-3 mb-4">
                    <!-- Fake App Store Buttons -->
                    <div style="width: 140px; height: 45px; background: #333; border-radius: 8px; border: 1px solid #555;"></div>
                    <div style="width: 140px; height: 45px; background: #333; border-radius: 8px; border: 1px solid #555;"></div>
                </div>
                <p style="color: rgba(255,255,255,0.5) !important;">
                    &copy; {{ date('Y') }} {{ getSetting('site_title') }}. All rights reserved.
                </p>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="font-weight-bold mb-4">Support</h6>
                <ul class="list-unstyled">
                    <li>IT Helpdesk</li>
                    <li>HR Contact</li>
                    <li>Office Map</li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="font-weight-bold mb-4">Company</h6>
                <ul class="list-unstyled">
                    <li>About Us</li>
                    <li>Culture</li>
                    <li>Events</li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6 class="font-weight-bold mb-4">Resources</h6>
                <ul class="list-unstyled">
                    <li>Employee Handbook</li>
                    <li>Benefits</li>
                    <li>Policy Docs</li>
                </ul>
            </div>
        </div>
    </div>
</footer>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Intersection Observer for Sticky Scroll
        const options = {
            root: null,
            rootMargin: '-40% 0px -40% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    document.querySelectorAll('.feature-text-block').forEach(el => el.classList.remove('active'));
                    entry.target.classList.add('active');

                    const targetId = entry.target.getAttribute('data-target');
                    document.querySelectorAll('.ui-mockup-wrapper').forEach(el => el.classList.remove('active'));
                    const targetUi = document.getElementById(targetId);
                    if (targetUi) targetUi.classList.add('active');
                }
            });
        }, options);

        document.querySelectorAll('.feature-text-block').forEach(block => {
            observer.observe(block);
        });
    });
</script>
@endsection
