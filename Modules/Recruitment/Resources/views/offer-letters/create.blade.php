@extends('layouts.backend')

@section('title')
    Generate Offer Letter
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
                        <li class="breadcrumb-item active">Generate Letter</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('recruitment.offers.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Offers
                    </a>
                </div>
            </div>
        </div>

        <!-- Alpine.js Data Container -->
        <div x-data="offerLetterGenerator()" class="row g-4" :class="{'position-fixed w-100 h-100 top-0 start-0 z-3 p-3': isMaximized}" :style="isMaximized ? 'background: rgba(0,0,0,0.1); backdrop-filter: blur(5px);' : ''">
            <!-- Left Side: Form -->
            <div class="col-lg-6" :class="{'col-lg-5': isMaximized}">
                <div class="card shadow-lg border-0 h-100">
                    <div class="card-header text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 1.5rem;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-edit me-2 fs-5"></i>
                                <h5 class="card-title mb-0 fw-bold">Offer Letter Details</h5>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-light" @click="toggleFormCollapse" :title="formCollapsed ? 'Expand Form' : 'Collapse Form'">
                                <i :class="formCollapsed ? 'fas fa-chevron-down' : 'fas fa-chevron-up'"></i>
                            </button>
                        </div>
                        <div class="position-absolute" style="top: 10px; right: 15px; opacity: 0.15; pointer-events: none;">
                            <i class="fas fa-clipboard-list" style="font-size: 3rem; color: rgba(255,255,255,0.8);"></i>
                        </div>
                    </div>
                    <div class="card-body" x-show="!formCollapsed" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-screen" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                        <form id="offerLetterForm" @submit.prevent="generatePdf">
                            @csrf
                            
                            <!-- Company Information -->
                            <div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, rgba(214, 31, 105, 0.05) 0%, rgba(236, 167, 112, 0.05) 100%); border-left: 4px solid #D61F69;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center" style="color: #D61F69;">
                                    <i class="fas fa-building me-2"></i>Company Information
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" x-model="formData.company_name" @input="updatePreview" required style="border: 2px solid #e2e8f0; border-radius: 8px;" placeholder="Enter your company name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Company Logo</label>
                                    <div class="logo-upload-area p-3 border-2 border-dashed rounded-3 text-center" style="border-color: #D61F69; background: rgba(214, 31, 105, 0.02);">
                                        <input type="file" class="form-control d-none" id="logoUpload" @change="handleLogoUpload" accept="image/*">
                                        <label for="logoUpload" class="cursor-pointer d-block">
                                            <div x-show="!logoPreview" class="text-muted">
                                                <i class="fas fa-cloud-upload-alt fs-2 mb-2" style="color: #D61F69;"></i>
                                                <div class="fw-semibold">Click to upload logo</div>
                                                <small>PNG, JPG, GIF up to 2MB</small>
                                            </div>
                                            <div x-show="logoPreview" class="position-relative">
                                                <img :src="logoPreview" alt="Logo Preview" class="img-thumbnail shadow-sm" style="max-height: 80px;">
                                                <div class="mt-2 text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>Logo uploaded</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Candidate Information -->
                            <div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%); border-left: 4px solid #3b82f6;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center" style="color: #3b82f6;">
                                    <i class="fas fa-user me-2"></i>Candidate Information
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Candidate Name <span class="text-danger">*</span></label>
                                    <div class="input-group" style="border: 2px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                        <select class="form-select" x-model="selectedCandidate" @change="loadCandidateData" 
                                                style="border: none; font-weight: normal; max-width: 200px;">
                                            <option value="" style="font-weight: normal; color: #6b7280;">Select from applications</option>
                                            @if(isset($candidates) && count($candidates) > 0)
                                                @foreach($candidates as $candidate)
                                                    <option value="{{ $candidate['id'] }}" 
                                                            data-name="{{ $candidate['name'] }}"
                                                            data-email="{{ $candidate['email'] ?? '' }}"
                                                            data-job-title="{{ $candidate['job_title'] }}"
                                                            data-department="{{ $candidate['department'] }}"
                                                            data-location="{{ $candidate['location'] }}"
                                                            data-salary="{{ $candidate['min_salary'] }}"
                                                            data-job-id="{{ $candidate['job_id'] }}">
                                                        {{ $candidate['name'] }} - {{ $candidate['job_title'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <input type="text" class="form-control form-control-lg" x-model="formData.candidate_name" required 
                                               style="border: none;" 
                                               placeholder="Enter candidate's full name or select from applications">
                                    </div>
                                    <small class="text-muted d-flex align-items-center mt-2" x-show="selectedCandidate == ''">
                                        <i class="fas fa-info-circle me-1"></i>
                                        You can select from existing applications or enter manually.
                                    </small>
                                </div>
                            </div>

                            <!-- Job Details -->
                            <div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(52, 211, 153, 0.05) 100%); border-left: 4px solid #10b981;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center" style="color: #10b981;">
                                    <i class="fas fa-briefcase me-2"></i>Job Details
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Job Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" x-model="formData.job_title" @input="updatePreview" required placeholder="Enter job title">
                                        <small x-show="selectedCandidate" class="text-muted">Auto-populated from application (editable)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" x-model="formData.department" @input="updatePreview" required placeholder="Enter department">
                                        <small x-show="selectedCandidate" class="text-muted">Auto-populated from application (editable)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Reporting To <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" x-model="formData.reporting_to" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" x-model="formData.start_date" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Work Schedule <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" x-model="formData.work_schedule" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" x-model="formData.location" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Compensation -->
                            <div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(251, 191, 36, 0.05) 100%); border-left: 4px solid #f59e0b;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center" style="color: #f59e0b;">
                                    <i class="fas fa-dollar-sign me-2"></i>Compensation
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                                        <select class="form-select" x-model="formData.currency" required>
                                            <option value="USD">🇺🇸 USD - US Dollar</option>
                                            <option value="EUR">🇪🇺 EUR - Euro</option>
                                            <option value="GBP">🇬🇧 GBP - British Pound</option>
                                            <!-- Author: Sanket - Added AED currency option per user requirement -->
                                            <option value="AED">🇦🇪 AED - UAE Dirham</option>
                                            <option value="INR">🇮🇳 INR - Indian Rupee</option>
                                            <option value="CAD">🇨🇦 CAD - Canadian Dollar</option>
                                            <option value="AUD">🇦🇺 AUD - Australian Dollar</option>
                                            <option value="JPY">🇯🇵 JPY - Japanese Yen</option>
                                            <option value="CHF">🇨🇭 CHF - Swiss Franc</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Salary Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" x-text="getCurrencySymbol()" style="background: #f8f9fa; border-color: #e2e8f0;"></span>
                                            <input type="number" step="0.01" class="form-control" x-model="formData.salary_amount" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Payment Period <span class="text-danger">*</span></label>
                                        <select class="form-select" x-model="formData.payment_period" required>
                                            <option value="Year">Per Year</option>
                                            <option value="Month">Per Month</option>
                                            <option value="Hour">Per Hour</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Pay Frequency <span class="text-danger">*</span></label>
                                        <select class="form-select" x-model="formData.pay_frequency" required>
                                            <option value="Weekly">Weekly</option>
                                            <option value="Bi-weekly">Bi-weekly</option>
                                            <option value="Monthly">Monthly</option>
                                            <option value="Annually">Annually</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Benefits -->
                            <div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, rgba(139, 69, 19, 0.05) 0%, rgba(180, 83, 9, 0.05) 100%); border-left: 4px solid #b45309;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center" style="color: #b45309;">
                                    <i class="fas fa-gift me-2"></i>Benefits Package
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Benefits & Perks</label>
                                    <textarea class="form-control" rows="4" x-model="formData.benefits" 
                                              style="border: 2px solid #e2e8f0; border-radius: 8px;" 
                                              placeholder="Enter each benefit on a new line&#10;Example:&#10;Health Insurance&#10;Paid Time Off&#10;401(k) Plan&#10;Professional Development"></textarea>
                                    <small class="text-muted d-flex align-items-center mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Each line will become a bullet point in the offer letter.
                                    </small>
                                </div>
                            </div>

                            <!-- Closing Information -->
                            <div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.05) 0%, rgba(196, 181, 253, 0.05) 100%); border-left: 4px solid #a855f7;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center" style="color: #a855f7;">
                                    <i class="fas fa-signature me-2"></i>Closing Information
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label">Contingencies</label>
                                    <textarea class="form-control" rows="3" x-model="formData.contingencies" 
                                              placeholder="Enter any contingencies for this offer"></textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Offer Expiration Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" x-model="formData.expiration_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sender Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" x-model="formData.sender_name" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Sender Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" x-model="formData.sender_title" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-3 d-md-flex justify-content-md-end mt-5 pt-4" style="border-top: 1px solid #e2e8f0;">
                                <button type="button" class="btn btn-outline-info btn-lg px-4 py-2" @click="quickPrint" style="border-radius: 10px;">
                                    <i class="fas fa-print me-2"></i>Quick Print
                                </button>
                                <button type="button" class="btn btn-outline-success btn-lg px-4 py-2" @click="saveOffer" :disabled="saving" style="border-radius: 10px;">
                                    <span x-show="!saving"><i class="fas fa-save me-2"></i>Save Offer</span>
                                    <span x-show="saving"><i class="fas fa-spinner fa-spin me-2"></i>Saving...</span>
                                </button>
                                <button type="submit" class="btn btn-lg px-4 py-2" :disabled="loading" 
                                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; border-radius: 10px; color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                                    <span x-show="!loading"><i class="fas fa-download me-2"></i>Download PDF</span>
                                    <span x-show="loading"><i class="fas fa-spinner fa-spin me-2"></i>Generating...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Side: Live Preview -->
            <div class="col-lg-6">
                <div class="card shadow-lg border-0" style="min-height: 700px;">
                    <div class="card-header text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #D61F69 0%, #ECA770 100%); padding: 1.5rem;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-eye me-2 fs-5"></i>
                                <h5 class="card-title mb-0 fw-bold">Live Preview</h5>
                            </div>
                            <!-- Zoom Controls -->
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-light me-2" @click="zoomOut" :disabled="currentZoom <= 0.3">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <span class="text-white me-2" style="font-size: 0.9rem; min-width: 40px;" x-text="Math.round(currentZoom * 100) + '%'"></span>
                                <button type="button" class="btn btn-sm btn-outline-light me-2" @click="zoomIn" :disabled="currentZoom >= 1.2">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light me-2" @click="resetZoom" title="Reset Zoom">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light" @click="toggleMaximize" :title="isMaximized ? 'Minimize' : 'Maximize'">
                                    <i :class="isMaximized ? 'fas fa-compress' : 'fas fa-expand'"></i>
                                </button>
                            </div>
                        </div>
                        <div class="position-absolute" style="top: 10px; right: 15px; opacity: 0.15; pointer-events: none;">
                            <i class="fas fa-file-contract" style="font-size: 3rem; color: rgba(255,255,255,0.8);"></i>
                        </div>
                    </div>
                    <div class="card-body p-2" style="background: #f8f9fa; min-height: 600px; max-height: calc(100vh - 200px); overflow-y: auto;">
                        <!-- A4 Document Container -->
                        <div class="preview-container d-flex justify-content-center" style="
                            width: 100%; 
                            height: 100%; 
                            overflow: auto;
                            padding: 10px;
                        ">
                            <div id="offerLetterPreview" class="offer-letter-preview shadow-lg" :style="{
                                'width': '210mm', 
                                'min-height': '297mm',
                                'max-width': '100%',
                                'background': 'white',
                                'font-family': 'Inter, sans-serif',
                                'transform': `scale(${currentZoom}) !important`,
                                '--custom-transform': `scale(${currentZoom})`,
                                'transform-origin': 'top center !important',
                                'border-radius': '8px',
                                'overflow': 'visible',
                                'position': 'relative',
                                'margin': '0 auto',
                                'transition': 'none !important',
                                'pointer-events': 'auto'
                            }">
                                
                                <!-- Professional Letterhead -->
                                <div class="letterhead position-relative" style="
                                    background: linear-gradient(135deg, #D61F69 0%, #C91E5A 50%, #B01E4D 100%);
                                    padding: 2rem;
                                    color: white;
                                ">
                                    <!-- Decorative Elements -->
                                    <div class="position-absolute top-0 start-0 w-100 h-100" style="
                                        background-image: 
                                            radial-gradient(circle at 20% 20%, rgba(255,255,255,0.1) 1px, transparent 1px),
                                            radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 1px, transparent 1px);
                                        background-size: 50px 50px;
                                    "></div>
                                    
                                    <!-- Header Content -->
                                    <div class="row align-items-center position-relative">
                                        <div class="col-8" x-effect="console.log('Effect triggered for company name:', formData.company_name)">
                                            <h1 class="mb-2 fw-bold" style="font-size: 2.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);" x-text="formData.company_name || 'MOM Digital'"></h1>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3" style="width: 4px; height: 40px; background: #ECA770; border-radius: 2px;"></div>
                                                <h2 class="mb-0 fw-semibold" style="font-size: 1.6rem; opacity: 0.95;">Job Offer Letter</h2>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div x-show="logoPreview" class="logo-container p-3 rounded-3" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                                <img :src="logoPreview" alt="Company Logo" class="img-fluid" style="max-height: 70px; max-width: 120px; filter: brightness(1.1);">
                                            </div>
                                            <div x-show="!logoPreview" class="logo-placeholder p-3 rounded-3 text-center" style="background: rgba(255,255,255,0.1); border: 2px dashed rgba(255,255,255,0.3);">
                                                <i class="fas fa-image mb-2" style="font-size: 1.5rem; opacity: 0.7;"></i>
                                                <div style="font-size: 0.8rem; opacity: 0.7;">Company Logo</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Document Body -->
                                <div class="document-body" style="padding: 2.5rem;">
                                    
                                    <!-- Date -->
                                    <div class="mb-4">
                                        <p class="mb-0 text-muted" x-text="formatDate(new Date())"></p>
                                    </div>

                    <!-- Candidate Address -->
                    <div class="mb-4" x-effect="console.log('Effect triggered for candidate name:', formData.candidate_name)">
                        <p class="mb-1 fw-bold" x-text="formData.candidate_name || 'Please enter candidate name'" style="color: #1a202c; font-size: 1.1rem;"></p>
                        <p class="mb-0">Dear <span x-text="formData.candidate_name || 'Candidate'"></span>,</p>
                    </div>                    <!-- Opening Paragraph -->
                    <div class="mb-4" style="line-height: 1.8;" x-effect="console.log('Effect triggered for job/dept:', formData.job_title, formData.department)">
                        <p class="mb-0">We are pleased to extend this offer of employment for the position of <strong x-text="formData.job_title || 'Job Position'" style="color: #D61F69;"></strong> in our <strong x-text="formData.department || 'Department'" style="color: #D61F69;"></strong> department at <strong style="color: #D61F69;" x-text="formData.company_name || 'MOM Digital'"></strong>.</p>
                    </div>                                    <!-- Job Details Section -->
                                    <div class="mb-4">
                                        <h4 class="fw-bold mb-3" style="color: #D61F69; border-bottom: 3px solid #ECA770; padding-bottom: 0.5rem; display: inline-block;">Position Details</h4>
                                        
                                        <div class="row mt-3" style="font-size: 0.95rem;">
                                            <div class="col-6">
                                                <div class="detail-item mb-2">
                                                    <span class="detail-label fw-semibold" style="color: #4a5568;">Job Title:</span>
                                                    <span class="detail-value ms-2" x-text="formData.job_title || 'Please enter job title'"></span>
                                                </div>
                                                <div class="detail-item mb-2">
                                                    <span class="detail-label fw-semibold" style="color: #4a5568;">Department:</span>
                                                    <span class="detail-value ms-2" x-text="formData.department || 'Please enter department'"></span>
                                                </div>
                                                <div class="detail-item mb-2">
                                                    <span class="detail-label fw-semibold" style="color: #4a5568;">Reporting To:</span>
                                                    <span class="detail-value ms-2" x-text="formData.reporting_to || 'To be determined'"></span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="detail-item mb-2">
                                                    <span class="detail-label fw-semibold" style="color: #4a5568;">Start Date:</span>
                                                    <span class="detail-value ms-2" x-text="formData.start_date ? formatDate(formData.start_date) : 'Please enter start date'"></span>
                                                </div>
                                                <div class="detail-item mb-2">
                                                    <span class="detail-label fw-semibold" style="color: #4a5568;">Location:</span>
                                                    <span class="detail-value ms-2" x-text="formData.location || 'Please enter location'"></span>
                                                </div>
                                                <div class="detail-item mb-2">
                                                    <span class="detail-label fw-semibold" style="color: #4a5568;">Schedule:</span>
                                                    <span class="detail-value ms-2" x-text="formData.work_schedule || 'Please enter work schedule'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Compensation Section -->
                                    <div class="mb-4">
                                        <h4 class="fw-bold mb-3" style="color: #D61F69; border-bottom: 3px solid #ECA770; padding-bottom: 0.5rem; display: inline-block;">Compensation</h4>
                                        <div class="compensation-highlight p-3 rounded-3 mt-3" style="background: linear-gradient(135deg, rgba(214, 31, 105, 0.1) 0%, rgba(236, 167, 112, 0.1) 100%); border-left: 4px solid #ECA770;">
                                            <p class="mb-0" style="font-size: 1.1rem;">Your starting salary will be <strong style="color: #D61F69; font-size: 1.2rem;"><span x-text="getCurrencySymbol()"></span><span x-text="formData.salary_amount ? Number(formData.salary_amount).toLocaleString() : '0'"></span> per <span x-text="(formData.payment_period || 'year').toLowerCase()"></span></strong>, paid <span x-text="(formData.pay_frequency || 'monthly').toLowerCase()"></span>.</p>
                                        </div>
                                    </div>

                                    <!-- Benefits Section -->
                                    <div class="mb-4" x-show="formData.benefits && formData.benefits.trim()">
                                        <h4 class="fw-bold mb-3" style="color: #D61F69; border-bottom: 3px solid #ECA770; padding-bottom: 0.5rem; display: inline-block;">Benefits Package</h4>
                                        <ul class="mt-3 benefits-list" style="padding-left: 1.5rem;">
                                            <template x-for="benefit in getBenefitsList()" :key="benefit">
                                                <li class="mb-2" style="line-height: 1.6;">
                                                    <i class="fas fa-check-circle me-2" style="color: #ECA770;"></i>
                                                    <span x-text="benefit"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

                                    <!-- Contingencies Section -->
                                    <div class="mb-4" x-show="formData.contingencies && formData.contingencies.trim()">
                                        <h4 class="fw-bold mb-3" style="color: #D61F69; border-bottom: 3px solid #ECA770; padding-bottom: 0.5rem; display: inline-block;">Terms & Conditions</h4>
                                        <div class="terms-box p-3 rounded-3 mt-3" style="background: #f7fafc; border: 1px solid #e2e8f0;">
                                            <p class="mb-0" x-text="formData.contingencies" style="line-height: 1.7;"></p>
                                        </div>
                                    </div>

                                    <!-- Closing Section -->
                                    <div class="mb-4">
                                        <p style="line-height: 1.8;">Please confirm your acceptance of this offer by <strong x-text="formData.expiration_date ? formatDate(formData.expiration_date) : 'Please set expiration date'" style="color: #D61F69;"></strong>. We are excited about the possibility of you joining our team and look forward to your positive response.</p>
                                    </div>

                                    <!-- Signature Section -->
                                    <div class="mt-5 pt-4" style="border-top: 1px solid #e2e8f0;">
                                        <p class="mb-1">Sincerely,</p>
                                        <div class="mt-4">
                                            <p class="mb-1 fw-bold" x-text="formData.sender_name || 'HR Department'" style="color: #1a202c; font-size: 1.1rem;"></p>
                                            <p class="mb-1 text-muted" x-text="formData.sender_title || 'Human Resources'"></p>
                                            <p class="mb-0 text-muted" x-text="formData.company_name || 'MOM Digital'"></p>
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
    </div>
