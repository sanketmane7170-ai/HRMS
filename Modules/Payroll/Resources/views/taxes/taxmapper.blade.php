@extends('layouts.backend')

@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('taxes_mapping_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('taxes_mapping_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.payroll.employeeTaxUsers.create') }}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i> {{ __trans('employee_mapping') }}
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
                                        <th>#</th>
                                        <th>{{__trans('User')}}</th>
                                        <th>{{__trans('Tax Type')}}</th>
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
<div id="editModal" class="modal"></div>

@endsection
@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.payroll.employeeTaxUsers.index')}}",
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'user_id',
                name: 'User',
            },
            {
                data: 'employee_tax_id',
                name: 'EmployeeTax',
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