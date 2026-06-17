<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Add KPI') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('kpi.store') }}" method="POST" class="ajax-form-submit reset" datatable="true">
            @csrf
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label for="duration_id" class="form-label fw-bold">{{ __trans('Duration') }}</label>
                    <select name="duration_id" class="form-control" required>
                        <option value="">{{ __trans('Select Duration') }}</option>
                        @foreach($durations as $duration)
                            <option value="{{ $duration->id }}">{{ $duration->label }} ({{ $duration->months }} months)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="title" class="form-label fw-bold">{{ __trans('Title') }}</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. 1 Month KPI" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-bold">{{ __trans('Description') }}</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Enter description here..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{ __trans('save') }}</button>
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
