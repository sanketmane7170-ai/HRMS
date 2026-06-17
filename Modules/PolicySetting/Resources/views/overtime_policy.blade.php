<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('overtime_policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.settings.overtime_policy')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check form-switch" style="float: left;font-size: x-large;margin-right:10px;">
                            <input 
                                class="form-check-input toggle-switch autoadd" @if($autoaddextraWork && $autoaddextraWork->value==1) checked @endif
                                type="checkbox" 
                                id="isAllowSwitch" 
                                data-url="{{ route('backend.autoAddExtraWork') }}"
                                data-token="{{ csrf_token() }}"
                            >
                            <label class="form-check-label" for="isAllowSwitch">Auto add extra hours</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-check-label" for="overtime_policy">
                            <span class="text-muted">Overtime hours <span style="color: red">(Enter value in minutes)</span></span>
                        </label>
                        <input class="form-control" type="text" id="overtime_hours" name="overtime_hours" value="{{ $overtime_hours?->value }}" >
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