<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('new_general_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.admin.generalRequest.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('user') }}:</strong></label>
                            <select name="user_id" class="select-search" id="user_id">
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                    <option value="{{$user->id}}">{{$user->employee_id}} {{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('general_type') }}:</strong></label>
                            <select name="general_type" class="select-search getTotal">
                                <option value="">Select general request type</option>
                                @foreach ($requestApp as $type)
                                    <option value="{{$type->id}}">{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('date') }}:</strong></label>
                            <input type="date" name="date" class="form-control" placeholder="{{__trans('date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('note') }}:</strong></label>
                            <textarea name="note" class="form-control" placeholder="{{__trans('note')}}"></textarea>
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
    flatpickr("input.datetime", {
        enableTime: false,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });

    $(document).on('change', '#user_id', function () {
        $('#showlimit').text('');
    });
</script>
