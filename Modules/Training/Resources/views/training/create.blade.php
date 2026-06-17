<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_training')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.training.store')}}" datatable="true" method="POST"
            class="ajax-form-submit reset">
            @csrf

            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="department_id">Select Department</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="title">Training Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="description">Training Description</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>

                    <!-- <div class="col-md-12 mb-3">
                        <label for="videos">Upload Training Videos (Multiple Allowed)</label>
                        <input type="file" name="videos[]" class="form-control" accept="video/*" multiple>
                    </div> -->
                    <div class="col-md-12 mb-3">
                        <label for="videos">Upload Files (Multiple Allowed)</label>
                        <input type="file" name="videos[]" class="form-control" multiple>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-primary">{{ __trans('save') }}</button>
            </div>
        </form>

    </div>
</div>
<script>
initselect2search();
loadAjaxSelect2();

flatpickr("input.datetime", {
    enableTime: true,
    // minDate: "today",
    dateFormat: "Y-m-d",
});
</script>