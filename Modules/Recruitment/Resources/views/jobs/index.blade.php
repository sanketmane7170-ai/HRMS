@extends('layouts.backend')
@section('content')

<!-- Premium Professional Job List Redesign by Sanket -->
<style>
/* PREMIUM UI CORE SETTINGS */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap');

#job-list-wrapper {
    background-color: #F8FAFC !important;
    min-height: 100vh;
    font-family: 'Inter', -apple-system, sans-serif !important;
    padding-bottom: 3rem;
    color: #1E293B !important;
}

/* Sophisticated Typography */
.job-list-title {
    font-family: 'Outfit', sans-serif !important;
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: #0F172A !important;
    letter-spacing: -0.02em;
    margin-bottom: 2px;
}

/* Glassmorphism Filter Bar */
.filter-card-premium {
    background: rgba(255, 255, 255, 0.8) !important;
    backdrop-filter: blur(8px) !important;
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02) !important;
    padding: 0.75rem 1rem !important;
    margin-bottom: 1rem !important;
}

.filter-label {
    font-size: 0.65rem !important;
    font-weight: 700 !important;
    color: #64748B !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}

.filter-input {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 8px !important;
    height: 34px !important;
    font-size: 0.8125rem !important;
    padding: 0.25rem 0.75rem !important;
    transition: all 0.2s ease;
    color: #1E293B !important;
}

.filter-input:focus {
    border-color: #3B82F6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
    background: #FFFFFF !important;
}

/* Premium Card Table High Density */
.job-table-card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 12px !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
    overflow: hidden;
}

.table-premium thead th {
    background: #F8FAFC !important;
    padding: 0.75rem 1rem !important;
    font-size: 0.7rem !important;
    font-weight: 700 !important;
    color: #475569 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #E2E8F0 !important;
}

.table-premium tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #F1F5F9;
}

.table-premium tbody tr:hover {
    background-color: #F8FAFC !important;
}

.table-premium td {
    padding: 0.75rem 1rem !important;
    font-size: 0.8125rem !important;
    vertical-align: middle !important;
    color: #334155;
}

.job-main-title {
    font-weight: 600 !important;
    color: #0F172A !important;
    display: block;
    font-size: 0.875rem;
}

.job-sub-info {
    font-size: 0.75rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 1px;
}

/* Action Buttons Refinement */
.action-btn-pill {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
    background: #F1F5F9;
    color: #64748B;
    border: none;
    text-decoration: none !important;
}

.action-btn-pill:hover {
    transform: translateY(-1px);
}

