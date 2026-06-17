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
                <div class="row">
                    @if(!$component)
                    <div class="col-md-6 form-group">
                        <label>{{ __trans('code') }} <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase" required placeholder="e.g. SHIFT_ALLOWANCE">
                        <small class="text-muted">A unique identifier. Spaces become underscores.</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __trans('type') }} <span class="text-danger">*</span></label>
                        <select name="type" class="form-control">
                            <option value="earning">{{ __trans('earning') }}</option>
                            <option value="deduction">{{ __trans('deduction') }}</option>
                            <option value="employer_contribution">Employer Contribution</option>
                        </select>
                    </div>
                    @endif
                    <div class="{{ $component ? 'col-md-8' : 'col-md-12' }} form-group">
                        <label>{{ __trans('name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="{{ $component->name ?? '' }}" placeholder="e.g. Shift Allowance">
                    </div>
                    <div class="{{ $component ? 'col-md-4' : 'col-md-12' }} form-group">
                        <label>{{ __trans('display_order') }}</label>
                        <input type="number" name="display_order" class="form-control" value="{{ $component->display_order ?? 0 }}">
                        <small class="text-muted">Lower numbers appear first.</small>
                    </div>
                </div>

                <hr class="my-2">
                <label class="d-block mb-2" style="font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">Treatment</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" id="sw_taxable" name="is_taxable" value="1" @checked($component->is_taxable ?? true)>
                            <label class="form-check-label" for="sw_taxable">{{ __trans('is_taxable') }}</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" id="sw_pf" name="considered_for_pf_wage" value="1" @checked($component->considered_for_pf_wage ?? false)>
                            <label class="form-check-label" for="sw_pf">{{ __trans('considered_for_pf_wage') }}</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" id="sw_ctc" name="is_part_of_ctc" value="1" @checked($component->is_part_of_ctc ?? true)>
                            <label class="form-check-label" for="sw_ctc">Part of CTC</label>
                        </div>
                        @if($component)
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" id="sw_active" name="is_active" value="1" @checked($component->is_active)>
                            <label class="form-check-label" for="sw_active">{{ __trans('active') }}</label>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __trans('save') }}</button>
            </div>
        </form>
    </div>
</div>
