@extends('layouts.backend')

@section('content')
<!-- Interviews Page - Professional Design by Sanket -->
<style>
/* MAXIMUM PRIORITY OVERRIDES - Interviews */
body {
    background: #F9FAFB !important;
}

.main-wrapper {
    background: #F9FAFB !important;
}

/* Force page wrapper styling */
#interviews-list-wrapper {
    background: #F9FAFB !important;
    min-height: 100vh;
}

#interviews-list-wrapper * {
    box-sizing: border-box;
}

/* Header Override */
#interviews-list-wrapper .page-header {
    background: #FFFFFF !important;
    border: none !important;
    border-bottom: 1px solid #E5E7EB !important;
    padding: 1.5rem 1.5rem !important;
    margin: 0 0 2rem 0 !important;
}

#interviews-list-wrapper .page-title {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    color: #111827 !important;
    margin: 0.5rem 0 0 0 !important;
}

#interviews-list-wrapper .breadcrumb {
    background: transparent !important;
    padding: 0 !important;
    margin: 0 0 0.5rem 0 !important;
    font-size: 0.875rem !important;
}

#interviews-list-wrapper .breadcrumb-item {
    color: #6B7280 !important;
}

#interviews-list-wrapper .breadcrumb-item + .breadcrumb-item::before {
    color: #D1D5DB !important;
}

#interviews-list-wrapper .breadcrumb-item a {
    color: #6B7280 !important;
    text-decoration: none !important;
}

#interviews-list-wrapper .breadcrumb-item a:hover {
    color: #2563EB !important;
}

/* Card Overrides */
#interviews-list-wrapper .card,
#interviews-list-wrapper .card-header,
#interviews-list-wrapper .card-body {
    background: #FFFFFF !important;
    border: 0 !important;
    border-style: none !important;
    border-width: 0 !important;
    border-color: transparent !important;
}

#interviews-list-wrapper .card {
    border-radius: 0.75rem !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
    margin-bottom: 1.5rem !important;
    overflow: hidden !important;
}

#interviews-list-wrapper .card-header {
    border-bottom: 1px solid #F3F4F6 !important;
    padding: 1.25rem 1.5rem !important;
}

#interviews-list-wrapper .card-title {
    font-size: 1.125rem !important;
    font-weight: 600 !important;
    color: #111827 !important;
    margin: 0 !important;
}

#interviews-list-wrapper .card-body {
    padding: 1.5rem !important;
}

/* Form Controls */
#interviews-list-wrapper .form-label {
    font-size: 0.8125rem !important;
    font-weight: 500 !important;
    color: #374151 !important;
    margin-bottom: 0.5rem !important;
}

#interviews-list-wrapper .form-control,
#interviews-list-wrapper .form-select,
#interviews-list-wrapper input[type="date"] {
    padding: 0.625rem 0.875rem !important;
    font-size: 0.875rem !important;
    border: 1px solid #D1D5DB !important;
    border-radius: 0.5rem !important;
    background: #FFFFFF !important;
    color: #111827 !important;
}

#interviews-list-wrapper .form-control:focus,
#interviews-list-wrapper .form-select:focus,
#interviews-list-wrapper input[type="date"]:focus {
    outline: none !important;
    border-color: #2563EB !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
}

/* Table Overrides */
#interviews-list-wrapper .table {
    width: 100% !important;
    background: #FFFFFF !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    margin: 0 !important;
}

#interviews-list-wrapper .table thead th {
    background: #F9FAFB !important;
    color: #6B7280 !important;
    font-size: 0.7rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    padding: 0.75rem 0.625rem !important;
    border: none !important;
    border-bottom: 1px solid #E5E7EB !important;
    white-space: nowrap !important;
}

#interviews-list-wrapper .table tbody tr {
    background: #FFFFFF !important;
}

#interviews-list-wrapper .table tbody tr:hover {
    background: #F9FAFB !important;
}

#interviews-list-wrapper .table tbody td {
    padding: 0.75rem 0.625rem !important;
    border: none !important;
    border-bottom: 1px solid #F3F4F6 !important;
    color: #374151 !important;
    font-size: 0.8125rem !important;
    vertical-align: middle !important;
}

