@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('extra_hours')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('my_extra_hours')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    {{--  <a href="{{route('backend.admin.apparelRequest.create')}}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i> {{__trans('add_apparel_request')}}
                    </a>  --}}
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
                                        <th>{{__trans('month-year')}}</th>
                                        <th>{{__trans('extra_hours')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($empextraWork as $hours)
                                        <tr class="attendance-{{$user->id}}">
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#">{{ Str::limit($user->name, 25). ' ('.$user->employee_id.')'}}</a>
                                                </h2>
                                            </td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#">{{ $hours->month .'-'. $hours->year }}</a>
                                                </h2>
                                            </td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#">{{ $hours->extra_hours }}</a>
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