</div>

<!-- Enhanced Styles -->
<style>
@media print {
    /* Hide everything by default */
    body > * {
        display: none !important;
    }

    /* Ensure body and html behave correctly */
    html, body {
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        background: white !important;
    }

    /* Make the preview container and its children visible */
    #offerLetterPreview, 
    #offerLetterPreview * {
        display: block !important; /* or flex/inline-block as needed, but block failsafe */
        visibility: visible !important;
    }
    
    /* Specific overrides for flex/grid children inside the preview */
    #offerLetterPreview .row {
        display: flex !important;
    }
    #offerLetterPreview .d-flex {
        display: flex !important;
    }
    #offerLetterPreview .col-6, 
    #offerLetterPreview .col-8, 
    #offerLetterPreview .col-4 {
        display: block !important; /* Bootstrap cols are block-ish in print usually, or flex-items */
    }

    #offerLetterPreview {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
        transform: none !important; /* Remove scaling */
    }

    /* Hide the parent containers wrapper logic */
    .page-wrapper, .content, .container-fluid, .card, .card-body {
        display: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* IMPORTANT: Re-enable the specific container holding the preview if necessary, 
       but usually moving it to absolute top-left is better. 
       However, since it's deep in DOM, body > * hide might hide its parents.
       We need to ensure parents are not 'display: none' if they wrap it? 
       Actually, standard simple print fix is:
       body { visibility: hidden; }
       #printable { visibility: visible; position: absolute; ... }
       
       But 'display: none' is better to remove layout gaps. 
       Let's stick to the visibility approach but refined, or use the body direct child hide approach.
    */
    
    /* REVISED PRINT STRATEGY */
    body * {
        visibility: hidden;
    }
    
    #offerLetterPreview, #offerLetterPreview * {
        visibility: visible;
    }
    
    #offerLetterPreview {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        background: white;
        margin: 0;
        padding: 20px; /* Add some padding for margins */
    }
    
    /* Ensure letterhead background prints */
    .letterhead {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

/* Enhanced Preview Styling */
.offer-letter-preview {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    line-height: 1.6;
    color: #2d3748;
    font-size: 14px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid #e2e8f0;
    position: relative;
    z-index: 1;
    transform-origin: top center !important;
    pointer-events: auto !important;
    transition: none !important;
}

/* Override any inherited hover effects */
* .offer-letter-preview,
*:hover .offer-letter-preview,
*:not(:hover) .offer-letter-preview {
    transition: none !important;
}

.offer-letter-preview * {
    box-sizing: border-box;
    pointer-events: auto;
}

/* Ensure content is visible */
.document-body {
    display: block !important;
    visibility: visible !important;
}

/* Completely disable any hover-based transformations */
.offer-letter-preview:hover,
.offer-letter-preview:not(:hover),
.preview-container:hover,
.preview-container:not(:hover),
.card:hover .offer-letter-preview,
.card:not(:hover) .offer-letter-preview {
    transform: none !important;
}

/* Force Alpine.js to always control the transform */
.offer-letter-preview[style*="transform"] {
    transform: var(--custom-transform) !important;
}

/* Prevent any parent container hover effects from affecting the preview */
.preview-container,
.card-body,
.card,
.col-lg-6 {
    transition: none !important;
}

.preview-container:hover *,
.card-body:hover *,
.card:hover *,
.col-lg-6:hover * {
    transform: none !important;
}

/* Specific override for the preview element */
#offerLetterPreview {
    transform: inherit !important;
}

