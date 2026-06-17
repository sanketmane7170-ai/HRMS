@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('employee_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('employee_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Edit User')
                    <a href="{{route('backend.users.send-welcome-notification-to-all')}}"
                        class="btn btn-sm btn-success action-button" method="POST"
                        data-alert="{{__trans('are_you_sure_want_to_send_welcome_notification?')}}">
                        {{__trans('send_welcome_notification_to_all')}}
                    </a>

                    @endcan
                    @can('Import User')


                    <a href="#" class="btn btn-info btn-sm me-1 " data-bs-target="#edit-import-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('update_user_form_import')}}
                    </a>

                    <a href="#" class="btn btn-warning btn-sm me-1 " data-bs-target="#import-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import')}}
                    </a>

                    <a href="#" class="btn btn-warning btn-sm me-1 " data-bs-target="#importmedicalpremium-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import_medical_premium')}}
                    </a>
                    @endcan

                </div>
                <div class="col-auto pe-md-1">
                    @can('Export User')
                    <div class="dropdown dropdown-action mb-0">
                        <a href="#" class="dropdown-toggle btn btn-success btn-sm" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-file-excel"></i> Export</a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{route('backend.users.export.pdf')}}"><img src="{{asset('assets/backend/img/icon-pdf.svg')}}" class="img-fluid" /> {{__trans('export_pdf')}}</a>
                            <a class="dropdown-item" href="{{route('backend.users.export.excel')}}"><img src="{{asset('assets/backend/img/icon-excel.svg')}}" class="img-fluid" /> {{__trans('export_excel')}}</a>
                            <a class="dropdown-item" href="{{route('backend.users.export.master')}}"><img src="{{asset('assets/backend/img/icon-excel.svg')}}" class="img-fluid" /> Master Sheet Export</a>
                            <!-- <a class="dropdown-item" href="{{route('backend.sample.export.excel')}}"><img src="{{asset('assets/backend/img/icon-excel.svg')}}" class="img-fluid" /> {{__trans('export_sample')}}</a> -->
                        </div>
                    </div>
                    @endcan
                </div>
                <div class="col-auto">
                    <!-- @can('Export User')
                    <a href="{{route('backend.users.export.excel')}}" title="{{__trans('export_excel')}}" class="btn btn-outline-primary me-1">
                        <i class="fas fa-file"></i>
                    </a>
                    <a href="{{route('backend.users.export.pdf')}}" title="{{__trans('export_pdf')}}" class="btn btn-outline-primary me-1">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                    @endcan -->

                    @can('Create User')
                    <a href="{{route('backend.users.create')}}" title="{{__trans('create_employee')}}" class="btn btn-primary">
                        + New Employee
                    </a>
                    @endcan
                </div>
            </div>
        <!-- Search Filter -->
        <div class="row filter-row pb-4">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label>{{__trans('department')}}</label>
                    <select name="department_id[]" id="department" class="form-control select-search" multiple>
                        @foreach (\App\Models\Department::all() as $department)
                        <option value="{{ $department->id }}"
                            @if(is_array($departmentId) && in_array($department->id, $departmentId)) selected @endif>
                            {{ $department->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label>{{__trans('status')}}</label>
                    <select name="statuses[]" id="status_filter" class="form-control select-search" multiple>
                        <option value="all" selected>{{__trans('all')}}</option>
                        <option value="active">{{__trans('active')}}</option>
                        <option value="in-active">{{__trans('in-active')}}</option>
                        <option value="resigned">{{__trans('resigned')}}</option>
                        <option value="terminated">{{__trans('terminated')}}</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- /Search Filter -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card ">
                    <div class="card-table">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-center table-hover" id="dataTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{__trans('name')}}</th>
                                            <th>{{__trans('email')}}</th>
                                            <th>{{__trans('phone')}}</th>
                                            <th>{{__trans('status')}}</th>
                                            <th>{{__trans('branch')}}</th>
                                            <th>{{__trans('department')}}</th>
                                            <th>{{__trans('designation')}}</th>
                                            <th>{{__trans('role')}}</th>
                                            <th>{{__trans('view_photo')}}</th>
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

    <div class="modal fade" id="viewProfile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">&nbsp;</h5>
                    <a href="#" onclick="closeModal()">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="profile" width="300px" height="100%;">
                </div>
            </div>
        </div>
    </div>
    <div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

    </div>
    <!-- /Page Wrapper -->
    @include('common.modals.edit-import-modal',['importUrl'=> route('backend.updateEmpToExcel'),'flag'=>true])
    @include('common.modals.import-modal',['importUrl'=> route('backend.users.import.excel'),'flag'=>true])
    @include('common.modals.importmedicalpremium-modal',['importUrl'=> route('backend.users.importmedicalpremium.excel'),'flag'=>true])
    @endsection

    @push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            var table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: window.location.href,
                    data: function(d) {
                        d.department_ids = $('#department').val();
                        d.statuses = $('#status_filter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name',searchable: true },
                    { data: 'email', name: 'email',searchable: true },
                    { data: 'phone', name: 'phone',searchable: true },
                    { data: 'status', orderable: false, searchable: false },
                    {
                        data: 'department',
                        name: 'department_name',
                        searchable: true,
                        render: function (data, type, row) {
                            return row.department ? row.department.name : (row.department_name ?? '');
                        }
                    },
                    {
                        data: 'department',
                        name: 'division_name',
                        searchable: true,
                        render: function (data, type, row) {
                            return row.divisions ? row.divisions.name : (row.division_name ?? '');
                        }
                    },
                    {
                        data: 'designation',
                        name: 'designation_name',
                        searchable: true,
                        render: function (data, type, row) {
                            return row.designations ? row.designations.name : (row.designation_name ?? '');
                        }
                    },
                    {
                        data: 'roles',
                        name: 'role_name',
                        orderable: true,
                        searchable: true
                    },
                    { data: 'ai_photo', name: 'ai_photo', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false },
                ]
            });
            $('#department, #status_filter').on('change', function() {
                table.ajax.reload();
            });
            $('#department').select2({
                placeholder: "Select Department(s)"
            });
            $('#status_filter').select2({
                placeholder: "Select Status(es)"
            });
        });
    </script>
    <script>
        function openProfile(src) {
            $('#profile').attr('src', src);
            $('#viewProfile').modal('show');
        }

        function closeModal(src) {
            $('#viewProfile').modal('hide');
        }
    </script>
    @endpush
