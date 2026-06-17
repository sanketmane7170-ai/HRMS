@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('review_responses')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('my_assigned_reviews')}}</li>
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
                            <table class="table table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __trans('Grade')}}</th>
                                        <th>{{ __trans('Question')}} Set</th>
                                        <th>{{ __trans('Status')}}</th>
                                        <th>{{ __trans('hr_review')}}</th>
                                        <th>{{ __trans('response')}}</th>
                                        <th>{{ __trans('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
        order: [
            [0, 'desc']
        ],
        ajax: {
            url: "{{route('backend.employee.reviewresponse')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'grade',
                name: 'scoreCriteria.title'
            },
            {
                data: 'question_set',
                name: 'questionSet.name'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'hr_review',
                name: 'hr_review'
            },
            {
                data: 'employee_response',
                name: 'employee_response'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
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