<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_feature')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.features.update',$feature)}}" datatable="true" method="POST" class="ajax-form-submit">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('date')}}</label>
                            <input type="text" name="date" value="{{$feature->date}}" class="form-control datepicker" id="edit-field-1" placeholder="date">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('version')}}</label>
                            <input type="text" name="version" value="{{$feature->version}}" class="form-control" id="edit-field-1" placeholder="version">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="feature" class="form-label">{{__trans('feature')}}</label>
                            <input type="text" name="feature" class="form-control" id="feature" value="{{$feature->feature}}" placeholder="{{__trans('feature')}}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="url" class="form-label">{{__trans('url')}}</label>
                            <input type="text" name="url" class="form-control" id="url" value="{{$feature->url}}" placeholder="{{__trans('url')}}">
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('update')}} </button>
            </div>
        </form>
    </div>
</div>
<script>
    initselect2search();
    loadAjaxSelect2();
</script>


@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>

    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });
</script>
@endpush
