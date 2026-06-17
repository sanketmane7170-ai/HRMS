<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_designation')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.departments.updateallowances', [$department->id, $allowance->id]) }}" datatable="true" method="POST" class="ajax-form-submit">
            @csrf
            @method('PUT')

            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __trans('allowance_name') }}</label>
                        <input type="text" name="allowance_name" class="form-control" value="{{ $allowance->allowance_name }}" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __trans('type') }}</label>
                        <select name="type" class="form-control" required>
                            <option value="monthly" {{ $allowance->type == 'monthly' ? 'selected' : '' }}>{{ __trans('monthly') }}</option>
                            <option value="yearly" {{ $allowance->type == 'yearly' ? 'selected' : '' }}>{{ __trans('yearly') }}</option>
                            <option value="one_time" {{ $allowance->type == 'one_time' ? 'selected' : '' }}>{{ __trans('one_time') }}</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __trans('allowance_type') }}</label>
                        <select name="allowance_type" class="form-control" required>
                            <option value="fixed" {{ $allowance->allowance_type == 'fixed' ? 'selected' : '' }}>{{ __trans('fixed') }}</option>
                            <option value="percentage" {{ $allowance->allowance_type == 'percentage' ? 'selected' : '' }}>{{ __trans('percentage') }}</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __trans('amount') }}</label>
                        <input type="number" name="amount" step="0.01" class="form-control" value="{{ $allowance->amount }}" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info">{{ __trans('update') }}</button>
            </div>
        </form>
    </div>
</div>
<script>
    initselect2search();
    loadAjaxSelect2();
</script>
