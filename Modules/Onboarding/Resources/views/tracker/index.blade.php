@extends('layouts.backend')

@section('title', 'Visa Workflow Tracker')

@push('css')
<style>
    /* Stepper CSS */
    .stepper {
        display: flex;
        justify-content: space-between;
        margin: 20px 0;
        position: relative;
    }
    .stepper::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }
    .step {
        position: relative;
        text-align: center;
        z-index: 1;
        background: #fff; /* Ensure label background matches card */
        padding: 0 10px;
        width: 25%;
    }
    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #fff;
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-weight: bold;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .step.active .step-circle {
        background: #3b82f6; /* Modern Blue */
        color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    .step.completed .step-circle {
        background: #10b981; /* Modern Green */
        color: #fff;
        border-color: #10b981;
    }
    .step-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
        margin-top: 5px;
    }
    .step.active .step-label {
        color: #3b82f6;
        font-weight: 700;
    }
    .step.completed .step-label {
        color: #10b981;
    }
    
    /* Card Design */
    .tracker-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: all 0.2s ease-in-out;
        position: relative; /* Establish stacking context */
        z-index: 1;
    }
    .tracker-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        border-color: #d1d5db;
        z-index: 10; /* Bring to front on hover */
    }
    /* Ensure dropdowns inside the card stay on top */
    .tracker-card .dropdown-menu {
        z-index: 1050; /* Bootstrap default is 1000, ensure it's higher */
    }
    
    /* Typography */
    .user-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111827;
        text-decoration: none;
    }
    .user-name:hover {
        color: #3b82f6;
    }
    .user-email {
        font-size: 0.9rem;
        color: #6b7280;
    }
    
    /* Status Badge in Center */
    .status-pill {
        background: #f3f4f6;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #4b5563;
        display: inline-block;
        margin-top: 15px;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-dark">Visa & Compliance Pipeline</h3>
                    <ul class="breadcrumb bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Workflow Tracker</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill font-weight-normal">
                        Candidates in Pipeline: <strong>{{ $trackers->total() }}</strong>
                    </span>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-sm-12">
                
                @if($trackers->isEmpty())
                <div class="text-center py-5 bg-white rounded shadow-sm border">
                    <img src="{{ asset('assets/backend/img/no-data.png') }}" onerror="this.src='{{ asset('assets/backend/img/profiles/avatar-01.jpg') }}'" class="img-fluid mb-3" style="max-height: 120px; opacity: 0.6;">
                    <h4 class="text-secondary">No active onboarding workflows</h4>
                    <p class="text-muted">Start a new hire process to see it tracked here.</p>
                </div>
                @endif

                @foreach($trackers as $user)
                    @php 
                        $visa = $user->visaProcess;
                        $compliance = $user->complianceRecord;
                        
                        // Determine Stage (0-6 scale for finer granularity)
                        // 0: Pre-Hire (Docs Pending)
                        // 1: MOHRE Contract / Work Permit
                        // 2: Entry Permit Issued
                        // 3: Status Changed / Medical Scheduled
                        // 4: Medical Fit
                        // 5: Residency / Insurance
                        // 6: Compliance Done
                        
                        $stage = 0;
                        if (!$visa) {
                             $stage = 0;   
                        } else {
                             if ($compliance && $compliance->food_safety_training_status == 'passed') $stage = 6;
                             elseif ($visa->residency_visa_status == 'stamped') $stage = 5;
                             elseif ($visa->medical_status == 'fit') $stage = 4;
                             elseif ($visa->medical_status == 'scheduled' || $visa->status_change_completed) $stage = 3;
                             elseif ($visa->entry_permit_status == 'issued') $stage = 2;
                             elseif ($visa->mohre_contract_status == 'signed' || $visa->work_permit_status == 'approved') $stage = 1;
                             else $stage = 0.5; // Workflow Started but no steps done
                        }
                    @endphp

                    <div class="card tracker-card mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                
                                {{-- 1. User Info --}}
                                <div class="col-lg-3 col-md-12 border-end-lg mb-3 mb-lg-0">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg me-3">
                                            <img class="avatar-img rounded-circle shadow-sm" src="{{ $user->profile_image_url ?? asset('assets/backend/img/profiles/avatar-01.jpg') }}" alt="User Image">
                                        </div>
                                        <div>
                                            {{-- Author: Sanket - Fix null pointer error --}}
                                            @if($visa)
                                                <a href="{{ route('onboarding.tracker.show', $visa->id) }}" class="user-name">{{ $user->name }}</a>
                                            @else
                                                <span class="user-name">{{ $user->name }}</span>
                                            @endif
                                            <div class="user-email">{{ $user->email }}</div>
                                            <div class="small text-muted mt-1"><i class="fas fa-briefcase me-1"></i> {{ $user->onboardingRecord->department->name ?? 'General' }}</div>
                                            @if($visa)
                                                <a href="{{ route('onboarding.tracker.show', $visa->id) }}" class="btn btn-xs btn-link p-0 mt-1">View Detailed Timeline &rarr;</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Progress Stepper --}}
                                <div class="col-lg-6 col-md-12 px-lg-5 mb-3 mb-lg-0 text-center">
                                    @if(!$visa)
                                        <div class="py-3">
                                            @if($user->documents->count() > 0)
                                                <span class="badge bg-warning text-dark border px-3 py-2 rounded-pill">
                                                    <i class="fas fa-folder-open me-2"></i> {{ $user->documents->count() }} Documents Uploaded
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                                                    <i class="fas fa-clock me-2"></i> Waiting for Candidate
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="stepper">
                                            <!-- Step 1: Permit -->
                                            <div class="step {{ $stage >= 2 ? 'completed' : ($stage >= 1 ? 'active' : '') }}">
                                                <div class="step-circle"><i class="fas fa-file-contract"></i></div>
                                                <div class="step-label">Permit</div>
                                            </div>
                                            
                                            <!-- Step 2: Medical -->
                                            <div class="step {{ $stage >= 4 ? 'completed' : ($stage >= 3 ? 'active' : '') }}">
                                                <div class="step-circle"><i class="fas fa-procedures"></i></div>
                                                <div class="step-label">Medical</div>
                                            </div>
                                            
                                            <!-- Step 3: Residency -->
                                            <div class="step {{ $stage >= 5 ? 'completed' : ($stage >= 5 ? 'active' : '') }}">
                                                <div class="step-circle"><i class="fas fa-passport"></i></div>
                                                <div class="step-label">Residency</div>
                                            </div>
                                            
                                            <!-- Step 4: Compliance -->
                                            <div class="step {{ $stage >= 6 ? 'completed' : ($stage >= 6 ? 'active' : '') }}">
                                                <div class="step-circle"><i class="fas fa-clipboard-check"></i></div>
                                                <div class="step-label">Compliance</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Latest Status Text -->
                                        <div class="status-pill">
                                            @if($visa->residency_visa_status == 'stamped') <span class="text-success"><i class="fas fa-check-circle me-1"></i> Visa Stamped (Exp: {{ $visa->visa_expiry_date }})</span>
                                            @elseif($visa->medical_status == 'fit') <span class="text-info"><i class="fas fa-info-circle me-1"></i> Medical Passed (FIT)</span>
                                            @elseif($visa->medical_status == 'scheduled') <span class="text-warning"><i class="fas fa-clock me-1"></i> Medical: {{ $visa->medical_appointment_date }}</span>
                                            @elseif($visa->entry_permit_status == 'issued') <span class="text-primary"><i class="fas fa-file-alt me-1"></i> Entry Permit Uploaded</span>
                                            @elseif($visa->work_permit_status == 'approved') <span class="text-primary"><i class="fas fa-file-signature me-1"></i> Work Permit Approved</span>
                                            @elseif($visa->mohre_contract_status == 'signed') <span class="text-secondary"><i class="fas fa-pen-nib me-1"></i> Contract Signed</span>
                                            @else <span class="text-secondary">Processing Invitation</span>
                                            @endif

                                            @if($user->operationalReadiness)
                                                <div class="mt-2">
                                                    <span class="badge bg-success text-white border px-3 py-2 rounded-pill shadow-sm">
                                                        <i class="fas fa-flag-checkered me-2"></i> READY FOR OPS
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- 3. Action Buttons --}}
                                <div class="col-lg-3 col-md-12 text-end ps-lg-4">
                                    @if(!$visa)
                                        @if($user->documents->count() > 0)
                                            <button class="btn btn-outline-warning w-100 shadow-sm mb-2" data-bs-toggle="modal" data-bs-target="#review_docs_{{ $user->id }}">
                                                <i class="fas fa-eye me-2"></i> Review Docs
                                            </button>
                                        @else
                                            <form action="{{ route('onboarding.tracker.init', $user->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="fas fa-play me-2"></i> Start Workflow</button>
                                            </form>
                                        @endif
                                    @else
                                        <div class="d-grid gap-2">
                                            {{-- Sequential Actions --}}
                                            @if($visa->mohre_contract_status != 'signed')
                                                <button class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#upload_mohre_{{ $user->id }}">
                                                    <i class="fas fa-file-contract me-2"></i> MOHRE Contract
                                                </button>
                                            @elseif($visa->work_permit_status != 'approved')
                                                <button class="btn btn-outline-dark w-100" data-bs-toggle="modal" data-bs-target="#upload_work_permit_{{ $user->id }}">
                                                    <i class="fas fa-briefcase me-2"></i> Work Permit
                                                </button>
                                            @elseif($visa->entry_permit_status != 'issued')
                                                <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#upload_entry_permit_{{ $user->id }}">
                                                    <i class="fas fa-upload me-2"></i> Upload Pink Visa
                                                </button>
                                            @elseif(!$visa->status_change_completed)
                                                 <form action="{{ route('onboarding.tracker.update-status', $user->visaProcess->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status_change_completed" value="1">
                                                    <button class="btn btn-outline-info text-dark w-100"><i class="fas fa-passport me-2"></i> Mark Status Change</button>
                                                </form>
                                            @elseif($visa->medical_status != 'fit' && $visa->medical_status != 'scheduled')
                                                <button class="btn btn-outline-warning text-dark w-100" data-bs-toggle="modal" data-bs-target="#schedule_medical_{{ $user->id }}">
                                                    <i class="fas fa-calendar-check me-2"></i> Medical Appt
                                                </button>
                                            @elseif($visa->medical_status == 'scheduled' && $visa->medical_status != 'fit')
                                                <button class="btn btn-warning w-100 text-white" data-bs-toggle="modal" data-bs-target="#upload_medical_result_{{ $user->id }}">
                                                    <i class="fas fa-file-medical me-2"></i> Upload Result
                                                </button>
                                            @elseif($visa->insurance_status != 'active')
                                                <button class="btn btn-outline-info text-dark w-100" data-bs-toggle="modal" data-bs-target="#upload_insurance_{{ $user->id }}">
                                                    <i class="fas fa-heart me-2"></i> Upload Insurance
                                                </button>
                                            @elseif($visa->residency_visa_status != 'stamped')
                                                <button class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#stamp_visa_{{ $user->id }}">
                                                    <i class="fas fa-stamp me-2"></i> Stamp Visa
                                                </button>
                                            @elseif($user->operationalReadiness)
                                                <form action="{{ route('onboarding.tracker.convert-employee', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will convert the candidate to a full employee and send credentials.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100 shadow-sm font-weight-bold">
                                                        <i class="fas fa-user-check me-2"></i> COMPLETE ONBOARDING
                                                    </button>
                                                </form>
                                            @else
                                                 <button class="btn btn-outline-secondary text-dark w-100" data-bs-toggle="modal" data-bs-target="#compliance_action_{{ $user->id }}">
                                                    <i class="fas fa-shield-alt me-2"></i> Compliance
                                                </button>
                                            @endif

                                            <div class="dropdown">
                                                <button class="btn btn-light w-100 border bg-white" type="button" data-bs-toggle="dropdown">
                                                    Actions <i class="fas fa-chevron-down ms-1 text-muted"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#upload_mohre_{{ $user->id }}">Contract</a>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#upload_entry_permit_{{ $user->id }}">Pink Visa</a>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#schedule_medical_{{ $user->id }}">Medical</a>
                                                    <div class="dropdown-divider"></div>
                                                    <span class="dropdown-item-text text-muted small">Manage Pipeline</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="mt-4 px-3">
                    {{ $trackers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals Section --}}
