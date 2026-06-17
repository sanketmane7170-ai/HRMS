<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ $component ? __trans('edit_component') : __trans('add_component') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form class="ajax-form-submit" method="POST" action="{{ $component ? route('backend.indian-payroll.salary-components.update', $component) : route('backend.indian-payroll.salary-components.store') }}">
            @csrf
            @if($component) @method('PUT') @endif
            <div class="modal-body">
                @if(!$component)
                <div class="form-group">
                    <label>{{ __trans('code') }}</label>
                    <input type="text" name="code" class="form-control text-uppercase" required placeholder="e.g. SHIFT_ALLOWANCE">
                </div>
                @endif
                <div class="form-group">
                    <label>{{ __trans('name') }}</label>
                    <input type="text" name="name" class="form-control" required value="{{ $component->name ?? '' }}">
                </div>
                @if(!$component)
                <div class="form-group">
                    <label>{{ __trans('type') }}</label>
                    <select name="type" class="form-control">
                        <option value="earning">{{ __trans('earning') }}</option>
                        <option value="deduction">{{ __trans('deduction') }}</option>
                    </select>
                </div>
                @endif
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" class="form-check-input" name="is_taxable" value="1" @checked($component->is_taxable ?? true)>
                    <label class="form-check-label">{{ __trans('is_taxable') }}</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" class="form-check-input" name="considered_for_pf_wage" value="1" @checked($component->considered_for_pf_wage ?? false)>
                    <label class="form-check-label">{{ __trans('considered_for_pf_wage') }}</label>
                </div>
                @if($component)
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" class="form-check-input" name="is_active" value="1" @checked($component->is_active)>
                    <label class="form-check-label">{{ __trans('active') }}</label>
                </div>
                @endif
                <div class="form-group">
                    <label>{{ __trans('display_order') }}</label>
                    <input type="number" name="display_order" class="form-control" value="{{ $component->display_order ?? 0 }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __trans('save') }}</button>
            </div>
        </form>
    </div>
</div>
