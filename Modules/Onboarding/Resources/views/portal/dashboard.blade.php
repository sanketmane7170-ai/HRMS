@extends('onboarding::portal.layout')

@section('title', 'My Onboarding')

@section('styles')
<style>
    /* --- Premium Dashboard Styles --- */
    /* (Styles inherited from previous version, kept concise) */
    .hero-section { padding: 4rem 0; position: relative; }
    .status-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: rgba(37, 99, 235, 0.05); color: var(--primary); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase; border-radius: 100px; margin-bottom: 1.5rem; }
    .journey-tile { background: #ffffff; border: 1px solid rgba(241, 245, 249, 1); border-radius: var(--radius-lg); padding: 1.5rem; transition: var(--transition); cursor: pointer; position: relative; overflow: hidden; }
    .journey-tile:hover:not(.locked) { border-color: var(--primary); transform: translateY(-4px); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05); }
    .journey-tile.locked { background: #fcfdfe; opacity: 0.7; cursor: not-allowed; }
    .tile-step { width: 48px; height: 48px; border-radius: 14px; background: #f8fafc; border: 1px solid var(--border-soft); display: flex; align-items: center; justify-content: center; font-weight: 800; color: #94a3b8; font-size: 0.9rem; transition: var(--transition); }
    .journey-tile:hover .tile-step { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .journey-tile.completed .tile-step { background: #059669; border-color: #059669; color: #fff; }
    .btn-action { padding: 10px 24px; font-weight: 700; font-size: 0.8rem; letter-spacing: 0.02em; text-transform: uppercase; }
    .readiness-card { background: #fff; border: 1px solid var(--border-soft); border-radius: var(--radius-lg); padding: 2rem; }
    .premium-progress { height: 8px; background: #f1f5f9; border-radius: 100px; overflow: hidden; }
    .sidebar-card { border-radius: var(--radius-lg); padding: 1.75rem; border: 1px solid var(--border-soft); background: #fff; }
    .support-dark { background: var(--dark); color: #fff; }
    .pulse { animation: premiumPulse 2s infinite; }
    @keyframes premiumPulse { 0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2); } 70% { box-shadow: 0 0 0 15px rgba(37, 99, 235, 0); } 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); } }
</style>
@endsection

@section('content')
<div class="container fade-in-up">
    <!-- Hero Header -->
    <div class="hero-section row align-items-center">
        <div class="col-lg-7">
            <div class="status-pill">
                <span class="logo-dot"></span> {{ getSetting('site_title') }} — Onboarding Phase
            </div>
            <h1 class="display-4 font-weight-bold mb-3" style="letter-spacing: -2px; line-height: 1.1;">
                Welcome home, <span class="text-primary">{{ explode(' ', $user->name)[0] }}</span>.
            </h1>
            <p class="text-muted lead mb-0" style="max-width: 500px;">
                Your journey at {{ getSetting('site_title') }} begins here. Complete your setup to officially join the team.
            </p>
        </div>
        <div class="col-lg-5 mt-5 mt-lg-0">
            <div class="readiness-card shadow-premium">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h6 class="font-weight-bold text-uppercase small text-muted mb-1" style="letter-spacing: 1px;">Overall Readiness</h6>
                        <div class="h3 font-weight-bold mb-0 text-dark">{{ $user->onboardingRecord->progress_percent ?? 0 }}%</div>
                    </div>
                    <div class="text-right">
                        @php $status = ($user->onboardingRecord->progress_percent ?? 0) >= 100 ? 'Complete' : 'In Progress'; @endphp
                        <span class="badge {{ $status == 'Complete' ? 'bg-success text-white' : 'bg-primary-soft text-primary' }}">
                            {{ strtoupper($status) }}
                        </span>
                    </div>
                </div>
                <div class="premium-progress">
                    <div class="progress-bar bg-primary" style="width: {{ $user->onboardingRecord->progress_percent ?? 0 }}%; transition: width 2s ease;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Journey -->
    <div class="row mt-5">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="font-weight-bold mb-0 text-dark">Your Mandatory Steps</h5>
                <span class="text-muted small">Sequential steps required for access</span>
            </div>

            <div class="journey-stack">
                <!-- Step 1: Personal Records -->
                @php $bioDone = isset($user->profile->bio); @endphp
                <div class="journey-tile mb-4 {{ $bioDone ? 'completed' : '' }}" onclick="location.href='{{ route('portal.personal-info') }}'">
                    <div class="d-flex align-items-center">
                        <div class="tile-step" style="margin-right: 2rem !important;">
                            @if($bioDone) <i class="fas fa-check"></i> @else 01 @endif
                        </div>
                        <div class="flex-grow-1 pr-4">
                            <h5 class="font-weight-bold mb-1 {{ $bioDone ? 'text-success' : 'text-dark' }}">Personal Records</h5>
                            <p class="text-muted small mb-0">Legal background, residency, and financial profiles.</p>
                        </div>
                        <div class="text-right">
                            @if($bioDone)
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            @else
                                <a href="{{ route('portal.personal-info') }}" class="btn btn-primary btn-action pulse shadow-sm">
                                    Continue <i class="fas fa-arrow-right ml-2 small"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Step 2: Employee Identity -->
                @php $photoDone = (bool)$user->profile_image; @endphp
                <div class="journey-tile mb-4 {{ $photoDone ? 'completed' : ($bioDone ? '' : 'locked') }}" 
                     @if($bioDone) onclick="location.href='{{ route('portal.photo-upload') }}'" @endif>
                    <div class="d-flex align-items-center">
                        <div class="tile-step" style="margin-right: 2rem !important;">
                            @if($photoDone) <i class="fas fa-check"></i> @else 02 @endif
                        </div>
                        <div class="flex-grow-1 pr-4">
                            <h5 class="font-weight-bold mb-1 {{ $photoDone ? 'text-success' : 'text-dark' }}">Employee Identity</h5>
                            <p class="text-muted small mb-0">Professional headshot for your digital credentials.</p>
                        </div>
                        <div class="text-right">
                            @if($photoDone)
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            @elseif($bioDone)
                                <a href="{{ route('portal.photo-upload') }}" class="btn btn-primary btn-action shadow-sm">
                                    Start <i class="fas fa-arrow-right ml-2 small"></i>
                                </a>
                            @else
                                <i class="fas fa-lock text-muted opacity-50"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Step 3: Document Vault -->
                @php $docsDone = ($user->onboardingRecord && $user->onboardingRecord->progress_percent >= 33); /* Simplified logic */ @endphp
                <div class="journey-tile mb-4 {{ $docsDone ? 'completed' : ($photoDone ? '' : 'locked') }}" 
                     @if($photoDone) onclick="location.href='{{ route('portal.documents') }}'" @endif>
                    <div class="d-flex align-items-center">
                        <div class="tile-step" style="margin-right: 2rem !important;">
                            @if($docsDone) <i class="fas fa-check"></i> @else 03 @endif
                        </div>
                        <div class="flex-grow-1 pr-4">
                            <h5 class="font-weight-bold mb-1 {{ $docsDone ? 'text-success' : 'text-dark' }}">Document Vault</h5>
                            <p class="text-muted small mb-0">Legal verification documents and certificates.</p>
                        </div>
                        <div class="text-right">
                            @if($docsDone)
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            @elseif($photoDone)
                                <a href="{{ route('portal.documents') }}" class="btn btn-primary btn-action shadow-sm">
                                    Start <i class="fas fa-arrow-right ml-2 small"></i>
                                </a>
                            @else
                                <i class="fas fa-lock text-muted opacity-50"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Step 4: Visa WorkFlow (Phase 2) -->
                @php 
                    $visa = $user->visaProcess;
                    $visaDone = $visa && $visa->residency_visa_status == 'stamped';
                    $visaStatus = 'Pending';
                    if($visa) {
                        if($visa->residency_visa_status == 'stamped') $visaStatus = 'Completed';
                        elseif($visa->medical_status == 'fit') $visaStatus = 'Medical Passed';
                        elseif($visa->medical_status == 'scheduled') $visaStatus = 'Medical Scheduled';
                        elseif($visa->entry_permit_status == 'issued') $visaStatus = 'Visa Issued';
                        elseif($visa->work_permit_status == 'approved') $visaStatus = 'Work Permit Approved';
                        elseif($visa->work_permit_status == 'applied') $visaStatus = 'Processing';
                    }
                @endphp
                <div class="journey-tile mb-4 {{ $visaDone ? 'completed' : ($docsDone ? '' : 'locked') }}">
                    <div class="d-flex align-items-center">
                        <div class="tile-step" style="margin-right: 2rem !important;">
                            @if($visaDone) <i class="fas fa-check"></i> @else 04 @endif
                        </div>
                        <div class="flex-grow-1 pr-4">
                            <h5 class="font-weight-bold mb-1 {{ $visaDone ? 'text-success' : 'text-dark' }}">Visa Processing</h5>
                            <p class="text-muted small mb-0">
                                Current Status: <span class="font-weight-bold text-primary">{{ $visaStatus }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            @if($visaDone)
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            @elseif($docsDone)
                                @if($visa && $visa->entry_permit_status == 'issued' && $visa->entry_permit_file)
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ asset('storage/' . $visa->entry_permit_file) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill font-weight-bold">
                                            <i class="fas fa-download mr-1"></i> Pink Visa
                                        </a>
                                    </div>
                                @elseif($visa && $visa->medical_status == 'scheduled')
                                    <div class="text-right">
                                        <div class="small font-weight-bold text-dark mb-1">Appt: {{ $visa->medical_appointment_date->format('d M') }}</div>
                                        <span class="badge bg-warning text-dark">Scheduled</span>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted">In Progress</span>
                                @endif
                            @else
                                <i class="fas fa-lock text-muted opacity-50"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Step 5: Compliance (Phase 3) -->
                @php 
                    $comp = $user->complianceRecord;
                    $compDone = $comp && $comp->food_safety_training_status == 'passed';
                @endphp
                <div class="journey-tile mb-4 {{ $compDone ? 'completed' : ($visaDone ? '' : 'locked') }}">
                    <div class="d-flex align-items-center">
                        <div class="tile-step" style="margin-right: 2rem !important;">
                            @if($compDone) <i class="fas fa-check"></i> @else 05 @endif
                        </div>
                        <div class="flex-grow-1 pr-4">
                            <h5 class="font-weight-bold mb-1 {{ $compDone ? 'text-success' : 'text-dark' }}">Compliance & Safety</h5>
                            <p class="text-muted small mb-0">Occupational Health & Food Safety Training.</p>
                        </div>
                        <div class="text-right">
                            @if($compDone)
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            @elseif($visaDone)
                                <a href="#" class="btn btn-primary btn-action shadow-sm">Start Training</a>
                            @else
                                <i class="fas fa-lock text-muted opacity-50"></i>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sidebar (Support) -->
        <div class="col-lg-4 mt-5 mt-lg-0">
            <div class="sidebar-card support-dark mb-4" style="background: #0f172a; border: none; overflow: hidden; position: relative;">
                <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, transparent 70%); border-radius: 50%;"></div>
                
                <div class="mb-4">
                    <h6 class="font-weight-bold text-uppercase mb-3" style="color: #60a5fa; letter-spacing: 2px; font-size: 0.75rem;">Support Center</h6>
                    <p class="small text-muted mb-0" style="line-height: 1.6;">Your onboarding journey is supported by our dedicated Talent Operations concierge team.</p>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div style="width: 38px; height: 38px; background: rgba(37, 99, 235, 0.1); color: var(--primary-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold" style="font-size: 0.9rem; letter-spacing: -0.01em;">Talent Ops Team</div>
                            <div style="font-size: 0.7rem; color: #10b981; font-weight: 700; text-transform: uppercase;">Available Now</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background: rgba(255,255,255,0.05);">
                   <span class="text-muted small">Need help?</span>
                   <button class="btn btn-sm btn-outline-light" style="border-radius: 20px;">Contact Us</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
