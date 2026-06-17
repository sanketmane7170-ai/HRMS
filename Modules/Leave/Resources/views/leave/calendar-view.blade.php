@extends('layouts.backend')
@section('content')
<style>
  .fc-time {
      display: none;
  }
  .fc-month-button {
    display: none;
  }
</style>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('leaves_planner')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('leaves_planner')}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <form action="{{route('backend.leaves.calender.search')}}" enctype="multipart/form-data" method="POST">
            @csrf
            <div class="row">
                <div class="col-xl-8 col-12">
                    <div class="card bg-white">
                        <div class="card-body">
                            <div class="row" id="custom_container">
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label>{{__trans('Employee')}}</label>
                                        <input type="text" name="search_emp" id="search_emp" class="form-control" value="{{$searchEmp}}">
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label>{{__trans('department')}}</label>
                                        <select name="department_id" id="department" class="form-control select">
                                            {{-- <option value="{{$user->department->id}}">{{$user->department?->name ?? 'NA'}}</option> --}}
                                            <option value="">{{__trans('select_department')}}</option>
                                            <option @if($departmentId == 'all') selected @endif value="all">{{__trans('all')}}</option>
                                                @foreach (\App\Models\Department::all() as $department)
                                                    <option @if($department->id == $departmentId) selected @endif value="{{$department->id}}">{{$department->name}}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3" style="margin-top: 31px !important;">
                                        <button type="submit" id="searchLeave" class="btn btn-primary">
                                            <i class="fa fa-search mr-2" style="display: inline"></i>Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                    <div class="container">
                    <div id='leaveCalendarFull'></div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@push('scripts')
<script>
  initselect2();
</script>
<script>
    var laravelLeaveEvents = @json($formattedEvents);
    var laravelLeave = @json($leaves);
    console.log('myevents', laravelLeaveEvents);
    console.log('leaves',laravelLeave)

</script>
<script src="{{asset('assets/backend/plugins/fullcalendar/custom-script.js?v=0.0.1')}}"></script>

<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
@endpush

