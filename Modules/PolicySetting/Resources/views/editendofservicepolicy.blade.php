<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('update_end_Of_service_policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.settings.editendOfServicePolicy',$spolicy->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="leave_type_id" class="form-label">{{__trans('leave_type')}}</label>
                            <select name="leave_type_id" id="leave_type_id" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach ($leaveTypes as $type)
                                    <option value="{{$type->id}}" @if($spolicy->leave_type_id == $type->id) selected @endif>{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="salary_type" class="form-label">{{__trans('salary_type')}}</label>
                            <select name="salary_type" id="salary_type" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                <option value="Gross" @if($spolicy->salary_type == 'Gross') selected @endif >{{__trans('gross')}}</option>
                                <option value="Basic" @if($spolicy->salary_type == 'Basic') selected @endif >{{__trans('basic')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="month_day" class="form-label">{{__trans('month_day')}}</label>
                            <select name="month_day" id="month_day" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                <option value="30" @if($spolicy->month_day == '30') selected @endif >{{__trans('30')}}</option>
                                <option value="30.5" @if($spolicy->month_day == '30.5') selected @endif >{{__trans('30.5')}}</option>
                                <option value="31" @if($spolicy->month_day == '31') selected @endif >{{__trans('31')}}</option>
                                <option value="365" @if($spolicy->month_day == '365') selected @endif >{{__trans('365')}}</option>
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
    var today = new Date();
    today.setHours(0, 0, 0, 0);

    initselect2search();
    flatpickr("input.datetime", {
        enableTime: false,
        dateFormat: "Y-m-d",
        startDate: today
    });
</script>
