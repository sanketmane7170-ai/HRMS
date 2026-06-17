<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_leave_type')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.leave-types.update',$leaveType)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{__trans('name')}}</label>
                            <input type="text" name="name" class="form-control datetime" placeholder="{{__trans('name')}}" value="{{$leaveType->name}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="days" class="form-label">{{__trans('leaves_per_year')}}</label>
                            <input type="number" min="0" name="days" class="form-control" placeholder="{{__trans('leaves allowed')}}" value="{{$leaveType->days}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="type" class="form-label">{{__trans('type')}}</label>
                            <select name="type" id="type" class="select-search">
                                @foreach (\Modules\Leave\Enums\LeaveType::cases() as $type)
                                <option value="{{$type->value}}" @if($leaveType->type->value == $type->value) selected @endif>{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="is_paid_leave" class="form-label">{{__trans('is_paid_leave')}}</label>
                            <select name="is_paid" id="is_paid_leave" class="select-search form-control">
                                <option value="1" @if($leaveType->is_paid) selected @endif>Yes</option>
                                <option value="0" @if(!$leaveType->is_paid) selected @endif>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="is_recurring" class="form-label">{{__trans('is_recurring')}}</label>
                            <select name="is_recurring" id="is_recurring" class="select-search form-control">
                                <option value="1" @if($leaveType->is_recurring) selected @endif>Yes</option>
                                <option value="0" @if(!$leaveType->is_recurring) selected @endif>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12" id="no_of_leaves_container" style="display:none;">
                        <div class="mb-3">
                            <label for="no_of_leaves" class="form-label">{{__trans('no_of_leaves')}}</label>
                            <input type="number" min="0" id="no_of_leaves_id" name="no_of_leaves" class="form-control" placeholder="{{__trans('no_of_leaves')}}" value="{{ $leaveType->no_of_leaves?? 0}}">
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
    $(document).ready(function () {
        toggleNoOfLeaves();
        $('#is_recurring').change(function () {
            toggleNoOfLeaves();
        });
        function toggleNoOfLeaves() {
            var isRecurringValue = $('#is_recurring').val();
            if (isRecurringValue === '1') {
                $('#no_of_leaves_container').show();
                $('#no_of_leaves_id').attr('min', '0');
            } else {
                $('#no_of_leaves_container').hide();
                $('#no_of_leaves_id').removeAttr('min');
            }
        }
    });
</script>
