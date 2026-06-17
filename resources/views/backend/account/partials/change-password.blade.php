<div class="card">
    <div class="card-header">
        <h5 class="card-title">{{__trans('change_password')}}</h5>
    </div>
    <div class="card-body">

        <!-- Form -->
        <form action="{{route('backend.update-password')}}" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="row form-group">
                <label for="current_password" class="col-sm-3 col-form-label input-label">{{__trans('current_password')}}</label>
                <div class="col-sm-9">
                    <input type="password" value="{{old('password')}}" class="form-control @error('current_password') is-invalid @enderror" name="current_password" id="current_password" placeholder="Enter current password">
                    @error('current_password')
                    <span class="invalid-feedback error" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <div class="row form-group">
                <label for="new_password" class="col-sm-3 col-form-label input-label">{{__trans('new_password')}}</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" id="password" placeholder="Enter new password">
                    @error('new_password')
                    <span class="invalid-feedback error" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <div class="progress progress-md mt-2">
                        <div class="progress-bar bg-danger" id="password-strength" role="progressbar" style="width: 2%" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <label for="confirm_password" class="col-sm-3 col-form-label input-label">{{__trans('confirm_new_password')}}</label>
                <div class="col-sm-9">
                    <div class="mb-3">
                        <input type="password" class="form-control @error('confirm_password') is-invalid @enderror" name="confirm_password" id="confirm_password" placeholder="Confirm your new password">
                        @error('confirm_password')
                        <span class="invalid-feedback error" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <h5>Password requirements:</h5>
                    <p class="mb-2">Ensure that these requirements are met:</p>
                    <ul class="list-unstyled small">
                        <li class="">
                            <span class="low-upper-case">
                                <i class="fas fa-circle" aria-hidden="true"></i>
                                &nbsp;Lowercase &amp; Uppercase
                            </span>
                        </li>
                        <li class="">
                            <span class="one-number">
                                <i class="fas fa-circle" aria-hidden="true"></i>
                                &nbsp;Number (0-9)
                            </span>
                        </li>
                        <li class="">
                            <span class="one-special-char">
                                <i class="fas fa-circle" aria-hidden="true"></i>
                                &nbsp;Special Character (!@#$%^&*)
                            </span>
                        </li>
                        <li class="">
                            <span class="eight-character">
                                <i class="fas fa-circle" aria-hidden="true"></i>
                                &nbsp;Atleast 8 Character
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">{{__trans('change_password')}}</button>
            </div>
        </form>
        <!-- /Form -->
    </div>
</div>
<style>
    .progress-bar-danger {
        background-color: #e90f10 !important;
    }

    .progress-bar-warning {
        background-color: #ffad00 !important;
    }

    .progress-bar-success {
        background-color: #02b502 !important;
    }
</style>


@push('scripts')
<script>
    let state = false;
    let password = document.getElementById("password");
    let passwordStrength = document.getElementById("password-strength");
    let lowUpperCase = document.querySelector(".low-upper-case i");
    let number = document.querySelector(".one-number i");
    let specialChar = document.querySelector(".one-special-char i");
    let eightChar = document.querySelector(".eight-character i");

    password.addEventListener("keyup", function() {
        let pass = document.getElementById("password").value;
        checkStrength(pass);
    });

    function toggle() {
        if (state) {
            document.getElementById("password").setAttribute("type", "password");
            state = false;
        } else {
            document.getElementById("password").setAttribute("type", "text")
            state = true;
        }
    }

    function myFunction(show) {
        show.classList.toggle("fa-eye-slash");
    }

    function checkStrength(password) {
        let strength = 0;

        //If password contains both lower and uppercase characters
        if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
            strength += 1;
            lowUpperCase.classList.remove('fa-circle');
            lowUpperCase.classList.add('fa-check');
            lowUpperCase.classList.add('text-success');
        } else {
            lowUpperCase.classList.add('fa-circle');
            lowUpperCase.classList.remove('fa-check');
            lowUpperCase.classList.remove('text-success');
        }
        //If it has numbers and characters
        if (password.match(/([0-9])/)) {
            strength += 1;
            number.classList.remove('fa-circle');
            number.classList.add('fa-check');
            number.classList.add('text-success');

        } else {
            number.classList.add('fa-circle');
            number.classList.remove('fa-check');
            number.classList.remove('text-success');
        }
        //If it has one special character
        if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
            strength += 1;
            specialChar.classList.remove('fa-circle');
            specialChar.classList.add('fa-check');
            specialChar.classList.add('text-success');

        } else {
            specialChar.classList.add('fa-circle');
            specialChar.classList.remove('fa-check');
            specialChar.classList.remove('text-success');
        }
        //If password is greater than 7
        if (password.length > 7) {
            strength += 1;
            eightChar.classList.remove('fa-circle');
            eightChar.classList.add('fa-check');
            eightChar.classList.add('text-success');
        } else {
            eightChar.classList.add('fa-circle');
            eightChar.classList.remove('fa-check');
            eightChar.classList.remove('text-success');
        }

        // If value is less than 2
        if (strength == 2) {
            passwordStrength.classList.remove('progress-bar-warning');
            passwordStrength.classList.remove('progress-bar-success');
            passwordStrength.classList.add('progress-bar-danger');
            passwordStrength.style = 'width: 40%';
        } else if (strength == 3) {
            passwordStrength.classList.remove('progress-bar-success');
            passwordStrength.classList.remove('progress-bar-danger');
            passwordStrength.classList.add('progress-bar-warning');
            passwordStrength.style = 'width: 80%';
        } else if (strength == 4) {
            passwordStrength.classList.remove('progress-bar-warning');
            passwordStrength.classList.remove('progress-bar-danger');
            passwordStrength.classList.add('progress-bar-success');
            passwordStrength.style = 'width: 100%';
        }
    }
</script>

@endpush
