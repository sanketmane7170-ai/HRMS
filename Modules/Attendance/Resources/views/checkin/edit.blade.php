<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_attendance')}} : {{$attendance->user->name}} [{{$attendance->date}}]</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.attendances.update',$attendance)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="time" class="form-label">{{__trans('clock_in')}}</label>
                            <input type="time" name="clock_in" class="form-control" id="clock_in" placeholder="{{__trans('clock_in')}}" value="{{$attendance->clock_in}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="time" class="form-label">{{__trans('clock_out')}}</label>
                            <input type="time" name="clock_out" class="form-control" id="clock_out" placeholder="{{__trans('clock_out')}}" value="{{$attendance->clock_out}}">
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