#offerLetterPreview[style] {
    transform: var(--custom-transform) !important;
}

.offer-letter-preview h1, 
.offer-letter-preview h2, 
.offer-letter-preview h3, 
.offer-letter-preview h4 {
    font-weight: 600;
    letter-spacing: -0.025em;
}

.offer-letter-preview p {
    margin-bottom: 1rem;
}

.offer-letter-preview .benefits-list {
    list-style: none;
    padding-left: 0;
}

.offer-letter-preview .benefits-list li {
    margin-bottom: 0.75rem;
    display: flex;
    align-items: flex-start;
}

/* Form Enhancements */
.cursor-pointer {
    cursor: pointer;
}

.form-control:focus {
    border-color: #D61F69;
    box-shadow: 0 0 0 0.2rem rgba(214, 31, 105, 0.15);
}

.form-select:focus {
    border-color: #D61F69;
    box-shadow: 0 0 0 0.2rem rgba(214, 31, 105, 0.15);
}

/* Logo Upload Hover Effect */
.logo-upload-area:hover {
    background: rgba(214, 31, 105, 0.05) !important;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* Preview Container Animation - Removed to prevent auto-minimizing on hover */

/* Button Hover Effects - Isolated to prevent interference */
.btn-outline-info:hover:not(.offer-letter-preview *) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
}

