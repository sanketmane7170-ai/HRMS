@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('expired_document_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('expired_document_list')}}</li>
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
                                        <th>{{__trans('employee')}}</th>
                                        <th>{{__trans('department')}}</th>
                                        <th>{{__trans('document')}}</th>
                                        <th>{{__trans('expiry_date')}}</th>
                                        <th>{{__trans('action')}}</th>
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
            url: "{{route('backend.analytic.document.expired.list')}}",
        },
        // order: [
        //     [4, 'asc']
        // ],
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'user.name',
                name: 'user.name'
            },
            {
                data: 'user.department.name',
                name: 'user.department.name',
            },
            {
                data: 'type'
            }, 
            // {
            //     data: 'expiry_date'
            // }, 
              {
                data: 'expiry_date',
                name: 'expiry_date',
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }

        ]
    });
</script>
@endpush