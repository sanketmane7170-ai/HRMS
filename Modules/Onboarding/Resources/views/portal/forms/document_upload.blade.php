@extends('onboarding::portal.layout')

@section('title', 'Upload Documents')

@section('styles')
<style>
    .upload-vault-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }
    
    .vault-header {
        padding: 2.5rem;
        border-bottom: 1px solid var(--border-soft);
        background: var(--dark);
        color: #fff;
    }

    .vault-body {
        padding: 2.5rem;
    }

    .upload-zone {
        background: #f8fafc;
        border: 2px dashed #e2e8f0;
        border-radius: var(--radius-md);
        padding: 2rem;
        transition: var(--transition);
    }
    
    .upload-zone:hover {
        border-color: var(--primary);
        background: var(--primary-soft);
    }

    .premium-select, .premium-file {
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        padding: 12px 16px !important;
        border-radius: var(--radius-md) !important;
        font-weight: 500;
    }

    .file-tile {
        padding: 1.25rem;
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: var(--radius-md);
        margin-bottom: 1rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .file-tile:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .file-icon {
        width: 44px;
        height: 44px;
        background: var(--primary-soft);
        color: var(--primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .status-indicator {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 6px;
    }
</style>
@endsection

@section('content')
<div class="container py-5 fade-in-up">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <a href="{{ route('portal.dashboard') }}" class="text-muted small font-weight-bold text-decoration-none mb-2 d-inline-block">
                        <i class="fas fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h2 class="font-weight-bold mb-0" style="letter-spacing: -1.5px;">Document Vault</h2>
                </div>
                <div class="text-right d-none d-md-block">
                    <div class="small text-muted font-weight-bold text-uppercase">Step 03 of 03</div>
                    <div class="font-weight-bold text-primary" style="font-size: 1.2rem;">90% Complete</div>
                </div>
            </div>

            <div class="upload-vault-card shadow-premium">
                <div class="vault-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary p-2 rounded-lg"><i class="fas fa-shield-alt text-white"></i></div>
                        <h5 class="mb-0 font-weight-bold text-uppercase small" style="letter-spacing: 2px;">Secured Verification Center</h5>
                    </div>
                </div>
                
                <div class="vault-body">
                    <div class="row">
                        <div class="col-lg-5 pr-lg-5 border-right">
                            <h5 class="font-weight-bold mb-4">Upload New File</h5>
                            <form action="{{ route('portal.save-document') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label class="small font-weight-bold text-uppercase text-muted mb-2">Category</label>
                                    <select name="document_type" class="form-control premium-select" required>
                                        <option value="">Select Document Type</option>
                                        <option value="passport">Passport Data Page</option>
                                        <option value="passport_photo">Digital Passport Photo (White BG)</option>
                                        <option value="visa">Current Visa / Cancellation Paper</option>
                                        <option value="emirates_id">Emirates ID (Front & Back)</option>
                                        <option value="education">Attested Degree / Certificate</option>
                                        <option value="iban_certificate">IBAN Certificate</option>
                                        <option value="cv">Curriculum Vitae (CV)</option>
                                        <option value="probation_contract">Probation Contract</option> <!-- Added for Bug 12 -->
                                        <option value="labor_contract">Labor Contract</option>     <!-- Added for Bug 12 -->
                                    </select>
                                </div>
                                <div class="upload-zone mb-4 text-center">
                                    <i class="fas fa-cloud-upload-alt text-primary mb-3 fa-2x"></i>
                                    <input type="file" name="document" class="form-control premium-file mb-2" required>
                                    <p class="small text-muted mb-0">PDF, JPG or PNG. Max filesize 10MB.</p>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-3 font-weight-bold">
                                    SECURE UPLOAD <i class="fas fa-lock ml-2 small"></i>
                                </button>
                            </form>
                        </div>

                        <div class="col-lg-7 pl-lg-5 mt-5 mt-lg-0">
                            <h5 class="font-weight-bold mb-4">Vault Inventory</h5>
                            
                            @if($documents->isEmpty())
                                <div class="text-center py-5 bg-light rounded-lg border">
                                    <i class="far fa-folder-open text-muted pulse mb-3" style="font-size: 2.5rem;"></i>
                                    <p class="text-muted font-weight-bold">Your vault is currently empty.</p>
                                </div>
                            @else
                                @foreach($documents as $doc)
                                    <div class="file-tile">
                                        <div class="file-icon">
                                            @php 
                                                $ext = pathinfo($doc->path, PATHINFO_EXTENSION);
                                                $icon = in_array($ext, ['jpg', 'png', 'jpeg']) ? 'fa-file-image' : ($ext == 'pdf' ? 'fa-file-pdf' : 'fa-file-alt');
                                            @endphp
                                            <i class="far {{ $icon }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="font-weight-bold text-dark">{{ ucfirst(str_replace('_', ' ', $doc->type->value ?? $doc->type)) }}</div>
                                            <div class="small text-muted">{{ $doc->original_name }}</div>
                                        </div>
                                        <div class="text-right d-flex align-items-center gap-2">
                                            <span class="status-indicator bg-success-soft text-success">Verified</span>
                                            <form action="{{ route('portal.delete-document', $doc->id) }}" method="POST" onsubmit="return confirm('Delete this file?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger border-0 p-1" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <div class="mt-4 p-3 bg-primary-soft rounded-lg d-flex align-items-center gap-3 border border-primary-light" style="border-style: dashed !important;">
                                    <i class="fas fa-info-circle text-primary"></i>
                                    <p class="small mb-0 text-primary font-weight-bold">
                                        Onboarding is ready for final submission once 3+ core documents are uploaded.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-top d-flex align-items-center justify-content-between">
                        <a href="{{ route('portal.dashboard') }}" class="btn btn-link text-muted font-weight-bold text-decoration-none">
                            <i class="fas fa-chevron-left mr-2"></i> Dashboard
                        </a>
                        @if($documents->count() >= 3)
                            <a href="{{ route('portal.dashboard') }}" class="btn btn-success px-5 py-3 font-weight-bold shadow-premium" style="background: #059669; border: none;">
                                FINALIZE ONBOARDING <i class="fas fa-check-double ml-2"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