/* Section Dividers */
.form-section {
    position: relative;
    margin-bottom: 2rem;
}

.form-section::after {
    content: '';
    position: absolute;
    bottom: -1rem;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
}

/* Enhanced Responsive Adjustments */
.preview-container {
    display: flex !important;
    justify-content: center !important;
    align-items: flex-start !important;
    min-height: 600px !important;
    width: 100% !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 15px !important;
}

.offer-letter-preview {
    flex-shrink: 0 !important;
    margin: 0 auto !important;
    max-width: none !important;
}

/* Maximized Mode Styles */
.z-3 {
    z-index: 1050 !important;
}

/* Form Transition Styles */
[x-cloak] {
    display: none !important;
}

/* Responsive breakpoints - Static scaling to prevent hover conflicts */
@media (min-width: 1600px) {
    .offer-letter-preview:not([style*="transform"]) {
        /* Let Alpine.js handle transform via inline styles */
    }
}

@media (max-width: 1400px) {
    .preview-container {
        min-height: 450px !important;
    }
    
    /* Better button layout on smaller screens */
    .card-header .d-flex {
        flex-wrap: wrap;
        gap: 10px;
    }
}

@media (max-width: 1200px) {
    .preview-container {
        min-height: 500px !important;
    }
    
    /* Better button layout on smaller screens */
    .card-header .d-flex {
        flex-wrap: wrap;
        gap: 10px;
    }
}

