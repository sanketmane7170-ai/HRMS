@extends('layouts.backend')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('warning_list') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('warning_list') }}</li>
                    </ul>
                </div>
                <div class="col-auto">

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
                                        <th>{{ __trans('date') }}</th>
                                        <th>{{ __trans('waring_type') }}</th>
                                        <th>{{ __trans('detail') }}</th>
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
@endsection

@push('scripts')
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('backend.employee.user-warnings.index') }}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'date'
            },
            {
                data: 'type'
            },
            {
                data: 'detail'
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            },
        ]
    });
</script>
@endpush
