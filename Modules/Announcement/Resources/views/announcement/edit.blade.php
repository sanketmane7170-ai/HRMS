<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_announcement')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.announcements.update',$announcement)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">

                    <!-- <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_at" class="form-label">{{__trans('department')}}</label>
                            <select name="department_id" id="department" class="form-control select">

                                <option value="">{{__trans('select_department')}}</option>

                                @foreach (\App\Models\Department::all() as $department)
                                <option @if($department->id == $announcement->department_id) selected @endif
                                    value="{{$department->id}}">{{$department->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_at" class="form-label">{{__trans('employee')}}</label>
                            <select name="user_id" id="user_id" class="form-control select">

                                <option value="">{{__trans('select_employee')}}</option>

                                @foreach (\App\Models\User::all() as $user)

                                <option @if($user->id == $announcement->user_id) selected @endif
                                    value="{{$user->id}}">{{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> -->

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_at" class="form-label">{{__trans('department')}}</label>
                            <select name="department_id" id="department" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.departments') }} ">

                                <option value="">{{__trans('select_department')}}</option>

                                @foreach (\App\Models\Department::all() as $department)
                                <option @if($department->id == $announcement->department_id) selected @endif
                                    value="{{$department->id}}">{{$department->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_at" class="form-label">{{__trans('employee')}}</label>
                            <select name="user_id" id="user_id" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.users') }}">

                                <option value="">{{__trans('select_employee')}}</option>

                                @foreach (\App\Models\User::all() as $user)

                                <option @if($user->id == $announcement->user_id) selected @endif
                                    value="{{$user->id}}">{{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_at" class="form-label">{{__trans('start_at')}}</label>
                            <input type="text" name="start_at" class="form-control datetime" placeholder="{{__trans('start_at')}}" value="{{$announcement->start_at}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_at" class="form-label">{{__trans('end_at')}}</label>
                            <input type="text" name="end_at" class="form-control datetime" placeholder="{{__trans('end_at')}}" value="{{$announcement->end_at}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="file" class="form-label">{{__trans('upload_file')}}</label>
                            <input type="file" name="file" class="form-control" placeholder="{{__trans('upload_file')}}">
                            @if($announcement->file)
                                <a href="{{ asset('uploads/users/'.$announcement->user_id.'/announcement/'.$announcement->file) }}" target="_blank">View Uploaded File</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="body" class="form-label">{{__trans('announcement_content')}} <span style="color: coral">(Image size should be 1000KB or less.)</span></label>
                            <textarea name="body" id="edit_body" class="form-control" cols="30" rows="6">{{$announcement->body}}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="announcement_type_id" class="form-label">{{__trans('announcement_type')}}</label>
                            <select name="announcement_type_id" class="form-control select-search" id="announcement_type_id">
                                @foreach ($announcementTypes as $announcementType)
                                <option value="{{$announcementType->id}}" @if($announcement->announcement_type_id == $announcementType->id) selected @endif>{{$announcementType->name}}</option>
                                @endforeach
                            </select>
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
    initTextEditor(['edit_body']);
    flatpickr("input.datetime", {
        enableTime: true,
        minDate: "today",
        dateFormat: "Y-m-d H:i",
    });
</script>
<script>
    loadAjaxSelect2();
</script>