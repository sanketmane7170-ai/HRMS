@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('uniform_report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('uniform_report')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </div>
        {{--  search  --}}
        <form action="{{route('backend.apparel.report.search')}}" enctype="multipart/form-data" method="POST">
            @csrf
            <div class="row">
                <div class="col-xl-8 col-12">
                    <div class="card bg-white">
                        <div class="card-body">
                            <div class="row" id="custom_container">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>{{__trans('Employee')}}</label>
                                        <input type="text" name="search_emp" id="search_emp" class="form-control" value="{{@$searchEmp}}">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>{{__trans('department')}}</label>
                                        <select name="department_id" id="department" class="form-control select">
                                            <option value="">{{__trans('select_department')}}</option>
                                            <option @if($departmentId == 'all') selected @endif value="all">{{__trans('all')}}</option>
                                                @foreach (\App\Models\Department::all() as $department)
                                                    <option @if($department->id == $departmentId) selected @endif value="{{$department->id}}">{{$department->name}}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>{{__trans('uniforms_type')}}</label>
                                        <select name="apparelsId[]" id="apparels" class="form-control select" multiple>
                                            @foreach ($appareldata as $apparel)
                                                <option @if( is_array($apparelsId) && in_array($apparel->id, $apparelsId)) selected @endif value="{{$apparel->id}}">{{$apparel->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3" style="margin-top: 31px !important;">
                                        <button type="submit" id="searchLeave" class="btn btn-primary">
                                            <i class="fa fa-search mr-2" style="display: inline"></i>Search
                                        </button>
                                        @if (count($users) > 0)
                                            <a href="{{route('backend.apparel.report.print', [$departmentId,is_array($apparelsId) ? implode(',', $apparelsId) : 'all', $searchEmp])}}" class="btn btn-sm btn-success mt-2">
                                                <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        {{--  end  --}}
        @if($search=='true')
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="background-color: #042356" class="text-white">Employee Name</th>
                                        @foreach ($apparels as $apparel)
                                            @php
                                                $total = Modules\Apparel\Entities\ApparelRequest::where('apparel_id',$apparel->id)->where('status',1)->sum('number_of_apparel');
                                            @endphp
                                            <th style="background-color: #042356" class="text-white">{{$apparel->name }} ({{ $apparel->number_of_given - $total }})</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $data)
                                        <tr>
                                            <th style="background-color: #042356" class="text-white">
                                                <strong>{{ $data->name }}</strong><br>
                                            </th>
                                            @foreach($apparels as $apparel)
                                            @php
                                                $total = Modules\Apparel\Entities\ApparelRequest::where([['apparel_id',$apparel->id],['user_id',$data->id]])->where('status',1)->sum('number_of_apparel');
                                            @endphp
                                                <td>
                                                    <span class="badge badge-primary mb-2">
                                                        <big>{{ $total }}</big>
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
        </div>
        @endif
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
<script>
loadAjaxSelect2();
$(document).ready(function() {
    $('#apparels').select2({
        placeholder: "All",
        allowClear: true
    });
});
</script>
@endpush
