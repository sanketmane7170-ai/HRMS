@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('uniform_request_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('uniform_request_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{route('backend.admin.apparelRequest.create')}}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i> {{__trans('add_uniform_request')}}
                    </a>
                    <button type="button" class="btn btn-primary dark">
                        <a href="{{ route('backend.apparel.apparelTransaction') }}" style="color: white;">
                            <i class="fa fa-list"></i> {{ __trans('role_adjustment_log') }}
                        </a>
                    </button>
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
                                        <th>{{__trans('user_name')}}</th>
                                        <th>{{__trans('uniform_name')}}</th>
                                        <th>{{__trans('number_of_uniform')}}</th>
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
        ajax: {
            url: window.location.href,
        },
        order: [[0, 'desc']],
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'user.name',
            },
            {
                data: 'apparel.name',
            },
            {
                data: 'number_of_apparel',
            },
            {
                data: 'status',
            },
            {
                data: 'action',
                name: 'action',
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
    // apparel approved
    $(document).on('click', '.reqApproved', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var currentPage = table.page();

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    toastr.success(response.success);
                } else if (response.error) {
                    toastr.error(response.error);
                }
                table.ajax.reload(null, false);
                table.page(currentPage).draw(false); 
            },
            error: function (xhr) {
                toastr.error('An error occurred while processing your request.');
            }
        });
    });

    // apparel reject
    $(document).on('click', '.reqRejected', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    toastr.warning(response.success);
                } else if (response.error) {
                    toastr.error(response.error);
                }
                table.ajax.reload();
            },
            error: function (xhr) {
                toastr.error('An error occurred while processing your request.');
            }
        });
    });
    
    // apparel remove
    $(document).on('click', '.reqRemove', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    toastr.warning(response.success);
                } else if (response.error) {
                    toastr.error(response.error);
                }
                table.ajax.reload();
            },
            error: function (xhr) {
                toastr.error('An error occurred while processing your request.');
            }
        });
    });

</script>
<script>
loadAjaxSelect2();
</script>
@endpush
