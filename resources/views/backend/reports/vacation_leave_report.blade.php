@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6 col-sm-12">
                    <h3 class="page-title">{{ __trans('vacation_leave_report') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('vacation_leave_report') }}</li>
                    </ul>
                </div>
                <div class="col-md-6 col-sm-12 d-flex align-items-end justify-content-end gap-2">
                    <button id="exportExcelBtn" class="btn btn-success mt-4">
                        Export to Excel <span id="excelLoader" class="spinner-border spinner-border-sm ms-2" style="display:none;"></span>
                    </button>
                    <button id="exportPdfBtn" class="btn btn-danger mt-4">
                        Export to PDF <span id="pdfLoader" class="spinner-border spinner-border-sm ms-2" style="display:none;"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <div id="loadingContainer" class="text-center my-4" style="display:block;">
                                <div class="progress" style="height: 25px; max-width: 400px; margin: 0 auto;">
                                    <div id="loadingBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                        role="progressbar" style="width: 0%">Loading...</div>
                                </div>
                                <p class="mt-2 text-muted">Fetching vacation leave data, please wait...</p>
                            </div>

                            <table class="table text-center table-hover" id="leaveTable">
                                <thead class="thead-light">
                                    <tr id="headerRow"></tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // let table;

    // function loadTable() {
    //     $.ajax({
    //         url: "{{ route('backend.reports.vacation_leave_report') }}",
    //         success: function(response) {
    //             let months = response.months;
    //             let baseColumns = [{
    //                     data: 'DT_RowIndex',
    //                     name: 'DT_RowIndex',
    //                     title: '#'
    //                 },
    //                 {
    //                     data: 'employee_id',
    //                     title: 'Employee ID'
    //                 },
    //                 {
    //                     data: 'employee_name',
    //                     title: 'Employee Name'
    //                 },
    //                 {
    //                     data: 'department_name',
    //                     title: 'Department'
    //                 },
    //                 {
    //                     data: 'location',
    //                     title: 'Location'
    //                 },
    //                 {
    //                     data: 'designation',
    //                     title: 'Designation'
    //                 },
    //                 {
    //                     data: 'join_date',
    //                     title: 'Join Date'
    //                 },
    //                 {
    //                     data: 'policy_type',
    //                     title: 'Policy Type'
    //                 },
    //                 {
    //                     data: 'annual_leave',
    //                     title: 'Annual Leave'
    //                 },
    //                 {
    //                     data: 'initial_balance',
    //                     title: 'Initial Balance'
    //                 },
    //                 {
    //                     data: 'initial_balance_date',
    //                     title: 'Initial Balance Date'
    //                 },
    //                 {
    //                     data: 'total_leave',
    //                     title: 'Total Leave'
    //                 },
    //                 {
    //                     data: 'total_month',
    //                     title: 'Total Month'
    //                 },
    //                 {
    //                     data: 'used_leave',
    //                     title: 'Used Leave'
    //                 },
    //                 {
    //                     data: 'balance_leave',
    //                     title: 'Balance Leave'
    //                 },
    //             ];

    //             months.forEach(m => {
    //                 baseColumns.push({
    //                     data: m,
    //                     title: m
    //                 });
    //             });

    //             // Build header
    //             let headerHtml = baseColumns.map(col => `<th>${col.title}</th>`).join('');
    //             $('#headerRow').html(headerHtml);

    //             if (table) table.destroy();

    //             table = $('#leaveTable').DataTable({
    //                 data: response.data,
    //                 columns: baseColumns,
    //                 responsive: true,
    //                 pageLength: 10,
    //             });
    //         }
    //     });
    // }
    let table;

    function loadTable() {
        $('#loadingContainer').show();
        let progress = 0;
        let progressInterval = setInterval(() => {
            progress = Math.min(progress + Math.random() * 10, 90); // animate until 90%
            $('#loadingBar').css('width', progress + '%');
        }, 300);

        $.ajax({
            url: "{{ route('backend.reports.vacation_leave_report') }}",
            success: function(response) {
                clearInterval(progressInterval);
                $('#loadingBar').css('width', '100%').text('Rendering table...');

                let months = response.months;
                let baseColumns = [{
                        data: 'DT_RowIndex',
                        title: '#'
                    },
                    {
                        data: 'employee_id',
                        title: 'Employee ID'
                    },
                    {
                        data: 'employee_name',
                        title: 'Employee Name'
                    },
                    {
                        data: 'department_name',
                        title: 'Department'
                    },
                    {
                        data: 'location',
                        title: 'Location'
                    },
                    {
                        data: 'designation',
                        title: 'Designation'
                    },
                    {
                        data: 'join_date',
                        title: 'Join Date'
                    },
                    {
                        data: 'policy_type',
                        title: 'Policy Type'
                    },
                    {
                        data: 'annual_leave',
                        title: 'Annual Leave'
                    },
                    {
                        data: 'initial_balance',
                        title: 'Initial Balance'
                    },
                    {
                        data: 'initial_balance_date',
                        title: 'Initial Balance Date'
                    },
                    {
                        data: 'total_leave',
                        title: 'Total Leave'
                    },
                    {
                        data: 'total_month',
                        title: 'Total Month'
                    },
                    {
                        data: 'used_leave',
                        title: 'Used Leave'
                    },
                    {
                        data: 'balance_leave',
                        title: 'Balance Leave'
                    },
                ];

                months.forEach(m => baseColumns.push({
                    data: m,
                    title: m
                }));

                // Build header
                let headerHtml = baseColumns.map(col => `<th>${col.title}</th>`).join('');
                $('#headerRow').html(headerHtml);

                if (table) table.destroy();

                table = $('#leaveTable').DataTable({
                    data: response.data,
                    columns: baseColumns,
                    responsive: true,
                    pageLength: 25,
                    initComplete: function() {
                        $('#loadingContainer').fadeOut(300);
                    }
                });
            },
            error: function() {
                clearInterval(progressInterval);
                $('#loadingBar').removeClass('bg-info').addClass('bg-danger').css('width', '100%').text('Failed to load data');
                setTimeout(() => $('#loadingContainer').fadeOut(300), 2000);
            }
        });
    }

    loadTable();


    loadTable();

    $('#exportExcelBtn').click(function() {
        $('#excelLoader').show();
        window.location.href = "{{ route('backend.reports.vacation_leave_report_export', ['type' => 'excel']) }}";
        setTimeout(() => $('#excelLoader').hide(), 2000);
    });

    $('#exportPdfBtn').click(function() {
        $('#pdfLoader').show();
        window.location.href = "{{ route('backend.reports.vacation_leave_report_export', ['type' => 'pdf']) }}";
        setTimeout(() => $('#pdfLoader').hide(), 2000);
    });
</script>
@endpush
