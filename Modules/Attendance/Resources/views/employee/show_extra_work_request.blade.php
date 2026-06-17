@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('extra_hours_request')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('my_extra_hours_request')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{route('backend.employee.addRequest')}}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i> {{__trans('add_request')}}
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
                                        <th>{{__trans('user_name')}}</th>
                                        <th>{{__trans('extra_hours')}}</th>
                                        <th>{{__trans('month-year')}}</th>
                                        <th>{{__trans('reason')}}</th>
                                        <th>{{__trans('status')}}</th>
                                        <th>{{__trans('actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($extraWorkRequest as $workData)
                                    <tr class="attendance">
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="#">{{ Str::limit($workData->user->name, 25). ' ('.$workData->user->employee_id.')'}}</a>
                                            </h2>
                                        </td>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="#">{{ $workData->extra_hours }}</a>
                                            </h2>
                                        </td>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="#">{{ $workData->month.'-'.$workData->year }}</a>
                                            </h2>
                                        </td>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="#">{{ $workData->reason }}</a>
                                            </h2>
                                        </td>
                                        <td>
                                            <h2 class="table-avatar">
                                                @if($workData->status==0)
                                                    <span class='badge badge-warning' style="color: black">Pending</span>
                                                @elseif($workData->status==1)
                                                    <span class="badge badge-success" style="color: black">Added To Payroll</span>
                                                @elseif($workData->status==2)
                                                    <span class="badge badge-info" style="color: black">Added To Leave</span>
                                                @elseif($workData->status==3)
                                                    <span class="badge badge-danger" style="color: black">Rejected</span>
                                                @endif
                                            </h2>
                                        </td>
                                        <td>
                                            <h2 class="table-avatar">
                                                @if($workData->status==0)
                                                    <a href="{{route('backend.employee.editRequest',$workData->id)}}" class="btn btn-sm inline-block me-2  btn-warning edit-button">
                                                        <i class="fa fa-edit"></i>Edit
                                                    </a>
                                                    <a href="{{route('backend.employee.removeRequest',$workData->id)}}" onclick="return confirmDelete(this)" class="btn btn-sm inline-block me-2  btn-danger reqRemove">Delete</a>
                                                @endif
                                            </h2>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
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
    function confirmDelete(link) {
        event.preventDefault(); // Prevent the default action (navigation)
        
        if (confirm("Are you sure you want to delete this request?")) {
            // If confirmed, proceed to the delete route
            window.location.href = link.href;
        }
    }
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
