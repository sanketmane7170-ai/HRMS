@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('assign_shift')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('shift_scheduler')}}</li>
                    </ul>
                </div>
                <div class="col-auto">

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
                                        <th>{{__trans('name')}}</th>
                                        <th>{{__trans('department')}}</th>
                                        <th>{{__trans('assign_shift')}}</th>
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
<div id="editModal" class="modal">

</div>
@endsection
@push('scripts')

<script>
  loadAjaxSelect2();
  initselect2();
  flatpickr("input.datepicker", {
    dateFormat: "Y-m-d",
  });
</script>

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.assign_shift.index')}}",
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'name',
            },
            {
                data: 'department.name',
                name: 'department.name',
            },
            {
                data: 'assign_shift',
                orderable: false,
                searchable: false
            },
        ]
    });
</script>
@endpush
