@extends('layouts.backend')

@section('title', 'My Applications')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-dark fw-bold">My Applications</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active text-muted">My Applications</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.employee.jobs.index') }}" class="btn btn-primary rounded-0 shadow-none">
                        <i class="fas fa-search me-1"></i> Browse Jobs
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-12">
                @if($applications->count() > 0)
                    <div class="row">
                        @foreach($applications as $application)
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 bg-white border shadow-none rounded-0" style="border-color: #e2e8f0 !important;">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title mb-0 text-dark fw-bold">{{ $application->job->title }}</h5>
                                            <span class="badge bg-{{ 
                                                $application->current_stage == 'applied' ? 'warning' : 
                                                ($application->current_stage == 'shortlisted' ? 'info' : 
                                                ($application->current_stage == 'interview' ? 'primary' : 
                                                ($application->current_stage == 'hired' ? 'success' : 'danger'))) 
                                            }} text-white rounded-0 px-2 py-1">
                                                {{ ucfirst(str_replace('_', ' ', $application->current_stage)) }}
                                            </span>
                                        </div>
                                        
                                        <div class="mb-3 py-2 px-3 bg-light border rounded-0" style="border-color: #f1f5f9 !important;">
                                            <p class="text-dark mb-1 small">
                                                <i class="fas fa-building me-2 text-primary"></i><strong>Dept:</strong> {{ $application->job->department->name ?? 'N/A' }}
                                            </p>
                                            <p class="text-dark mb-1 small">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i><strong>Applied:</strong> {{ $application->applied_on ? \Carbon\Carbon::parse($application->applied_on)->format('M d, Y') : $application->created_at->format('M d, Y') }}
                                            </p>
                                            @if($application->notes)
                                            <p class="text-dark mb-0 small">
                                                <i class="fas fa-sticky-note me-2 text-primary"></i><strong>Note:</strong> {{ Str::limit($application->notes, 60) }}
                                            </p>
                                            @endif
                                        </div>

                                        <div class="mt-auto d-flex justify-content-between pt-3">
                                            <a href="{{ route('backend.employee.applications.show', $application->id) }}" class="btn btn-sm btn-primary rounded-0 w-50 me-2 shadow-none">
                                                <i class="fas fa-eye me-1"></i> Details
                                            </a>
                                            <a href="{{ route('backend.employee.jobs.show', $application->job->id) }}" class="btn btn-sm btn-outline-primary rounded-0 w-50 border shadow-none">
                                                <i class="fas fa-briefcase me-1"></i> Job Info
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($applications->hasPages())
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-center mt-3">
                                {{ $applications->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                @else
                    <div class="card bg-white border shadow-none rounded-0" style="border-color: #e2e8f0 !important;">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-clipboard-list fa-4x text-muted"></i>
                            </div>
                            <h4 class="text-dark fw-bold mb-3">No Applications Yet</h4>
                            <p class="text-muted mb-4">You haven't applied for any positions yet. Browse available jobs to get started.</p>
                            <a href="{{ route('backend.employee.jobs.index') }}" class="btn btn-primary btn-lg rounded-0 shadow-none px-4">
                                <i class="fas fa-search me-2"></i> Browse Available Positions
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.2s ease;
}
.card:hover {
    border-color: #cbd5e1 !important;
}
.badge {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
@endsection
