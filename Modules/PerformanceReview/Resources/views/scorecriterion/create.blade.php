<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Add Score Criterion') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('scorecriterion.store') }}" method="POST" class="ajax-form-submit reset" datatable="true">
            @csrf
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Title') }}</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Min Score') }}</label>
                    <input type="number" name="min_score" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Max Score') }}</label>
                    <input type="number" name="max_score" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Description') }}</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info">{{ __trans('save') }}</button>
            </div>
        </form>
    </div>
</div>
