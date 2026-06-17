@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('user_settlement_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('user_settlement_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                <a href="{{route('backend.settlement.export')}}" class="btn btn-success btn-sm me-1">
                        <i class="fas fa-file-excel"></i> {{__trans('export')}}
                    </a>
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
                                        <th>{{__trans('name')}}</th>
                                        <th>{{__trans('hire_date')}}</th>
                                        <th>{{__trans('departure_date')}}</th>
                                        <th>{{__trans('total_service_duration')}}</th>
                                        <th>{{__trans('departure_reason')}}</th>
                                        <th>{{__trans('contract_type')}}</th>
                                        <th>{{__trans('settlement_amount')}}</th>
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
            url: "{{route('backend.settlement.transaction')}}",
        },
        columns: [
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'hire_date',
                name: 'hire_date',
            },
            {
                data: 'departure_date',
                name: 'departure_date',
            },
            {
                data: 'total_service_duration',
                name: 'total_service_duration',
            },
            {
                data: 'departure_reason',
                name: 'departure_reason',
            },
            {
                data: 'contract_type',
                name: 'contract_type',
            },
            {
                data: 'settlement_amount',
                name: 'settlement_amount',
            },
        ]
    });
</script>
@endpush
