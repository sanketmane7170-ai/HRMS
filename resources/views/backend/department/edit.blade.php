<div class="modal-dialog modal-lg">
    <div class="modal-content ">
        <div class="modal-header">
            <!-- <h4 class="modal-title">{{__trans('edit_department')}}</h4> -->
            <h4 class="modal-title">{{__trans('edit_branch')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.departments.update',$department)}}" datatable="true" method="POST"
            class="ajax-form-submit">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('name')}}</label>
                            <input type="text" name="name" value="{{$department->name}}" class="form-control"
                                id="edit-field-1" placeholder="Admin">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('short_name')}}</label>
                            <input type="text" name="short_name" value="{{$department->short_name}}"
                                class="form-control" id="short_name" placeholder="ADM">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('start_number')}}</label>
                            <input type="number" name="start_number" value="{{$department->start_number}}"
                                class="form-control" id="start_number" placeholder="ADM">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="code" class="form-label">{{__trans('code')}}</label>
                            <input type="text" name="code" class="form-control" id="code" value="{{$department->code}}"
                                placeholder="{{__trans('code')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="address" class="form-label">{{__trans('address')}}</label>
                            <input type="text" name="address" class="form-control" id="address"
                                value="{{$department->address}}" placeholder="{{__trans('address')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="login_radius" class="form-label">{{__trans('login_radius')}}</label>
                            <input type="number" name="login_radius" class="form-control" id="login_radius"
                                value="{{$department->login_radius}}" placeholder="{{__trans('login_radius')}}" min="0"
                                oninput="this.value = Math.abs(this.value)"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="budget" class="form-label">{{__trans('budget')}}</label>
                            <input type="number" name="budget" class="form-control" id="budget"
                                value="{{$department->budget}}" placeholder="{{__trans('budget')}}" min="0"
                                oninput="this.value = Math.abs(this.value)"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('cancel_off_credit') }}</label>
                            <select name="cancel_off_credit" id="edit_cancel_off_credit" class="form-control"
                                onchange="toggleCancelOffAmount(this.value)">
                                <option value="">{{ __trans('select_option') }}</option>
                                <option value="leave" {{ $department->cancel_off_credit == 'leave' ? 'selected' : '' }}>
                                    {{ __trans('leave') }}
                                </option>
                                <option value="amount"
                                    {{ $department->cancel_off_credit == 'amount' ? 'selected' : '' }}>
                                    {{ __trans('amount') }}
                                </option>
                            </select>

                        </div>
                    </div>

                    <!-- Cancel Off Amount (Conditional) -->
                    <div class="col-md-6 {{ $department->cancel_off_credit == 'amount' ? '' : 'd-none' }}"
                        id="edit_cancel_off_amount_wrapper">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('cancel_off_amount') }}</label>
                            <input type="number" name="cancel_off_amount" class="form-control" min="0"
                                value="{{ $department->cancel_off_amount }}"
                                oninput="this.value = Math.abs(this.value)">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label d-block">{{ __trans('over_time') }}</label>

                            <!-- Hidden field ensures 0 is sent when unchecked -->
                            <input type="hidden" name="over_time" value="0">

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="over_time" value="1"
                                    {{ $department->over_time ? 'checked' : '' }}>
                                <label class="form-check-label" for="over_time">
                                    {{ __trans('enable_over_time') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Logo Upload -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('logo') }}</label>
                            <input type="file" name="logo" class="form-control">
                            @if($department->logo)
                            <img src="{{ asset('storage/' . $department->logo) }}" class="img-thumbnail mt-2"
                                style="max-height: 100px;">
                            @endif
                        </div>
                    </div>

                    <!-- Small Logo Upload -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('small_logo') }}</label>
                            <input type="file" name="small_logo" class="form-control">
                            @if($department->small_logo)
                            <img src="{{ asset('storage/' . $department->small_logo) }}" class="img-thumbnail mt-2"
                                style="max-height: 100px;">
                            @endif
                        </div>
                    </div>

                    <!-- header Upload -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('sign') }}</label>
                            <input type="file" name="sign" class="form-control">
                            @if($department->sign)
                            <img src="{{ asset('storage/' . $department->sign) }}" class="img-thumbnail mt-2"
                                style="max-height: 100px;">
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('header') }}</label>
                            <input type="file" name="header" class="form-control">
                            @if($department->header)
                            <img src="{{ asset('storage/' . $department->header) }}" class="img-thumbnail mt-2"
                                style="max-height: 100px;">
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('footer') }}</label>
                            <input type="file" name="footer" class="form-control">
                            @if($department->footer)
                            <img src="{{ asset('storage/' . $department->footer) }}" class="img-thumbnail mt-2"
                                style="max-height: 100px;">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('update')}} </button>
            </div>
        </form>
    </div>
</div>
<script>
initselect2search();
</script>
<script>
document.addEventListener('shown.bs.modal', function() {
    const select = document.getElementById('edit_cancel_off_credit');
    if (select) {
        toggleCancelOffAmount(select.value);
    }
});
</script>
<script>
function toggleCancelOffAmount(value) {
    const wrapper = document.getElementById('edit_cancel_off_amount_wrapper');

    if (!wrapper) return;

    if (value === 'amount') {
        wrapper.classList.remove('d-none');
    } else {
        wrapper.classList.add('d-none');
        $('#edit_cancel_off_amount_wrapper input').val('');
    }
}
</script>
