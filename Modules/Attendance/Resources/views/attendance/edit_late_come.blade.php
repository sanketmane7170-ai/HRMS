<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('update_extra_work_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.editRequest',$lateComeRequest->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('user') }}:</strong></label>
                            <select class="select-search" id="user_id" disabled>
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                    <option @if($user->id==$lateComeRequest->user_id) selected @endif>{{$user->employee_id}} {{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('late_minute') }}:</strong></label>
                            <input type="number" name="late_minute" value="{{ number_format($lateComeRequest->late_minute)}}" class="form-control" placeholder="{{__trans('late_minute')}}">
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
