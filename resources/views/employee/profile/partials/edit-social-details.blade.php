<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_social_details')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.employee.social.details.update')}}" html="#employee-social-details" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>{{__trans('linkedin_url')}}</label>
                            <input type="text" class="form-control" name="linkedin_profile_url" value="{{auth()->user()->profile?->linkedin_url}}">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>{{__trans('skills')}}</label>
                            <textarea rows="6" cols="5" class="form-control" name="skills">{{auth()->user()->profile?->skills}}</textarea>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>{{__trans('hobbies')}}</label>
                            <textarea rows="6" cols="5" class="form-control" name="hobbies">{{auth()->user()->profile?->hobbies}}</textarea>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>
