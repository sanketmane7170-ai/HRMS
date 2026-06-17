<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_attendance')}} : {{$attendance->user->name}} [{{$attendance->date}}]</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.attendance.user-day-attendance.update',[$attendance->user,$attendance->date])}}" replace=".attendance-{{$attendance->user_id}}" method="POST" class="ajax-form-submit">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="clock_in" class="form-label">{{__trans('clock_in')}}</label>
                            <!-- <input type="text" name="clock_in" class="form-control timepicker" id="clock_in" placeholder="{{__trans('clock_in')}}" value="{{$attendance->clock_in}}"> -->
                            <input type="text" name="clock_in" class="form-control timepicker" id="clock_in" placeholder="{{__trans('clock_in')}}" value="{{isset($first_clock_in->clock_in) ? $first_clock_in->clock_in : ''}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">{{__trans('attendance_status')}}</label>
                            <select name="status" id="status" class="form-select select">
                                @foreach ($attendanceStatuses as $status)
                                <option value="{{$status->value}}" @selected(($attendance->status && $attendance->status->value == $status->value))>{{$status->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="clock_out" class="form-label">{{__trans('clock_out')}}</label>
                            <!-- <input type="text" name="clock_out" class="form-control timepicker" id="clock_out" placeholder="{{__trans('clock_out')}}" value="{{ $attendance->clock_out>$attendance->clock_in ?  $attendance->clock_out : '' }}"> -->
                            <input type="text" name="clock_out" class="form-control timepicker" id="clock_out" placeholder="{{__trans('clock_out')}}" value="{{ isset($last_clock_out->clock_out) ? $last_clock_out->clock_out : '' }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="clock_out" class="form-label">{{__trans('clock_out_date')}}</label>
                            <!-- <input type="text" id="clock_out" class="form-control datepickerclockout" name="clockout_date" placeholder="{{__trans('select_date')}}" value="{{$attendance->clock_out>$attendance->clock_in ? $attendance->clockout_date : '' }}"> -->
                            <input type="text" id="clock_out" class="form-control datepickerclockout" name="clockout_date" placeholder="{{__trans('select_date')}}" value="{{isset($last_clock_out->clockout_date) ? $last_clock_out->clockout_date : '' }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remark" class="form-label">{{__trans('reason_for_update')}}</label>
                            <textarea name="remark" id="remark" class="form-control" cols="30" rows="6">{{$attendance->remark}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            @can('Edit Attendance')
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
            @endcan
        </form>
    </div>
</div>

<script>
    initselect2();
    flatpickr('.timepicker', {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
    })
    flatpickr("input.datepickerclockout", {
        dateFormat: "Y-m-d",
        //maxDate: new Date()
        minDate: "{{ $attendance->date }}"
    });
</script>