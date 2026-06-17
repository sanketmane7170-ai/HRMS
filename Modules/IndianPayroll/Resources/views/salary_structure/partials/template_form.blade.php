<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ $template ? __trans('edit_template') : __trans('add_template') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="{{ $template ? route('backend.indian-payroll.salary-templates.update', $template) : route('backend.indian-payroll.salary-templates.store') }}">
            @csrf
            @if($template) @method('PUT') @endif
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ __trans('name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ $template->name ?? '' }}" placeholder="e.g. Standard Employee CTC">
                </div>
                <div class="form-group">
                    <label>{{ __trans('description') }}</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Briefly describe how this template splits the CTC.">{{ $template->description ?? '' }}</textarea>
                </div>
                @if($template)
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" class="form-check-input" id="tpl_active" name="is_active" value="1" @checked($template->is_active)>
                    <label class="form-check-label" for="tpl_active">{{ __trans('active') }}</label>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __trans('save') }}</button>
            </div>
        </form>
    </div>
</div>
