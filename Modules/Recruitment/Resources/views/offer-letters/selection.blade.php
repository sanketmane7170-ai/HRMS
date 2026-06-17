@extends('layouts.backend')

@section('title')
    Select Offer Letter Method
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Generate Offer Letter</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.offers.index') }}">Offers</a></li>
                        <li class="breadcrumb-item active">Select Method</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('recruitment.offers.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Offers
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="text-center mb-5">
                    <h2 class="fw-bold" style="color: #1e3a8a;">Choose Generation Method</h2>
                    <p class="text-muted fs-5">Select how you would like to create this offer letter</p>
                </div>

                <div class="row g-4">
                    <!-- Option 1: Upload Document -->
                    <div class="col-md-4">
                        <div class="card h-100 selection-card border-0 shadow-sm" style="border-radius: 20px; transition: all 0.3s ease;">
                            <div class="card-body p-4 text-center d-flex flex-column">
                                <div class="icon-wrapper mb-4 mx-auto" style="width: 80px; height: 80px; background: rgba(59, 130, 246, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-upload fs-1" style="color: #3b82f6;"></i>
                                </div>
                                <h4 class="fw-bold mb-3">Upload Document</h4>
                                <p class="text-muted mb-4">Upload a custom DOCX file, edit placeholders, and save as a new offer.</p>
                                <div class="mt-auto">
                                    <button class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                        Select & Proceed
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Use Template -->
                    <div class="col-md-4">
                        <div class="card h-100 selection-card border-0 shadow-sm" style="border-radius: 20px; transition: all 0.3s ease;">
                            <div class="card-body p-4 text-center d-flex flex-column">
                                <div class="icon-wrapper mb-4 mx-auto" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-copy fs-1" style="color: #10b981;"></i>
                                </div>
                                <h4 class="fw-bold mb-3">Use Template</h4>
                                <p class="text-muted mb-4">Choose from your saved custom DOCX templates and customize for this candidate.</p>
                                <div class="mt-auto">
                                    <button class="btn btn-success w-100 py-3 fw-bold" style="border-radius: 12px; background-color: #10b981; border: none;" data-bs-toggle="modal" data-bs-target="#templateModal">
                                        Select & Proceed
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option 3: System Default -->
                    <div class="col-md-4">
                        <div class="card h-100 selection-card border-0 shadow-sm" style="border-radius: 20px; transition: all 0.3s ease;">
                            <div class="card-body p-4 text-center d-flex flex-column">
                                <div class="icon-wrapper mb-4 mx-auto" style="width: 80px; height: 80px; background: rgba(214, 31, 105, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-laptop-code fs-1" style="color: #D61F69;"></i>
                                </div>
                                <h4 class="fw-bold mb-3">System Default</h4>
                                <p class="text-muted mb-4">Use the standard offer letter format with live preview and specialized form fields.</p>
                                <div class="mt-auto">
                                    <a href="{{ route('recruitment.offer-letters.create') }}" class="btn btn-info w-100 py-3 fw-bold text-white" style="border-radius: 12px; background: linear-gradient(135deg, #D61F69 0%, #ECA770 100%); border: none;">
                                        Select & Proceed
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5 pt-2">
                <div class="text-center mb-4">
                    <i class="fas fa-cloud-upload-alt fs-1 mb-3" style="color: #3b82f6;"></i>
                    <h3 class="fw-bold">Upload DOCX</h3>
                    <p class="text-muted">Choose a candidate and upload your DOCX file</p>
                </div>
                <form action="{{ route('recruitment.offer-letters.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select Application <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" name="application_id" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                            <option value="">Choose application...</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate['id'] }}" {{ $application_id == $candidate['id'] ? 'selected' : '' }}>
                                    {{ $candidate['name'] }} - {{ $candidate['job_title'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">DOCX File <span class="text-danger">*</span></label>
                        <input type="file" name="docx_file" class="form-control form-control-lg" accept=".docx" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 12px;">
                        Upload & Open Editor
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5 pt-2">
                <div class="text-center mb-4">
                    <i class="fas fa-layer-group fs-1 mb-3" style="color: #10b981;"></i>
                    <h3 class="fw-bold">Use Template</h3>
                    <p class="text-muted">Select an application and a saved template</p>
                </div>
                <form action="{{ route('recruitment.offer-letters.edit-template') }}" method="GET">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select Application <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" name="application_id" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                            <option value="">Choose application...</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate['id'] }}" {{ $application_id == $candidate['id'] ? 'selected' : '' }}>
                                    {{ $candidate['name'] }} - {{ $candidate['job_title'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select Template <span class="text-danger">*</span></label>
                        @php
                            $templates = \Modules\Recruitment\Entities\OfferLetterTemplate::all();
                        @endphp
                        <select class="form-select form-select-lg" name="template_id" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                            <option value="">Choose template...</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                            @if($templates->isEmpty())
                                <option value="" disabled>No templates saved yet</option>
                            @endif
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold" style="border-radius: 12px; background-color: #10b981; border: none;">
                        Load Template
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .selection-card {
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        cursor: pointer;
        overflow: hidden;
    }
    .selection-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        border-color: #3b82f6 !important;
    }
    .icon-wrapper {
        transition: all 0.3s ease !important;
    }
    .selection-card:hover .icon-wrapper {
        transform: scale(1.15) rotate(5deg) !important;
    }
    .selection-card h4 {
        color: #1e293b !important;
        transition: color 0.3s ease;
    }
    .selection-card:hover h4 {
        color: #2563eb !important;
    }
</style>
@endsection
