@extends('layouts.backend')

@push('css')
<style>
    .info {
        margin-top: 0.5rem !important;
    }
</style>

@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{$user->name}}</h3>
                    <ul class="breadcrumb">
                    </ul>
                </div>

            </div>
        </div>
        <div class="col-xl-12">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <!-- <h5>{{__trans('personal_details')}}</h5> -->
                            <div class="row">
                                <div class="col">
                                    <h5>{{__trans('personal_details')}}</h5>
                                </div>
                                <div class="col-auto">
                                    <a href="{{route('backend.employee.profile.details.edit')}}" class="edit-button"> <i class="fa fa-edit"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong class="font-bold">{{__trans('name')}} :</strong>
                                        <span>{{$user->name}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('gender')}} :</strong>
                                        <span>{{$user->profile->gender}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong class="font-bold">{{__trans('work_email')}} :</strong>
                                        <span>{{$user->email}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong class="font-bold">{{__trans('employee_id')}} :</strong>
                                        <span>{{$user->employee_id}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('work_phone')}} :</strong>
                                        <span>{{$user->phone}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong class="font-bold">{{__trans('personal_email')}} :</strong>
                                        <span>{{$user->profile->personal_email}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('personal_phone')}} :</strong>
                                        <span>{{$user->profile->personal_phone}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('date_of_birth')}} :</strong>
                                        <span>{{$user->profile->date_of_birth->format(config('project.birth_date_format'))}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('nationality')}} :</strong>
                                        <span>{{$user->profile->country?->name}}</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('marital_status')}} :</strong>
                                        <span>{{$user->profile->martial_status->name}}</span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('address')}} :</strong>
                                        <span>{{$user->profile->address}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{__trans('work_details')}}</h5>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('company_name')}} : </strong>
                                        <span>{{$user->workDetail->company_name}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('branch')}} : </strong>
                                        <span>{{$user->department?->name ?? 'NA'}}</span>
                                    </div>
                                </div>
                                 <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('department')}} : </strong>
                                        <span>{{ $user->division->name ?? 'Not Assigned' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('designation')}} : </strong>
                                        <span>{{$user->designation->name}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('reporting_manager')}} : </strong>
                                        <?php 
                                        if(!empty($user->workDetail->report_to_ids)){
                                            $reportToIds = $user->workDetail->report_to_ids;
                                            $reportUsers = App\Models\User::whereIn('id', $reportToIds)->pluck('name');
                                            $report_user_name = $reportUsers->implode(', ');
                                            //$report_user_name = App\Models\User::find($user->workDetail->report_to_id)->name;
                                            
                                        }else{
                                            $report_user_name = "";
                                        }
                                        
                                        ?>
                                        <span>{{$report_user_name}}</span>
                                        <!-- <span>{{$user->department->manager?->name}}</span> //Not working this relationship. --> 
                                    </div>
                                </div>
                                <div class="col-md-6"> </div>

                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('date_of_joining')}} : </strong>
                                        <span>{{$user->workDetail?->joining_date->format(config('project.date_format'))}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('probation_month')}} : </strong>
                                        <span>{{$user->workDetail?->probation_month}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('probation_end_date')}} : </strong>
                                        <span>{{$user->workDetail->probation_end_date->format(config('project.date_format'))}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('work_week')}} : </strong>
                                        <span>{{$user->workDetail->work_week}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong class="font-bold">{{__trans('location')}} : </strong>
                                        <span>{{$user->workDetail->location}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6" id="employee-social-details">
                    @include('employee.profile.partials.social-details')
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="modal"></div>
@endsection