/* Badges */
#interviews-list-wrapper .badge {
    padding: 0.25rem 0.5rem !important;
    border-radius: 0.375rem !important;
    font-size: 0.7rem !important;
    font-weight: 600 !important;
    border: none !important;
}

/* Buttons */
#interviews-list-wrapper .btn {
    border-radius: 0.5rem !important;
    font-weight: 500 !important;
    padding: 0.625rem 1.25rem !important;
    font-size: 0.875rem !important;
}

#interviews-list-wrapper .btn-primary {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
}

#interviews-list-wrapper .btn-primary:hover {
    background: #1D4ED8 !important;
    border-color: #1D4ED8 !important;
}

#interviews-list-wrapper .btn-outline-secondary {
    background: #FFFFFF !important;
    border-color: #D1D5DB !important;
    color: #374151 !important;
}

#interviews-list-wrapper .btn-outline-secondary:hover {
    background: #F9FAFB !important;
    border-color: #9CA3AF !important;
}

#interviews-list-wrapper .btn-sm {
    padding: 0.375rem 0.75rem !important;
    font-size: 0.75rem !important;
}

#interviews-list-wrapper .btn-info {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
}

#interviews-list-wrapper .btn-info:hover {
    background: #1D4ED8 !important;
}

#interviews-list-wrapper .btn-success {
    background: #10B981 !important;
    border-color: #10B981 !important;
    color: #FFFFFF !important;
}

#interviews-list-wrapper .btn-success:hover {
    background: #059669 !important;
}

#interviews-list-wrapper .btn-danger {
    background: #EF4444 !important;
    border-color: #EF4444 !important;
    color: #FFFFFF !important;
}

#interviews-list-wrapper .btn-danger:hover {
    background: #DC2626 !important;
}
</style>

