@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Employee Performance Appraisal List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('Employee Performance Appraisal List') }}</li>
                    </ul>
                </div>
                <!-- <div class="col-auto">
                    @can('Create Performance')
                        <a href="{{ route('performance.create') }}" class="btn btn-primary me-1 edit-button">
                            <i class="fas fa-plus"></i>
                        </a>
                        <a href="{{ route('performance.exportPdf') }}" class="btn btn-primary me-1">
                            Export Report
                        </a>
                    @endcan
                </div> -->
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
                                        <!-- <th>{{ __trans('employee') }}</th> -->
                                        <th>{{ __trans('reviewer') }}</th>
                                        <!-- <th>{{ __trans('period') }}</th> -->
                                         <th>{{ __trans('appraisal_date') }}</th> 
                                        <th>{{ __trans('status') }}</th>
                                        <th>{{ __trans('actions') }}</th>
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
<!-- /Page Wrapper -->
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true"></div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/backend/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/backend/plugins/flatpickr/flatpickr.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
            url: "{{ route('backend.employee.performancelist') }}",
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id' },
            // { data: 'employee_name', name: 'employee.name' },
            { data: 'reviewer_name', name: 'reviewer.name' },
            // { data: 'period', name: 'period' },
            { data: 'appraisal_date', name: 'appraisal_date' },
            { data: 'status', name: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    flatpickr("input.datetime", {
        dateFormat: "Y-m-d",
    });
</script>
<script>
    loadAjaxSelect2();
</script>
@endpush