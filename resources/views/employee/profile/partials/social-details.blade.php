<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <h5>{{__trans('social_details')}}</h5>
            </div>
            <div class="col-auto">
                <a href="{{route('backend.employee.social.details.edit')}}" class="edit-button"> <i class="fa fa-edit"></i></a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="info">
                    <strong class="font-bold">{{__trans('linkedin_url')}} : </strong>
                    <span>{{$user->profile?->linkedin_url ?? __trans('not_available')}}</span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="info">
                    <strong class="font-bold">{{__trans('skills')}} : </strong>
                    <span>{{$user->profile->skills}}</span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="info">
                    <strong class="font-bold">{{__trans('hobbies')}} : </strong>
                    <span>{{$user->profile->hobbies}}</span>
                </div>
            </div>
        </div>
    </div>
</div>
