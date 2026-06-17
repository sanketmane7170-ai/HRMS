@extends('layouts.backend')

@section('title')
    Edit Job Position
@endsection

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-dark fw-bold">Edit Job Position</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.jobs.index') }}">Jobs</a></li>
                        <li class="breadcrumb-item active text-muted">Edit Job</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('recruitment.jobs.index') }}" class="btn btn-light border rounded-0 px-4 shadow-none" style="border-color: #e2e8f0 !important;">
                        <i class="fas fa-arrow-left me-2"></i>Back to Jobs
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Clean Single Form Layout -->
        <div class="container-fluid" style="max-width: 1000px;">
            <form action="{{ route('recruitment.jobs.update', $job->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-body p-4">
                        <!-- Basic Information -->
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-4 d-flex align-items-center">
                                <span class="bg-primary text-white rounded-0 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.9rem;">1</span>
                                BASIC INFORMATION
                            </h5>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="title" class="text-muted small fw-bold d-block mb-2">JOB TITLE <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-0 border-light-gray fw-bold text-dark @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $job->title) }}" 
                                           placeholder="e.g. Senior Software Developer" required style="border-color: #e2e8f0;">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="department_id" class="text-muted small fw-bold d-block mb-2">DEPARTMENT <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-0 border-light-gray fw-bold text-dark @error('department_id') is-invalid @enderror" 
                                            id="department_id" name="department_id" required style="border-color: #e2e8f0;">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" 
                                                {{ old('department_id', $job->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="text-muted small fw-bold d-block mb-2">LOCATION</label>
                                    <input type="text" class="form-control rounded-0 border-light-gray fw-bold text-dark @error('location') is-invalid @enderror" 
                                           id="location" name="location" value="{{ old('location', $job->location) }}" 
                                           placeholder="e.g. New York, Remote" style="border-color: #e2e8f0;">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-top mb-5" style="border-color: #f1f5f9 !important;"></div>

                        <!-- Job Details -->
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-4 d-flex align-items-center">
                                <span class="bg-primary text-white rounded-0 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.9rem;">2</span>
                                JOB DETAILS
                            </h5>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="description" class="text-muted small fw-bold d-block mb-2">DESCRIPTION <span class="text-danger">*</span></label>
                                    <textarea class="form-control rounded-0 border-light-gray text-dark @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="5" required style="border-color: #e2e8f0;">{{ old('description', $job->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="requirements" class="text-muted small fw-bold d-block mb-2">REQUIREMENTS</label>
                                    <textarea class="form-control rounded-0 border-light-gray text-dark @error('requirements') is-invalid @enderror"
                                              id="requirements" name="requirements" rows="4" style="border-color: #e2e8f0;">{{ old('requirements', is_array($job->requirements) ? implode("\n", $job->requirements) : $job->requirements) }}</textarea>
                                    @error('requirements')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="responsibilities" class="text-muted small fw-bold d-block mb-2">RESPONSIBILITIES</label>
                                    <textarea class="form-control rounded-0 border-light-gray text-dark @error('responsibilities') is-invalid @enderror" 
                                              id="responsibilities" name="responsibilities" rows="4" 
                                              placeholder="Key responsibilities and duties for this role..." style="border-color: #e2e8f0;">{{ old('responsibilities', $job->responsibilities) }}</textarea>
                                    @error('responsibilities')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="skills-input" class="text-muted small fw-bold d-block mb-2">SKILLS REQUIRED</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-light rounded-0 border-end-0" style="border-color: #e2e8f0;"><i class="fas fa-tags text-muted"></i></span>
                                        <input type="text" id="skills-input" class="form-control rounded-0 border-start-0" placeholder="Type skill and press Enter" style="border-color: #e2e8f0;">
                                    </div>
                                    <input type="hidden" name="skills" id="skills-hidden" value="{{ old('skills', is_array($job->skills) ? json_encode($job->skills) : $job->skills) }}">
                                    <div class="skills-tags-container bg-light p-3 border rounded-0 d-flex flex-wrap gap-2 min-height-50" id="skills-container" style="border-color: #f1f5f9 !important;">
                                        <!-- Tags will be rendered here -->
                                    </div>
                                    @error('skills')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="benefits" class="text-muted small fw-bold d-block mb-2">BENEFITS</label>
                                    <textarea class="form-control rounded-0 border-light-gray text-dark @error('benefits') is-invalid @enderror" 
                                              id="benefits" name="benefits" rows="3" style="border-color: #e2e8f0;">{{ old('benefits', $job->benefits) }}</textarea>
                                    @error('benefits')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-top mb-5" style="border-color: #f1f5f9 !important;"></div>

                        <!-- Employment & Compensation -->
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-4 d-flex align-items-center">
                                <span class="bg-primary text-white rounded-0 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.9rem;">3</span>
                                EMPLOYMENT & COMPENSATION
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="job_type" class="text-muted small fw-bold d-block mb-2">JOB TYPE <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-0 border-light-gray fw-bold text-dark @error('job_type') is-invalid @enderror" id="job_type" name="job_type" required style="border-color: #e2e8f0;">
                                        <option value="full_time" {{ old('job_type', $job->job_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('job_type', $job->job_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="contract" {{ old('job_type', $job->job_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                        <option value="internship" {{ old('job_type', $job->job_type) == 'internship' ? 'selected' : '' }}>Internship</option>
                                    </select>
                                    @error('job_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="experience_level" class="text-muted small fw-bold d-block mb-2">EXPERIENCE LEVEL</label>
                                    <select class="form-select rounded-0 border-light-gray fw-bold text-dark @error('experience_level') is-invalid @enderror" id="experience_level" name="experience_level" style="border-color: #e2e8f0;">
                                        <option value="entry" {{ old('experience_level', $job->experience_level) == 'entry' ? 'selected' : '' }}>Entry Level</option>
                                        <option value="mid" {{ old('experience_level', $job->experience_level) == 'mid' ? 'selected' : '' }}>Mid Level</option>
                                        <option value="senior" {{ old('experience_level', $job->experience_level) == 'senior' ? 'selected' : '' }}>Senior Level</option>
                                        <option value="executive" {{ old('experience_level', $job->experience_level) == 'executive' ? 'selected' : '' }}>Executive</option>
                                    </select>
                                    @error('experience_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="min_salary" class="text-muted small fw-bold d-block mb-2">MIN SALARY</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light rounded-0 border-end-0" style="border-color: #e2e8f0;">$</span>
                                        <input type="number" class="form-control rounded-0 border-start-0 fw-bold text-dark @error('min_salary') is-invalid @enderror" 
                                               id="min_salary" name="min_salary" value="{{ old('min_salary', $job->min_salary) }}" 
                                               placeholder="50000" style="border-color: #e2e8f0;">
                                    </div>
                                    @error('min_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="max_salary" class="text-muted small fw-bold d-block mb-2">MAX SALARY</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light rounded-0 border-end-0" style="border-color: #e2e8f0;">$</span>
                                        <input type="number" class="form-control rounded-0 border-start-0 fw-bold text-dark @error('max_salary') is-invalid @enderror" 
                                               id="max_salary" name="max_salary" value="{{ old('max_salary', $job->max_salary) }}" 
                                               placeholder="80000" style="border-color: #e2e8f0;">
                                    </div>
                                    @error('max_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 pt-2">
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input rounded-0" id="remote_work" name="remote_work" value="1" 
                                               {{ old('remote_work', $job->remote_work) ? 'checked' : '' }}>
                                        <label class="form-check-label text-dark fw-bold ms-2" for="remote_work">
                                            Remote Work Available
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top mb-5" style="border-color: #f1f5f9 !important;"></div>

                        <!-- Hiring Information -->
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-4 d-flex align-items-center">
                                <span class="bg-primary text-white rounded-0 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.9rem;">4</span>
                                HIRING INFORMATION
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label for="positions_available" class="text-muted small fw-bold d-block mb-2">POSITIONS AVAILABLE</label>
                                    <input type="number" class="form-control rounded-0 border-light-gray fw-bold text-dark @error('positions_available') is-invalid @enderror" 
                                           id="positions_available" name="positions_available" min="1" 
                                           value="{{ old('positions_available', $job->positions_available ?? 1) }}" placeholder="1" style="border-color: #e2e8f0;">
                                    @error('positions_available')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="application_deadline" class="text-muted small fw-bold d-block mb-2">APPLICATION DEADLINE</label>
                                    <input type="date" class="form-control rounded-0 border-light-gray fw-bold text-dark @error('application_deadline') is-invalid @enderror" 
                                           id="application_deadline" name="application_deadline" 
                                           value="{{ old('application_deadline', $job->application_deadline ? $job->application_deadline->format('Y-m-d') : '') }}" style="border-color: #e2e8f0;">
                                    @error('application_deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="status" class="text-muted small fw-bold d-block mb-2">JOB STATUS</label>
                                    <select class="form-select rounded-0 border-light-gray fw-bold text-dark @error('status') is-invalid @enderror" id="status" name="status" style="border-color: #e2e8f0;">
                                        <option value="draft" {{ old('status', $job->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="active" {{ old('status', $job->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="paused" {{ old('status', $job->status) == 'paused' ? 'selected' : '' }}>Paused</option>
                                        <option value="closed" {{ old('status', $job->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 pt-2">
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input rounded-0" id="is_featured" name="is_featured" value="1" 
                                               {{ old('is_featured', $job->is_featured) ? 'checked' : '' }}>
                                        <label class="form-check-label text-dark fw-bold ms-2" for="is_featured">
                                            Featured Position
                                        </label>
                                        <small class="form-text text-muted d-block mt-1">Featured jobs appear at the top of job listings</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-3 pt-4 border-top" style="border-color: #f1f5f9 !important;">
                            <button type="button" class="btn btn-light border rounded-0 px-5 shadow-none py-2 fw-bold text-secondary" style="border-color: #e2e8f0 !important;" onclick="history.back()">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary rounded-0 px-5 shadow-none py-2 fw-bold">
                                <i class="fas fa-save me-2"></i>Update Job Position
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .border-light-gray { border-color: #e2e8f0 !important; }
    .min-height-50 { min-height: 50px; }
    
    .skill-tag {
        display: inline-flex;
        align-items: center;
        background: #ebf4ff;
        color: #007bff;
        padding: 5px 12px;
        border: 1px solid #bee3f8;
        border-radius: 0;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .skill-tag:hover {
        background: #dbeafe;
    }
    
    .skill-tag-remove {
        margin-left: 8px;
        cursor: pointer;
        font-weight: bold;
        color: #e53e3e;
        transition: transform 0.2s;
    }
    
    .skill-tag-remove:hover {
        transform: scale(1.2);
    }

    .form-control:focus, .form-select:focus {
        border-color: #007bff !important;
        box-shadow: none !important;
    }

    .custom-checkbox .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .custom-checkbox .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.1rem;
    }
</style>
@endpush

@section('scripts')
<script>
$(document).ready(function() {
    // Skills Tag System
    const skillsInput = document.getElementById('skills-input');
    const skillsContainer = document.getElementById('skills-container');
    const skillsHidden = document.getElementById('skills-hidden');
    let skills = [];

    // Load existing skills if editing
    if (skillsHidden.value) {
        try {
            const skillsData = skillsHidden.value;
            if (skillsData.startsWith('[')) {
                // JSON format
                skills = JSON.parse(skillsData);
            } else {
                // Comma-separated format
                skills = skillsData.split(',').filter(skill => skill.trim() !== '');
            }
        } catch (e) {
            skills = [];
        }
        renderSkills();
    }

    skillsInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSkill();
        }
    });

    skillsInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            addSkill();
        }
    });

    function addSkill() {
        const skill = skillsInput.value.trim();
        if (skill && !skills.includes(skill)) {
            skills.push(skill);
            skillsInput.value = '';
            renderSkills();
            updateHiddenInput();
        }
    }

    function removeSkill(skillToRemove) {
        skills = skills.filter(skill => skill !== skillToRemove);
        renderSkills();
        updateHiddenInput();
    }

    function renderSkills() {
        if (skills.length === 0) {
            skillsContainer.innerHTML = '<span class="text-muted small font-italic">No skills added yet...</span>';
            return;
        }
        skillsContainer.innerHTML = skills.map(skill => `
            <span class="skill-tag">
                ${skill}
                <span class="skill-tag-remove" onclick="removeSkillByText('${skill}')">&times;</span>
            </span>
        `).join('');
    }

    function updateHiddenInput() {
        skillsHidden.value = JSON.stringify(skills);
    }

    // Make removeSkillByText globally accessible
    window.removeSkillByText = function(skill) {
        removeSkill(skill);
    };
    
    // Auto-resize textareas
    $('textarea').each(function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight + 2) + 'px';
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight + 2) + 'px';
    });
    
    // Salary validation
    $('#min_salary, #max_salary').on('input', function() {
        const minSalary = parseFloat($('#min_salary').val()) || 0;
        const maxSalary = parseFloat($('#max_salary').val()) || 0;
        
        if (minSalary > 0 && maxSalary > 0 && minSalary >= maxSalary) {
            $('#max_salary')[0].setCustomValidity('Maximum salary must be greater than minimum salary');
        } else {
            $('#max_salary')[0].setCustomValidity('');
        }
    });
    
    // Form submission with loading state
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
        submitBtn.prop('disabled', true);
        
        // Re-enable after 5 seconds to prevent permanent lock
        setTimeout(() => {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }, 5000);
    });
});
</script>
@endsection