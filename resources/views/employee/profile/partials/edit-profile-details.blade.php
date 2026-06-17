<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_personal_details')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.employee.profile.details.update')}}" datatable="true" method="POST" class="ajax-form-submit reset" {{$action ??''}}>
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('name')}}</label>
                            <input type="text" name="name" class="form-control" id="name" placeholder="{{__trans('name')}}" value="{{ $user->name }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('gender')}}</label>
                            <select name="gender" id="gender" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach (\App\Enums\Gender::cases() as $gender)
                                <option value="{{$gender->value}}" @if($user->profile->gender == $gender->value) selected @endif>{{$gender->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('work_email')}}</label>
                            <input type="text" name="email" class="form-control" id="email" placeholder="{{__trans('work_email')}}" value="{{ $user->email }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('emp_id')}}</label>
                            <input type="text" name="employee_id" class="form-control" id="employee_id" placeholder="{{__trans('employee_id')}}" value="{{ $user->employee_id }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('work_phone')}}</label>
                            <input type="text" name="work_phone" class="form-control" id="work_phone" placeholder="{{__trans('work_phone')}}" value="{{ $user->phone }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('personal_email')}}</label>
                            <input type="text" name="personal_email" class="form-control" id="personal_email" placeholder="{{__trans('personal_email')}}" value="{{ $user->profile->personal_email }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('personal_phone')}}</label>
                            <input type="text" name="personal_phone" class="form-control" id="personal_phone" placeholder="{{__trans('personal_phone')}}" value="{{ $user->profile->personal_phone }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('date_of_birth')}}</label>
                            <input type="date" name="date_of_birth" class="form-control" id="date_of_birth" placeholder="{{__trans('date_of_birth')}}" value="{{ $user->profile->date_of_birth->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('nationality')}}</label>
                            <select name="country_id" id="country_id" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                <option>{{__trans('select_a_option')}}</option>
                                @foreach (getCountryList() as $country)
                                <option value="{{$country->id}}" @if($user->profile->country_id == $country->id) selected @endif>{{$country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('marital_status')}}</label>
                            <select name="martial_status" id="martial_status" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach (\App\Enums\MartialStatus::cases() as $status)
                                <option value="{{$status->value}}" @if($user->profile->martial_status->value == $status->value) selected @endif>{{$status->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('address')}}</label>
                            <textarea rows="5" name="address" cols="5" class="form-control" placeholder="938 Green Acres Road">{{$user->profile->address}}</textarea>
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

<script>
    initselect2search();
</script>
