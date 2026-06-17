@extends('layouts.backend')

@section('title', $job->title)

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $job->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.employee.jobs.index')}}">Available Positions</a></li>
                        <li class="breadcrumb-item active">{{ $job->title }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card bg-white border shadow-none mb-4 rounded-0" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <h4 class="card-title text-dark mb-0">{{ $job->title }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-5 py-3 bg-light rounded-0 mx-0 border" style="border-color: #e2e8f0 !important;">
                            <div class="col-sm-6 border-end" style="border-color: #e2e8f0 !important;">
                                <p class="mb-2 text-dark"><i class="fas fa-building me-2 text-primary"></i><strong>Department:</strong> {{ $job->department->name ?? 'N/A' }}</p>
                                <p class="mb-0 text-dark"><i class="fas fa-briefcase me-2 text-primary"></i><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $job->hiring_type)) }}</p>
                            </div>
                            <div class="col-sm-6 ps-4">
                                <p class="mb-2 text-dark"><strong><i class="fas fa-info-circle me-2 text-primary"></i>Status:</strong> 
                                    <span class="badge bg-{{ $job->status == 'active' ? 'success' : 'warning' }} text-white rounded-0">
                                        {{ ucfirst($job->status) }}
                                    </span>
                                </p>
                                <p class="mb-0 text-dark"><strong><i class="fas fa-calendar-alt me-2 text-primary"></i>Posted:</strong> {{ $job->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2 mb-3" style="border-color: #e2e8f0 !important;">Job Description</h5>
                            <div class="job-description text-dark">
                                {!! $job->description !!}
                            </div>
                        </div>

                        @if($job->requirements)
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2 mb-3" style="border-color: #e2e8f0 !important;">Requirements</h5>
                            <div class="job-requirements text-dark">
                                @if(is_array($job->requirements))
                                    <ul class="list-unstyled">
                                        @foreach($job->requirements as $requirement)
                                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>{{ $requirement }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {!! $job->requirements !!}
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card bg-white border shadow-none rounded-0" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <h5 class="card-title text-dark mb-0">Apply for this Position</h5>
                    </div>
                    <div class="card-body">
                        @if($hasApplied)
                            <div class="alert alert-soft-success mb-4 rounded-0 border" style="background-color: #f8fafc; color: #2e7d32; border-color: #e2e8f0 !important;">
                                <i class="fas fa-check-circle me-2"></i> You have already applied for this position.
                            </div>
                            <a href="{{ route('backend.employee.applications.index') }}" class="btn btn-outline-primary btn-lg w-100 rounded-0">
                                <i class="fas fa-list me-2"></i> View My Applications
                            </a>
                        @else
                            <form action="{{ route('backend.employee.jobs.apply', $job->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label for="cover_letter" class="form-label text-dark fw-bold">Cover Letter <span class="text-danger">*</span></label>
                                    <textarea name="cover_letter" id="cover_letter" class="form-control text-dark rounded-0 border @error('cover_letter') is-invalid @enderror" style="border-color: #e2e8f0 !important;" rows="6" required placeholder="Tell us why you're a great fit for this role...">{{ old('cover_letter') }}</textarea>
                                    @error('cover_letter')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="resume" class="form-label text-dark fw-bold">Resume (Optional)</label>
                                    <input type="file" name="resume" id="resume" class="form-control text-dark rounded-0 border @error('resume') is-invalid @enderror" style="border-color: #e2e8f0 !important;" accept=".pdf,.doc,.docx">
                                    <small class="form-text text-muted d-block mt-2">PDF, DOC, DOCX up to 5MB</small>
                                    @error('resume')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-0">
                                    <i class="fas fa-paper-plane me-2"></i> Submit Application
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.job-description, .job-requirements {
    line-height: 1.6;
}
.job-description p, .job-requirements p {
    margin-bottom: 1rem;
}
.job-description ul, .job-requirements ul {
    margin-left: 1.5rem;
}
</style>
@endsection
