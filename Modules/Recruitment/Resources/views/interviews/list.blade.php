@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Interview Schedule</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Recruitment</a></li>
                        <li class="breadcrumb-item active">Interviews</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('recruitment.interviews.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Schedule Interview
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Interviewer</label>
                                <select name="interviewer_id" class="form-control">
                                    <option value="">All Interviewers</option>
                                    @foreach($interviewers as $interviewer)
                                        <option value="{{ $interviewer->id }}">{{ $interviewer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="rescheduled">Rescheduled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="phone">Phone</option>
                                    <option value="video">Video</option>
                                    <option value="in_person">In Person</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary" style="margin-top: 32px;">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interview Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Interview Schedule</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="interviewTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Candidate</th>
                                        <th>Job Title</th>
                                        <th>Interviewer</th>
                                        <th>Scheduled Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#interviewTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('recruitment.interview-list.index') }}",
            type: 'GET',
            data: function (d) {
                d.interviewer_id = $('select[name="interviewer_id"]').val();
                d.status = $('select[name="status"]').val();
                d.type = $('select[name="type"]').val();
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error: function (xhr, error, thrown) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error loading interview data. Please refresh the page.');
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'candidate', name: 'candidate'},
            {data: 'job_title', name: 'job_title'},
            {data: 'interviewer_name', name: 'interviewer_name'},
            {data: 'scheduled_date', name: 'scheduled_date'},
            {data: 'type_display', name: 'type_display'},
            {data: 'status_display', name: 'status_display'},
            {data: 'duration_display', name: 'duration_display'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        order: [[4, 'asc']],
        pageLength: 25,
        responsive: true
    });

    // Filter functionality
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    // Auto-filter on select change
    $('#filterForm select').on('change', function() {
        table.draw();
    });
});
</script>
@endsection