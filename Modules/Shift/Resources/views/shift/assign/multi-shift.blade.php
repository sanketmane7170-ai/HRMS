<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('assign_multiple_shift')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.assign_multishift.toUser',[$user]) }}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">{{__trans('start_date')}}</label>
                            <div class="mb-3">
                                <input type="text" name="start_date" class="form-control datepicker" placeholder="{{__trans('select_start_date')}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">{{__trans('end_date')}}</label>
                            <div class="mb-3">
                                <input type="text" name="end_date" class="form-control datepicker" placeholder="{{__trans('select_end_date')}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                      <div class="form-group">
                          <label>Shift</label>
                          <select class="form-control select" name="schedule_id" id="shiftSelect">
                              <option>Select Shift</option>
                              @foreach($shifts as $shift)
                                <x-shift::ShiftOption :shift="$shift" />
                              @endforeach
                          </select>
                      </div>   
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('add')}} </button>
            </div>
        </form>
    </div>
</div>


<script>
    loadAjaxSelect2();
    initselect2();
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });
</script>