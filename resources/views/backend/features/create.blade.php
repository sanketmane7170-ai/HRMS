<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
<div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__trans('add_feature')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('backend.features.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('date')}}</label>
                                <input type="text" class="form-control datepicker" name="date"  value="{{date('Y-m-d')}}" placeholder="{{__trans('date')}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('version')}}</label>
                                <input type="text" name="version" class="form-control" id="field-1" placeholder="{{__trans('version')}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="feature" class="form-label">{{__trans('feature')}}</label>
                                <input type="text" name="feature" class="form-control" id="feature" placeholder="{{__trans('feature')}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="url" class="form-label">{{__trans('url')}}</label>
                                <input type="text" name="url" class="form-control" id="url" placeholder="{{__trans('feature')}}">
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
                </div>
            </form>
        </div>
    </div>
    <script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
    <script>
        loadAjaxSelect2();
        flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });
    </script>
    
