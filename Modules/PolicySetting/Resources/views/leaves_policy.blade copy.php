<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('leave_policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.settings.leaves_policy')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="leave_policy"
                                name="leave_policy_1" value="1" {{ $leavepolicy[0]->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="leave_policy">
                                <strong>Policy 1</strong><br>
                                <span class="text-muted">Within 6 months accrual of 2 days</span>
                            </label>
                        </div>
                    </div>

                    

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="leave_policy"
                                name="leave_policy_2" value="1" {{ $leavepolicy[1]->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="leave_policy">
                                <strong>Policy 2</strong><br>
                                <span class="text-muted">More than 6 months accrual of 2.5 days.</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="leave_policy"
                                name="leave_policy_3" value="1" {{ $leavepolicy[2]->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="leave_policy">
                                <strong>Policy 3</strong><br>
                                <span class="text-muted">Upto 1 Year accrual of 2.5days</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="leave_policy"
                                name="leave_policy_4" value="1" {{ $leavepolicy[3]->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="leave_policy">
                                <strong>Policy 4</strong><br>
                                <span class="text-muted">No Leave Allowed in probation.</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="leave_policy"
                                name="leave_policy_5" value="1" {{ $leavepolicy[4]->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="leave_policy">
                                <strong>Policy 5</strong><br>
                                <span class="text-muted">Addition of 30 days annual leave under no obligation.</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="leave_policy"
                                name="leave_policy_6" value="1" {{ $leavepolicy[5]->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="leave_policy">
                                <strong>Policy 6</strong><br>
                                <span class="text-muted">Previous Year balance to be added.</span>
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