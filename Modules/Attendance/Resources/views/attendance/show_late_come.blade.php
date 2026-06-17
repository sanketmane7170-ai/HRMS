@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('Employee_late_comer')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('Employee_late_comer')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    {{--  <a href="{{route('backend.showExtraHoursReport')}}" class="btn btn-primary me-1">
                        <i class="fas fa-info"></i> {{__trans('show late come report')}}
                    </a>
                    <a href="{{route('backend.extra.show_report')}}" id="exportButton" class="btn btn-info me-1">
                        <i class="fas fa-file-excel"></i> {{__trans('Export report')}}
                    </a>  --}}
                    <div class="">
                        <div class="att-filter-box-inner">
                            <div class="form-group">
                                <label><strong>{{ __trans('select_month') }}:</strong></label>
                                <select name="month" class="form-control select-search" id="selected_month_input">
                                    <option value="all" {{ $month == 'all' ? 'selected' : '' }}>All</option>
                                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ (string) $month === (string) $i ? 'selected' : '' }}>
                                        {{ date('F', strtotime(date('Y') . '-' . $i)) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <select name="take_actions" class="form-control select2" id="takeActions" style="display: none;">
                        <option value="" disabled selected>{{ __trans('select_actions ...') }}</option>
                        <option value="1">Approved</option>
                        <option value="2">Reject</option>
                    </select>
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
                                        {{--  <th>{{ __trans('select') }} <input type="checkbox" name="select_check" id="selectAll" class="selectall" ></th>  --}}
                                        <th>{{ __trans('user_name') }}</th>
                                        <th>{{ __trans('late_comer (Minutes)') }}</th>
                                        <th>{{ __trans('date') }}</th>
                                        <th>{{ __trans('deduction_amount') }}</th>
                                        <th>{{ __trans('status') }}</th>
                                        <th>{{ __trans('actions') }}</th>
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
        stateSave: true,
        ajax: {
            url: '{{ route('backend.late_come.show') }}',
            data: function(d) {
                d.month = $('#selected_month_input').val();
            }
        },
        rowId: 'id',
        columns: [
            {{--  { data: 'select', name: 'select' ,orderable: false, searchable: false},  --}}
            { data: 'user_name', name: 'user_name' },
            { data: 'late_come', name: 'late_come', orderable: false, searchable: false  },
            { 
                data: 'date.display', 
                name: 'date',
                orderData: [2]
            },
            { data: 'charge_amount', name: 'charge_amount', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
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

    document.getElementById('takeActions').addEventListener('change', function () {

        let selectedOption = this.value;
        let checkedValues = [];

        $('.selectCheck:checked').each(function () {
            checkedValues.push($(this).val());
        });

        if (checkedValues.length === 0) {
            alert("Please select at least one item.");
            return;
        }

        let confirmation = confirm("Are you sure you want to proceed with this action?");
        
        if (confirmation) {
            $.ajax({
                url: "{{ route('backend.allRequestUpdate') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: selectedOption,
                    selected_ids: checkedValues
                },
                success: function (response) {
                    alert("Request updated successfully!");
                    location.reload();
                },
                error: function (xhr) {
                    alert("Error: " + xhr.responseText);
                }
            });
        }
    });

    $(document).on('click', '.selectCheck', function () {
        if ($('.selectCheck:checked').length > 0) {
            $('#takeActions').show();
        } else {
            $('#takeActions').hide();
        }
    });
    $(document).on('click', '#selectAll', function () {
        $('.selectCheck').prop('checked', this.checked);
        if ($('.selectCheck:checked').length > 0) {
            $('#takeActions').show();
        } else {
            $('#takeActions').hide();
        }
    });
    var table = $('#dataTable').DataTable();
    $(document).on('change','#selected_month_input',function() {
        table.ajax.reload();
    });

    document.getElementById('exportButton').addEventListener('click', function(e) {
        e.preventDefault();
        const month = document.getElementById('selected_month_input').value;
        let exportUrl = "{{ route('backend.extra.show_report') }}";
        if (month) {
            exportUrl += '?month=' + month;
        }
        window.location.href = exportUrl;
    });
    document.getElementById('exportButton').addEventListener('click', function(e) {
        e.preventDefault();
        const month = document.getElementById('selected_month_input').value;
        let exportUrl = "{{ route('backend.extra.show_report') }}";
        if (month) {
            exportUrl += '?month=' + month;
        }
        window.location.href = exportUrl;
    });
    document.getElementById('selected_month_input').addEventListener('change', function () {
        const selectedMonth = this.value;
        let newUrl = "{{ route('backend.late_come.show') }}";

        if (selectedMonth && selectedMonth !== 'all') {
            newUrl += '?month=' + selectedMonth;
        }

        window.location.href = newUrl;
    });
</script>
<script>
loadAjaxSelect2();
</script>
@endpush
