@extends('layouts.backend')

@section('title')
    Offer Details
@endsection

@section('content')
<div class="page-wrapper bg-white">
    <div class="content container-fluid p-0">
        <!-- High-Impact Header -->
        <div class="bg-white border-bottom border-light-faded px-4 py-4 mb-4 shadow-sm border-0">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-dark fw-bold mb-1 fs-4">Offer Details</h3>
                    <ul class="breadcrumb bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-primary text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.offers.index') }}" class="text-primary text-decoration-none">Offers</a></li>
                        <li class="breadcrumb-item active text-muted">ID: #{{ $offer->id }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <a href="{{ route('recruitment.offers.index') }}" class="btn btn-white border border-light-faded text-dark rounded-0 px-4 transition-all hover-lift shadow-none">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        @if($offer->status === 'pending')
                            <a href="{{ route('recruitment.offers.edit', $offer->id) }}" class="btn btn-white border border-light-faded text-primary rounded-0 px-4 transition-all hover-lift shadow-none">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                            <button type="button" class="btn btn-dark rounded-0 px-4 transition-all hover-lift shadow-none" onclick="sendOffer({{ $offer->id }})">
                                <i class="fas fa-paper-plane me-2 text-primary"></i>Send Offer
                            </button>
                        @endif
                        @if($offer->status === 'sent')
                            <button type="button" class="btn btn-success text-white rounded-0 px-4 transition-all hover-lift shadow-none" onclick="acceptOffer({{ $offer->id }})">
                                <i class="fas fa-check me-2"></i>Accept
                            </button>
                            <button type="button" class="btn btn-danger text-white rounded-0 px-4 transition-all hover-lift shadow-none" onclick="declineOffer({{ $offer->id }})">
                                <i class="fas fa-times me-2"></i>Decline
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 pb-5">
            <div class="row g-4">
                <!-- Sidebar (Left) -->
                <div class="col-lg-4">
                    <!-- Candidate Info Card -->
                    <div class="card border border-light-faded rounded-0 shadow-none mb-4 bg-white interactive-card overflow-hidden">
                        <div class="card-header bg-white border-bottom border-light-faded py-3">
                            <h5 class="card-title text-dark fw-bold mb-0 small text-uppercase letter-spacing-2">
                                <i class="fas fa-user-circle me-2 text-primary"></i>Candidate Profile
                            </h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            @php
                                $candidate = $offer->application->user;
                                $candidateName = $candidate ? $candidate->name : ($offer->application->candidate_name ?? 'Unknown');
                                $candidateEmail = $candidate ? $candidate->email : ($offer->application->candidate_email ?? 'N/A');
                            @endphp
                            
                            <div class="mb-4">
                                <div class="avatar-container mb-3 m-auto">
                                    <div class="bg-light p-4 border border-light-faded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                        <i class="fas fa-user-tie text-muted fs-1"></i>
                                    </div>
                                </div>
                                <h5 class="text-dark fw-extra-bold mb-1">{{ $candidateName }}</h5>
                                <p class="text-muted small mb-0">{{ $candidateEmail }}</p>
                            </div>
                            
                            <div class="text-start space-y-3 mt-4 pt-4 border-top border-light-faded">
                                <div>
                                    <label class="text-muted small text-uppercase letter-spacing-1 d-block mb-1">Position Applied</label>
                                    <p class="text-dark fw-bold mb-0 fs-6">{{ $offer->application->job->title ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="text-muted small text-uppercase letter-spacing-1 d-block mb-1">Status</label>
                                    <span class="badge bg-soft-primary text-primary rounded-0 px-3 py-2 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $offer->application->stage)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Card -->
                    @if($offer->application->logs->isNotEmpty())
                    <div class="card border border-light-faded rounded-0 shadow-none bg-white interactive-card">
                        <div class="card-header bg-white border-bottom border-light-faded py-3">
                            <h5 class="card-title text-dark fw-bold mb-0 small text-uppercase letter-spacing-2">
                                <i class="fas fa-history me-2 text-primary"></i>Application Activity
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="timeline-v3">
                                @foreach($offer->application->logs->sortByDesc('created_at')->take(6) as $log)
                                <div class="timeline-v3-item pb-4">
                                    <div class="timeline-v3-marker"></div>
                                    <div class="timeline-v3-content ps-3">
                                        <h6 class="text-dark fw-bold mb-1 small">{{ $log->description }}</h6>
                                        <p class="text-muted mb-0 smaller-v3">
                                            {{ $log->created_at->format('M d, Y • h:i A') }}
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Main Content (Right) -->
                <div class="col-lg-8">
                    <!-- Premium Offer Header Summary -->
                    <div class="card border border-light-faded rounded-0 shadow-none mb-4 bg-white overflow-hidden banner-card">
                        <div class="card-body p-0">
                            <div class="row g-0 align-items-stretch">
                                <div class="col-md-7 p-4 p-md-5 d-flex flex-column justify-content-center">
                                    <div class="mb-2">
                                        <span class="text-primary fw-bold text-uppercase letter-spacing-2 small mb-2 d-inline-block">OFFICIAL OFFER PACKAGE</span>
                                    </div>
                                    <h1 class="text-dark fw-extra-bold mb-4 display-6">{{ $offer->application->job->title ?? 'N/A' }}</h1>
                                    
                                    <div class="d-flex align-items-center gap-5">
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'sent' => 'info', 
                                                'accepted' => 'success',
                                                'declined' => 'danger'
                                            ];
                                            $statusColor = $statusColors[$offer->status] ?? 'secondary';
                                        @endphp
                                        <div class="info-block">
                                            <label class="text-muted small text-uppercase letter-spacing-1 d-block mb-1">OFFER STATUS</label>
                                            <span class="badge bg-{{ $statusColor }} text-{{ $statusColor === 'warning' ? 'dark' : 'white' }} rounded-0 px-3 py-2 fw-bold text-uppercase small">
                                                {{ ucfirst($offer->status) }}
                                            </span>
                                        </div>
                                        <div class="info-block border-start ps-4 border-light-faded">
                                            <label class="text-muted small text-uppercase letter-spacing-1 d-block mb-1">ISSUE DATE</label>
                                            <span class="text-dark fw-extra-bold fs-5">{{ $offer->offer_date ? $offer->offer_date->format('M d, Y') : $offer->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 bg-light p-5 text-center d-flex flex-column justify-content-center border-start border-light-faded">
                                    <label class="text-muted fw-bold text-uppercase letter-spacing-2 d-block mb-2 small">ANNUAL COMPENSATION</label>
                                    <div class="d-flex align-items-baseline justify-content-center">
                                        <span class="currency-symbol fs-3 text-muted me-1">$</span>
                                        <h1 class="text-dark fw-black mb-0 display-4">{{ number_format($offer->salary) }}</h1>
                                    </div>
                                    <p class="text-primary fw-bold mb-0 mt-2">{{ ucfirst($offer->currency ?? 'USD') }} • Base Salary</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Unified Detail Tabs -->
                    <div class="card border border-light-faded rounded-0 shadow-none bg-white interactive-card">
                        <div class="card-header bg-white border-bottom border-light-faded p-0">
                            <ul class="nav nav-tabs border-0" id="offerTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active rounded-0 border-0 py-4 px-5 fw-extra-bold text-uppercase small letter-spacing-1" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button">
                                        <i class="fas fa-th-list me-2"></i>Package Details
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link rounded-0 border-0 py-4 px-5 fw-extra-bold text-uppercase small letter-spacing-1" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button">
                                        <i class="fas fa-file-signature me-2"></i>Offer Letter
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <div class="tab-content">
                                <!-- Details Tab -->
                                <div class="tab-pane fade show active" id="details" role="tabpanel">
                                    <div class="row g-5 mb-5">
                                        <div class="col-md-6 border-end border-light-faded">
                                            <div class="section-block mb-5">
                                                <label class="text-muted fw-bold text-uppercase letter-spacing-1 d-block mb-3 small">Joining Protocol</label>
                                                <div class="d-flex align-items-center p-3 bg-light border border-light-faded">
                                                    <div class="bg-white p-3 border border-light-faded me-4 shadow-sm">
                                                        <i class="far fa-calendar-alt text-primary fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted smaller mb-0">Expected Start Date</p>
                                                        <h5 class="text-dark fw-extra-bold mb-0">
                                                            {{ $offer->joining_date ? \Carbon\Carbon::parse($offer->joining_date)->format('M d, Y') : ($offer->start_date ? \Carbon\Carbon::parse($offer->start_date)->format('M d, Y') : 'TBD') }}
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="section-block">
                                                <label class="text-muted fw-bold text-uppercase letter-spacing-1 d-block mb-3 small">Decision Window</label>
                                                <div class="d-flex align-items-center p-3 bg-soft-warning border border-warning-light">
                                                    <div class="bg-white p-3 border border-light-faded me-4 shadow-sm">
                                                        <i class="far fa-clock text-warning fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted smaller mb-0">Response Deadline</p>
                                                        <h5 class="text-dark fw-extra-bold mb-0">
                                                            {{ $offer->response_deadline ? \Carbon\Carbon::parse($offer->response_deadline)->format('M d, Y') : 'N/A' }}
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="section-block mb-5">
                                                <label class="text-muted fw-bold text-uppercase letter-spacing-1 d-block mb-3 small">Reporting Structure</label>
                                                <div class="p-3 border border-light-faded">
                                                    <div class="d-flex align-items-center mb-0">
                                                        <i class="fas fa-sitemap text-muted me-3 fs-5"></i>
                                                        <div>
                                                            <p class="text-muted smaller mb-0">Department</p>
                                                            <p class="text-dark fw-bold mb-0">
                                                                @php
                                                                    $department = $offer->application->job->department ?? 'N/A';
                                                                    if (is_string($department) && $department !== 'N/A') {
                                                                        echo $department;
                                                                    } elseif (is_object($department) || is_array($department)) {
                                                                        $deptData = is_string($department) ? json_decode($department, true) : $department;
                                                                        echo $deptData['name'] ?? $deptData->name ?? 'N/A';
                                                                    } else {
                                                                        echo $department;
                                                                    }
                                                                @endphp
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($offer->offer_letter_url)
                                            <div class="section-block">
                                                <label class="text-muted fw-bold text-uppercase letter-spacing-1 d-block mb-3 small">Digital Document</label>
                                                <div class="p-4 border border-light-faded bg-white d-flex align-items-center justify-content-between hover-border-primary transition-all">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-soft-danger p-2 me-3">
                                                            <i class="fas fa-file-pdf text-danger fs-3"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="text-dark fw-bold mb-0 small">Official_Offer_{{ $offer->id }}.pdf</p>
                                                            <p class="text-muted smaller mb-0">Signed PDF document</p>
                                                        </div>
                                                    </div>
                                                    <a href="{{ Storage::url(preg_replace('/^(storage\/|public\/|\/storage\/|\/public\/)/', '', $offer->offer_letter_url)) }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-0 fw-bold px-3">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Compensation & Benefits -->
                                    @if($offer->benefits)
                                    <div class="mb-5 animation-fade-in-up">
                                        <h6 class="text-dark fw-extra-bold mb-3 border-start border-primary border-5 ps-3 text-uppercase small letter-spacing-1">
                                            Compensation & Perquisites
                                        </h6>
                                        <div class="bg-light p-4 border border-light-faded text-dark line-height-2 transition-all hover-shadow-sm">
                                            <div class="benefits-content">{!! nl2br(e($offer->benefits)) !!}</div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Terms and Conditions -->
                                    @if($offer->terms_conditions)
                                    <div class="mb-0 animation-fade-in-up">
                                        <h6 class="text-dark fw-extra-bold mb-3 border-start border-primary border-5 ps-3 text-uppercase small letter-spacing-1">
                                            Compliance & Legal Terms
                                        </h6>
                                        <div class="bg-light p-4 border border-light-faded text-dark line-height-2 transition-all hover-shadow-sm">
                                            <div class="terms-content">{!! nl2br(e($offer->terms_conditions)) !!}</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Letter Preview Tab -->
                                <div class="tab-pane fade" id="preview" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-4 p-4 bg-light border border-light-faded">
                                        <div class="d-flex align-items-center">
                                            <div class="pulse-icon me-3"></div>
                                            <span class="text-dark fw-extra-bold text-uppercase small letter-spacing-1">Live Document Projection</span>
                                        </div>
                                        <button class="btn btn-dark btn-sm rounded-0 px-4 fw-bold shadow-none transition-all hover-lift" onclick="window.print()">
                                            <i class="fas fa-print me-2 text-primary"></i>Export to PDF
                                        </button>
                                    </div>
                                    <div class="letter-background p-0 bg-light border border-dashed border-light-faded overflow-auto" style="max-height: 1000px;">
                                        <div id="offer-preview" class="offer-letter-canvas" style="background: white; padding: {{ $offer->content ? '40px' : '60px 80px' }}; border: 1px solid #dee2e6; font-family: 'Inter', sans-serif; line-height: 1.6; max-width: 900px; margin: 20px auto; box-shadow: 0 10px 40px rgba(0,0,0,0.05); min-height: 297mm;">
                                            @if($offer->content)
                                                <div class="custom-offer-content">
                                                    {!! $offer->content !!}
                                                </div>
                                            @else
                                                @php
                                                    $currencySymbols = [
                                                        'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹',
                                                        'CAD' => 'C$', 'AUD' => 'A$', 'JPY' => '¥', 'CHF' => 'CHF', 'AED' => 'AED '
                                                    ];
                                                    $currency = $offer->currency ?? 'USD';
                                                    $symbol = $currencySymbols[$currency] ?? '$';
                                                @endphp
                                                <!-- Letter Header -->
                                                <div style="border-bottom: 3px solid #1a1a1a; padding-bottom: 30px; margin-bottom: 50px;">
                                                    <h1 style="margin: 0; font-size: 2.8rem; font-weight: 900; color: #1a1a1a; letter-spacing: -1px;">LETTER OF OFFER</h1>
                                                    <p style="color: #6c757d; margin: 10px 0 0 0; font-size: 1.2rem; font-weight: 500; text-transform: uppercase; letter-spacing: 2px;">{{ config('app.name', 'MOM Digital') }}</p>
                                                </div>

                                                <div style="display: flex; justify-content: space-between; margin-bottom: 40px;">
                                                    <div>
                                                        <p style="margin: 0; font-weight: 800; text-transform: uppercase; color: #6c757d; font-size: 0.85rem; letter-spacing: 1px;">Addressee</p>
                                                        <h4 style="margin: 5px 0 2px 0; font-weight: 900;">{{ $candidateName }}</h4>
                                                        <p style="margin: 0; color: #444;">{{ $candidateEmail }}</p>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <p style="margin: 0; font-weight: 800; text-transform: uppercase; color: #6c757d; font-size: 0.85rem; letter-spacing: 1px;">Issue Date</p>
                                                        <p style="margin: 5px 0 0 0; font-weight: 700;">{{ $offer->offer_date ? $offer->offer_date->format('F d, Y') : $offer->created_at->format('F d, Y') }}</p>
                                                    </div>
                                                </div>

                                                <div style="font-size: 1.05rem; color: #222;">
                                                    <p>Dear {{ $candidateName }},</p>
                                                    <p>Following our recent discussions, we are delighted to formally offer you the position of <strong>{{ $offer->application->job->title ?? 'N/A' }}</strong> within our organization.</p>
                                                    
                                                    <div style="margin: 35px 0; padding: 25px; border: 1px solid #1a1a1a; background: #fafafa;">
                                                        <p style="margin: 0 0 10px 0; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem; color: #6c757d;">Financial Package</p>
                                                        <h3 style="margin: 0; font-weight: 900; font-size: 1.8rem; color: #1a1a1a;">{{ $symbol }}{{ number_format($offer->salary) }} <span style="font-size: 1rem; color: #666; font-weight: 400;">per annum</span></h3>
                                                    </div>

                                                    @if($offer->benefits)
                                                    <div style="margin-top: 35px;">
                                                        <h4 style="text-transform: uppercase; font-size: 0.95rem; font-weight: 900; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; letter-spacing: 1px;">Incentives & Benefits</h4>
                                                        <div style="font-size: 1rem;">{!! nl2br(e($offer->benefits)) !!}</div>
                                                    </div>
                                                    @endif

                                                    @if($offer->terms_conditions)
                                                    <div style="margin-top: 35px;">
                                                        <h4 style="text-transform: uppercase; font-size: 0.95rem; font-weight: 900; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; letter-spacing: 1px;">Terms of Engagement</h4>
                                                        <div style="font-size: 1rem; color: #444;">{!! nl2br(e($offer->terms_conditions)) !!}</div>
                                                    </div>
                                                    @endif
                                                </div>

                                                <div style="margin-top: 60px; border-top: 1px solid #1a1a1a; padding-top: 30px;">
                                                    <p style="margin: 0; font-weight: 400; color: #666;">On behalf of the Management,</p>
                                                    <h4 style="font-weight: 900; margin: 10px 0 0 0; text-transform: uppercase; letter-spacing: 1px;">HR Directorate</h4>
                                                    <p style="color: #6c757d; font-weight: 600;">{{ config('app.name', 'MOM Digital') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Offer Modal -->
<div class="modal fade" id="sendOfferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-0 border-0 shadow-lg bg-white overflow-hidden">
            <div class="modal-header bg-dark py-4 px-4 border-0">
                <h5 class="modal-title text-white fw-extra-bold text-uppercase letter-spacing-2 small">Action Required</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendOfferForm" method="POST">
                @csrf
                <div class="modal-body p-5 text-center">
                    <div class="bg-light p-4 d-inline-block border border-light-faded mb-4 pulse-container">
                        <i class="fas fa-paper-plane text-primary fs-2"></i>
                    </div>
                    <h5 class="text-dark fw-extra-bold mb-3 d-block">Dispatch Job Offer?</h5>
                    <p class="text-muted mb-4">You are about to send a formal offer to <br><span class="text-dark fw-black fs-5">{{ $candidateName }}</span></p>
                    <div class="p-3 bg-soft-info border border-info-light rounded-0 text-start d-flex align-items-center mb-0">
                        <i class="fas fa-info-circle text-info me-3 fs-5"></i>
                        <span class="small text-dark fw-semibold">Candidate will be notified immediately via Email & Dashboard.</span>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-4 px-4">
                    <button type="button" class="btn btn-white border border-light-faded rounded-0 px-4 fw-bold shadow-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark text-white rounded-0 px-5 fw-bold shadow-none transition-all hover-lift">
                        <span class="text-primary me-2">●</span>Confirm Dispatch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
/* 
    ULTRA-AGGRESSIVE STYLE RESET 
    Purpose: Force square corners and light borders by overriding theme defaults.
    Author: Sanket
*/
:root {
    --primary-color: #556ee6;
    --border-light: #f1f5f9;
}

/* Base resets with highest specificity to override parent theme classes */
html body .main-wrapper .card,
html body .main-wrapper .card-header,
html body .main-wrapper .card-body,
html body .main-wrapper .card-footer,
html body .main-wrapper .btn,
html body .main-wrapper .badge,
html body .main-wrapper .nav-link,
html body .main-wrapper .nav-tabs,
html body .main-wrapper .modal-content,
html body .main-wrapper .avatar-container div {
    border-radius: 0 !important;
}

/* Eliminate all thick/dark top borders often found in Admin themes (like card-outline) */
html body .main-wrapper .card {
    border: 1px solid #f1f5f9 !important;
    border-top: 1px solid #f1f5f9 !important; /* Explicitly reset top border */
    box-shadow: none !important;
    background: #fff !important;
}

/* Nuke pseudo-elements that often create dark bars at top of cards in themes like AdminLTE */
html body .main-wrapper .card::before,
html body .main-wrapper .card::after {
    display: none !important;
}

html body .main-wrapper .card-header {
    border-bottom: 1px solid #f1f5f9 !important;
    border-top: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    background: #fff !important;
}

/* Interactive elements */
.interactive-card:hover {
    border-color: #556ee6 !important;
    box-shadow: 0 10px 40px rgba(85, 110, 230, 0.04) !important;
}

.border-light-faded {
    border-color: #f1f5f9 !important;
}

/* Premium Typography & Spacing */
.fw-extra-bold { font-weight: 800 !important; }
.fw-black { font-weight: 950 !important; }
.letter-spacing-1 { letter-spacing: 0.03em; }
.letter-spacing-2 { letter-spacing: 0.1em; }
.line-height-2 { line-height: 2; }
.smaller-v3 { font-size: 0.7rem; }

/* Timeline V3 Style */
.timeline-v3 {
    position: relative;
    padding-left: 20px;
}
.timeline-v3-item {
    position: relative;
    border-left: 1px solid #f1f5f9;
}
.timeline-v3-marker {
    position: absolute;
    left: -4.5px;
    top: 6px;
    width: 8px;
    height: 8px;
    background: #556ee6;
    z-index: 1;
}
.timeline-v3-item:last-child {
    border-left: 0;
}

/* Premium Navigation Tabs */
.nav-tabs .nav-link {
    color: #94a3b8 !important;
    background: #fff !important;
    border: none !important;
    border-bottom: 3px solid transparent !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.nav-tabs .nav-link.active {
    color: #1e293b !important;
    border-bottom: 3px solid #556ee6 !important;
}
.nav-tabs .nav-link:hover:not(.active) {
    background: #f8fafc !important;
    color: #556ee6 !important;
}

/* Custom Badges & Colors */
.bg-soft-primary { background-color: rgba(85, 110, 230, 0.1); }
.bg-soft-info { background-color: rgba(80, 165, 241, 0.05); }
.bg-soft-warning { background-color: rgba(241, 180, 76, 0.05); }
.bg-soft-danger { background-color: rgba(244, 106, 106, 0.1); }

/* Animation - Fade In */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
.animation-fade-in-up { 
    animation: fadeInUp 0.4s ease-out forwards; 
}

/* Status Pulse Icon */
.pulse-icon {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #556ee6;
    box-shadow: 0 0 0 0 rgba(85, 110, 230, 0.7);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(85, 110, 230, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(85, 110, 230, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(85, 110, 230, 0); }
}

@media print {
    .btn, .breadcrumb, .page-header, .col-lg-4, .nav-tabs, .px-4.py-4, .banner-card {
        display: none !important;
    }
    .col-lg-8 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
    .card { border: none !important; }
    .letter-background { padding: 0 !important; background: #fff !important; border: none !important; }
    .offer-letter-canvas { box-shadow: none !important; padding: 0 !important; border: none !important; margin: 0 !important; width: 100% !important; }
}
/* Custom Offer Content Styling */
.custom-offer-content {
    font-size: 14px;
    color: #333;
}
.custom-offer-content p {
    margin-bottom: 1rem;
}
.custom-offer-content table {
    width: 100% !important;
    border-collapse: collapse;
    margin-bottom: 1rem;
}
.custom-offer-content img {
    max-width: 100% !important;
    height: auto !important;
}
</style>
@endpush

@push('scripts')
<script>
function sendOffer(offerId) {
    $('#sendOfferForm').attr('action', `/recruitment/offers/${offerId}/send`);
    $('#sendOfferModal').modal('show');
}

$('#sendOfferForm').on('submit', function(e) {
    e.preventDefault();
    const $btn = $(this).find('button[type="submit"]');
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin me-2"></i>Processing...');
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Failed to send offer');
                $btn.prop('disabled', false).html(originalText);
            }
        },
        error: function() {
            alert('Error sending offer');
            $btn.prop('disabled', false).html(originalText);
        }
    });
});

function acceptOffer(offerId) {
    if (confirm('Confirm marking this offer as ACCEPTED?')) {
        $.ajax({
            url: `/recruitment/offers/${offerId}/accept`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) location.reload();
            }
        });
    }
}

function declineOffer(offerId) {
    const reason = prompt('Please specify decline reason (optional):');
    if (reason !== null) {
        $.ajax({
            url: `/recruitment/offers/${offerId}/decline`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', reason: reason },
            success: function(response) {
                if (response.success) location.reload();
            }
        });
    }
}

$(document).ready(function() {
    // URL Hash handling for tabs
    if (window.location.hash) {
        let tabEl = document.querySelector(`button[data-bs-target="${window.location.hash}"]`);
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
});
</script>
@endpush