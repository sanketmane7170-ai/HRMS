<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Edit Review Duration') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('reviewduration.update', $duration->id) }}" method="POST" class="ajax-form-submit reset" datatable="true">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label for="label" class="form-label fw-bold">{{ __trans('Name') }}</label>
                    <input type="text" name="label" class="form-control" value="{{ $duration->label }}" placeholder="e.g. Quarterly" required>
                </div>

                <div class="mb-4">
                    <label for="months" class="form-label fw-bold">{{ __trans('Duration') }}</label>
                    <input type="number" name="months" class="form-control" value="{{ $duration->months }}" placeholder="e.g. 3" min="1" max="24" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{ __trans('update') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    flatpickr("input.datetime", {
        enableTime: false,
        dateFormat: "Y-m-d",
    });
</script>
