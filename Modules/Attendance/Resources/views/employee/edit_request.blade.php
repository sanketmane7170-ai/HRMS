<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('update_extra_work_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.employee.editRequest',$extraWorkRequest->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('extra_hours') }}:</strong></label>
                            <input type="text" name="extra_hours" value="{{ $extraWorkRequest->extra_hours}}" class="form-control" placeholder="{{__trans('extra_hours')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('reason') }}:</strong></label>
                            <input type="text" name="reason" value="{{ $extraWorkRequest->reason}}" class="form-control" placeholder="{{__trans('add_reason')}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('update')}} </button>
            </div>
        </form>
    </div>
</div>
<script>
    initselect2search();
    flatpickr("input.datetime", {
        enableTime: false,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });
</script>
