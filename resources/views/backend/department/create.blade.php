<div id="addResourceModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h4 class="modal-title">{{__trans('add_department')}}</h4> -->
                <h4 class="modal-title">{{__trans('add_branch')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('backend.departments.store')}}" datatable="true" method="POST"
                class="ajax-form-submit reset">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('name')}}</label>
                                <input type="text" name="name" class="form-control" id="field-1"
                                    placeholder="{{__trans('name')}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('short_name')}}</label>
                                <input type="text" name="short_name" class="form-control" id="short_name"
                                    placeholder="{{__trans('short_name')}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('start_number')}}</label>
                                <input type="number" name="start_number" class="form-control" id="start_number"
                                    placeholder="{{__trans('start_number')}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">{{__trans('code')}}</label>
                                <input type="text" name="code" class="form-control" id="code"
                                    placeholder="{{__trans('code')}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address" class="form-label">{{__trans('address')}}</label>
                                <input type="text" name="address" class="form-control" id="address"
                                    placeholder="{{__trans('address')}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="login_radius" class="form-label">{{__trans('login_radius')}}</label>
                                <input type="number" name="login_radius" class="form-control" id="login_radius"
                                    placeholder="{{__trans('login_radius')}}" min="0"
                                    oninput="this.value = Math.abs(this.value)"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="budget" class="form-label">{{__trans('budget')}}</label>
                                <input type="number" name="budget" class="form-control" id="budget"
                                    placeholder="{{__trans('budget')}}" min="0"
                                    oninput="this.value = Math.abs(this.value)"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __trans('cancel_off_credit') }}</label>
                                <select name="cancel_off_credit" id="cancel_off_credit" class="form-control">
                                    <option value="">{{ __trans('select_option') }}</option>
                                    <option value="leave">{{ __trans('leave') }}</option>
                                    <option value="amount">{{ __trans('amount') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Take Amount (Hidden by default) -->
                        <div class="col-md-6 d-none" id="cancel_off_amount_wrapper">
                            <div class="mb-3">
                                <label class="form-label">{{ __trans('cancel_off_amount') }}</label>
                                <input type="number" name="cancel_off_amount" class="form-control" min="0"
                                    oninput="this.value = Math.abs(this.value)">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label d-block">{{ __trans('over_time') }}</label>

                                <!-- Hidden field ensures 0 is sent when unchecked -->
                                <input type="hidden" name="over_time" value="0">

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="over_time" id="over_time"
                                        value="1">
                                    <label class="form-check-label" for="over_time">
                                        {{ __trans('enable_over_time') }}
                                    </label>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="logo" class="form-label">{{__trans('logo')}}</label>
                                <input type="file" name="logo" class="form-control" id="logo">
                            </div>
                        </div>

                        <!-- Small Logo Upload -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="small_logo" class="form-label">{{__trans('small_logo')}}</label>
                                <input type="file" name="small_logo" class="form-control" id="small_logo">
                            </div>
                        </div>

                        <!-- Sign Upload -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sign" class="form-label">{{__trans('sign')}}</label>
                                <input type="file" name="sign" class="form-control" id="sign">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="header" class="form-label">{{__trans('header')}}</label>
                                <input type="file" name="header" class="form-control" id="header">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="footer" class="form-label">{{__trans('footer')}}</label>
                                <input type="file" name="footer" class="form-control" id="footer">
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
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const creditSelect = document.getElementById('cancel_off_credit');
    const amountWrapper = document.getElementById('cancel_off_amount_wrapper');

    creditSelect.addEventListener('change', function() {
        if (this.value === 'amount') {
            amountWrapper.classList.remove('d-none');
        } else {
            amountWrapper.classList.add('d-none');
        }
    });
});
</script>
