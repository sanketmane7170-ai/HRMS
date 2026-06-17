@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- <div class="page-header">
            <h3 class="page-title">{{ __trans('Initial Leave Balance Report') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __trans('Initial Leave Balance Report') }}</li>
            </ul>
        </div> -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6 col-sm-12">
                    <h3 class="page-title">{{ __trans('Initial Leave Balance Report') }}</h3>
                </div>
                <div class="col-md-6 col-sm-12 d-flex align-items-end justify-content-end gap-2">
                    <!-- ✅ Import Modal Trigger -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-leave-modal">
                        Update Initial Leave from Import
                    </button>

                </div>
            </div>
        </div>


        <div class="card card-table">
            <div class="card-body">
                <div class="table-responsive">

                    <!-- ✅ Progress Bar (Same as Vacation Report) -->
                    <div id="loadingContainer" class="text-center my-4" style="display:block;">
                        <div class="progress" style="height: 25px; max-width: 400px; margin: 0 auto;">
                            <div id="loadingBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                role="progressbar" style="width: 0%">Loading...</div>
                        </div>
                        <p class="mt-2 text-muted">Fetching initial leave balance data, please wait...</p>
                    </div>

                    <table id="initialBalanceTable" class="table table-hover text-center">
                        <thead class="thead-light">
                            <tr id="headerRow"></tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="edit-leave-modal" class="modal" role="dialog" aria-labelledby="importModal" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__trans('initial_leave_update_from_import_excel')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('backend.reports.initial_leave_balance.import') }}" datatable="true" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__trans('Upload_excel_file')}} ( <a href="{{ route('backend.reports.initial_leave_balance.export') }}"> {{__trans('download_sample')}}</a> )</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                        <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table;

    function loadInitialBalanceReport() {
        // ✅ Show animated progress bar
        $('#loadingContainer').show();
        let progress = 0;
        let progressInterval = setInterval(() => {
            progress = Math.min(progress + Math.random() * 10, 90); // animate until 90%
            $('#loadingBar').css('width', progress + '%');
        }, 300);

        $.ajax({
            url: "{{ route('backend.reports.initial_leave_balance.data') }}",
            success: function(response) {
                clearInterval(progressInterval);
                $('#loadingBar').css('width', '100%').text('Rendering table...');

                let leaveTypes = response.leave_types;

                // Base employee columns
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
                        data: 'designation',
                        title: 'Designation'
                    },
                    {
                        data: 'join_date',
                        title: 'Join Date'
                    },
                    {
                        data: 'initial_balance_date',
                        title: 'Initial Balance Date'
                    },
                ];

                // Add dynamic leave type columns
                leaveTypes.forEach(type => {
                    baseColumns.push({
                        data: type,
                        title: type,
                        defaultContent: '0'
                    });
                });

                // Build table header
                let headerHtml = baseColumns.map(col => `<th>${col.title}</th>`).join('');
                $('#headerRow').html(headerHtml);

                // Destroy previous instance
                if (table) table.destroy();

                // Initialize DataTable
                table = $('#initialBalanceTable').DataTable({
                    data: response.data,
                    columns: baseColumns,
                    responsive: true,
                    pageLength: 25,
                    initComplete: function() {
                        $('#loadingContainer').fadeOut(400);
                    }
                });
            },
            error: function() {
                clearInterval(progressInterval);
                $('#loadingBar')
                    .removeClass('bg-info')
                    .addClass('bg-danger')
                    .css('width', '100%')
                    .text('Failed to load data');
                setTimeout(() => $('#loadingContainer').fadeOut(400), 2000);
            }
        });
    }

    // Load on page ready
    loadInitialBalanceReport();
</script>
@endpush
