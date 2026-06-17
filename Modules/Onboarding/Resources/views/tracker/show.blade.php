@extends('layouts.backend')

@section('title', 'Visa Process: ' . $user->name)

@push('css')
<style>
    /* Professional White Theme */
    :root {
        --primary-color: #4f46e5; /* Indigo */
        --secondary-color: #64748b; /* Slate */
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --border-color: #e2e8f0;
    }

    body {
        background-color: var(--bg-color);
        color: #334155;
    }

    /* Card Styling */
    .pro-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .pro-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        /* transform: translateY(-2px); */ /* Optional movement */
    }

    /* Header Styling within Card */
    .card-header-clean {
        background: #ffffff;
        border-bottom: 1px solid var(--border-color);
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-header-clean .title {
        font-weight: 700;
        font-size: 1.05rem;
        color: #1e293b;
        display: flex;
        align-items: center;
    }
    .card-header-clean .icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 1.1rem;
    }
    
    /* Colors for Icons/Stages */
    .stage-1 .icon-box { background: #e0e7ff; color: #4f46e5; } /* Indigo */
    .stage-2 .icon-box { background: #dbeafe; color: #2563eb; } /* Blue */
    .stage-3 .icon-box { background: #fee2e2; color: #ef4444; } /* Red */
    .stage-4 .icon-box { background: #d1fae5; color: #10b981; } /* Emerald */

    .card-body-clean {
        padding: 1.5rem;
    }

    /* Data Field Styling */
    .field-group {
        margin-bottom: 0.5rem;
    }
    .field-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 4px;
        display: block;
    }
    .field-value {
        font-size: 0.95rem;
        font-weight: 500;
        color: #0f172a;
        min-height: 24px; /* Ensure alignment even if empty */
    }
    .field-value a {
        text-decoration: none;
        color: var(--primary-color);
        transition: color 0.2s;
    }
    .field-value a:hover {
        color: #312e81;
        text-decoration: underline;
    }

    /* Status Badges */
    .status-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
    }
    .bg-soft-success { background: #d1fae5; color: #065f46; }
    .bg-soft-warning { background: #fef3c7; color: #92400e; }
    .bg-soft-secondary { background: #f1f5f9; color: #475569; }

    /* Profile Sidebar */
    .profile-card {
        text-align: center;
    }
    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        object-fit: cover;
        margin-bottom: 1rem;
    }
    .profile-name {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    .profile-role {
        font-size: 0.875rem;
        color: #64748b;
        background: #f1f5f9;
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    .info-list {
        list-style: none;
        padding: 0;
        margin-top: 1.5rem;
        text-align: left;
    }
    .info-list li {
        border-bottom: 1px solid #f1f5f9;
        padding: 10px 0;
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
    }
    .info-list li:last-child {
        border-bottom: none;
    }
    .info-list span.label { color: #94a3b8; }
    .info-list span.val { font-weight: 600; color: #334155; }

    /* Document Vault */
    .doc-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
        margin-bottom: 8px;
        background: #f8fafc;
        transition: all 0.2s;
        text-decoration: none;
        color: #334155;
    }
    .doc-item:hover {
        background: #fff;
        border-color: #cbd5e1;
        transform: translateX(2px);
    }
    .doc-icon {
        width: 32px;
        height: 32px;
        background: #fff;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ef4444;
        margin-right: 10px;
        font-size: 1rem;
        border: 1px solid #e2e8f0;
    }

    /* Action Buttons */
    .btn-update {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        transition: all 0.2s;
    }
    .btn-update:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: #eef2ff;
    }

</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="page-title mb-1 text-dark font-weight-bold">Visa Workflow</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('onboarding.tracker.index') }}" class="text-muted">Tracker</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detailed View</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('onboarding.tracker.index') }}" class="btn btn-white border shadow-sm rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Back to Tracker
            </a>
        </div>

        <div class="row">
            <!-- Sidebar: User Info -->
            <div class="col-lg-3 col-md-12 mb-4">
                <!-- Profile Card -->
                <div class="pro-card profile-card pt-4 pb-2">
                    <div class="card-body-clean">
                        <img src="{{ $user->profile_image_url ?? asset('assets/backend/img/profiles/avatar-01.jpg') }}" class="profile-avatar" alt="Profile">
                        <h4 class="profile-name">{{ $user->name }}</h4>
                        <div class="profile-role">{{ $user->onboardingRecord->department->name ?? 'New Hire' }}</div>
                        
                        <ul class="info-list">
                            <li><span class="label">Email</span> <span class="val">{{ \Illuminate\Support\Str::limit($user->email, 20) }}</span></li>
                            <li><span class="label">Phone</span> <span class="val">{{ $user->profile->personal_phone ?? '--' }}</span></li>
                            <li><span class="label">Nationality</span> <span class="val">{{ $user->profile->country->name ?? '--' }}</span></li>
                            <li><span class="label">DOB</span> <span class="val">{{ $user->profile->date_of_birth ?? '--' }}</span></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Offer Letter Card -->
                <div class="pro-card">
                    <div class="card-header-clean py-3">
                        <div class="title" style="font-size: 0.95rem;">Recruitment Offer</div>
                    </div>
                    <div class="card-body-clean p-3">
                        @if($offer)
                            <div class="d-flex align-items-center mb-2">
                                <div class="badge bg-soft-success text-success me-2">{{ strtoupper($offer->status) }}</div>
                                <div class="small text-muted">{{ $offer->offer_date->format('d M, Y') }}</div>
                            </div>
                            <div class="small fw-bold mb-1">{{ $offer->position }}</div>
                            <div class="small text-muted mb-3">{{ $offer->currency }} {{ number_format($offer->salary, 2) }} / {{ $offer->payment_period }}</div>
                            
                            @if($offer->offer_letter_url)
                                <a href="{{ $offer->offer_letter_url }}" class="btn btn-sm btn-outline-primary w-100" target="_blank">
                                    <i class="fas fa-file-download me-1"></i> Download Offer
                                </a>
                            @else
                                <a href="{{ route('recruitment.offer-letters.create', ['offer_id' => $offer->id]) }}" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fas fa-magic me-1"></i> Regenerate Letter
                                </a>
                            @endif
                        @else
                            <div class="text-center py-2">
                                <p class="small text-muted mb-3">No official offer found.</p>
                                <a href="{{ route('recruitment.offer-letters.create', ['application_id' => $user->onboardingRecord->application_id ?? '']) }}" class="btn btn-sm btn-primary w-100">
                                    <i class="fas fa-plus me-1"></i> Create Offer Letter
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Document Vault -->
                <div class="pro-card">
                    <div class="card-header-clean py-3">
                        <div class="title" style="font-size: 0.95rem;">Document Vault</div>
                        <span class="badge bg-soft-secondary text-dark">{{ $user->documents->count() }}</span>
                    </div>
                    <div class="card-body-clean p-3">
                        @foreach($user->documents as $doc)
                            <a href="{{ route('onboarding.secure.download', ['type'=>'user_document', 'id'=>$doc->id, 'field'=>'path']) }}" class="doc-item" target="_blank">
                                <div class="doc-icon"><i class="fas fa-file-pdf"></i></div>
                                <div class="text-truncate">
                                    <div class="small fw-bold text-dark">{{ ucfirst($doc->type->value ?? $doc->type) }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">Download</div>
                                </div>
                            </a>
                        @endforeach
                        @if($user->documents->isEmpty())
                            <div class="text-center py-3 text-muted small">No documents uploaded.</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Content: Workflow Stages -->
            <div class="col-lg-9 col-md-12">
                
                <!-- Stage 1: MOHRE & Offer -->
                <div class="pro-card stage-1">
                    <div class="card-header-clean">
                        <div class="title">
                            <div class="icon-box"><i class="fas fa-file-signature"></i></div>
                            <div>
                                <div class="text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Stage 1</div>
                                MOHRE & Labor Contract
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="status-badge {{ $visa->mohre_contract_status == 'signed' ? 'bg-soft-success' : 'bg-soft-warning' }} me-3">
                                {{ ucfirst($visa->mohre_contract_status ?? 'Pending') }}
                            </span>
                            <button class="btn-update" data-bs-toggle="modal" data-bs-target="#update_stage_1">Update</button>
                        </div>
                    </div>
                    <div class="card-body-clean">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="field-group">
                                    <label class="field-label">Offer Letter</label>
                                    <div class="field-value">
                                        @if($visa->mohre_offer_file)
                                            <a href="{{ route('onboarding.secure.download', ['type'=>'visa', 'id'=>$visa->id, 'field'=>'mohre_offer_file']) }}" target="_blank"><i class="fas fa-paperclip me-1"></i> View Signed Offer</a>
                                        @else
                                            <span class="text-muted font-weight-normal font-italic">Not Uploaded</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="field-group">
                                    <label class="field-label">MOHRE Contract</label>
                                    <div class="field-value">
                                        @if($visa->mohre_contract_file)
                                            <a href="{{ route('onboarding.secure.download', ['type'=>'visa', 'id'=>$visa->id, 'field'=>'mohre_contract_file']) }}" target="_blank"><i class="fas fa-file-contract me-1"></i> View MOHRE Contract</a>
                                        @else
                                            <span class="text-muted font-weight-normal font-italic">Not Uploaded</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="field-group">
                                    <label class="field-label">Labor Card</label>
                                    <div class="field-value">{{ $visa->labor_card_number ?? '--' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage 2: Entry Permit -->
                <div class="pro-card stage-2">
                    <div class="card-header-clean">
                        <div class="title">
                            <div class="icon-box"><i class="fas fa-plane-arrival"></i></div>
                             <div>
                                <div class="text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Stage 2</div>
                                Entry Permit & Status
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="status-badge {{ $visa->entry_permit_status == 'issued' ? 'bg-soft-success' : 'bg-soft-secondary' }} me-3">
                                {{ $visa->entry_permit_status == 'issued' ? 'Issued' : 'Pending' }}
                            </span>
                            @if($visa->entry_permit_file)
                                <a href="{{ route('onboarding.secure.download', ['type'=>'visa', 'id'=>$visa->id, 'field'=>'entry_permit_file']) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-download"></i> Pink Visa</a>
                            @endif
                            <button class="btn-update" data-bs-toggle="modal" data-bs-target="#update_stage_2">Update</button>
                        </div>
                    </div>
                    <div class="card-body-clean">
                        <div class="row">
                             <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">Permit Number</label>
                                    <div class="field-value">{{ $visa->entry_permit_number ?? '--' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">UID Number</label>
                                    <div class="field-value">{{ $visa->uid_number ?? '--' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">Status Change</label>
                                    <div class="field-value">
                                        @if($visa->status_change_completed)
                                            <span class="text-success"><i class="fas fa-check-circle me-1"></i> Completed</span>
                                        @else
                                            <span class="text-warning"><i class="fas fa-clock me-1"></i> Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage 3: Medical & Biometrics -->
                <div class="pro-card stage-3">
                    <div class="card-header-clean">
                         <div class="title">
                            <div class="icon-box"><i class="fas fa-heartbeat"></i></div>
                             <div>
                                <div class="text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Stage 3</div>
                                Medical Test & Emirates ID
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="status-badge {{ $visa->medical_status == 'fit' ? 'bg-soft-success' : ($visa->medical_status == 'scheduled' ? 'bg-soft-warning' : 'bg-soft-secondary') }} me-3">
                                {{ ucfirst($visa->medical_status ?? 'Pending') }}
                            </span>
                            <button class="btn-update" data-bs-toggle="modal" data-bs-target="#update_stage_3">Update</button>
                        </div>
                    </div>
                    <div class="card-body-clean">
                         <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">Medical Center</label>
                                    <div class="field-value">
                                        {{ $visa->medical_center_name ?? '--' }} 
                                        @if($visa->medical_type) <span class="badge bg-light text-muted border border-secondary">{{ ucfirst($visa->medical_type) }}</span> @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">Appointment</label>
                                    <div class="field-value">{{ $visa->medical_appointment_date ? $visa->medical_appointment_date->format('d M, Y') : '--' }}</div>
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">Result</label>
                                    <div class="field-value">
                                        @if($visa->medical_result_file)
                                            <a href="{{ route('onboarding.secure.download', ['type'=>'visa', 'id'=>$visa->id, 'field'=>'medical_result_file']) }}" target="_blank" class="text-success fw-bold"><i class="fas fa-file-medical me-1"></i> VIEW FIT REPORT</a>
                                        @else
                                            --
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded border border-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label class="field-label">EID Application</label>
                                        <div class="field-value">
                                            @if($visa->eid_application_form)
                                                <a href="{{ route('onboarding.secure.download', ['type'=>'visa', 'id'=>$visa->id, 'field'=>'eid_application_form']) }}" target="_blank"><i class="fas fa-file-alt me-1"></i> View Application Form</a>
                                            @else
                                                <span class="text-muted">Not Typed Yet</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                     <div class="field-group">
                                        <label class="field-label">Biometrics Date</label>
                                        <div class="field-value">{{ $visa->eid_biometrics_date ? $visa->eid_biometrics_date->format('d M, Y') : 'Not Scheduled' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage 4: Residency & Finalization -->
                <div class="pro-card stage-4">
                    <div class="card-header-clean">
                       <div class="title">
                            <div class="icon-box"><i class="fas fa-passport"></i></div>
                             <div>
                                <div class="text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Stage 4</div>
                                Residency & Finalization
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="status-badge {{ $visa->residency_visa_status == 'stamped' ? 'bg-soft-success' : 'bg-soft-secondary' }} me-3">
                                {{ ucfirst($visa->residency_visa_status == 'stamped' ? 'Stamped' : 'Processing') }}
                            </span>
                            <button class="btn-update" data-bs-toggle="modal" data-bs-target="#update_stage_4">Update</button>
                        </div>
                    </div>
                    <div class="card-body-clean">
                        <div class="row">
                             <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">Residency File No.</label>
                                    <div class="field-value">{{ $visa->residency_file_number ?? '--' }}</div>
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">Visa Expiry Date</label>
                                    <div class="field-value text-danger">{{ $visa->visa_expiry_date ? $visa->visa_expiry_date->format('d M, Y') : '--' }}</div>
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label">Stamped Visa</label>
                                    <div class="field-value">
                                        @if($visa->residency_visa_file)
                                             <a href="{{ route('onboarding.secure.download', ['type'=>'visa', 'id'=>$visa->id, 'field'=>'residency_visa_file']) }}" target="_blank" class="text-primary"><i class="fas fa-stamp me-1"></i> View Stamped Visa</a>
                                        @else
                                            --
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3 pt-3 border-top">
                             <div class="col-md-6">
                                <div class="field-group">
                                    <label class="field-label">Health Insurance</label>
                                    <div class="field-value">
                                        @if($visa->insurance_card_file)
                                             <a href="{{ route('onboarding.secure.download', ['type'=>'visa', 'id'=>$visa->id, 'field'=>'insurance_card_file']) }}" target="_blank" class="text-info"><i class="fas fa-id-card me-1"></i> View Insurance Card</a>
                                        @else
                                            <span class="text-muted small">Not Issued</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge {{ $visa->insurance_status == 'active' ? 'bg-soft-success' : 'bg-soft-secondary' }}">
                                    Insurance: {{ ucfirst($visa->insurance_status ?? 'Pending') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage 5: Operational Readiness -->
                <div class="pro-card stage-1" style="border-left: 4px solid var(--primary-color);">
                    <div class="card-header-clean">
                       <div class="title">
                            <div class="icon-box" style="background: #eef2ff; color: #4f46e5;"><i class="fas fa-tasks"></i></div>
                             <div>
                                <div class="text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Internal</div>
                                Operational Readiness
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            @php
                                $readinessScore = 0;
                                if($readiness->it_login_created) $readinessScore++;
                                if($readiness->email_created) $readinessScore++;
                                if($readiness->induction_completed) $readinessScore++;
                                if($readiness->asset_id) $readinessScore++;
                                if($readiness->apparel_id) $readinessScore++;
                            @endphp
                            <span class="status-badge {{ $readinessScore >= 5 ? 'bg-soft-success' : 'bg-soft-warning' }} me-3">
                                {{ $readinessScore }}/5 Tasks Meta
                            </span>
                            <button class="btn-update" data-bs-toggle="modal" data-bs-target="#update_readiness">Update</button>
                        </div>
                    </div>
                    <div class="card-body-clean">
                        <div class="row">
                             <div class="col-md-2">
                                <div class="field-group">
                                    <label class="field-label">IT Login</label>
                                    <div class="field-value">
                                        @if($readiness->it_login_created)
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Created</span>
                                        @else
                                            <span class="text-muted">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-2">
                                <div class="field-group">
                                    <label class="field-label">Email</label>
                                    <div class="field-value">
                                        @if($readiness->email_created)
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Done</span>
                                        @else
                                            <span class="text-muted">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-3">
                                <div class="field-group">
                                    <label class="field-label">Hardware</label>
                                    <div class="field-value">
                                        @if($readiness->asset)
                                            <span class="text-dark fw-bold" title="{{ $readiness->asset->unique_id }}"><i class="fas fa-laptop text-primary"></i> {{ $readiness->asset->model }}</span>
                                        @else
                                            <span class="text-muted">Not Set</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-3">
                                <div class="field-group">
                                    <label class="field-label">Uniform</label>
                                    <div class="field-value">
                                        @if($readiness->apparel)
                                            <span class="text-dark fw-bold"><i class="fas fa-tshirt text-info"></i> {{ $readiness->apparel->name }}</span>
                                        @else
                                            <span class="text-muted">Not Issued</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-2 text-end">
                                <div class="field-group">
                                    <label class="field-label">Induction</label>
                                    <div class="field-value">
                                        @if($readiness->induction_completed)
                                            <span class="text-success fw-bold">Done</span>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Food Safety Section within Readiness --}}
                        <div class="mt-4 p-3 rounded" style="background: #fffbeb; border: 1px solid #fef3c7;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-warning" style="font-weight: 700; font-size: 0.85rem;"><i class="fas fa-utensils me-1"></i> FOOD SAFETY COMPLIANCE</h6>
                                <button class="btn btn-sm btn-outline-warning py-0" style="font-size: 0.7rem;" data-bs-toggle="modal" data-bs-target="#update_compliance">Edit</button>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                     <div class="field-group">
                                        <label class="field-label">Training Status</label>
                                        <div class="field-value small">{{ ucfirst($compliance->food_safety_training_status ?? 'Pending') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                     <div class="field-group">
                                        <label class="field-label">Completion Date</label>
                                        <div class="field-value small">{{ $compliance->training_completion_date ? $compliance->training_completion_date->format('d M, Y') : '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                     <div class="field-group">
                                        <label class="field-label">OHC Card</label>
                                        <div class="field-value small">
                                            @if($compliance->ohc_file)
                                                <a href="{{ route('onboarding.secure.download', ['type'=>'compliance', 'id'=>$compliance->id, 'field'=>'ohc_file']) }}" target="_blank" class="text-success"><i class="fas fa-id-card me-1"></i> View Card</a>
                                            @else
                                                <span class="text-muted">Wait for Fit Report</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final Phase: Employee Take-on -->
                @if($user->hasRole('new-hire'))
                <div class="text-center mt-5 mb-5">
                    <div class="p-4 rounded-3 border" style="background: #f8fafc; border-style: dashed !important;">
                        <h4 class="mb-3">Ready to Onboard?</h4>
                        <p class="text-muted mb-4">Once all visa and operational stages are complete, you can officially convert this candidate into an active employee.</p>
                        
                        <form action="{{ route('onboarding.tracker.convert-employee', $user->id) }}" method="POST" onsubmit="return confirm('Ensure all documents are verified. This will update the user role to Employee and generate an ID. Proceed?')">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-user-check me-2"></i> CONVERT TO EMPLOYEE
                            </button>
                        </form>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Modals for Update Actions --}}
<!-- Kept the modals but stripped of details for brevity in this specific call, assumed they are same as before just hidden here or need re-adding if logic requires complete replacement -->
<!-- ... Re-adding simplified Modals to ensure functionality works ... -->

<!-- Modal Stage 1 -->
<div id="update_stage_1" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stage 1: MOHRE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.tracker.update-status', $visa->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label>Signed Offer Letter</label>
                        <input type="file" name="mohre_offer_file" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Signed MOHRE Contract</label>
                        <input type="file" name="mohre_contract_file" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Labor Card Number</label>
                        <input type="text" name="labor_card_number" class="form-control" value="{{ $visa->labor_card_number }}">
                    </div>
                    <div class="submit-section text-center">
                         <button class="btn btn-primary w-100">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Placeholder Modals Replaced with Real Logic --}}
<!-- Modal Stage 2: Entry Permit -->
<div id="update_stage_2" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stage 2: Entry & Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.tracker.update-status', $visa->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label>Entry Permit File (Pink Visa)</label>
                        <input type="file" name="entry_permit_file" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Permit Number</label>
                            <input type="text" name="entry_permit_number" class="form-control" value="{{ $visa->entry_permit_number }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>UID Number</label>
                            <input type="text" name="uid_number" class="form-control" value="{{ $visa->uid_number }}">
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="status_change_completed" value="1" id="statusChange" {{ $visa->status_change_completed ? 'checked' : '' }}>
                        <label class="form-check-label" for="statusChange">
                            Mark Status Change as Completed
                        </label>
                    </div>
                    <div class="submit-section text-center">
                         <button class="btn btn-primary w-100">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Stage 3: Medical & EID -->
<div id="update_stage_3" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stage 3: Medical & EID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.tracker.update-status', $visa->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h6 class="text-primary mb-3">Medical Details</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Medical Center</label>
                            <input type="text" name="medical_center_name" class="form-control" value="{{ $visa->medical_center_name }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Type</label>
                            <select name="medical_type" class="form-control">
                                <option value="normal" {{ $visa->medical_type == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="vip_24" {{ $visa->medical_type == 'vip_24' ? 'selected' : '' }}>VIP (24h)</option>
                                <option value="vip_48" {{ $visa->medical_type == 'vip_48' ? 'selected' : '' }}>VIP (48h)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Appointment Date</label>
                        <input type="date" name="medical_appointment_date" class="form-control" value="{{ $visa->medical_appointment_date ? $visa->medical_appointment_date->format('Y-m-d') : '' }}">
                    </div>
                     <div class="mb-3">
                        <label>Upload Fitness Result</label>
                        <input type="file" name="medical_result_file" class="form-control">
                        <small class="text-muted">Uploading this marks result as FIT (unless Status overridden).</small>
                    </div>
                    <div class="mb-3">
                         <label>Medical Status Override</label>
                         <select name="medical_result_status" class="form-control">
                             <option value="">Auto (based on file)</option>
                             <option value="fit">FIT</option>
                             <option value="unfit">UNFIT</option>
                         </select>
                    </div>

                    <hr>
                    <h6 class="text-danger mb-3">Emirates ID</h6>
                     <div class="mb-3">
                        <label>EID Application Form</label>
                        <input type="file" name="eid_application_form" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Biometrics Date</label>
                        <input type="date" name="eid_biometrics_date" class="form-control" value="{{ $visa->eid_biometrics_date ? $visa->eid_biometrics_date->format('Y-m-d') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label>Emirates ID Card</label>
                        <input type="file" name="eid_card_file" class="form-control">
                    </div>

                    <div class="submit-section text-center">
                         <button class="btn btn-primary w-100">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Stage 4: Residency -->
<div id="update_stage_4" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stage 4: Residency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.tracker.update-status', $visa->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label>Stamped Visa File</label>
                        <input type="file" name="residency_visa_file" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Residency File Number</label>
                            <input type="text" name="residency_file_number" class="form-control" value="{{ $visa->residency_file_number }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Visa Expiry Date</label>
                            <input type="date" name="visa_expiry_date" class="form-control" value="{{ $visa->visa_expiry_date ? $visa->visa_expiry_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Health Insurance Card</label>
                        <input type="file" name="insurance_card_file" class="form-control">
                    </div>
                    
                    <div class="submit-section text-center">
                         <button class="btn btn-primary w-100">Complete Residency</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Operational Readiness -->
<div id="update_readiness" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Operational Tasks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.tracker.update-readiness', $readiness->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="it_login_created" value="1" id="itLogin" {{ $readiness->it_login_created ? 'checked' : '' }}>
                            <label class="form-check-label" for="itLogin">POS / System Login Created</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="email_created" value="1" id="emailAccess" {{ $readiness->email_created ? 'checked' : '' }}>
                            <label class="form-check-label" for="emailAccess">Corporate Email Provided</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="induction_completed" value="1" id="induction" {{ $readiness->induction_completed ? 'checked' : '' }}>
                            <label class="form-check-label" for="induction">Induction & Orientation Complete</label>
                        </div>
                    </div>
                    <div class="mb-3">
                         <label>Assigned Hardware (Laptop/Mobile)</label>
                         <select name="asset_id" class="form-control select2">
                             <option value="">-- No Hardware Assigned --</option>
                             @foreach($availableAssets as $as)
                                 <option value="{{ $as->id }}" {{ $readiness->asset_id == $as->id ? 'selected' : '' }}>{{ $as->name }}</option>
                             @endforeach
                         </select>
                         <small class="text-muted">Only showing 'Available' assets.</small>
                    </div>

                    <div class="mb-3">
                         <label>Uniform & Apparel</label>
                         <select name="apparel_id" class="form-control">
                             <option value="">-- No Uniform Issued --</option>
                             @foreach($availableApparel as $ap)
                                 <option value="{{ $ap->id }}" {{ $readiness->apparel_id == $ap->id ? 'selected' : '' }}>{{ $ap->name }} ({{ $ap->id }})</option>
                             @endforeach
                         </select>
                    </div>
                    <div class="submit-section text-center">
                         <button class="btn btn-primary w-100">Save Readiness</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Compliance -->
<div id="update_compliance" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compliance & Training</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.tracker.update-compliance', $compliance->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h6 class="text-primary mb-3">Occupational Health (OHC)</h6>
                    <div class="mb-3">
                        <label>OHC Card File</label>
                        <input type="file" name="ohc_file" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>OHC Expiry Date</label>
                        <input type="date" name="ohc_expiry_date" class="form-control" value="{{ $compliance->ohc_expiry_date ? $compliance->ohc_expiry_date->format('Y-m-d') : '' }}">
                    </div>

                    <hr>
                    <h6 class="text-warning mb-3">Food Safety Training</h6>
                     <div class="mb-3">
                        <label>Training Status</label>
                        <select name="food_safety_status" class="form-control">
                            <option value="pending" {{ $compliance->food_safety_training_status == 'pending' ? 'selected' : '' }}>Not Started</option>
                            <option value="assigned" {{ $compliance->food_safety_training_status == 'assigned' ? 'selected' : '' }}>Assigned / In Progress</option>
                            <option value="passed" {{ $compliance->food_safety_training_status == 'passed' ? 'selected' : '' }}>Assessment Passed</option>
                        </select>
                    </div>

                    <div class="submit-section text-center">
                         <button class="btn btn-primary w-100">Update Compliance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
