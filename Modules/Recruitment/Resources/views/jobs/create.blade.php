@extends('layouts.backend')

@section('title')
    Create Job Position
@endsection

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('create_job') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.jobs.index') }}">{{ __trans('jobs') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('create_job') }}</li>
                    </ul>
                </div>
                <div class="col-auto top-actions">
                    <a href="{{ route('recruitment.jobs.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-1"></i>{{ __trans('back_to_jobs') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Page Container -->
        <div class="page-container">
<style>
    .page-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .create-page-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    @media (min-width: 992px) {
        .create-page-grid {
            grid-template-columns: 5fr 7fr; /* Split layout 5/12 and 7/12 */
        }
    }
    
    .card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: none;
        margin-bottom: 24px;
    }
    
    .card-header {
        background: linear-gradient(135deg, #0C1733 0%, #29324e 100%);
        border: none;
        padding: 16px 24px;
        margin-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
        border-radius: 12px 12px 0 0;
    }
    
    .card-title {
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
    }
    
    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #DDE1E7;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 10px 20px;
    }
    
    .skill-tag {
        background: #667eea;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .skill-tag-remove {
        cursor: pointer;
        font-weight: bold;
        opacity: 0.7;
    }
    
    .skill-tag-remove:hover { opacity: 1; }
    
    .ai-btn-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
            <form action="{{ route('recruitment.jobs.store') }}" method="POST" enctype="multipart/form-data" id="jobForm">
                @csrf

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Please correct the errors below.</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <div class="create-page-grid">
                    
                    <!-- LEFT COLUMN: Inputs -->
                    <div class="input-section">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-list-alt me-2"></i>Job Details
                                </h5>
                            </div>
                            <div class="card-body">
                                
                                <div class="mb-4">
                                    <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="role_id" class="form-label">Role</label>
                                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id">
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="hiring_type" class="form-label">Hiring Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('hiring_type') is-invalid @enderror" id="hiring_type" name="hiring_type" required>
                                        <option value="">Select Hiring Type</option>
                                        <option value="internal" {{ old('hiring_type') == 'internal' ? 'selected' : '' }}>Internal</option>
                                        <option value="external" {{ old('hiring_type') == 'external' ? 'selected' : '' }}>External</option>
                                        <option value="internal_external" {{ old('hiring_type') == 'internal_external' ? 'selected' : '' }}>Internal & External</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" value="{{ old('location') }}" placeholder="e.g. New York, Remote">
                                </div>

                                <hr class="my-4">

                                <div class="mb-4">
                                    <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Senior Software Developer" required>
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="job_type" class="form-label">Job Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('job_type') is-invalid @enderror" id="job_type" name="job_type" required>
                                        <option value="">Select Job Type</option>
                                        <option value="full_time" {{ old('job_type') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('job_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="contract" {{ old('job_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                                        <option value="internship" {{ old('job_type') == 'internship' ? 'selected' : '' }}>Internship</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="experience_level" class="form-label">Experience Level</label>
                                    <select class="form-select" id="experience_level" name="experience_level">
                                        <option value="">Select Experience Level</option>
                                        <option value="entry" {{ old('experience_level') == 'entry' ? 'selected' : '' }}>Entry Level</option>
                                        <option value="mid" {{ old('experience_level') == 'mid' ? 'selected' : '' }}>Mid Level</option>
                                        <option value="senior" {{ old('experience_level') == 'senior' ? 'selected' : '' }}>Senior Level</option>
                                        <option value="executive" {{ old('experience_level') == 'executive' ? 'selected' : '' }}>Executive</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="remote_work" class="form-label">Remote Work</label>
                                    <select class="form-select" id="remote_work" name="remote_work">
                                        <option value="0" {{ old('remote_work') == '0' ? 'selected' : 'selected' }}>No</option>
                                        <option value="1" {{ old('remote_work') == '1' ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Min Salary</label>
                                        <input type="number" class="form-control" name="min_salary" value="{{ old('min_salary') }}" min="0">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Max Salary</label>
                                        <input type="number" class="form-control" name="max_salary" value="{{ old('max_salary') }}" min="0">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Positions</label>
                                        <input type="number" class="form-control" name="positions_available" value="{{ old('positions_available', 1) }}" min="1">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Deadline</label>
                                        <input type="date" class="form-control" name="application_deadline" value="{{ old('application_deadline') }}">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1">
                                    <label class="form-check-label" for="is_featured">Featured Job</label>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    <!-- RIGHT COLUMN: Content & AI -->
                    <div class="content-section">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-file-alt me-2"></i>Job Content
                                </h5>
                            </div>
                            <div class="card-body">
                                
                                <div class="alert alert-light border-start border-4 border-info py-2 mb-4" style="font-size: 0.85rem;">
                                    <i class="fas fa-magic text-info me-2"></i> 
                                    Use the <strong>✨ AI</strong> buttons next to each field to auto-generate content based on your settings.
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="description" class="form-label mb-0">Job Description <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-sm btn-outline-info ai-gen-btn" data-field="description">
                                            <i class="fas fa-magic me-1"></i>Gen AI
                                        </button>
                                    </div>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="8" required>{{ old('description') }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="requirements" class="form-label mb-0">Requirements</label>
                                        <button type="button" class="btn btn-sm btn-outline-info ai-gen-btn" data-field="requirements">
                                            <i class="fas fa-magic me-1"></i>Gen AI
                                        </button>
                                    </div>
                                    <textarea class="form-control" id="requirements" name="requirements" rows="6">{{ old('requirements') }}</textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="responsibilities" class="form-label mb-0">Responsibilities</label>
                                        <button type="button" class="btn btn-sm btn-outline-info ai-gen-btn" data-field="responsibilities">
                                            <i class="fas fa-magic me-1"></i>Gen AI
                                        </button>
                                    </div>
                                    <textarea class="form-control" id="responsibilities" name="responsibilities" rows="6">{{ old('responsibilities') }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="skills-input" class="form-label mb-0">Skills Required</label>
                                        <button type="button" class="btn btn-sm btn-outline-info ai-gen-btn" data-field="skills">
                                            <i class="fas fa-magic me-1"></i>Gen AI
                                        </button>
                                    </div>
                                    <div class="input-group mb-2">
                                        <input type="text" id="skills-input" class="form-control" placeholder="Type skill and press Enter">
                                        <button class="btn btn-outline-secondary" type="button" onclick="addSkill()">Add</button>
                                    </div>
                                    <input type="hidden" name="skills" id="skills-hidden" value="{{ old('skills') }}">
                                    <div id="skills-container" class="p-2 border rounded bg-light min-h-50"></div>
                                </div>

                                <div class="mt-5 text-end">
                                    <a href="{{ route('recruitment.jobs.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Create Job</button>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // ---------------------------------------------------------
    // Skills Tag System
    // ---------------------------------------------------------
    const skillsInput = document.getElementById('skills-input');
    const skillsContainer = document.getElementById('skills-container');
    const skillsHidden = document.getElementById('skills-hidden');
    let skills = [];

    // Init from hidden input
    if (skillsHidden.value) {
        try {
            const data = skillsHidden.value;
            skills = data.startsWith('[') ? JSON.parse(data) : data.split(',').filter(s => s.trim());
        } catch(e) { skills = []; }
        renderSkills();
    }

    // Add Keypress Event
    skillsInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); addSkill(); }
    });
    
    // Add Blur Event
    skillsInput.addEventListener('blur', function() {
        if (this.value.trim()) addSkill();
    });

    window.addSkill = function() {
        const val = skillsInput.value.trim();
        if (val && !skills.includes(val)) {
            skills.push(val);
            skillsInput.value = '';
            renderSkills();
            updateHiddenInput();
        }
    }

    window.removeSkill = function(val) {
        skills = skills.filter(s => s !== val);
        renderSkills();
        updateHiddenInput();
    }

    function renderSkills() {
        skillsContainer.innerHTML = skills.map(s => `
            <span class="skill-tag">${s} <span class="skill-tag-remove" onclick="removeSkill('${s}')">&times;</span></span>
        `).join('');
    }

    function updateHiddenInput() {
        skillsHidden.value = JSON.stringify(skills);
    }
    
    // ---------------------------------------------------------
    // MOM AI: Granular Generator (Per Field)
    // ---------------------------------------------------------
    $('.ai-gen-btn').click(function() {
        const btn = $(this);
        const field = btn.data('field'); // 'description', 'requirements', etc.
        const title = $('#title').val();

        // Basic validation
        if (!title || title.length < 3) {
            alert('⚠️ Please enter a Job Title on the left first so the AI knows what to write.');
            $('#title').focus();
            return;
        }

        const originalHtml = btn.html();
        
        // 1. Gather Context
        const context = {
            _token: "{{ csrf_token() }}",
            title: title,
            field: field, // Target specific field
            department_id: $('#department_id').val(),
            job_type: $('#job_type').val(),
            experience_level: $('#experience_level').val(),
            location: $('#location').val(),
            remote_work: $('#remote_work').val(),
            skills: $('#skills-hidden').val()
        };

        // 2. Loading State (PRODUCTION READY UX)
        btn.prop('disabled', true)
           .removeClass('btn-outline-info')
           .addClass('btn-secondary')
           .html('<i class="fas fa-spinner fa-spin me-1"></i> Thinking...');

        // 3. API Call
        $.ajax({
            url: "{{ route('recruitment.jobs.generate-ai') }}",
            type: "POST",
            data: context,
            success: function(response) {
                if (response.success && response.data) {
                    const content = response.data;

                    if (field === 'skills') {
                        // Special handling for skills array
                        if (Array.isArray(content)) {
                            // Add new skills
                            let addedCount = 0;
                            content.forEach(s => {
                                if (!skills.includes(s)) {
                                    skills.push(s);
                                    addedCount++;
                                }
                            });
                            if (addedCount > 0) {
                                renderSkills();
                                updateHiddenInput();
                            }
                        }
                    } else {
                        // Textarea handling
                        const el = $('#' + field);
                        
                        // Basic formatting cleanup
                        let text = content;
                        if (Array.isArray(text)) text = text.join('\n');
                        
                        // Populate and animate
                        el.val(text).css('opacity', 0).animate({opacity: 1}, 500);
                        
                        // Auto-resize
                        el.css('height', 'auto');
                        el.css('height', el[0].scrollHeight + 'px');
                    }

                    // Success Feedback
                    btn.removeClass('btn-secondary').addClass('btn-success text-white').html('<i class="fas fa-check me-1"></i> Done');
                    
                    // Reset button after delay
                    setTimeout(() => {
                        btn.removeClass('btn-success text-white').addClass('btn-outline-info').html(originalHtml).prop('disabled', false);
                    }, 2500);
                }
            },
            error: function(xhr) {
                let msg = 'Failed to generate content.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                
                // Error Feedback
                alert('❌ ' + msg);
                btn.removeClass('btn-secondary').addClass('btn-outline-danger').html('<i class="fas fa-exclamation-triangle"></i> Retry');
                
                setTimeout(() => {
                    btn.removeClass('btn-outline-danger').addClass('btn-outline-info').html(originalHtml).prop('disabled', false);
                }, 3000);
            }
        });
    });
});
</script>
@endpush