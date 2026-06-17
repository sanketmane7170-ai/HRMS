@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Increment Criteria List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('Increment Criteria') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Manage Performance Review')
                    <a href="{{ route('incrementcriteria.create') }}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        
        <!-- /Page Header -->

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover text-center" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Min Score</th>
                                        <th>Max Score</th>
                                        <th>Basic %</th>
                                        <th>Housing %</th>
                                        <th>Transport %</th>
                                        <th>Other %</th>
                                        <th>Incentive %</th>
                                        <th>Total Increment %</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('incrementcriteria.index') }}",
        },
        order: [
            [0, 'desc']
        ],
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'label',
                name: 'label'
            },
            {
                data: 'min_score',
                name: 'min_score'
            },
            {
                data: 'max_score',
                name: 'max_score'
            },
            {
                data: 'basic_percent',
                name: 'basic_percent'
            },
            {
                data: 'housing_percent',
                name: 'housing_percent'
            },
            {
                data: 'transport_percent',
                name: 'transport_percent'
            },
            {
                data: 'other_percent',
                name: 'other_percent'
            },
            {
                data: 'incentive_percent',
                name: 'incentive_percent'
            },
            {
                data: 'increment_percent',
                name: 'increment_percent'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
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