@foreach($trackers as $user)
    @if(!$user->visaProcess)
        @if($user->documents->count() > 0)
        <!-- Review Documents Modal -->
        <div id="review_docs_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Review Candidate Documents</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            @foreach($user->documents as $doc)
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm border p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ ucfirst($doc->type->value ?? $doc->type) }}</strong>
                                                <br><small class="text-muted">{{ $doc->original_name }}</small>
                                            </div>
                                            <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="btn btn-sm btn-light border"><i class="fas fa-download"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 text-center">
                            <form action="{{ route('onboarding.tracker.init', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg px-5">Approve & Start Visa Workflow</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else
        <!-- 1. Upload MOHRE Contract -->
        <div id="upload_mohre_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload MOHRE Contract</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('onboarding.tracker.update-status', $user->visaProcess->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="mohre_contract_status" value="signed">
                            <div class="form-group mb-3">
                                <label class="form-label">Signed Contract <span class="text-danger">*</span></label>
                                <input class="form-control" type="file" name="mohre_contract_file" required>
                            </div>
                            <div class="submit-section text-center">
                                <button class="btn btn-primary submit-btn">Upload Contract</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Upload Work Permit -->
        <div id="upload_work_permit_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Work Permit Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('onboarding.tracker.update-status', $user->visaProcess->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="work_permit_status" value="approved">
                            <div class="form-group mb-3">
                                <label class="form-label">Work Permit Document <span class="text-danger">*</span></label>
                                <input class="form-control" type="file" name="work_permit_file" required>
                            </div>
                            <div class="submit-section text-center">
                                <button class="btn btn-dark submit-btn">Confirm Approval</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Upload Entry Permit Modal (Existing) -->
        <div id="upload_entry_permit_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Entry Permit (Pink Visa)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('onboarding.tracker.upload-entry-permit', $user->visaProcess->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Entry Permit File <span class="text-danger">*</span></label>
                                <input class="form-control" type="file" name="entry_permit_file" required>
                            </div>
                            <div class="submit-section text-center">
                                <button class="btn btn-primary submit-btn">Upload & Notify Candidate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Schedule Medical (Existing) -->
        <div id="schedule_medical_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Schedule Medical Test</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('onboarding.tracker.schedule-medical', $user->visaProcess->id) }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="medical_date" required>
                            </div>
                            <div class="submit-section text-center">
                                <button class="btn btn-primary submit-btn">Schedule Appointment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 4.b Upload Medical Result (New) -->
        <div id="upload_medical_result_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Medical Test Result</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('onboarding.tracker.upload-medical-result', $user->visaProcess->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label mb-2">Test Result Status</label>
                                <select name="medical_status" class="form-control">
                                    <option value="fit">FIT (Passed)</option>
                                    <option value="unfit">UNFIT (Failed)</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Result Document <span class="text-danger">*</span></label>
                                <input class="form-control" type="file" name="medical_result_file" required>
                            </div>
                            <div class="submit-section text-center">
                                <button class="btn btn-success submit-btn">Update Medical Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Upload Insurance (New) -->
        <div id="upload_insurance_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Health Insurance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('onboarding.tracker.upload-insurance', $user->visaProcess->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Insurance Card/Certificate <span class="text-danger">*</span></label>
                                <input class="form-control" type="file" name="insurance_card_file" required>
                            </div>
                            <div class="submit-section text-center">
                                <button class="btn btn-info text-white submit-btn">Upload Insurance</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 6. Stamp Visa Modal (Existing) -->
        <div id="stamp_visa_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Finalize Residency Stamping</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('onboarding.tracker.stamp-visa', $user->visaProcess->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Visa Expiry Date (2 Years) <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="visa_expiry_date" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Stamped Visa Copy <span class="text-danger">*</span></label>
                                <input class="form-control" type="file" name="visa_file" required>
                            </div>
                            <div class="submit-section text-center">
                                <button class="btn btn-primary submit-btn">Complete Process</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    @if($user->complianceRecord)
        <!-- Compliance Action Modal (Existing) -->
        <div id="compliance_action_{{ $user->id }}" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Compliance Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('onboarding.tracker.update-compliance', $user->complianceRecord->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Upload OHC Card (If Issued)</label>
                                <input class="form-control" type="file" name="ohc_file">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">OHC Expiry Date</label>
                                <input class="form-control" type="date" name="ohc_expiry_date">
                            </div>
                            <hr>
                            <div class="form-group mb-3">
                                <label class="form-label">Food Safety Training Status</label>
                                <select class="form-control" name="food_safety_status">
                                    <option value="pending" {{ $user->complianceRecord->food_safety_training_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="assigned" {{ $user->complianceRecord->food_safety_training_status == 'assigned' ? 'selected' : '' }}>Assigned (In Progress)</option>
                                    <option value="passed" {{ $user->complianceRecord->food_safety_training_status == 'passed' ? 'selected' : '' }}>Passed / Certified</option>
                                </select>
                            </div>
                            <div class="submit-section text-center">
                                <button class="btn btn-primary submit-btn">Update Compliance</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
