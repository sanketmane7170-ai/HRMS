<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_end_Of_service_policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.settings.addendOfServicePolicy')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="leave_type_id" class="form-label">{{__trans('leave_type')}}</label>
                            <select name="leave_type_id" id="leave_type_id" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach ($leaveTypes as $type)
                                <option value="{{$type->id}}">{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="salary_type" class="form-label">{{__trans('salary_type')}}</label>
                            <select name="salary_type" id="salary_type" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                <option value="Gross">{{__trans('Gross')}}</option>
                                <option value="Basic">{{__trans('Basic')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="month_day" class="form-label">{{__trans('month_day')}}</label>
                            <select name="month_day" id="month_day" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                <option value="30">{{__trans('30')}}</option>
                                <option value="30.5">{{__trans('30.5')}}</option>
                                <option value="31">{{__trans('31')}}</option>
                                <option value="365">{{__trans('365')}}</option>
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
