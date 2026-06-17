@extends('layouts.backend')

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('user_company_policy')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('user_company_policy_list')}}</li>
                    </ul>
                </div>
                <!-- <div class="col-auto">
                    <a href="{{route('backend.addCompanyPolicy')}}" class="btn btn-primary me-1">
                        <i class="fas fa-plus"></i>
                    </a>
                </div> -->
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
                                        <th>User</th>
                                        <th>Policy</th>
                                        <th>Ack Status</th>
                                        <th>Ack Date</th>
                                        <th>Document</th>
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
            url: "{{ route('backend.userCompanyPolicy') }}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'user_name',
                name: 'user.name'
            },
            {
                data: 'policy_title',
                name: 'policy.title'
            },
            {
                data: 'ack_status',
                orderable: false,
                searchable: false
            },
            {
                data: 'ack_datetime'
            },
            {
                data: 'ack_document',
                orderable: false,
                searchable: false
            },
           
        ]
    });
</script>
@endpush