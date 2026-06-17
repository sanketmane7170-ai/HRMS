@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('company_document_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('company_document_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create CompanyDocument')
                    <a href="{{route('backend.companydocument.create')}}" class="btn btn-primary me-1 edit-button">
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
                                        <th>{{__trans('legal_trade_name')}}</th>
                                        <th>{{__trans('short_name')}}</th>
                                        <th>{{__trans('license_number')}}</th>
                                        <th>{{__trans('license_expiry')}}</th>
                                        <th>{{__trans('added_date')}}</th>
                                        <th>{{__trans('mol_code')}}</th>
                                        <th>{{__trans('employer_reference')}}</th>
                                        <th>{{__trans('routing_number')}}</th>
                                        <!-- <th>{{__trans('document')}}</th> -->
                                        <!-- <th>{{__trans('status')}}</th> -->
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

@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush

@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [
            [0, 'desc']
        ],
        ajax: {
            url: "{{route('backend.companydocument.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            
            {
                data: 'legal_trade_name',
                name: 'legal_trade_name'
            },
            {
                data: 'short_name',
                name: 'short_name'
            },
            {
                data: 'license_number',
                name: 'license_number',
            },
            {
                data: 'license_expiry',
                name: 'license_expiry',
            },
            {
                data: 'added_date',
                name: 'added_date',
            },
            {
                data: 'mol_code',
                name: 'mol_code',
            },
             {
                data: 'employer_reference',
                name: 'employer_reference',
            },
             {
                data: 'routing_number',
                name: 'routing_number',
            },
            // {
            //     data: 'status',
            //     name: 'status',
            // },
            {
                data: 'action',
                orderable: false,
                searchable: false
            },
        ]
    });
    flatpickr("input.datetime", {
        //enableTime: true,
        // maxDate: today,
        dateFormat: "Y-m-d",
    });
</script>
<script>
    loadAjaxSelect2();
</script>
@endpush