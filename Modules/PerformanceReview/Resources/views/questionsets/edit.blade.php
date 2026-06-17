<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Edit Question Set') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('questionset.update', $questionSet->id) }}" method="POST" class="ajax-form-submit reset" datatable="true">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label for="name" class="form-label fw-bold">{{ __trans('Name') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ $questionSet->name }}" required>
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
