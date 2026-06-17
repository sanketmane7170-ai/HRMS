@extends('layouts.backend')

@section('content')
<!-- Applications List Page - Professional Design by Sanket -->
<style>
/* MAXIMUM PRIORITY OVERRIDES - Applications List */
body {
    background: #F9FAFB !important;
}

.main-wrapper {
    background: #F9FAFB !important;
}

/* Force page wrapper styling */
#applications-list-wrapper {
    background: #F9FAFB !important;
    min-height: 100vh;
}

#applications-list-wrapper * {
    box-sizing: border-box;
}

/* Header Override */
#applications-list-wrapper .page-header {
    background: #FFFFFF !important;
    border: none !important;
    border-bottom: 1px solid #E5E7EB !important;
    padding: 1.5rem 1.5rem !important;
    margin: 0 0 2rem 0 !important;
}

#applications-list-wrapper .page-title {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    color: #111827 !important;
    margin: 0.5rem 0 0 0 !important;
}

#applications-list-wrapper .breadcrumb {
    background: transparent !important;
    padding: 0 !important;
    margin: 0 0 0.5rem 0 !important;
    font-size: 0.875rem !important;
}

#applications-list-wrapper .breadcrumb-item {
    color: #6B7280 !important;
}

#applications-list-wrapper .breadcrumb-item + .breadcrumb-item::before {
    color: #D1D5DB !important;
}

#applications-list-wrapper .breadcrumb-item a {
    color: #6B7280 !important;
    text-decoration: none !important;
}

#applications-list-wrapper .breadcrumb-item a:hover {
    color: #2563EB !important;
}

/* Card Overrides */
#applications-list-wrapper .card,
#applications-list-wrapper .card-header,
#applications-list-wrapper .card-body {
    background: #FFFFFF !important;
    border: 0 !important;
    border-style: none !important;
    border-width: 0 !important;
    border-color: transparent !important;
}

#applications-list-wrapper .card {
    border-radius: 0.75rem !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
    margin-bottom: 1.5rem !important;
}

#applications-list-wrapper .card-header {
    background: #FFFFFF !important;
    border-bottom: 1px solid #F3F4F6 !important;
    padding: 1.25rem 1.5rem !important;
}

#applications-list-wrapper .card-title {
    font-size: 1.125rem !important;
    font-weight: 600 !important;
    color: #111827 !important;
    margin: 0 !important;
}

#applications-list-wrapper .card-body {
    background: #FFFFFF !important;
    padding: 1.5rem !important;
}

/* Form Controls */
#applications-list-wrapper .form-label {
    font-size: 0.8125rem !important;
    font-weight: 500 !important;
    color: #374151 !important;
    margin-bottom: 0.5rem !important;
}

#applications-list-wrapper .form-control,
#applications-list-wrapper .form-select {
    padding: 0.625rem 0.875rem !important;
    font-size: 0.875rem !important;
    border: 1px solid #D1D5DB !important;
    border-radius: 0.5rem !important;
    background: #FFFFFF !important;
    color: #111827 !important;
}

#applications-list-wrapper .form-control:focus,
#applications-list-wrapper .form-select:focus {
    outline: none !important;
    border-color: #2563EB !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
}

/* Table Overrides */
#applications-list-wrapper .table {
    width: 100% !important;
    background: #FFFFFF !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    margin: 0 !important;
}

#applications-list-wrapper .table thead th {
    background: #F9FAFB !important;
    color: #6B7280 !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    padding: 0.875rem 1rem !important;
    border: none !important;
    border-bottom: 1px solid #F3F4F6 !important;
}

#applications-list-wrapper .table tbody tr {
    background: #FFFFFF !important;
}

#applications-list-wrapper .table tbody tr:hover {
    background: #F9FAFB !important;
}

#applications-list-wrapper .table tbody td {
    padding: 1rem !important;
    border: none !important;
    border-bottom: 1px solid #F3F4F6 !important;
    color: #374151 !important;
    font-size: 0.875rem !important;
    vertical-align: middle !important;
}

/* Badges */
#applications-list-wrapper .badge {
    padding: 0.375rem 0.75rem !important;
    border-radius: 0.375rem !important;
    font-size: 0.75rem !important;
    font-weight: 500 !important;
    border: none !important;
}

/* Buttons */
#applications-list-wrapper .btn {
    border-radius: 0.5rem !important;
    font-weight: 500 !important;
    padding: 0.625rem 1.25rem !important;
    font-size: 0.875rem !important;
}

#applications-list-wrapper .btn-primary {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
}

#applications-list-wrapper .btn-primary:hover {
    background: #1D4ED8 !important;
    border-color: #1D4ED8 !important;
}

#applications-list-wrapper .btn-outline-secondary {
    background: #FFFFFF !important;
    border-color: #D1D5DB !important;
    color: #374151 !important;
}

#applications-list-wrapper .btn-outline-secondary:hover {
    background: #F9FAFB !important;
    border-color: #9CA3AF !important;
}

#applications-list-wrapper .btn-sm {
    padding: 0.5rem 1rem !important;
    font-size: 0.8125rem !important;
}

#applications-list-wrapper .btn-info {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
}

#applications-list-wrapper .btn-info:hover {
    background: #1D4ED8 !important;
}
</style>