.btn-view:hover { background: #E0F2FE; color: #0369A1; }
.btn-edit:hover { background: #EEF2FF; color: #4338CA; }
.btn-delete:hover { background: #FEE2E2; color: #B91C1C; }

/* Status Badges Premium - Compact */
.badge-premium {
    padding: 4px 10px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    font-size: 0.7rem !important;
    letter-spacing: 0.01em;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.badge-active { background: #DCFCE7 !important; color: #15803D !important; border: 1px solid #BBF7D0 !important; }
.badge-inactive { background: #FEE2E2 !important; color: #B91C1C !important; border: 1px solid #FECACA !important; }
.badge-closed { background: #F1F5F9 !important; color: #475569 !important; border: 1px solid #E2E8F0 !important; }
.badge-type { background: #EFF6FF !important; color: #1D4ED8 !important; border: 1px solid #DBEAFE !important; }

</style>

<div id="job-list-wrapper" class="page-wrapper">
    <div class="content container-fluid">
        <!-- Premium Header -->
        <div class="page-header d-flex justify-content-between align-items-center mb-3 pt-3">
            <div>
                <h1 class="job-list-title">{{ __trans('job_list') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-muted">Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.dashboard') }}" class="text-muted">Recruitment</a></li>
                        <li class="breadcrumb-item active fw-600 text-primary">Job Positions</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                @can('recruitment.jobs.create')
                <a href="{{ route('recruitment.jobs.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 shadow-sm" style="border-radius: 10px; height: 36px; font-weight: 600; font-size: 0.8125rem;">
                    <i class="fas fa-plus-circle"></i> {{ __trans('add_job') }}
                </a>
                @endcan
                <a href="{{ route('recruitment.dashboard') }}" class="btn btn-outline-white d-inline-flex align-items-center gap-2 px-3 shadow-xs" style="border-radius: 10px; height: 36px; background: #fff; border: 1px solid #E2E8F0; color: #475569; font-weight: 600; font-size: 0.8125rem;">
                    <i class="fas fa-grid-2"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Premium Alerts Container -->
        <div id="alert-container">
            @if(session('success'))
                <div class="alert border-0 shadow-sm px-4 py-3 mb-4 d-flex align-items-center animate__animated animate__fadeInDown" style="background: #FFFFFF; border-left: 4px solid #10B981 !important; border-radius: 12px;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; background: #DCFCE7; color: #10B981;">
                        <i class="fas fa-check"></i>
                    </div>
                    <span style="font-size: 0.9375rem; font-weight: 600; color: #1F2937;">{{ session('success') }}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <!-- Glassmorphism Filter Bar -->
        <div class="filter-card-premium">
            <form id="filterForm" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="filter-label">{{ __trans('department') }}</label>
                    <select name="department_id" class="form-select filter-input shadow-none">
                        <option value="">{{ __trans('all_departments') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="filter-label">{{ __trans('job_type') }}</label>
                    <select name="type" class="form-select filter-input shadow-none">
                        <option value="">{{ __trans('all_types') }}</option>
                        <option value="full_time" {{ request('type') == 'full_time' ? 'selected' : '' }}>{{ __trans('full_time') }}</option>
                        <option value="part_time" {{ request('type') == 'part_time' ? 'selected' : '' }}>{{ __trans('part_time') }}</option>
                        <option value="contract" {{ request('type') == 'contract' ? 'selected' : '' }}>{{ __trans('contract') }}</option>
                        <option value="internship" {{ request('type') == 'internship' ? 'selected' : '' }}>{{ __trans('internship') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="filter-label">{{ __trans('status') }}</label>
                    <select name="status" class="form-select filter-input shadow-none">
                        <option value="">{{ __trans('all_statuses') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __trans('active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __trans('inactive') }}</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>{{ __trans('closed') }}</option>
                    </select>
                </div>
                <div class="col-md-auto ms-auto">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" style="border-radius: 10px; height: 40px; font-weight: 600;">
                            <i class="fas fa-search"></i> {{ __trans('filter') }}
                        </button>
                        <a href="{{ route('recruitment.jobs.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2 px-3" style="border-radius: 10px; height: 40px; font-weight: 600; background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
                            <i class="fas fa-redo"></i> {{ __trans('clear') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Premium Job Table -->
        <div class="job-table-card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-premium mb-0" id="jobsTable">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">#</th>
                            <th>{{ __trans('job_details') }}</th>
                            <th>{{ __trans('location') }}</th>
                            <th>{{ __trans('employment_type') }}</th>
                            <th>{{ __trans('status') }}</th>
                            <th class="text-center">{{ __trans('applications') }}</th>
                            <th>{{ __trans('posted_on') }}</th>
                            <th width="150" class="text-end">{{ __trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($jobs) && $jobs->count() > 0)
                            @foreach($jobs as $index => $job)
                            <tr>
                                <td class="text-center text-muted fw-500">{{ ($jobs->currentPage() - 1) * $jobs->perPage() + $index + 1 }}</td>
                                <td>
                                    <span class="job-main-title">{{ $job->title }}</span>
                                    <span class="job-sub-info">
                                        <i class="fas fa-building text-primary opacity-50" style="font-size: 0.7rem;"></i> 
                                        {{ $job->department ? $job->department->name : 'Unassigned' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background: #EEF2FF; color: #6366F1;">
                                            <i class="fas fa-map-marker-alt" style="font-size: 0.75rem;"></i>
                                        </div>
                                        <span class="fw-500" style="font-size: 0.875rem;">{{ $job->location ?? 'Headquarters' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-premium badge-type">
                                        <i class="fas fa-briefcase opacity-50"></i>
                                        {{ ucwords(str_replace('_', ' ', $job->hiring_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'badge-active';
                                        $icon = 'fa-check-circle';
                                        if($job->status == 'inactive') { $statusClass = 'badge-inactive'; $icon = 'fa-times-circle'; }
                                        if($job->status == 'closed') { $statusClass = 'badge-closed'; $icon = 'fa-lock'; }
                                    @endphp
                                    <span class="badge badge-premium {{ $statusClass }}">
                                        <i class="fas {{ $icon }} opacity-75"></i>
                                        {{ ucfirst($job->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center justify-content-center" style="min-width: 32px; height: 32px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; font-weight: 700; color: #0F172A;">
                                        {{ $job->applications()->count() }}
                                    </div>
                                </td>
                                <td>
                                    <div class="job-sub-info">
                                        <i class="fas fa-calendar-alt opacity-50"></i>
                                        {{ $job->created_at->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('recruitment.jobs.show', $job->id) }}" class="action-btn-pill btn-view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('recruitment.jobs.edit')
                                        <a href="{{ route('recruitment.jobs.edit', $job->id) }}" class="action-btn-pill btn-edit" title="Edit Job">
                                            <i class="fas fa-pencil-alt" style="font-size: 0.85rem;"></i>
                                        </a>
                                        @endcan
                                        @can('recruitment.jobs.destroy')
                                        <button type="button" class="action-btn-pill btn-delete border-0" title="Remove Job" onclick="confirmDelete({{ $job->id }})">
                                            <i class="fas fa-trash-alt" style="font-size: 0.85rem;"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #F8FAFC; border-radius: 24px;">
                                        <i class="fas fa-inbox text-muted opacity-25 fa-3x"></i>
                                    </div>
                                    <h5 class="fw-700 text-slate-800 mb-1">No Job Postings Found</h5>
                                    <p class="text-muted mb-4 small">Try adjusting your filters or create a new job position.</p>
                                    @can('recruitment.jobs.create')
                                    <a href="{{ route('recruitment.jobs.create') }}" class="btn btn-primary px-4" style="border-radius: 12px; font-weight: 600;">
                                        Create New Position
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            @if(isset($jobs) && method_exists($jobs, 'links'))
            <div class="px-4 py-3 bg-light d-flex justify-content-between align-items-center">
                <div class="fw-600 text-slate-500" style="font-size: 0.8125rem;">
                    Showing <span class="text-slate-900">{{ $jobs->firstItem() ?? 0 }}-{{ $jobs->lastItem() ?? 0 }}</span> 
                    of <span class="text-slate-900">{{ $jobs->total() ?? 0 }}</span> positions
                </div>
                <div class="premium-pagination">
                    {{ $jobs->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-filter functionality
        $('#filterForm select').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Auto-hide success messages
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

    function confirmDelete(jobId) {
        Swal.fire({
            title: 'Delete Job Position?',
            text: "This will remove the job and all associated data permanently.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Yes, Delete Job',
            cancelButtonText: 'Cancel',
            background: '#ffffff',
            customClass: {
                popup: 'premium-swal-popup',
                confirmButton: 'premium-swal-confirm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('recruitment/jobs') }}/${jobId}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Job position removed successfully.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => { window.location.reload(); });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    }
</script>
<style>
    /* Pagination Overrides */
    .premium-pagination .pagination { margin-bottom: 0; gap: 4px; }
    .premium-pagination .page-link {
        border: 1px solid #E2E8F0;
        border-radius: 10px !important;
        padding: 6px 12px;
        color: #475569;
        font-weight: 600;
        font-size: 0.8125rem;
    }
    .premium-pagination .page-item.active .page-link {
        background: #3B82F6;
        border-color: #3B82F6;
        color: #fff;
    }
    .premium-pagination .page-link:hover {
        background: #F1F5F9;
        border-color: #CBD5E1;
    }

    /* Custom Swal Styles */
    .premium-swal-popup { border-radius: 20px !important; padding: 2rem !important; }
    .premium-swal-confirm { border-radius: 12px !important; padding: 10px 24px !important; font-weight: 600 !important; }
    
    /* Global Overrides for Job Page */
    #job-list-wrapper .breadcrumb-item + .breadcrumb-item::before { content: "•"; color: #CBD5E1; }
    #job-list-wrapper .btn-outline-white:hover { background: #F8FAFC !important; border-color: #CBD5E1 !important; }
</style>
@endpush