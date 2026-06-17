@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('holiday_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('holiday_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create Holiday')
                    <a href="{{route('backend.holidays.create')}}" class="edit-button btn btn-primary">
                        <i class="fa fa-plus"></i> {{__trans('add_holiday')}}</a>
                    @endcan
                    <div class="form-check form-switch" style="float: left;font-size: x-large;margin-right:10px;">
                        <input 
                            class="form-check-input toggle-switch withoutAttendPH" @if($withoutAttendPH && $withoutAttendPH->value==1) checked @endif
                            type="checkbox" 
                            id="isAllowSwitch" 
                            data-url="{{ route('backend.isWithoutAttendPHLeave') }}"
                            data-token="{{ csrf_token() }}"
                        >
                        <label class="form-check-label" for="isAllowSwitch">Given without attendance PH leave</label>
                    </div>
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
                                        <th>{{__trans('start_date')}}</th>
                                        <th>{{__trans('end_date')}}</th>
                                        <th>{{__trans('description')}}</th>
                                        <th>{{__trans('action')}}</th>
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
            url: "{{route('backend.holidays.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'start_date',
            },
            {
                data: 'end_date',
            },
            {
                data: 'detail',
            },
            {
                data: 'action'
            }
        ]
    });

    document.querySelector('.withoutAttendPH').addEventListener('change', function () {
        let isChecked = this.checked;
        let url = this.dataset.url;
        let token = this.dataset.token;

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });
</script>
@endpush
