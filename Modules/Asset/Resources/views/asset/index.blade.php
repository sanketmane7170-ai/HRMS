@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('asset_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('asset_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Manage Asset')
                    <a href="{{route('backend.asset-types.index')}}" class="btn btn-primary me-1">
                        <i class="fas fa-plus"></i> {{__trans('assets_type')}}
                    </a>
                    @endcan
                    @can('Manage Asset')
                    <a href="{{route('backend.asset-manufacturers.index')}}" class="btn btn-primary me-1">
                        <i class="fas fa-plus"></i> {{__trans('assets_manufacturer')}}
                    </a>
                    @endcan
                    @can('Create Asset')
                    <a href="{{route('backend.asset.create')}}" class="btn btn-primary edit-button">
                        <i class="fas fa-plus"></i> {{__trans('add_asset')}}
                    </a>
                    @endcan
                    @can('  ')
                    <a href="{{route('backend.asset.assign-user')}}" class="btn btn-primary btn-md edit-button">
                        <i class="fas fa-plus"></i> {{__trans('assign_user')}}
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
                                        <th>{{__trans('serial_number')}}</th>
                                        <th>{{__trans('model')}}</th>
                                        <th>{{__trans('assign_user')}}</th>
                                        <th>{{__trans('type')}}</th>
                                        <th>{{__trans('manufacturer')}}</th>
                                        <th>{{__trans('status')}}</th>
                                        <th>{{__trans('actions')}}</th>
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
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

</div>
@endsection
@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
            url: "{{route('backend.asset.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'unique_id',
            },
            {
                data: 'model',
            },
            {
                data: 'assignUser',
            },
            {
                data: 'type.name',
                name: 'type.name'
            },
            {
                data: 'manufacturer.name',
                name: 'manufacturer.name'
            },
            {
                data: 'status',
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
