<div class="card">
    <div class="card-header">
        <h5 class="card-title">{{__trans('basic_info')}}</h5>
    </div>
    <div class="card-body">

        <!-- Form -->
        <form action="{{route('backend.update-account')}}" method="POST" enctype="multipart/form-data" class="ajax-form-submit">
            @csrf
            <div class="row form-group">
                <label for="name" class="col-sm-3 col-form-label input-label">{{__trans('profile')}}</label>
                <div class="col-sm-9">
                    <div class="d-flex align-items-center">
                        <label class="avatar avatar-xxl profile-cover-avatar m-0" for="edit_img">
                            <img id="avatarImg" class="avatar-img" src="{{auth()->user()->getProfileImage()}}" alt="Profile Image">
                            <input type="file" name="profile_image" id="profile-image" accept="image/*" onchange="previewImage('profile-image','avatarImg')">
                            <span class="avatar-edit" onclick="$('#profile-image').click()">
                                <i data-feather="edit-2" class="avatar-uploader-icon shadow-soft"></i>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <label for="name" class="col-sm-3 col-form-label input-label">{{__trans('name')}}</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Your Name" value="{{old('name',auth()->user()->name)}}">
                </div>
            </div>
            <div class="row form-group">
                <label for="email" class="col-sm-3 col-form-label input-label">{{__trans('email')}}</label>
                <div class="col-sm-9">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Email" value="{{old('email',auth()->user()->email)}}">

                </div>
            </div>
            <div class="row form-group">
                <label for="phone" class="col-sm-3 col-form-label input-label">{{__trans('phone')}} <span class="text-muted">(Optional)</span></label>
                <div class="col-sm-9">
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" placeholder="+x(xxx)xxx-xx-xx" value="{{old('phone',auth()->user()->phone)}}">
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">{{__trans('update_profile')}}</button>
            </div>
        </form>
        <!-- /Form -->
    </div>
</div>

@push('scripts')

<script>
    function previewImage(id, previewImage) {
        src = '';
        var total_file = document.getElementById(id).files.length;
        for (var i = 0; i < total_file; i++) {
            src += URL.createObjectURL(event.target.files[i]);
        }
        $('#' + previewImage).attr('src', src);
    }
</script>

@endpush
