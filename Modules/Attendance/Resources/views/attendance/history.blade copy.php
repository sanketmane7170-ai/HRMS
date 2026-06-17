@extends('layouts.backend')

@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <form action="{{route('backend.payslip.user-payslip.store')}}" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ __trans('attendance_history') }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{__trans('attendance')}}</li>
                            <li class="breadcrumb-item active">{{ __trans('checkin_history') }}</li>
                        </ul>
                    </div>
            </form>
        </div>
        <form action="{{route('backend.payslip.user-payslip.index')}}" method="GET"
            id="select-month-dropdown select-year-dropdown" class="ajax-form-submit reset">
            @csrf
            <div class="row align-items-center">
                <div class="col">
                    <h5>{{ __trans('employee_checkin_history') }}: {{ $userName }}</h5>
                </div>
            </div>
        </form>
    </div>

    <!-- /Page Header -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-table">
                <div class="card-body">
                    <div class="table-responsive">
                        <button id="exportButton" type="submit" class="btn btn-success">
                            <i class="fa fa-download"></i>
                        </button>
                        <table class="table text-center table-hover" id="dataTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{__trans('date')}}</th>
                                    <th>{{__trans('time')}}</th>
                                    <th>{{__trans('type')}}</th>
                                    <th>{{__trans('mode')}}</th>
                                    <th>{{__trans('checkout_reason')}}</th>
                                    <th>{{__trans('is_rider')}}</th>
                                    <th>{{__trans('location')}}</th>
                                    <!-- <th>{{__trans('latitude')}}</th> -->
                                    <!-- <th>{{__trans('longitude')}}</th> -->
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
<div class="modal" id="editModal"></div>
<!-- /Page Wrapper -->
@endsection
@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<!-- <script>
    loadAjaxSelect2();
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.user.attendance.history',[$user])}}",
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'date',
            },
            {
                data: 'time',
                name: 'time',
            },
            {
                data: 'type',
                name: 'type',
            },
            {
                data: 'mode',
                name: 'mode',
            },
            {
                data: 'checkout_reason',
                name: 'checkout_reason',
            },
            {
                data: 'is_rider',
                name: 'is_rider',
            },
            {
                data: 'location',
                name: 'location',
            },
            // {
            //     data: 'latitude',
            //     name: 'latitude',
            // },
            // {
            //     data: 'longitude',
            //     name: 'longitude',
            // },
        ]
    });
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/5.2.0/js/tableexport.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize the DataTable

        $("#exportButton").click(function() {
            // Fetch all data from the server
            $.ajax({
                url: "{{route('backend.user.attendance.history',[$user])}}",
                method: "GET",
                success: function(response) {
                    // Extract data from response
                    const data = response.data.map(row => ({
                        ID: stripHtml(row.id),
                        Date: row.date,
                        Time: row.time,
                        Type: row.type,
                        Mode: row.mode,
                        CheckoutReason: row.checkout_reason,
                        IsRider: row.is_rider,
                        Location: row.location,
                    }));

                    // Convert data to a worksheet
                    const worksheet = XLSX.utils.json_to_sheet(data);

                    // Create a workbook and append the worksheet
                    const workbook = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(workbook, worksheet, "Attendance");

                    // Export the workbook to a file
                    XLSX.writeFile(workbook, "attendance_data.xlsx");
                },
                error: function(error) {
                    alert("Failed to fetch data for export!");
                    console.error(error);
                }
            });
        });

        
    });
</script> -->

<script>
    function stripHtml(html) {
        var div = document.createElement("div");
        div.innerHTML = html;
        return div.textContent || div.innerText || "";
    }
</script>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- PDFMake (for PDF export) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- SheetJS (for Excel export) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>


<script>
    $(document).ready(function() {
        var table = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('backend.user.attendance.history', [$user])}}",
            },
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'time',
                    name: 'time'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'mode',
                    name: 'mode'
                },
                {
                    data: 'checkout_reason',
                    name: 'checkout_reason'
                },
                {
                    data: 'is_rider',
                    name: 'is_rider'
                },
                {
                    data: 'location',
                    name: 'location'
                },
            ],
            dom: 'Bfrtip',
            buttons: [{
                    text: 'Export All to Excel',
                    action: function(e, dt, button, config) {
                        // Fetch all data from the server
                        $.ajax({
                            url: "{{route('backend.user.attendance.history', [$user])}}",
                            method: "GET",
                            data: {
                                length: -1
                            }, // Request all records
                            success: function(response) {
                                const allData = response.data.map(row => ({
                                    ID: stripHtml(row.id),
                                    Date: row.date,
                                    Time: row.time,
                                    Type: row.type,
                                    Mode: row.mode,
                                    CheckoutReason: row.checkout_reason,
                                    IsRider: row.is_rider,
                                    Location: row.location,
                                }));

                                // Convert data to worksheet
                                const worksheet = XLSX.utils.json_to_sheet(allData);

                                // Create workbook and append worksheet
                                const workbook = XLSX.utils.book_new();
                                XLSX.utils.book_append_sheet(workbook, worksheet, "Attendance Data");

                                // Export to Excel file
                                XLSX.writeFile(workbook, "attendance_data.xlsx");
                            },
                            error: function(xhr, status, error) {
                                alert("Failed to fetch all data for export.");
                                console.error(error);
                            }
                        });
                    }
                },
                {
                    text: 'Export All to PDF',
                    action: function(e, dt, button, config) {
                        // Fetch all data from the server
                        $.ajax({
                            url: "{{route('backend.user.attendance.history', [$user])}}",
                            method: "GET",
                            data: {
                                length: -1
                            }, // Request all records
                            success: function(response) {
                                const allData = response.data.map(row => [
                                    stripHtml(row.id),
                                    row.date,
                                    row.time,
                                    row.type,
                                    row.mode,
                                    row.checkout_reason,
                                    row.is_rider,
                                    row.location,
                                ]);

                                // Define PDF document structure
                                const docDefinition = {
                                    content: [{
                                            text: 'Attendance Data',
                                            style: 'header'
                                        },
                                        {
                                            table: {
                                                headerRows: 1,
                                                body: [
                                                    ['ID', 'Date', 'Time', 'Type', 'Mode', 'Checkout Reason', 'Is Rider', 'Location'], // Headers
                                                    ...allData, // Data rows
                                                ],
                                            },
                                        },
                                    ],
                                };

                                // Export to PDF
                                pdfMake.createPdf(docDefinition).download('attendance_data.pdf');
                            },
                            error: function(xhr, status, error) {
                                alert("Failed to fetch all data for export.");
                                console.error(error);
                            }
                        });
                    }
                }
            ],
        });
    });
</script>


@endpush