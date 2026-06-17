<div class="card">
    <div class="card-body grid-structure">
        <form action="{{route('backend.settings.social-login.post')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12 grid-container">
                    {{__trans('google_recaptcha_settings')}}
                </div>
                <div class="col-md-6">
                    <label> {{__trans('is_google_recaptcha_enable')}} </label>
                    <select name="google_recaptcha_enable" class="form-control select" id="google_recaptcha_enable">
                        <option value="0" @if(getSetting('google_recaptcha_enable')==0) selected @endif>Disable</option>
                        <option value="1" @if(getSetting('google_recaptcha_enable')==1) selected @endif>Enable</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label> {{__trans('google_recaptcha_version')}} </label>
                    <select name="google_recaptcha_version" class="form-control select" id="google_recaptcha_version">
                        <option value="v3" @if(getSetting('google_recaptcha_version')=='v3' ) selected @endif>v3</option>
                        <option value="v2" @if(getSetting('google_recaptcha_version')=='v2' ) selected @endif>v2</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('google_recaptcha_site_key')}} </label>
                        <input type="text" name="google_recaptcha_site_key" class="form-control @error('google_recaptcha_site_key') is-invalid @enderror" value="{{old('google_recaptcha_site_key',getSetting('google_recaptcha_site_key'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('google_recaptcha_site_secret')}} </label>
                        <input type="text" name="google_recaptcha_site_secret" class="form-control @error('google_recaptcha_site_secret') is-invalid @enderror" value="{{old('google_recaptcha_site_secret',getSetting('google_recaptcha_site_secret'))}}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 grid-container">
                    {{__trans('google_login_settings')}}
                </div>
                <div class="col-md-12 mb-2">
                    <div class="col-md-6">
                        <label> {{__trans('is_google_login_enable')}} </label>
                        <select name="social_google_enable" class="form-control select" id="social_google_enable">
                            <option value="0" @if(getSetting('social_google_enable')==0) selected @endif>Disable</option>
                            <option value="1" @if(getSetting('social_google_enable')==1) selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('google_client_id')}} </label>
                        <input type="text" name="social_google_id" class="form-control @error('social_google_id') is-invalid @enderror" value="{{old('social_google_id',getSetting('social_google_id'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('google_client_secret')}} </label>
                        <input type="text" name="social_google_secret" class="form-control @error('social_google_secret') is-invalid @enderror" value="{{old('social_google_secret',getSetting('social_google_secret'))}}">
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-12 grid-container">
                    {{__trans('facebook_login_settings')}}
                </div>
                <div class="col-md-12 mb-2">
                    <div class="col-md-6">
                        <label> {{__trans('is_facebook_login_enable')}}</label>
                        <select name="social_facebook_enable" class="form-control select" id="social_facebook_enable">
                            <option value="0" @if(getSetting('social_facebook_enable')==0) selected @endif>Disable</option>
                            <option value="1" @if(getSetting('social_facebook_enable')==1) selected @endif>Enable</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('facebook_client_id')}} </label>
                        <input type="text" name="social_facebook_id" class="form-control @error('social_facebook_id') is-invalid @enderror" value="{{old('social_facebook_id',getSetting('social_facebook_id'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('facebook_client_secret')}} </label>
                        <input type="text" name="social_facebook_secret" class="form-control @error('social_facebook_secret') is-invalid @enderror" value="{{old('social_facebook_secret',getSetting('social_facebook_secret'))}}">
                    </div>
                </div>
            </div>
            <div class=" text-end mt-4">
                <button type="submit" class="btn btn-primary">{{__trans('update_settings')}} </button>
            </div>
        </form>
    </div>
</div>
