@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('allowance_list')}} for {{ $department->name }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('Allwances_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">

                    @can('Create Feature')
                    <a href="{{ route('backend.departments.createallowances', $department->id) }}" class="btn btn-primary btn-sm me-1 edit-button" method="GET">
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
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Allowance Type</th>
                                    <th>Amount</th>
                                    <th>Action</th>
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
        order: [
            [0, 'desc']
        ],
        ajax: "{{ route('backend.departments.allowanceslist', $department->id) }}",
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'allowance_name',
                name: 'allowance_name'
            },
            {
                data: 'type',
                name: 'type'
            },
            {
                data: 'allowance_type',
                name: 'allowance_type'
            },
            {
                data: 'amount',
                name: 'amount'
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
