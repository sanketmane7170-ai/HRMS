@extends('layouts.backend')

@section('page-title')
    {{ __('Employee Details') }}
@endsection

@section('content')
<style>
    /* Sharp UI Overrides - Strict Enforcement */
    .card, .btn, .form-control, .nav-link, .progress, .progress-bar, .badge, .dropdown-menu, .modal-content, .img-thumbnail, .avatar-img, .card-header, .nav-tabs {
        border-radius: 0 !important;
    }
    
    /* Remove default card header borders and styling */
    .card-header {
        border: none !important;
        background-color: #fff !important;
    }

    /* Modern Sharp Top Navigation */
    .nav-tabs {
        border-bottom: 2px solid #e2e8f0;
        border-radius: 0 !important; /* Double check */
    }
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-weight: 600;
        padding: 1rem 1.5rem;
        margin-right: 0.5rem;
        transition: all 0.2s;
    }
    .nav-tabs .nav-link:hover {
        color: #0d6efd; /* Bootstrap Primary Blue */
        background-color: #f8fafc;
    }
    .nav-tabs .nav-link.active {
        border-bottom: 2px solid #0d6efd;
        color: #0d6efd;
        background: transparent;
    }
    
    /* Form Styling */
    .form-label {
        color: #475569;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    .form-control {
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        color: #1e293b;
    }
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
    }
    
    /* Section Headers */
    .section-title {
        color: #1e293b;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }
    .section-title::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 18px;
        background-color: #0d6efd;
        margin-right: 10px;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-uppercase font-weight-bold text-dark" style="letter-spacing: 0.5px;">Employee Onboarding</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('onboarding.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('onboarding.new-hires') }}">New Hires</a></li>
                        <li class="breadcrumb-item active">{{ $record->full_name }}</li>
                    </ul>
                </div>
                <div class="col-auto d-flex gap-2">
                    <form action="{{ route('onboarding.provide-access', $record->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary px-4 border-2 font-weight-bold text-uppercase shadow-sm" style="font-size: 0.8rem;">
                            <i class="fas fa-paper-plane mr-2"></i> {{ $record->user_id ? 'Resend Invitation' : 'Send Invitation' }}
                        </button>
                    </form>

                    <a href="{{ route('onboarding.new-hires') }}" class="btn btn-outline-secondary px-4 border-2 font-weight-bold text-uppercase" style="font-size: 0.8rem;">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Widget -->
        <div class="card mb-4 border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($record->full_name) }}&background=0d6efd&color=fff&size=128&rounded=false&bold=true" 
                             class="avatar-img shadow-sm" style="width: 90px; height: 90px;" alt="Profile">
                    </div>
                    <div class="col ml-3">
                        <h2 class="mb-1 font-weight-bold text-dark">{{ $record->full_name }}</h2>
                        <div class="text-muted mb-3 d-flex align-items-center">
                            <i class="far fa-envelope mr-2"></i> {{ $record->email }}
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="badge bg-white text-primary border border-primary px-3 py-2 mr-2 shadow-sm text-uppercase">
                                {{ $record->department_id ?? 'General' }}
                            </span>
                            <span class="badge bg-white text-secondary border border-secondary px-3 py-2 shadow-sm">
                                <i class="far fa-calendar-alt mr-2"></i> Joined: {{ \Carbon\Carbon::parse($record->joining_date)->format('d M, Y') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-auto text-right pl-5 border-left">
                        <div class="mb-2 font-weight-bold text-uppercase small text-muted text-right">Progress Status</div>
                        <h3 class="text-primary font-weight-bold mb-0 text-right">{{ $record->progress_percent }}%</h3>
                        <div class="progress mt-2" style="height: 8px; width: 180px; background-color: #f1f5f9;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $record->progress_percent }}%;" 
                                 aria-valuenow="{{ $record->progress_percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="mt-2 text-right">
                            @if($record->status == 'pending')
                                <span class="text-warning font-weight-bold small text-uppercase"><i class="fas fa-clock mr-1"></i> Pending Action</span>
                            @elseif($record->status == 'in_progress')
                                <span class="text-info font-weight-bold small text-uppercase"><i class="fas fa-spinner mr-1"></i> In Progress</span>
                            @elseif($record->status == 'completed')
                                <span class="text-success font-weight-bold small text-uppercase"><i class="fas fa-check-circle mr-1"></i> Completed</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                <ul class="nav nav-tabs card-header-tabs" id="onboardingTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active text-uppercase small" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">
                            <i class="far fa-user mr-2"></i> Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-uppercase small" id="documents-tab" data-bs-toggle="tab" href="#documents" role="tab" aria-controls="documents" aria-selected="false">
                            <i class="far fa-folder-open mr-2"></i> Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-uppercase small" id="forms-tab" data-bs-toggle="tab" href="#forms" role="tab" aria-controls="forms" aria-selected="false">
                            <i class="far fa-check-square mr-2"></i> Forms
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4 bg-white border-top">
                <div class="tab-content" id="onboardingTabsContent">
                    
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                        <h5 class="section-title">Edit Employee Information</h5>
                        <form action="{{ route('onboarding.update', $record->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-uppercase font-weight-bold">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" value="{{ $record->full_name }}" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-uppercase font-weight-bold">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ $record->email }}" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-uppercase font-weight-bold">Department (Division)</label>
                                    <select name="division_id" class="form-control">
                                        <option value="">Select Department</option>
                                        @foreach($divisions as $div)
                                            <option value="{{ $div->id }}" {{ $record->division_id == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-uppercase font-weight-bold">Branch</label>
                                    <select name="department_id" class="form-control">
                                        <option value="">Select Branch</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ $record->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-uppercase font-weight-bold">Joining Date</label>
                                    <input type="date" name="joining_date" class="form-control" value="{{ \Carbon\Carbon::parse($record->joining_date)->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-uppercase font-weight-bold">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="pending" {{ $record->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $record->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $record->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-uppercase font-weight-bold">Progress (%)</label>
                                    <input type="number" name="progress_percent" class="form-control" value="{{ $record->progress_percent }}" min="0" max="100">
                                </div>
                            </div>
                            <div class="text-right mt-3 border-top pt-4">
                                <button type="submit" class="btn btn-primary px-5 py-2 font-weight-bold text-uppercase shadow-sm">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Documents Tab -->
                    <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="section-title mb-0">Documents Repository</h5>
                            <button class="btn btn-primary btn-sm px-4 py-2 font-weight-bold text-uppercase shadow-sm" data-bs-toggle="modal" data-bs-target="#upload_document_modal">
                                <i class="fas fa-cloud-upload-alt mr-2"></i> Upload New
                            </button>
                        </div>

                        <div class="table-responsive border">
                            <table class="table table-hover table-center mb-0 bg-white">
                                <thead style="background-color: #f1f5f9;">
                                    <tr>
                                        <th class="py-3 pl-4 text-uppercase small font-weight-bold text-muted">File Name</th>
                                        <th class="py-3 text-uppercase small font-weight-bold text-muted">Uploaded On</th>
                                        <th class="py-3 text-right pr-4 text-uppercase small font-weight-bold text-muted">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $documents = $documents ?? []; @endphp
                                    @forelse($documents as $doc)
                                    <tr>
                                        <td class="pl-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box bg-light p-2 mr-3 border">
                                                    <i class="far fa-file-alt text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold text-dark">{{ $doc->original_name }}</div>
                                                    <div class="small text-muted text-uppercase">{{ ucfirst(str_replace('_', ' ', $doc->type->value ?? $doc->type)) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            {{ \Carbon\Carbon::parse($doc->created_at)->format('d M, Y') }}
                                            @if($doc->is_verified)
                                                <span class="badge bg-success ml-2">Verified</span>
                                            @else
                                                <span class="badge bg-warning text-dark ml-2">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-right pr-4 py-3">
                                            <form action="{{ route('onboarding.verify-document', $doc->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @if(!$doc->is_verified)
                                                    <button type="submit" class="btn btn-sm btn-outline-success border-2 mr-1" title="Verify"><i class="fas fa-check"></i></button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary border-2 mr-1" title="Unverify"><i class="fas fa-times"></i></button>
                                                @endif
                                            </form>
                                            <a href="{{ route('onboarding.secure.download', ['type'=>'user_document', 'id'=>$doc->id, 'field'=>'path']) }}" target="_blank" class="btn btn-sm btn-outline-primary border-2 mr-1" title="Download"><i class="fas fa-download"></i></a>
                                            <form action="{{ route('onboarding.delete-document', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-2" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="py-4 opacity-50">
                                                <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="64" class="mb-3 grayscale" style="filter: grayscale(100%);">
                                                <h6 class="text-muted font-weight-bold text-uppercase">No Documents Found</h6>
                                                <p class="text-muted small mb-0">Upload employee documents to see them here.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Forms Tab -->
                    <div class="tab-pane fade" id="forms" role="tabpanel" aria-labelledby="forms-tab">
                         <h5 class="section-title">Pending Forms</h5>
                         
                         @php $forms = $forms ?? []; @endphp
                         @if(empty($forms))
                            <div class="text-center py-5 border" style="background-color: #f8fafc;">
                                <div class="py-4">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-white p-3 mb-3 border shadow-sm" style="width: 60px; height: 60px;">
                                        <i class="fas fa-tasks text-muted fa-lg"></i>
                                    </div>
                                    <h6 class="text-muted font-weight-bold text-uppercase mb-1">All Caught Up!</h6>
                                    <p class="text-muted small mb-0">No forms pending for this employee.</p>
                                </div>
                            </div>
                         @else
                             <!-- Loop for forms -->
                         @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="upload_document_modal" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.upload-document', $record->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Document Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="document_name" required placeholder="e.g. Passport, Resume">
                    </div>
                    <div class="form-group">
                        <label>Category <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="passport">Passport</option>
                            <option value="visa">Visa</option>
                            <option value="labor_contract">Labor Contract</option>
                            <option value="probation_contract">Probation Contract</option>
                            <option value="education">Education/Certificate</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select File <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" name="document" required>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
