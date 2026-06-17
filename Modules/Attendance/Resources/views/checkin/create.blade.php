<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_attendance')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.attendances.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">{{__trans('attendance_user')}}</label>
                            <div class="mb-3">
                                <select name="user_id" id="user_id" class="ajax-select2" data-target="{{route('ajax.select2.fetch.attendance-users')}}">

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label"></label>
                            <div class="mb-3">
                                <input type="text" name="date" class="form-control datepicker">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="time" class="form-label">{{__trans('clock_in')}}</label>
                            <input type="text" name="clock_in" class="form-control datetime" id="clock_in" placeholder="{{__trans('clock_in')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="time" class="form-label">{{__trans('clock_out')}}</label>
                            <input type="text" name="clock_out" class="form-control datetime" id="clock_out" placeholder="{{__trans('clock_out')}}">
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
    loadAjaxSelect2();
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });
    flatpickr("input.datetime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
    });
</script>