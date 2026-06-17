<div class="row">
    <div class="col-sm-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>{{__trans('personal_details')}}</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('employee_id')}} : </strong>
                            <span>{{$user->employee_id}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info font-style">
                            <strong class="font-bold">{{__trans('name')}} :</strong>
                            <span>{{$user->name}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info font-style">
                            <strong class="font-bold">{{__trans('work_email')}} :</strong>
                            <span>{{$user->email}}</span>
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
                            <span>{{$user->profile?->personal_email}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('personal_phone')}} :</strong>
                            <span>{{$user->profile?->personal_phone}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('date_of_birth')}} :</strong>
                            <span>{{$user->profile?->date_of_birth?->format(config('project.birth_date_format'))}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('nationality')}} :</strong>
                            <span>{{$user->profile?->country?->name}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('gender')}} :</strong>
                            <span>{{$user->profile?->gender}}</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('marital_status')}} :</strong>
                            <span>{{$user->profile?->martial_status?->name}}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('address')}} :</strong>
                            <span>{{$user->profile?->address}}</span>
                        </div>
                    </div>
                   
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('visa_category')}} :</strong>
                            <span>{{ __trans($user->profile?->visa_category) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('visa_type')}} :</strong>
                            <span>{{ $user->profile?->visa_type }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('visa_designation')}} :</strong>
                            <span>{{ $user->profile?->visa_designation }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>{{__trans('work_details')}}</h5>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="info">
                            <strong class="font-bold">{{__trans('company_name')}} : </strong>
                            <span>{{$user->workDetail?->company_name ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('branch')}} : </strong>
                            <span>{{$user->department?->name ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('department')}} : </strong>
                            <span>{{$user->division?->name ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('designation')}} : </strong>
                            <span>{{$user->designation?->name ?? ''}}</span>

                        </div>
                    </div>
                    <?php
                        if (! empty($user->workDetail->report_to_ids)) {
                            $reportToIds      = $user->workDetail->report_to_ids;
                            $reportUsers      = App\Models\User::whereIn('id', $reportToIds)->pluck('name');
                            $report_user_name = $reportUsers->implode(', ');
                            //$report_user_name = App\Models\User::find($user->workDetail->report_to_id)->name;

                        } else {
                            $report_user_name = "";
                        }

                    ?>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('reporting_manager')}} : </strong>
                            <span>{{$report_user_name}}</span>
                        </div>
                    </div>
                    <div class="col-md-6"> </div>

                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('date_of_joining')}} : </strong>
                            <span>{{$user->workDetail?->joining_date?->format(config('project.date_format')) ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('probation_month')}} : </strong>
                            <span>{{$user->workDetail?->probation_month ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('probation_end_date')}} : </strong>
                            <span>{{$user->workDetail?->probation_end_date?->format(config('project.date_format')) ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('work_week')}} : </strong>
                            <span>{{$user->workDetail?->work_week ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('location')}} : </strong>
                            <span>{{$user->workDetail?->location ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('mol_number')}} : </strong>
                            <span>{{$user->workDetail?->mol_number ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('insurance_number')}} : </strong>
                            <span>{{$user->workDetail?->insurance_number ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('insurance_expiry')}} : </strong>
                            <span>{{$user->workDetail?->insurance_expiry?->format(config('project.date_format')) ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('last_working_day')}} : </strong>
                            <span>{{$user->workDetail?->last_working_day?->format(config('project.date_format')) ?? ''}}</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="info">
                            <strong class="font-bold">{{__trans('remarks')}} : </strong>
                            <span>{{$user->workDetail?->remarks ?? ''}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-sm-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>{{__trans('social_details')}}</h5>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="info">
                            <strong class="font-bold">{{__trans('linkedin_url')}} : </strong>
                            @if($user->profile && $user->profile?->linkedin_url)
                            <span>{{$user->profile?->linkedin_url}}</span>
                            @else
                            <span>{{__trans('not_available')}}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="info">
                            <strong class="font-bold">{{__trans('skills')}} : </strong>
                            <span>{{$user->profile?->skills}}</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="info">
                            <strong class="font-bold">{{__trans('hobbies')}} : </strong>
                            <span>@if($user->profile && $user->profile->hobbies){{$user->profile->hobbies}}@else{{__trans('not_available')}}@endif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>{{__trans('bank_details')}}</h5>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('bank_name')}} : </strong>
                            <span>@if(isset($user->bankDetail->bank_name)){{$user->bankDetail->bank_name}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('account_number')}} : </strong>
                            <span>@if(isset($user->bankDetail->account_number)){{$user->bankDetail->account_number}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('routing_number')}} : </strong>
                            <span>@if(isset($user->bankDetail->routing_number)){{$user->bankDetail->routing_number}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="info">
                            <strong class="font-bold">{{__trans('International_Bank_Account_Number')}} : </strong>
                            <span>@if(isset($user->bankDetail->iba_number)){{$user->bankDetail->iba_number}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('swift_code')}} : </strong>
                            <span>@if(isset($user->bankDetail->swift_code)){{$user->bankDetail->swift_code}} @endif</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>{{__trans('emergency_contact_details')}}</h5>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('name')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->emergency_name)){{$user->emergencyContacts->emergency_name}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('relation')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->emergency_relation)){{$user->emergencyContacts->emergency_relation}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('isd_code')}} : </strong>
                            <span> @if(isset($user->emergencyContacts->emergency_isd_code)){{$user->emergencyContacts->emergency_isd_code}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('phone')}} : </strong>
                            <span> @if(isset($user->emergencyContacts->emergency_phone)){{$user->emergencyContacts->emergency_phone}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="info">
                            <strong class="font-bold">{{__trans('email')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->emergency_email)){{$user->emergencyContacts->emergency_email}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('home_country')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->emergency_home_country)){{$user->emergencyContacts->emergency_home_country}} @endif</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('home_address')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->emergency_home_address)){{$user->emergencyContacts->emergency_home_address}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('local_person_name')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->local_person_name)){{$user->emergencyContacts->local_person_name}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('local_person_relation')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->local_person_relation)){{$user->emergencyContacts->local_person_relation}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('local_person_phone')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->local_person_phone)){{$user->emergencyContacts->local_person_phone}} @endif</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info">
                            <strong class="font-bold">{{__trans('local_country')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->emergency_local_country)){{$user->emergencyContacts->emergency_local_country}} @endif</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info">
                            <strong class="font-bold">{{__trans('local_address')}} : </strong>
                            <span>@if(isset($user->emergencyContacts->emergency_local_address)){{$user->emergencyContacts->emergency_local_address}} @endif</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
