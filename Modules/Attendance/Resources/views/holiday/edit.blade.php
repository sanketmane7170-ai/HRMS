<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edi_holiday')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.holidays.update',$holiday)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="detail">{{__trans('description')}}</label>
                            <div class="mb-3">
                                <input name="detail" id="detail" class="form-control" value="{{$holiday->detail}}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">{{__trans('holiday_start_date')}}</label>
                            <div class="mb-3">
                                <input type="text" name="start_date" class="form-control datepicker" placeholder="{{__trans('select_start_date')}}" value="{{$holiday->start_date}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">{{__trans('holiday_end_date')}}</label>
                            <div class="mb-3">
                                <input type="text" name="end_date" class="form-control datepicker" placeholder="{{__trans('select_end_date')}}" value="{{$holiday->end_date}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_recurring">
                                <input type="checkbox" value="1" class="form-check-input" name="is_recurring" id="is_recurring" @if($holiday->is_recurring) checked @endif> {{__trans('is_recurring_holiday')}}
                            </label>
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
    loadAjaxSelect2();
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });
</script>
