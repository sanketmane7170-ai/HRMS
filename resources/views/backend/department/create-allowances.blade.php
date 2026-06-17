    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Allowance for {{ $department->name }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('backend.departments.storeallowances', $department->id) }}" method="POST" class="ajax-form-submit reset" datatable="true">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __trans('allowance_name') }}</label>
                                <input type="text" name="allowance_name" class="form-control" placeholder="{{ __trans('allowance_name') }}" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __trans('type') }}</label>
                                <select name="type" class="form-control" required>
                                    <option value="monthly">{{ __trans('monthly') }}</option>
                                    <option value="yearly">{{ __trans('yearly') }}</option>
                                    <option value="one_time">{{ __trans('one_time') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __trans('allowance_type') }}</label>
                                <select name="allowance_type" class="form-control" required>
                                    <option value="fixed">{{ __trans('fixed') }}</option>
                                    <option value="percentage">{{ __trans('percentage') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __trans('amount') }}</label>
                                <input type="number" name="amount" step="0.01" class="form-control" placeholder="{{ __trans('amount') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                    <button type="submit" class="btn btn-info">{{ __trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        loadAjaxSelect2();
    </script>
