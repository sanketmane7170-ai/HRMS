<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('attendance_policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.settings.attendance_policy')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="attendance_policy"
                                name="attendance_policy_1" value="1" {{ $attendancepolicy[0]->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="attendance_policy">
                                <strong>Policy 1</strong><br>
                                <span class="text-muted">Late comers by 15 mt ,30 to be considered as 1 hour or 2 hours late.</span>
                            </label>
                        </div>
                    </div>

                    

                    <div class="col-md-12">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="attendance_policy"
                                name="attendance_policy_2" value="1" {{ $attendancepolicy[1]->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="attendance_policy">
                                <strong>Policy 2</strong><br>
                                <span class="text-muted">Payroll to be run by attendance basis or no attendance basis.</span>
                            </label>
                        </div>
                    </div>

                   


                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>