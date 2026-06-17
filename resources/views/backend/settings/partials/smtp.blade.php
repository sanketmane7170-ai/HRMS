<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <h3>{{__trans('smtp_settings')}}</h3>
            </div>
            <div class="col-auto">
                <a href="{{route('backend.settings.test.smtp')}}" class="btn btn-sm btn-warning"> {{__trans('test_email')}}</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form action="{{route('backend.settings.smtp.post')}}" method="POST" enctype="multipart/form-data" class="ajax-form-submit">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('smtp_driver')}}</label>
                        <input type="text" name="smtp_driver" class="form-control @error('smtp_driver') is-invalid @enderror" value="{{old('smtp_driver',getSetting('smtp_driver'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('smtp_host')}}</label>
                        <input type="text" name="smtp_host" class="form-control @error('smtp_host') is-invalid @enderror" value="{{old('smtp_host',getSetting('smtp_host'))}}">

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('smtp_port')}}</label>
                        <input type="text" name="smtp_port" class="form-control @error('smtp_port') is-invalid @enderror" value="{{old('smtp_port',getSetting('smtp_port'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('smtp_encryption')}}</label>
                        <input type="text" name="smtp_encryption" class="form-control @error('smtp_encryption') is-invalid @enderror" value="{{old('smtp_encryption',getSetting('smtp_encryption'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('username')}}</label>
                        <input type="text" name="smtp_username" class="form-control @error('smtp_username') is-invalid @enderror" value="{{old('smtp_username',getSetting('smtp_username'))}}">

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('password')}}</label>
                        <input type="text" name="smtp_password" class="form-control @error('smtp_password') is-invalid @enderror" value="{{old('smtp_password',getSetting('smtp_password'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('sender_name')}}</label>
                        <input type="text" name="smtp_sender_name" class="form-control @error('smtp_sender_name') is-invalid @enderror" value="{{old('smtp_sender_name',getSetting('smtp_sender_name'))}}">

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('sender_email')}}</label>
                        <input type="email" name="smtp_sender_email" class="form-control @error('smtp_sender_email') is-invalid @enderror" value="{{old('smtp_sender_email',getSetting('smtp_sender_email'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('smtp_test_email')}}</label>
                        <input type="email" name="smtp_test_email" class="form-control @error('smtp_test_email') is-invalid @enderror" value="{{old('smtp_test_email',getSetting('smtp_test_email'))}}">
                    </div>
                </div>
            </div>
            <div class=" text-end mt-4">
                <button type="submit" class="btn btn-primary">{{__trans('save_smtp_settings')}} </button>
            </div>
        </form>
    </div>
</div>
