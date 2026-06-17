@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Interview Schedule (Simple)</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Interview Schedule</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ $interviews->count() }}</h3>
                        <p>Total Interviews</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ $interviews->where('status', 'scheduled')->count() }}</h3>
                        <p>Scheduled</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ $interviews->where('status', 'completed')->count() }}</h3>
                        <p>Completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ $interviews->where('status', 'cancelled')->count() }}</h3>
                        <p>Cancelled</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Interviewer</label>
                                <select name="interviewer_id" class="form-control">
                                    <option value="">All Interviewers</option>
                                    @foreach($interviewers as $interviewer)
                                        <option value="{{ $interviewer->id }}">{{ $interviewer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="rescheduled">Rescheduled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="phone">Phone</option>
                                    <option value="video">Video</option>
                                    <option value="in_person">In Person</option>
                                </select>
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

@push('script')
<script>
$(document).ready(function() {
    console.log('Starting DataTable initialization...');
    
    var table = $('#interviewTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('recruitment.simple-interviews.data') }}",
            type: 'GET',
            data: function (d) {
                // Add filter parameters
                d.interviewer_id = $('select[name="interviewer_id"]').val() || '';
                d.status = $('select[name="status"]').val() || '';
                d.type = $('select[name="type"]').val() || '';
                console.log('Sending filter data:', d);
                return d;
            },
            dataSrc: function(json) {
                console.log('Received data:', json);
                return json.data;
            },
            error: function (xhr, error, thrown) {
                console.error('DataTable AJAX Error:', {
                    status: xhr.status,
                    responseText: xhr.responseText,
                    error: error,
                    thrown: thrown
                });
                alert('Error loading data: ' + xhr.status + ' - ' + error);
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'candidate', name: 'candidate'},
            {data: 'job_title', name: 'job_title'},
            {data: 'interviewer_name', name: 'interviewer_name'},
            {data: 'scheduled_date', name: 'scheduled_date'},
            {data: 'type', name: 'type'},
            {data: 'status', name: 'status'},
            {data: 'duration', name: 'duration'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: true,
        order: [[4, 'desc']],
        language: {
            processing: "Loading interviews...",
            emptyTable: "No interviews found",
            zeroRecords: "No interviews match your filter criteria"
        }
    });

    // Filter functionality
    $('#filterForm select').on('change', function() {
        console.log('Filter changed, reloading table...');
        table.ajax.reload();
    });
    
    // Manual reload button for testing
    $('<button class="btn btn-secondary btn-sm ml-2">Reload Data</button>')
        .appendTo('.col-md-3:last')
        .on('click', function() {
            console.log('Manual reload triggered');
            table.ajax.reload();
        });
    
    console.log('DataTable initialized successfully');
});
</script>
@endpush