<div id="applications-list-wrapper" class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Applications</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Recruitment</a></li>
                        <li class="breadcrumb-item active">Applications</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('recruitment.applications.create')
                    <a href="{{ route('recruitment.applications.create') }}" class="btn btn-primary me-1">
                        <i class="fas fa-plus"></i> Add Application
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="card" style="border: none !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important; border-radius: 12px !important;">
                    <div class="card-body">
                        <form id="filterForm" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Job</label>
                                <select name="job_id" class="form-control">
                                    <option value="">All Jobs</option>
                                    @foreach($jobs as $job)
                                        <option value="{{ $job->id }}" {{ request('job_id') == $job->id ? 'selected' : '' }}>{{ $job->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stage</label>
                                <select name="stage" class="form-control">
                                    <option value="">All Stages</option>
                                    @foreach($stages as $stage => $label)
                                        <option value="{{ $stage }}" {{ request('stage') == $stage ? 'selected' : '' }}>{{ ucfirst($stage) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date Range</label>
                                <input type="text" name="date_range" class="form-control daterangepicker-input" placeholder="Select date range" value="{{ request('date_range') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm" style="flex: 1;">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="{{ route('recruitment.applications.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card" style="border: none !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important; border-radius: 12px !important;">
                    <div class="card-header" style="border: none !important; border-bottom: 1px solid #F3F4F6 !important; background: #FFFFFF !important;">
                        <h4 class="card-title">
                            <i class="fas fa-users" style="margin-right: 0.5rem; color: #2563EB;"></i>Job Applications
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0 !important; border: none !important;">
                        @if(isset($applications) && $applications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Candidate</th>
                                        <th>Job Title</th>
                                        <th>Stage</th>
                                        <th>Score</th>
                                        <th>Applied Date</th>
                                        <th style="width: 80px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applications as $index => $application)
                                    <tr>
                                        <td style="color: #9CA3AF; font-weight: 500;">{{ ($applications->currentPage() - 1) * $applications->perPage() + $index + 1 }}</td>
                                        <td>
                                            <div style="font-weight: 500; color: #111827;">{{ $application->candidate_name ?? ($application->user ? $application->user->name : 'N/A') }}</div>
                                            <div style="font-size: 0.8125rem; color: #6B7280;">{{ $application->candidate_email ?? ($application->user ? $application->user->email : '') }}</div>
                                        </td>
                                        <td style="font-weight: 500;">{{ $application->job ? $application->job->title : 'N/A' }}</td>
                                        <td>
                                            @php
                                                $stageVal = is_array($application->stage) ? implode('', $application->stage) : $application->stage;
                                                $badgeColors = [
                                                    'applied' => 'background: #DBEAFE !important; color: #1E40AF !important;',
                                                    'screening' => 'background: #E0E7FF !important; color: #4338CA !important;',
                                                    'shortlisted' => 'background: #FEF3C7 !important; color: #92400E !important;',
                                                    'interview' => 'background: #FED7AA !important; color: #9A3412 !important;',
                                                    'offer' => 'background: #DDD6FE !important; color: #5B21B6 !important;',
                                                    'hired' => 'background: #D1FAE5 !important; color: #065F46 !important;',
                                                    'rejected' => 'background: #FEE2E2 !important; color: #991B1B !important;',
                                                    'withdrawn' => 'background: #F3F4F6 !important; color: #4B5563 !important;'
                                                ];
                                                $badgeStyle = $badgeColors[$stageVal] ?? $badgeColors['applied'];
                                            @endphp
                                            <span class="badge" style="{{ $badgeStyle }}">{{ ucfirst($stageVal) }}</span>
                                        </td>
                                        <td style="color: #6B7280;">{{ $application->score ? number_format($application->score, 1) : 'N/A' }}</td>
                                        <td style="color: #6B7280;">{{ $application->applied_on ? $application->applied_on->format('M d, Y') : 'N/A' }}</td>
                                        <td style="text-align: center;">
                                            <a href="{{ route('recruitment.applications.show', $application->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(method_exists($applications, 'links'))
                        <div class="d-flex justify-content-between align-items-center" style="padding: 1rem 1.5rem; border-top: 1px solid #F3F4F6;">
                            <div style="font-size: 0.875rem; color: #6B7280;">
                                Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() ?? 0 }} entries
                            </div>
                            <div>
                                {{ $applications->appends(request()->query())->links() }}
                            </div>
                        </div>
                        @endif
                        @else
                        <div style="text-align: center; padding: 4rem 2rem;">
                            <div style="font-size: 3rem; color: #D1D5DB; margin-bottom: 1rem;">
                                <i class="fas fa-search"></i>
                            </div>
                            <h5 style="font-size: 1.125rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">No Applications Found</h5>
                            <p style="color: #6B7280; margin-bottom: 1.5rem;">
                                No applications match your current search criteria.<br>
                                Try adjusting your filters or search terms.
                            </p>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-refresh"></i> Clear All Filters
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@push('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Date Range Picker
        $('.daterangepicker-input').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'MM/DD/YYYY'
            },
            ranges: {
               'Today': [moment(), moment()],
               'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Last 7 Days': [moment().subtract(6, 'days'), moment()],
               'Last 30 Days': [moment().subtract(29, 'days), moment()],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('.daterangepicker-input').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('.daterangepicker-input').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Auto-filter functionality
        $('#filterForm select').on('change', function() {
            $(this).closest('form')[0].submit();
        });

        $('.daterangepicker-input').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            $(this).closest('form')[0].submit();
        });
    });

    function clearFilters() {
        $('select[name="job_id"]').val('');
        $('select[name="stage"]').val('');
        $('input[name="date_range"]').val('');
        $('form').first().submit();
    }
</script>
@endpush