<div id="interviews-list-wrapper" class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Interviews</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Recruitment</a></li>
                        <li class="breadcrumb-item active">Interviews</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('recruitment.interviews.create')
                    <a href="{{ route('recruitment.interviews.create') }}" class="btn btn-primary me-1">
                        <i class="fas fa-plus"></i> Schedule Interview
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
                                <label class="form-label">Interviewer</label>
                                <select name="interviewer_id" class="form-control">
                                    <option value="">All Interviewers</option>
                                    @foreach($interviewers as $interviewer)
                                        <option value="{{ $interviewer->id }}" {{ request('interviewer_id') == $interviewer->id ? 'selected' : '' }}>{{ $interviewer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="rescheduled" {{ request('status') == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="phone" {{ request('type') == 'phone' ? 'selected' : '' }}>Phone</option>
                                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                                    <option value="in_person" {{ request('type') == 'in_person' ? 'selected' : '' }}>In Person</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm" style="flex: 1;">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="{{ route('recruitment.interviews.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interviews Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card" id="main-interview-card" style="border: none !important; border-width: 0 !important; outline: none !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;">
                    <div class="card-header" style="border: none !important; border-bottom: 1px solid #F3F4F6 !important; background: #FFFFFF !important;">
                        <h4 class="card-title">
                            <i class="fas fa-calendar-alt" style="margin-right: 0.5rem; color: #2563EB;"></i>Interview Schedule
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0 !important; border: none !important;">
                        @if(isset($interviews) && $interviews->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Candidate</th>
                                        <th>Job Title</th>
                                        <th>Interviewer</th>
                                        <th>Scheduled Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th style="white-space: nowrap;">Dur.</th>
                                        <th style="width: 120px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($interviews as $index => $interview)
                                    <tr>
                                        <td style="color: #9CA3AF; font-weight: 500;">{{ $index + 1 }}</td>
                                        <td>
                                            <div style="font-weight: 500; color: #111827;">{{ $interview->application->user->name ?? $interview->application->candidate_name ?? 'Unknown' }}</div>
                                        </td>
                                        <td style="font-weight: 500;">{{ $interview->application->job->title ?? 'N/A' }}</td>
                                        <td style="color: #6B7280;">{{ $interview->interviewer->name ?? 'N/A' }}</td>
                                        <td style="color: #6B7280;">{{ $interview->scheduled_at ? $interview->scheduled_at->format('M d, Y H:i') : 'N/A' }}</td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'phone' => 'background: #DBEAFE !important; color: #1E40AF !important;',
                                                    'video' => 'background: #DDD6FE !important; color: #5B21B6 !important;',
                                                    'in_person' => 'background: #D1FAE5 !important; color: #065F46 !important;'
                                                ];
                                                $typeStyle = $typeColors[$interview->type ?? 'phone'] ?? $typeColors['phone'];
                                            @endphp
                                            <span class="badge" style="{{ $typeStyle }}">{{ ucfirst(str_replace('_', ' ', $interview->type ?? 'phone')) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'scheduled' => 'background: #FEF3C7 !important; color: #92400E !important;',
                                                    'completed' => 'background: #D1FAE5 !important; color: #065F46 !important;',
                                                    'cancelled' => 'background: #FEE2E2 !important; color: #991B1B !important;',
                                                    'rescheduled' => 'background: #E0E7FF !important; color: #4338CA !important;'
                                                ];
                                                $statusStyle = $statusColors[$interview->status] ?? $statusColors['scheduled'];
                                            @endphp
                                            <span class="badge" style="{{ $statusStyle }}">{{ ucfirst($interview->status) }}</span>
                                        </td>
                                        <td style="color: #6B7280;">{{ $interview->duration_minutes }} min</td>
                                        <td style="text-align: center;">
                                            <div style="display: inline-flex; gap: 0.25rem;">
                                                <a href="{{ route('recruitment.interviews.show', $interview->id) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(auth()->user() && (auth()->user()->hasRole(['admin', 'hr', 'HR Manager']) || auth()->user()->can('Manage Interviews')))
                                                    <a href="{{ route('recruitment.interviews.edit', $interview->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($interview->status === 'scheduled')
                                                        <button type="button" class="btn btn-sm btn-success" title="Mark as Complete" onclick="completeInterview({{ $interview->id }})">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                    @if($interview->status !== 'completed')
                                                        <form action="{{ route('recruitment.interviews.destroy', $interview->id) }}" method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(method_exists($interviews, 'links'))
                        <div class="d-flex justify-content-between align-items-center" style="padding: 1rem 1.5rem; border-top: 1px solid #F3F4F6;">
                            <div style="font-size: 0.875rem; color: #6B7280;">
                                Showing {{ $interviews->firstItem() ?? 0 }} to {{ $interviews->lastItem() ?? 0 }} of {{ $interviews->total() ?? 0 }} entries
                            </div>
                            <div>
                                {{ $interviews->appends(request()->query())->links() }}
                            </div>
                        </div>
                        @endif
                        @else
                        <div style="text-align: center; padding: 4rem 2rem;">
                            <div style="font-size: 3rem; color: #D1D5DB; margin-bottom: 1rem;">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <h5 style="font-size: 1.125rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">No Interviews Scheduled</h5>
                            <p style="color: #6B7280;">
                                @if(isset($interviews))
                                    No interviews match your current filters.
                                @else
                                    Loading interviews...
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Interview Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeForm">
                <div class="modal-body">
                    <input type="hidden" id="interviewId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Score (1-10)</label>
                                <input type="number" name="score" class="form-control" min="1" max="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Recommendation <span class="text-danger">*</span></label>
                                <select name="recommendation" class="form-control" required>
                                    <option value="">Select Recommendation</option>
                                    <option value="hire">Hire</option>
                                    <option value="reject">Reject</option>
                                    <option value="second_interview">Second Interview</option>
                                    <option value="on_hold">Put On Hold</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea name="feedback" class="form-control" rows="4" placeholder="Enter detailed feedback about the candidate's performance..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Complete Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-filter functionality
        $('#filterForm select, #filterForm input[type="date"]').on('change', function() {
            $(this).closest('form')[0].submit();
        });
    });

    function completeInterview(id) {
        $('#interviewId').val(id);
        var modal = new bootstrap.Modal(document.getElementById('completeModal'));
        modal.show();
    }

    $('#completeForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const interviewId = $('#interviewId').val();
        
        $.ajax({
            url: `{{ url('recruitment/interviews') }}/${interviewId}/complete`,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#completeModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to complete interview');
                }
            },
            error: function(xhr) {
                let message = 'Failed to complete interview';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            }
        });
    });

    function confirmDelete() {
        return confirm('Are you sure you want to delete this interview? This action cannot be undone.');
    }
</script>
@endpush
