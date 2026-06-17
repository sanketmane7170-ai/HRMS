@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('user_extra_hours_report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('user_extra_hours_report')}}</li>
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
                                        <th>{{ __trans('user_name') }}</th>
                                        <th>{{ __trans('extra_hours') }}</th>
                                        <th>{{ __trans('date') }}</th>
                                        <th>{{ __trans('cash_amount') }}</th>
                                        <th>{{ __trans('status') }}</th>
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
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('backend.extra.show') }}',
        columns: [
            { data: 'user_name', name: 'user_name' },
            { data: 'extra_hours', name: 'extra_hours' },
            { 
                data: 'date.display', 
                name: 'date',
                orderData: [2]
            },
            { data: 'cash_amount', name: 'cash_amount', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
        ],
        order: [[2, 'desc']],
    });
    function confirmDelete(link) {
        event.preventDefault(); // Prevent the default action (navigation)
        
        if (confirm("Are you sure you want to reject this request?")) {
            // If confirmed, proceed to the delete route
            window.location.href = link.href;
        }
    }
    flatpickr("input.datetime", {
        //enableTime: true,
        // maxDate: today,
        dateFormat: "Y-m-d",
    });
    // apparel approved
    $(document).on('click', '.updatest', function () {
        toggleLoader();
    });
</script>
<script>
loadAjaxSelect2();
</script>
@endpush
