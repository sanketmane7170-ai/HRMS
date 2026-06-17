<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Edit Score Criterion') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('scorecriterion.update', $criterion->id) }}" method="POST" class="ajax-form-submit reset" datatable="true">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Title') }}</label>
                    <input type="text" name="title" class="form-control" value="{{ $criterion->title }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Min Score') }}</label>
                    <input type="number" name="min_score" class="form-control" value="{{ $criterion->min_score }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Max Score') }}</label>
                    <input type="number" name="max_score" class="form-control" value="{{ $criterion->max_score }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Description') }}</label>
                    <textarea name="description" class="form-control" rows="3">{{ $criterion->description }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info">{{ __trans('update') }}</button>
            </div>
        </form>
    </div>
</div>
