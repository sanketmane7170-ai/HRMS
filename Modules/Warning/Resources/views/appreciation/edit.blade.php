<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('raise_new_appreciation') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.appreciation.update', $userAppreciation) }}" datatable="true" method="POST"
            class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">{{ __trans('employee') }}</label>
                            <select name="user_id" id="user_id" class="form-control ajax-select2"
                                data-target="{{ route('ajax.select2.fetch.users') }}">
                                <option value="{{ $userAppreciation->user->id }}">{{ $userAppreciation->user->employee_id }} -
                                    {{ $userAppreciation->user->name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">{{ __trans('date') }}</label>
                            <input type="text" class="form-control datepicker" name="date"
                                value="{{ $userAppreciation->date }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="type" class="form-label">{{ __trans('type') }}</label>
                            <input type="text" class="form-control" name="type" value="{{ $userAppreciation->type }}" placeholder="enter type of appreciation">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="document" class="form-label">{{ __trans('document') }}</label>
                            <input type="file" name="document" id="document" class="form-control"></input>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="document" class="form-label">{{ __trans('acknowledgement') }}</label>
                            <input type="checkbox"  {{ $userAppreciation->acknowledgement=="Yes"? 'checked': '' }} name="acknowledgement" id="acknowledgement" >
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">  
                            <label for="detail" class="form-label">{{ __trans('details') }}</label>
                            <textarea name="detail" id="detail" class="form-control">{!! $userAppreciation->detail !!}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{ __trans('save') }} </button>
            </div>
        </form>
    </div>
</div>
<script>
    initselect2();
    loadAjaxSelect2();
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
        maxDate: "today",
    });
    initTextEditor(['detail']);
</script>
