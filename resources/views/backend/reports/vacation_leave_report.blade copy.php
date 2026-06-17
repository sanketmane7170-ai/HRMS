@extends('layouts.backend')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-6 col-sm-12">
                        <h3 class="page-title">{{ __trans('vacation_leave_report') }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                            <li class="breadcrumb-item active">{{ __trans('vacation_leave_report') }}</li>
                        </ul>
                    </div>

                    <div class="col-md-6 col-sm-12 d-flex align-items-end justify-content-end gap-2">
                        <div>
                            <input type="month" id="monthFilter" class="form-control" value="{{ date('Y-m') }}">
                        </div>
                        <button id="exportExcelBtn" class="btn btn-success mt-4">
                            Export to Excel
                            <span id="excelLoader" class="spinner-border spinner-border-sm ms-2"
                                style="display:none;"></span>
                        </button>
                        <button id="exportPdfBtn" class="btn btn-danger mt-4">
                            Export to PDF
                            <span id="pdfLoader" class="spinner-border spinner-border-sm ms-2" style="display:none;"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table text-center table-hover" id="leaveTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Employee ID</th>
                                            <th>Employee Name</th>
                                            <th>Department</th>
                                            <th>Locaton</th>
                                            <th>Designation</th>
                                            <th>Join Date</th>
                                            <th>Leave Policy</th>
                                            <th>Annual Leave</th>
                                            <th>Initial Balance</th>
                                            <th>Initial Balance Date</th>
                                            <th>Montly/Annual leave</th>
                                            <th>Total Month</th>
                                            <th>Total Leave</th>
                                            <th>Used Leave</th>
                                            <th>Balance Leave</th>

                                        </tr>
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
        var table = $('#leaveTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('backend.reports.vacation_leave_report') }}",
                data: function (d) {
                    d.month = $('#monthFilter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'employee_id', name: 'employee_id' },
                { data: 'employee_name', name: 'employee_name' },
                { data: 'department_name', name: 'department_name' },
                { data: 'location', name: 'location' },
                { data: 'designation', name: 'designation' },
                { data: 'join_date', name: 'join_date' },
                { data: 'policy_type', name: 'policy_type' },
                { data: 'annual_leave', name: 'annual_leave' },
                { data: 'initial_balance', name: 'initial_balance' },
                { data: 'initial_balance_date', name: 'initial_balance_date' },
                { data: 'monthly_or_annual', name: 'monthly_or_annual' },
                { data: 'total_month', name: 'total_month' },
                { data: 'total_leave', name: 'total_leave' },
                { data: 'used_leave', name: 'used_leave' },
                { data: 'balance_leave', name: 'balance_leave' },
            ],

            columnDefs: [
                { targets: [0, 4, 5, 6], className: 'text-center' },
            ],
            responsive: true,
            pageLength: 10,
        });

        $('#monthFilter').change(function () {
            table.ajax.reload();
        });

        $('#exportExcelBtn').click(function () {
            $('#excelLoader').show();
            var month = $('#monthFilter').val();
            var url = "{{ route('backend.reports.vacation_leave_report_export', ['type' => 'excel']) }}";
            window.location.href = url + "?month=" + month;
            setTimeout(() => $('#excelLoader').hide(), 3000);
        });

        $('#exportPdfBtn').click(function () {
            $('#pdfLoader').show();
            var month = $('#monthFilter').val();
            var url = "{{ route('backend.reports.vacation_leave_report_export', ['type' => 'pdf']) }}";
            window.location.href = url + "?month=" + month;
            setTimeout(() => $('#pdfLoader').hide(), 3000);
        });
    </script>
@endpush
