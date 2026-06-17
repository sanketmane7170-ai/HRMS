@extends('layouts.backend')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Appraisal Templates') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('Appraisal Templates') }}</li>
                    </ul>
                </div>

                <div class="col-auto">
                    @can('Create Performance')
                        <a href="{{ route('performance.template.create') }}"
                           class="btn btn-primary me-1 edit-button">
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
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __trans('Template Name') }}</th>
                                        <th>{{ __trans('Period Type') }}</th>
                                        <th>{{ __trans('Branch') }}</th>
                                        <th>{{ __trans('Department') }}</th>
                                        <th>{{ __trans('Status') }}</th>
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

<div id="editModal" class="modal" role="dialog"></div>

@endsection

@push('scripts')
<script>
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('performance.template.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'period_type', name: 'period_type' },
            { data: 'branch', name: 'branch' },
            { data: 'department', name: 'department' },
            { data: 'status', name: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });
</script>
@endpush
