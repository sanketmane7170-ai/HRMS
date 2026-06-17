@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('end_of_service_policy')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('policy')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{route('backend.settings.addendOfServicePolicy')}}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i> {{__trans('Add_New_Policy')}}
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
                                        <th>{{__trans('leave_type')}}</th>
                                        <th>{{__trans('salary_type')}}</th>
                                        <th>{{__trans('month_day')}}</th>
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
            url: "{{ route('backend.settings.endOfServicePolicy') }}",
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id' },
            { data: 'type.name'},
            { data: 'salary_type'},
            { data: 'month_day'},
            { data: 'action', orderable: false, searchable: false }
        ]
    });
    
    flatpickr("input.datetime", {
        //enableTime: true,
        // maxDate: today,
        dateFormat: "Y-m-d",
    });
</script>
<script>
loadAjaxSelect2();
</script>
@endpush
