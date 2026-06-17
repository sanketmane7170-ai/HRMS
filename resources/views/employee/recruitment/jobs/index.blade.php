@extends('layouts.backend')

@section('title', __trans('available_positions'))

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Available Job Positions</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Available Positions</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Search and Filter -->
        <div class="row">
            <div class="col-md-12">
                <div class="card bg-white border shadow-none rounded-0 mb-4" style="border-color: #e2e8f0 !important;">
                    <div class="card-body">
                        <form method="GET" action="{{ route('backend.employee.jobs.index') }}" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-dark fw-bold">Search Jobs</label>
                                <input type="text" name="search" class="form-control rounded-0 border text-dark" style="border-color: #e2e8f0 !important;" placeholder="Keywords, title..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-dark fw-bold">Department</label>
                                <select name="department_id" class="form-select rounded-0 border text-dark" style="border-color: #e2e8f0 !important;">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary rounded-0 w-50 me-2 shadow-none">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                                <a href="{{ route('backend.employee.jobs.index') }}" class="btn btn-light rounded-0 w-50 border shadow-none" style="border-color: #e2e8f0 !important;">
                                    <i class="fas fa-sync-alt me-1"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Listings -->
        <div class="row">
            @forelse($jobs as $job)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card h-100 bg-white border shadow-none rounded-0" style="border-color: #e2e8f0 !important;">
                    <div class="card-header bg-white border-bottom rounded-0" style="border-color: #e2e8f0 !important;">
                        <h5 class="card-title text-dark mb-0 fw-bold">{{ $job->title }}</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3 py-2 px-3 bg-light border rounded-0" style="border-color: #f1f5f9 !important;">
                            <p class="text-dark mb-1 small">
                                <i class="fas fa-building me-2 text-primary"></i><strong>Dept:</strong> {{ $job->department->name ?? 'N/A' }}
                            </p>
                            <p class="text-dark mb-1 small">
                                <i class="fas fa-briefcase me-2 text-primary"></i><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $job->hiring_type)) }}
                            </p>
                            <p class="text-dark mb-0 small">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i><strong>Posted:</strong> {{ $job->created_at->diffForHumans() }}
                            </p>
                        </div>
                        
                        <p class="card-text flex-grow-1 text-muted small mt-2">
                            {{ Str::limit(strip_tags($job->description), 120) }}
                        </p>
                        
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $job->status == 'active' ? 'success' : 'warning' }} text-white rounded-0 px-3 py-2">
                                    {{ ucfirst($job->status) }}
                                </span>
                                <div>
                                    <a href="{{ route('backend.employee.jobs.show', $job->id) }}" class="btn btn-sm btn-outline-primary rounded-0 px-3">
                                        View Details <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card bg-white border shadow-none rounded-0" style="border-color: #e2e8f0 !important;">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                        <h4 class="text-dark">No Job Positions Available</h4>
                        <p class="text-muted">There are currently no open positions. Please check back later.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($jobs->hasPages())
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                {{ $jobs->withQueryString()->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
