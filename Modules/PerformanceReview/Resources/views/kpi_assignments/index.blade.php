@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('KPI Assignments') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('KPI Assignment List') }}</li>
                    </ul>
                </div>
                {{-- Add a button if you need to manually assign KPI --}}
                {{-- <div class="col-auto">
                    @can('Manage Performance Review')
                        <a href="#" class="btn btn-primary me-1">
                            <i class="fas fa-plus"></i> {{ __trans('Assign KPI') }}
                </a>
                @endcan
            </div> --}}
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
                                    <th>{{ __trans('Employee Name') }}</th>
                                    <th>{{ __trans('Email') }}</th>
                                    <th>{{ __trans('Duration') }}</th>
                                    <th>{{ __trans('Assigned On') }}</th>
                                    <th>{{ __trans('Status') }}</th>
                                    <th>{{ __trans('Grade') }}</th>
                                    <th>{{ __trans('Remark') }}</th>
                                    <th>{{ __trans('Action') }}</th>

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
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [
            [0, 'desc']
        ],
        ajax: {
            url: "{{ route('kpi.assignments.index') }}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'employee_name',
                name: 'user.name'
            },
            {
                data: 'email',
                name: 'user.email'
            },
            {
                data: 'duration_label',
                name: 'duration.label'
            },
            {
                data: 'assigned_on',
                name: 'created_at'
            },
            {
                data: 'status',
                name: 'status'
            }, {
                data: 'grade',
                name: 'grade'
            }, {
                data: 'remark',
                name: 'remark'
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }

        ]
    });

    flatpickr("input.datetime", {
        dateFormat: "Y-m-d",
    });
</script>
@endpush