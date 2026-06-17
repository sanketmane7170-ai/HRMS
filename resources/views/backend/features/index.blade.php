@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('feature_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('feature_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                   
                    @can('Create Feature')
                    <a href="{{route('backend.features.create')}}" class="btn btn-primary btn-sm me-1 edit-button" method="GET">
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
                                        <th>{{__trans('date')}}</th>
                                        <th>{{__trans('vesrion')}}</th>
                                        <th>{{__trans('feature')}}</th>
                                        <th>{{__trans('url')}}</th>
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
<!-- @include('common.modals.import-modal',['importUrl'=> route('backend.features.import')]) -->
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
            url: "{{route('backend.features.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'version',
                name: 'version'
            },
            {
                data: 'feature',
                render: function (data, row, col) {
                    console.log(data);
                    console.log("row");
                    console.log(row);
                    console.log("col");
                    console.log(col.url);
                        return "<a href='"+col.url+"'>"+data+"</a>";


                    },
                name: 'feature',
            },
            {
                data: 'url',
                name: 'url'
            },
           
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ]
    });
</script>
@endpush
