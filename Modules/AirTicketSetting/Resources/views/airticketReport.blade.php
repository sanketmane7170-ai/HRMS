@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('Air_Ticket_Setting_List')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('Air_Ticket_Setting_List')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.exportAirTicketReport') }}" class="btn btn-success">
                        <i class="fa fa-file-pdf"></i> {{ __trans('export-pdf') }}
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
                                        <th>{{__trans('user_name')}}</th>
                                        <th>{{__trans('date')}}</th>
                                        <th>{{__trans('amount')}}</th>
                                        <th>{{__trans('quantity')}}</th>
                                        <th>{{__trans('total_amount')}}</th>
                                        <th>{{__trans('details')}}</th>
                                        <th>{{__trans('status')}}</th>
                                        <th>{{__trans('approval_date')}}</th>
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script type="text/javascript">
    $.ajax({
        url: "{{ route('backend.settings.air-ticket-setting.index') }}",
        success: function(response) {
            console.log(response);
        }
    });
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [
            [0, 'desc']
        ],
        ajax: {
            url: "{{ route('backend.airTicketReport') }}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'user_name',
                name: 'user_name'
            },
            {
                data: 'date',
                name: 'date',
                render: function(data) {
                    return moment(data).format('DD-MM-YYYY');
                }
            },
            {
                data: 'amount',
                name: 'amount'
            },
            {
                data: 'quantity',
                name: 'quantity'
            },
            {
                data: 'total_amount',
                name: 'total_amount'
            },
            {
                data: 'details',
                name: 'details'
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
    const statuses = ['Pending', 'Approved', 'Rejected'];
    const colors = {
        'Pending': 'warning',
        'Approved': 'success',
        'Rejected': 'danger'
    };
    let currentColor = colors[data] || 'secondary';

    // If status is not Pending, just show badge
    if(data !== 'Pending') {
        return `<span class="badge bg-${currentColor}">${data}</span>`;
    }

    // Otherwise, show dropdown to change
    let dropdownItems = statuses.map(status => {
        // Only allow selecting other statuses
        if(status === data) return '';
        return `<a class="dropdown-item status-change" href="#" data-id="${row.id}" data-status="${status}">${status}</a>`;
    }).join('');

    return `
    <div class="dropdown">
        <button class="btn btn-${currentColor} btn-sm dropdown-toggle" type="button" id="statusDropdown${row.id}" data-bs-toggle="dropdown" aria-expanded="false">
            ${data}
        </button>
        <ul class="dropdown-menu" aria-labelledby="statusDropdown${row.id}">
            ${dropdownItems}
        </ul>
    </div>
    `;
}


            },
             {
                data: 'approve_date',
                name: 'approve_date'
            },

        ]
    });
    flatpickr("input.datetime", {
        //enableTime: true,
        // maxDate: today,
        dateFormat: "Y-m-d",
    });

    // $('#dataTable').on('change', '.status-select', function() {
    //     const id = $(this).data('id');
    //     const status = $(this).val();
    //     const $select = $(this);

    //     $select.prop('disabled', true);

    //     $.ajax({
    //         url: "{{ route('backend.updateAirTicketStatus') }}",
    //         method: "POST",
    //         data: {
    //             id: id,
    //             status: status,
    //             _token: "{{ csrf_token() }}"
    //         },
    //         success: function(response) {
    //             toastr.success(response.message);
    //             $('#dataTable').DataTable().ajax.reload(null, false);
    //         },
    //         error: function() {
    //             toastr.error('Something went wrong!');
    //         },
    //         complete: function() {
    //             $select.prop('disabled', false);
    //         }
    //     });
    // });
    $('#dataTable').on('click', '.status-change', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const status = $(this).data('status');

        if (!confirm(`Are you sure you want to change the status to "${status}"?`)) {
            return;
        }

        $.ajax({
            url: "{{ route('backend.updateAirTicketStatus') }}",
            method: "POST",
            data: {
                id: id,
                status: status,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                toastr.success(response.message);
                $('#dataTable').DataTable().ajax.reload(null, false);
            },
            error: function() {
                toastr.error('Something went wrong!');
            }
        });
    });
</script>
<script>
    loadAjaxSelect2();
</script>
@endpush