@extends('layouts.backend')

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-dark fw-bold">{{ $job->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.jobs.index') }}">{{ __trans('jobs') }}</a></li>
                        <li class="breadcrumb-item active text-muted">{{ __trans('job_details') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('recruitment.jobs.edit')
                    <a href="{{ route('recruitment.jobs.edit', $job->id) }}" class="btn btn-primary rounded-0 px-4 shadow-none">
                        <i class="fas fa-edit me-1"></i> {{ __trans('edit') }}
                    </a>
                    @endcan
                    <a href="{{ route('recruitment.jobs.index') }}" class="btn btn-light border rounded-0 px-4 shadow-none ms-1" style="border-color: #e2e8f0 !important;">
                        <i class="fas fa-arrow-left me-1"></i> {{ __trans('back') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <!-- Job Details Card -->
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0 text-dark fw-bold">{{ __trans('job_information') }}</h4>
                            <div class="card-options">
                                @if($job->status === 'active')
                                    <span class="badge bg-success text-white rounded-0 px-3 py-2" style="font-size: 0.7rem;">{{ __trans('active') }}</span>
                                @else
                                    <span class="badge bg-secondary text-white rounded-0 px-3 py-2" style="font-size: 0.7rem;">{{ ucfirst($job->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small fw-bold d-block mb-1">{{ __trans('department') }}</label>
                                <p class="text-dark fw-bold mb-0"><i class="fas fa-building text-primary me-2"></i>{{ $job->department->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small fw-bold d-block mb-1">{{ __trans('location') }}</label>
                                <p class="text-dark fw-bold mb-0"><i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $job->location ?: 'Not specified' }}</p>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small fw-bold d-block mb-1">{{ __trans('job_type') }}</label>
                                <span class="badge bg-soft-info text-info rounded-0 border fw-bold px-2 py-1" style="border-color: #bee3f8 !important;">
                                    <i class="fas fa-briefcase me-1"></i>{{ ucfirst(str_replace('_', ' ', $job->type)) }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small fw-bold d-block mb-1">{{ __trans('experience_level') }}</label>
                                <p class="text-dark fw-bold mb-0"><i class="fas fa-signal text-primary me-2"></i>{{ $job->experience_level ? ucfirst($job->experience_level) : 'Not specified' }}</p>
                            </div>
                        </div>

                        @if($job->min_salary || $job->max_salary)
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="text-muted small fw-bold d-block mb-1">{{ __trans('salary_range') }}</label>
                                <p class="text-dark fw-bold mb-0" style="font-size: 1.1rem;">
                                    <i class="fas fa-money-bill-wave text-success me-2"></i>${{ number_format($job->min_salary ?? 0) }} - ${{ number_format($job->max_salary ?? 0) }}
                                </p>
                            </div>
                        </div>
                        @endif

                        <div class="mb-4">
                            <h5 class="text-dark fw-bold mb-2">{{ __trans('job_description') }}</h5>
                            <div class="p-3 bg-light border rounded-0 text-dark" style="border-color: #f1f5f9 !important;">
                                {!! $job->description !!}
                            </div>
                        </div>

                        @if($job->requirements)
                        <div class="mb-4">
                            <h5 class="text-dark fw-bold mb-2">{{ __trans('requirements') }}</h5>
                            <div class="p-3 bg-light border rounded-0 text-dark" style="border-color: #f1f5f9 !important;">
                                @if(is_array($job->requirements))
                                    <ul class="mb-0">
                                        @foreach($job->requirements as $requirement)
                                            <li>{{ $requirement }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {!! $job->requirements !!}
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($job->responsibilities)
                        <div class="mb-4">
                            <h5 class="text-dark fw-bold mb-2">{{ __trans('responsibilities') }}</h5>
                            <div class="p-3 bg-light border rounded-0 text-dark" style="border-color: #f1f5f9 !important;">
                                {!! $job->responsibilities !!}
                            </div>
                        </div>
                        @endif

                        @if($job->benefits)
                        <div class="mb-0">
                            <h5 class="text-dark fw-bold mb-2">{{ __trans('benefits') }}</h5>
                            <div class="p-3 bg-light border rounded-0 text-dark" style="border-color: #f1f5f9 !important;">
                                {!! $job->benefits !!}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Job Statistics -->
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <h4 class="card-title mb-0 text-dark fw-bold">{{ __trans('job_statistics') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 border-end" style="border-color: #f1f5f9 !important;">
                                <h3 class="text-primary fw-bold mb-1">{{ $job->applications_count }}</h3>
                                <p class="text-muted small mb-0">{{ __trans('applications') }}</p>
                            </div>
                            <div class="col-6">
                                <h3 class="text-info fw-bold mb-1">{{ $job->positions_available }}</h3>
                                <p class="text-muted small mb-0">{{ __trans('positions') }}</p>
                            </div>
                        </div>
                        
                        @if($job->application_deadline)
                        <div class="mt-4 pt-4 border-top text-center" style="border-color: #f1f5f9 !important;">
                            <label class="text-muted small fw-bold d-block mb-1">{{ __trans('deadline') }}</label>
                            <span class="text-{{ \Carbon\Carbon::parse($job->application_deadline)->isPast() ? 'danger' : 'success' }} fw-bold h5">
                                <i class="fas fa-calendar-times me-1"></i>{{ \Carbon\Carbon::parse($job->application_deadline)->format('M d, Y') }}
                            </span>
                            @if(!\Carbon\Carbon::parse($job->application_deadline)->isPast())
                                <small class="d-block text-muted mt-1">
                                    {{ \Carbon\Carbon::parse($job->application_deadline)->diffForHumans() }}
                                </small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Applications -->
                @if($job->applications->count() > 0)
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0 text-dark fw-bold">{{ __trans('recent_applications') }}</h4>
                            <a href="{{ route('recruitment.applications.index', ['job_id' => $job->id]) }}" class="btn btn-sm btn-outline-primary rounded-0 px-3 shadow-none">
                                {{ __trans('view_all') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($job->applications->take(5) as $application)
                            <li class="list-group-item border-0 border-bottom bg-transparent py-3" style="border-color: #f1f5f9 !important;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm rounded-0 bg-soft-primary me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #ebf4ff;">
                                        <span class="text-primary fw-bold">{{ strtoupper(substr($application->candidate_name, 0, 1)) }}</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-dark fw-bold mb-1">{{ $application->candidate_name }}</h6>
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ $application->applied_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge bg-{{ 
                                        $application->stage === 'applied' ? 'info' : 
                                        ($application->stage === 'hired' ? 'success' : 
                                        ($application->stage === 'rejected' || $application->stage === 'offer_declined' ? 'danger' : 'warning')) 
                                    }} text-white rounded-0 px-2 py-1" style="font-size: 0.65rem;">
                                        {{ ucfirst($application->stage) }}
                                    </span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.bg-soft-info { background-color: #e3f2fd; }
.text-info { color: #0288d1 !important; }
.bg-soft-primary { background-color: #ebf4ff; }
.card-header { padding: 1rem 1.25rem; }
.card-body { padding: 1.25rem; }
</style>

@endsection