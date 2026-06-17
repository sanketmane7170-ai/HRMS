@extends('layouts.backend')

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('user_increment_letters_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.user-increment')}}">{{__trans('user-increment-type')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('user_increment_letter')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create Document Type')
                    <a href="{{route('backend.user_increment_letter.create')}}" class="btn btn-primary me-1">
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
                                        <th>{{__trans('user_name')}}</th>
                                        <th>{{__trans('department_name')}}</th>
                                        <th>{{__trans('letter_type')}}</th>
                                        <th>{{__trans('amount')}}</th>
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
        ajax: {
            url: "{{route('backend.user_increment_letter')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'name', name: 'users.name'
            },
            {
                data: 'department_name',
                searchable: false,
                orderable: false,
            },
            {
                data: 'letter_type',
                searchable: false,
                orderable: false,
            },
            {
                data: 'amount',
                searchable: false,
                orderable: false,
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