@media (max-width: 992px) {
    .preview-container {
        min-height: 700px !important;
    }
    
    /* Stack zoom controls on mobile */
    .card-header .d-flex .d-flex {
        flex-wrap: wrap;
        justify-content: center;
        gap: 5px;
    }
}

@media (max-width: 768px) {
    .preview-container {
        min-height: 600px !important;
        padding: 5px !important;
    }
    
    .card-body {
        padding: 0.5rem !important;
    }
    
    /* Smaller buttons on mobile */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

/* Maximized View Responsive */
@media (max-width: 768px) {
    [data-maximized="true"] .offer-letter-preview {
        transform: scale(0.6) !important;
    }
}

/* Loading Animation */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.btn:disabled {
    animation: pulse 2s infinite;
}

/* Professional Document Styling */
.letterhead {
    position: relative;
    overflow: hidden;
}

.letterhead::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: rotate(45deg);
}

.detail-item {
    padding: 0.25rem 0;
    border-bottom: 1px dotted #e2e8f0;
}

.detail-item:last-child {
    border-bottom: none;
}

.compensation-highlight {
    position: relative;
    overflow: hidden;
}

.compensation-highlight::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}
</style>

<!-- Alpine.js Script -->
<script>
function offerLetterGenerator() {
    return {
        loading: false,
        loading: false,
        saving: false,
        logoPreview: null,
        logoFile: null,
        currentZoom: 0.45,
        isMaximized: false,
        formCollapsed: false,
        selectedCandidate: '',
        formData: {
            company_name: @json($defaultData['company_name']),
            candidate_name: @json($defaultData['candidate_name']),
            job_title: @json($defaultData['job_title']),
            department: @json($defaultData['department']),
            location: @json($defaultData['location']),
            start_date: @json($defaultData['start_date']),
            currency: 'USD',
            salary_amount: @json($defaultData['salary_amount']),
            payment_period: @json($defaultData['payment_period']),
            pay_frequency: @json($defaultData['pay_frequency']),
            work_schedule: @json($defaultData['work_schedule']),
            reporting_to: @json($defaultData['reporting_to']),
            benefits: @json($defaultData['benefits']),
            contingencies: @json($defaultData['contingencies']),
            expiration_date: @json($defaultData['expiration_date']),
            sender_name: @json($defaultData['sender_name']),
            sender_title: @json($defaultData['sender_title'])
        },

        getCurrencySymbol() {
            const symbols = {
                'USD': '$',
                'EUR': '€',
                'GBP': '£',
                // Author: Sanket - Mapped AED symbol with a space for PDF and preview clarity
                'AED': 'AED ',
                'INR': '₹',
                'CAD': 'C$',
                'AUD': 'A$',
                'JPY': '¥',
                'CHF': 'CHF'
            };
            return symbols[this.formData.currency] || '$';
        },

        handleLogoUpload(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file size (2MB limit)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    return;
                }
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file');
                    return;
                }
                
                this.logoFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        getBenefitsList() {
            if (!this.formData.benefits) return [];
            return this.formData.benefits.split('\n')
                .map(benefit => benefit.trim())
                .filter(benefit => benefit);
        },

        getCandidateName() {
            if (!this.selectedCandidate) return 'Please select a candidate';
            const select = document.querySelector('select[x-model="selectedCandidate"]');
            const selectedOption = select?.querySelector(`option[value="${this.selectedCandidate}"]`);
            return selectedOption?.dataset.name || 'Candidate Name';
        },

        getJobTitle() {
            if (!this.selectedCandidate) return 'Job Title';
            const select = document.querySelector('select[x-model="selectedCandidate"]');
            const selectedOption = select?.querySelector(`option[value="${this.selectedCandidate}"]`);
            return selectedOption?.dataset.jobTitle || 'Job Title';
        },

        getDepartment() {
            if (!this.selectedCandidate) return 'Department';
            const select = document.querySelector('select[x-model="selectedCandidate"]');
            const selectedOption = select?.querySelector(`option[value="${this.selectedCandidate}"]`);
            return selectedOption?.dataset.department || 'Department';
        },



        zoomIn() {
            if (this.currentZoom < 1.2) {
                this.currentZoom = Math.min(1.2, this.currentZoom + 0.1);
            }
        },

        zoomOut() {
            if (this.currentZoom > 0.3) {
                this.currentZoom = Math.max(0.3, this.currentZoom - 0.1);
            }
        },

        resetZoom() {
            this.currentZoom = this.isMaximized ? 0.7 : 0.45;
        },

        toggleMaximize() {
            this.isMaximized = !this.isMaximized;
            if (this.isMaximized) {
                this.currentZoom = 0.7;
                document.body.style.overflow = 'hidden';
            } else {
                this.currentZoom = 0.45;
                document.body.style.overflow = 'auto';
            }
        },

        toggleFormCollapse() {
            this.formCollapsed = !this.formCollapsed;
        },

        loadCandidateData() {
            if (!this.selectedCandidate) {
                // Clear form if no candidate selected
                this.formData.candidate_name = '';
                this.formData.job_title = '';
                this.formData.department = '';
                this.formData.location = '';
                this.formData.salary_amount = '';
                return;
            }

            // Get candidate data from the selected option
            const select = document.querySelector('select[x-model="selectedCandidate"]');
            const selectedOption = select.querySelector(`option[value="${this.selectedCandidate}"]`);
            
            if (selectedOption) {
                this.formData.candidate_name = selectedOption.dataset.name;
                this.formData.job_title = selectedOption.dataset.jobTitle;
                this.formData.department = selectedOption.dataset.department;
                this.formData.location = selectedOption.dataset.location;
                this.formData.salary_amount = selectedOption.dataset.salary;
                this.selectedCandidateId = selectedOption.value; // Store the application ID
            }
        },

        quickPrint() {
            window.print();
        },

        async saveOffer() {
            this.saving = true;
            
            try {
                const formData = new FormData();
                
                // Debug: Log what we're sending
                console.log('Form data being sent:', this.formData);
                
                // Add all form fields
                Object.keys(this.formData).forEach(key => {
                    formData.append(key, this.formData[key] || '');
                });
                
                // Add application and job IDs from URL params if available
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('application_id')) {
                    formData.append('application_id', urlParams.get('application_id'));
                }
                if (urlParams.get('job_id')) {
                    formData.append('job_id', urlParams.get('job_id'));
                }
                
                // Add selected candidate ID if available
                if (this.selectedCandidateId) {
                    formData.append('selected_candidate_id', this.selectedCandidateId);
                }
                
                // Add offer ID if we're editing an existing offer
                @if(isset($existingOffer) && $existingOffer)
                formData.append('offer_id', '{{ $existingOffer->id }}');
                @endif
                
                // Add logo file if exists
                if (this.logoFile) {
                    formData.append('logo', this.logoFile);
                }
                
                // Add CSRF token
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch('{{ route("recruitment.offer-letters.store") }}', {
                    method: 'POST',
                    body: formData
                });
                
                // Debug: Check if response is actually JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const htmlResponse = await response.text();
                    console.error('Server returned HTML instead of JSON:', htmlResponse);
                    throw new Error('Server error - check browser console for details');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success notification
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message);
                    } else {
                        alert('Offer saved successfully! You can view it in Offer Management.');
                    }
                    
                    // Redirect after a short delay
                    setTimeout(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    }, 2000);
                } else {
                    // Show error notification
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Unknown error');
                    } else {
                        alert('Error saving offer: ' + (data.message || 'Unknown error'));
                    }
                }
            } catch (error) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error saving offer: ' + error.message);
                } else {
                    alert('Error saving offer: ' + error.message);
                }
            } finally {
                this.saving = false;
            }
        },

        async generatePdf() {
            this.loading = true;
            
            try {
                const formData = new FormData();
                
                // Add all form fields
                Object.keys(this.formData).forEach(key => {
                    formData.append(key, this.formData[key] || '');
                });
                
                // Add logo file if exists
                if (this.logoFile) {
                    formData.append('logo', this.logoFile);
                }
                
                // Add CSRF token
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch('{{ route("recruitment.offer-letters.generate-pdf") }}', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    // Download the PDF
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `offer_letter_${this.formData.candidate_name.replace(/\s+/g, '_')}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    const errorData = await response.json();
                    alert('Error generating PDF: ' + (errorData.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error generating PDF: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        init() {
            console.log('Alpine.js initialized', this.formData);
            
            // Set up watchers for live preview updates
            this.$watch('selectedCandidate', () => {
                this.loadCandidateData();
            });
            
            // Watch form data changes for live preview with debugging
            this.$watch('formData', (newValue, oldValue) => {
                console.log('Form data changed:', newValue);
                // Force preview update - Alpine will reactively update bound elements
                this.$nextTick(() => {
                    console.log('Next tick after form data change');
                });
            }, { deep: true });
            
            // Add specific watchers for key preview fields
            this.$watch('formData.candidate_name', (newValue) => {
                console.log('Candidate name changed to:', newValue);
            });
            
            this.$watch('formData.job_title', (newValue) => {
                console.log('Job title changed to:', newValue);
            });
            
            this.$watch('formData.department', (newValue) => {
                console.log('Department changed to:', newValue);
            });
            
            this.$watch('formData.company_name', (newValue) => {
                console.log('Company name changed to:', newValue);
            });
        },

        updatePreview() {
            console.log('Manual preview update triggered');
            // Force Alpine.js to re-evaluate bindings
            this.$nextTick(() => {
                console.log('Preview updated with:', {
                    company_name: this.formData.company_name,
                    candidate_name: this.formData.candidate_name,
                    job_title: this.formData.job_title,
                    department: this.formData.department
                });
            });
        }
    }
}
</script>

@endsection

@push('scripts')
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Fallback direct event handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fallback direct input handling if Alpine.js fails
    setTimeout(function() {
        const inputs = document.querySelectorAll('input[x-model], textarea[x-model], select[x-model]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                console.log('Direct input event:', input.getAttribute('x-model'), input.value);
                // Force a manual update if Alpine.js isn't working
                updatePreviewDirectly();
            });
        });
    }, 1000);
});

function updatePreviewDirectly() {
    // Get form values directly
    const companyName = document.querySelector('input[x-model="formData.company_name"]')?.value || '';
    const candidateName = document.querySelector('input[x-model="formData.candidate_name"]')?.value || '';
    const jobTitle = document.querySelector('input[x-model="formData.job_title"]')?.value || '';
    const department = document.querySelector('input[x-model="formData.department"]')?.value || '';
    
    // Update preview elements directly
    const companyElements = document.querySelectorAll('[x-text*="formData.company_name"]');
    companyElements.forEach(el => {
        if (companyName) el.textContent = companyName;
    });
    
    const nameElements = document.querySelectorAll('[x-text*="formData.candidate_name"]');
    nameElements.forEach(el => {
        if (candidateName) el.textContent = candidateName;
    });
    
    const jobElements = document.querySelectorAll('[x-text*="formData.job_title"]');
    jobElements.forEach(el => {
        if (jobTitle) el.textContent = jobTitle;
    });
    
    const deptElements = document.querySelectorAll('[x-text*="formData.department"]');
    deptElements.forEach(el => {
        if (department) el.textContent = department;
    });
    
    console.log('Manual preview update:', {companyName, candidateName, jobTitle, department});
}
</script>

<!-- Inter Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@endpush