<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('Shift_Policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="#" datatable="true" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check form-switch" style="float: left;font-size: x-large;margin-right:10px;">
                            <input 
                                class="form-check-input toggle-switch autoadd" @if($is_admin_shift_show_to_manager && $is_admin_shift_show_to_manager->value==true) checked @endif
                                type="checkbox" 
                                id="isAllowSwitch" 
                                data-url="{{ route('backend.settings.shiftPolicy') }}"
                                data-token="{{ csrf_token() }}"
                            >
                            <label class="form-check-label" for="isAllowSwitch">Allow Managers To View All Shifts</label>
                            <br>
                            <input 
                                class="form-check-input toggle-switch autoadd" @if($shift_show_to_all && $shift_show_to_all->value==true) checked @endif
                                type="checkbox" 
                                id="isAllowSwitch" 
                                data-url="{{ route('backend.settings.viewToAllShiftPolicy') }}"
                                data-token="{{ csrf_token() }}"
                            >
                            <label class="form-check-label" for="isAllowSwitch">Shift View To All</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                {{--  <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>  --}}
            </div>
        </form>
    </div>
</div>