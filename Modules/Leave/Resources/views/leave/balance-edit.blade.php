<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_leave_balance_of_')}} {{$response['user']->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.leave-balance.update',[$response['user'],$response['leaveType']]) }}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="leave_name" class="form-label">{{__trans('name')}}</label>
                            <input type="text" class="form-control" value="{{ $response['leaveType']->name }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="leave_type_id" class="form-label">{{__trans('leave_type')}}</label>
                            <input type="text" class="form-control" value="{{ $response['leaveType']->type }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="leave_name" class="form-label">{{__trans('available')}}</label>
                            <input type="text" name="available" class="form-control" value="{{ $response['balance'] }}">
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
