@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('salary_change_transaction')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('salary_change_transaction')}}</li>
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
                                        <th>{{__trans('transaction_type')}}</th>
                                        <th>{{__trans('transaction_date')}}</th>
                                        <th>{{__trans('previous_salary')}}</th>
                                        <th>{{__trans('updated_salary')}}</th>
                                        <th>{{__trans('new_salary')}}</th>
                                        <th>{{__trans('description')}}</th>
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
@endsection

@push('scripts')
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.payslip.showSalaryTransaction')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'employee_name',
            },
            {
                data: 'transaction_type',
            },
            {
                data: 'transaction_date',
            },
            {
                data: 'previous_value',
            },
            {
                data: 'updated_value',
            },
            {
                data: 'new_value',
            },
            {
                data: 'description',
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
