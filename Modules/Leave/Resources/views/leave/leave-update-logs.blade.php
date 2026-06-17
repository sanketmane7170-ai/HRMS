@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('leave_balance_logs')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('leave_balance_logs')}}</li>
                    </ul>
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
                                        <th>{{__trans('employee_name')}}</th>
                                        <th>{{__trans('leave_type')}}</th>
                                        <th>{{__trans('previous_balance')}}</th>
                                        <th>{{__trans('updated_balance')}}</th>
                                        <th>{{__trans('updated_by')}}</th>
                                        <th>{{__trans('updated_at')}}</th>
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
            url: "{{route('backend.leave-balance.update.logs')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'employee_name',
            },
            {
                data: 'leave_type',
            },
            {
                data: 'previous_balance',
            },
            {
                data: 'updated_balance',
            },
            {
                data: 'updated_by',
            },
            {
                data: 'updated_at',
            }
        ]
    });
</script>
<script>
loadAjaxSelect2();
</script>
@endpush
