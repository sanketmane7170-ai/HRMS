@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('my_assets')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('my_assets')}}</li>
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
                                        <th>{{__trans('manufacturer')}}</th>
                                        <th>{{__trans('type')}}</th>
                                        <th>{{__trans('unique_id')}}</th>
                                        <th>{{__trans('model')}}</th>
                                        <th>{{__trans('issue_date')}}</th>
                                        <th>{{__trans('return_date')}}</th>
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
        ajax: {
            url: "{{route('backend.employee.assets.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'asset.manufacturer.name',
                name: 'asset.manufacturer.name'
            },
            {
                data: 'asset.type.name',
                name: 'asset.type.name'
            },
            {
                data: 'asset.unique_id',
                name: 'asset.unique_id'
            },
            {
                data: 'asset.model',
                name: 'asset.model'
            },
            {
                data: 'issue_date',
            },
            {
                data: 'return_date',
            }
        ]
    });
</script>
@endpush
