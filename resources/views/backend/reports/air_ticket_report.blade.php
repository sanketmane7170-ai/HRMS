@extends('layouts.backend')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6 col-sm-12">
                    <h3 class="page-title">{{ __trans('airticket_report') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('airticket_report') }}</li>
                    </ul>
                </div>


                <div class="col-md-6 col-sm-12 d-flex align-items-end justify-content-end gap-2">
                    <div>
                        <input type="month" id="monthFilter" class="form-control" value="{{ date('Y-m') }}">
                    </div>
                    <button type="button" id="exportExcelBtn" class="btn btn-success mt-4">
                        Export to Excel
                        <span id="excelLoader" class="spinner-border spinner-border-sm ms-2" style="display:none;" role="status"></span>
                    </button>
                    <button type="button" id="exportPdfBtn" class="btn btn-danger mt-4">
                        Export to PDF
                        <span id="pdfLoader" class="spinner-border spinner-border-sm ms-2" style="display:none;" role="status"></span>
                    </button>
                </div>

            </div>
        </div>

        <!-- /Page Header -->

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __trans('employee') }}</th>
                                        <th>{{ __trans('department_name') }}</th>
                                        <th>{{ __trans('date') }}</th>
                                        <th>{{ __trans('amount') }}</th>
                                        <th>{{ __trans('quantity') }}</th>
                                        <th>{{ __trans('total_amount') }}</th>

                                        <th class="text-left">{{ __trans('details') }}</th> <!-- new last column -->
                                        <th>{{ __trans('status') }}</th>
                                        <th>{{ __trans('approval_date') }}</th>
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
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('backend.reports.air_ticket_report') }}",
            data: function(d) {
                d.month = $('#monthFilter').val(); // pass selected month
            },
            beforeSend: function() {
                $('#loader').show(); // show loader before ajax request
            },
            complete: function() {
                $('#loader').hide(); // hide loader after ajax completes
            }
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'department',
                name: 'department'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'amount',
                name: 'amount'
            },
            {
                data: 'quantity',
                name: 'quantity'
            },
            {
                data: 'total_amount',
                name: 'total_amount'
            },
            {
                data: 'details',
                name: 'details',
                defaultContent: '-',
                orderable: false,
                searchable: false
            },
            { data: 'status', name: 'status' }, 
            { data: 'approve_date', name: 'approve_date' }, 

        ],
        columnDefs: [{
                targets: [0, 3, 4, 5,7],
                className: 'text-center'
            }, // center index, date, amount, quantity
            {
                targets: [6],
                className: 'text-left'
            } // details left aligned
        ],
        // optional: responsive, pageLength, etc.
        responsive: true,
        pageLength: 10,
    });
    $('#monthFilter').change(function() {
        table.ajax.reload(); // reload table when month changes
    });
    // $('#exportExcelBtn').click(function() {
    //     var month = $('#monthFilter').val(); // e.g. 2025-11
    //     var url = "{{ route('backend.reports.air_ticket_report_export', ['type' => 'excel']) }}";
    //     window.location.href = url + "?month=" + month;
    // });

    // $('#exportPdfBtn').click(function() {
    //     var month = $('#monthFilter').val();
    //     var url = "{{ route('backend.reports.air_ticket_report_export', ['type' => 'pdf']) }}";
    //     window.location.href = url + "?month=" + month;
    // });
    $('#exportExcelBtn').click(function() {
        $('#excelLoader').show();
        var month = $('#monthFilter').val();
        var url = "{{ route('backend.reports.air_ticket_report_export', ['type' => 'excel']) }}";
        // show spinner
        console.log('Exporting to Excel for month:', month);
        console.log('URL:', url + "?month=" + month);
        window.location.href = url + "?month=" + month;
        setTimeout(function() {
            $('#excelLoader').hide();
        }, 3000);

    });

    $('#exportPdfBtn').click(function() {
        $('#pdfLoader').show(); // show spinner

        var month = $('#monthFilter').val();
        var url = "{{ route('backend.reports.air_ticket_report_export', ['type' => 'pdf']) }}";
        window.location.href = url + "?month=" + month;
        setTimeout(function() {
            $('#pdfLoader').hide();
        }, 3000);

    });
</script>



@endpush
