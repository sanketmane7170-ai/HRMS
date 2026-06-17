@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('expense_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('expense_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create Expense')
                    <a href="{{route('backend.expense.create')}}" class="btn btn-primary me-1 edit-button">
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
                                        <th>{{__trans('creator_name')}}</th>
                                        <th>{{__trans('employee_name')}}</th>
                                        <th>{{__trans('date')}}</th>
                                        <th>{{__trans('type')}}</th>
                                        <th>{{__trans('name')}}</th>
                                        <th>{{__trans('payment_mode')}}</th>
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
            url: "{{route('backend.expense.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'creator.name',
                orderable: false,
                // searchable: false,
                render: function(data, type, row) {
                    return data && data ? data : ""; // Check if creator and creator.name exist
                }
            },
            {
                data: 'user.name',
                // name: 'user.name'
                render: function(data, type, row) {
                    return data && data ? data : ""; // Check if creator and creator.name exist
                },
                orderable: false,
                // searchable: false,
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'type.name',
                name: 'type.name',
                orderable: false,
                // searchable: false,
            },
            {
                data: 'name',
                name: 'name',
            },
            {
                data: 'payment_mode',
                name: 'payment_mode',
            },
            {
                data: 'status',
                name: 'status',
            },
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