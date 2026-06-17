@extends('layouts.backend')
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('leaves_report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('leaves_report')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="#" class="btn btn-info btn-sm me-1 " data-bs-target="#edit-leave-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('update_leave_form_import')}}
                    </a>
                </div>
            </div>
        </div>
        <form action="{{route('backend.leaves.report.search')}}" enctype="multipart/form-data" method="POST" id="leaveForm">
            @csrf
            <div class="row">
                <div class="col-xl-12 col-12">
                    <div class="card bg-white">
                        <div class="card-body">
                            <div class="row" id="custom_container">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>{{__trans('Employee')}}</label>
                                        <input type="text" name="search_emp" id="search_emp" class="form-control"
                                            value="{{$searchEmp}}">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>{{__trans('department')}}</label>
                                        <select name="department_id" id="department" class="form-control select">
                                            {{-- <option value="{{$user->department->id}}">{{$user->department?->name ?? 'NA'}}
                                            </option> --}}
                                            {{--  <option value="">{{__trans('select_department')}}</option>  --}}
                                            <option @if($departmentId=='all' ) selected @endif value="all">
                                                {{__trans('all')}}</option>
                                            @foreach (\App\Models\Department::all() as $department)
                                            <option @if($department->id == $departmentId) selected @endif
                                                value="{{$department->id}}">{{$department->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if (isset($users) && count($users) > 0)
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>{{__trans('leave_type')}}</label>
                                            <select name="type_id[]" id="type" class="form-control select" multiple>

                                                <!-- <option value="">{{__trans('select_leave_type')}}</option>
                                                <option @if( is_array($type_id) && in_array('all', $type_id)) selected
                                                    @endif value="all">
                                                    {{__trans('all')}}</option> -->
                                                @foreach(\Modules\Leave\Entities\LeaveType::all() as $type)
                                                <option @if( is_array($type_id) && in_array($type->id, $type_id)) selected
                                                    @endif
                                                    value="{{$type->id}}">{{$type->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <div class="mb-3" style="margin-top: 31px !important;">
                                        @if (isset($users))
                                            <button type="submit" id="searchLeave" class="btn btn-primary">
                                                <i class="fa fa-search mr-2" style="display: inline"></i>Search
                                            </button>
                                        @endif
                                        @if (isset($phleavereport))
                                            <button type="submit" id="searchphLeave" class="btn btn-primary">
                                                <i class="fa fa-search mr-2" style="display: inline"></i>Search
                                            </button>
                                        @endif
                                        @if (isset($users) && count($users) > 0)
                                            @if ($search=='true')
                                                <a href="{{route('backend.leaves.report.print', [$departmentId,is_array($type_id) ? implode(',', $type_id) : 'all', $searchEmp])}}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                                                </a>
                                            @endif
                                            <a href="{{route('backend.phleaves.report')}}"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-file" style="display: inline"></i>PH Leave report
                                            </a>
                                        @endif
                                        @if(isset($phleavereport))
                                            <a href="{{route('backend.leaves.report')}}"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-file" style="display: inline"></i>Leave report
                                            </a>
                                            @if ($search=='true')
                                                <a href="{{route('backend.phleaves.report.print', [$departmentId,$searchEmp])}}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- /Page Header -->
        @if (isset($users) && count($users) > 0)
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">

                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col" style="background-color: #042356; width:100px" class="text-white">
                                            <strong>Employee Name</strong>
                                        </th>
                                        @foreach($types as $type)

                                        <th class="text-center text-white" style="background-color: #042356">
                                            <strong>
                                                <small><strong>{{$type->name}} ({{ $type->days }})</strong></small>
                                            </strong>
                                        </th>

                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <th style="background-color: #042356" class="text-white">
                                            <strong>{{ Str::limit($user->name, 25) }}</strong><br>
                                            <small>({{ $user->employee_id }})</small>
                                            <small>({{ $user->department?->name ?? 'NA' }})</small>
                                        </th>
                                        @foreach($types as $type)

                                        <td>
                                            <span class="badge badge-primary mb-2">
                                                <small>{{ calculatePendingLeave($type, $user->id) }}</small>
                                            </span>
                                        </td>

                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @if($search && isset($users))
                <p class="text-danger">{{__trans('no_employee_found')}}</p>
            @endif
        @endif
        @if (isset($phleavereport) && count($phleavereport) > 0)
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">

                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col" style="background-color: #042356; width:100px" class="text-white">
                                            <strong>Employee Name</strong>
                                        </th>
                                        @foreach($holidays as $holiday)
                                            @php
                                                $holidayStart = Carbon\Carbon::parse($holiday->start_date)->toDateString();
                                                $holidayEnd = Carbon\Carbon::parse($holiday->end_date)->toDateString();
                                                $diffInDays = Carbon\Carbon::parse($holidayStart)->diffInDays(Carbon\Carbon::parse($holidayEnd)) + 1;
                                            @endphp
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{ $holiday->detail . ' ('.$diffInDays.')' }}</strong></small>
                                                </strong>
                                            </th>
                                        @endforeach
                                        {{--  <th scope="col" style="background-color: #71dbca; width:100px" class="text-white">
                                            <strong>Previous PH Balance</strong>
                                        </th>  --}}
                                        <th scope="col" style="background-color: #65acd5; width:100px" class="text-white">
                                            <strong>Total PH Balance</strong>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emp as $user)
                                        @php
                                            $phcount = 0;
                                            $currentYear = now()->year;
                                            if ($phleavereport instanceof \Illuminate\Support\Collection) {
                                                $allphcount = $phleavereport->filter(function ($item) use ($user, $currentYear) {
                                                    return $item->user_id == $user->id && \Carbon\Carbon::parse($item->date)->year == $currentYear;
                                                })->count();
                                            }
                                        @endphp
                                        <tr>
                                            <th style="background-color: #042356" class="text-white">
                                                <strong>{{ Str::limit($user->name, 25) }}</strong><br>
                                                <small>({{ $user->employee_id }})</small>
                                                <small>({{ $user->department?->name ?? 'NA' }})</small>
                                            </th>
                                            @foreach($holidays as $holidaydata)
                                                @php
                                                    $phcount = 0;
                                                    $currentYear = now()->year;
                                                    if ($phleavereport instanceof \Illuminate\Support\Collection) {
                                                        $phcount = $phleavereport->filter(function ($item) use ($user, $currentYear, $holidaydata) {
                                                            return $item->user_id == $user->id && $item->holiday_id == $holidaydata->id && \Carbon\Carbon::parse($item->date)->year == $currentYear;
                                                        })->count();
                                                    }
                                                    $phbalance = 0;
                                                    $leavetype = Modules\Leave\Entities\LeaveType::where('name', 'like', '%PH%')->first();
                                                    if($leavetype){
                                                        $balance = Modules\Leave\Entities\LeaveBalance::where([
                                                            'user_id' => $user->id,
                                                            'year' => date('Y'),
                                                            'leave_type_id' => $leavetype->id,
                                                        ])->first();
                                                        $phbalance = $balance->available;
                                                    }
                                                @endphp
                                                <td>
                                                    <span class="badge badge-primary mb-2">
                                                        <small>{{ $phcount }}</small>
                                                    </span>
                                                </td>
                                            @endforeach
                                            {{--  <td>
                                                <span class="badge badge-primary mb-2" style="background-color: #71dbca !important">
                                                    <small>{{ $phbalance - $allphcount }}</small>
                                                </span>
                                            </td>  --}}
                                            <td>
                                                <span class="badge badge-primary mb-2" style="background-color: #65acd5 !important">
                                                    <small>{{ $allphcount }}</small>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @if(isset($phleavereport))
                <p class="text-danger">{{__trans('no_data_found')}}</p>
            @endif
        @endif
    </div>
</div>
@include('common.modals.edit-leave-modal',['importUrl'=> route('backend.updateLeaveToExcel'),'flag'=>true])

@endsection

@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
flatpickr("input.datepicker", {
    dateFormat: "Y-m-d",
});
</script>
<script>
    $(document).ready(function() {
        $('#type').select2({
            placeholder: "All",
            allowClear: true
        });
    });
</script>
<script>
    $('#searchphLeave').on('click', function () {
        // Update the form action dynamically
        $('#leaveForm').attr('action', '{{ route("backend.phleaves.report") }}');
        $('#leaveForm').submit();
    });
</script>
@endpush