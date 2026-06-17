@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Question Set List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('Question Set List') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Manage Performance Review')
                        <a href="{{ route('questionset.create') }}" class="btn btn-primary me-1 edit-button">
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
                                        <th>{{ __trans('Name') }}</th>
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
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
            url: "{{ route('questionset.index') }}",
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id' },
            { data: 'name', name: 'name' },
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
