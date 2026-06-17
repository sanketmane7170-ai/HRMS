<div id="addResourceModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h4 class="modal-title">{{__trans('add_department')}}</h4> -->
                <h4 class="modal-title">{{__trans('add_portal_information')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('backend.settings.portals.info.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('name')}}</label>
                                <input type="text" name="name" class="form-control" id="field-1" placeholder="{{__trans('name')}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="base_url" class="form-label">{{__trans('base_url')}}</label>
                                <input type="text" name="base_url" class="form-control" id="base_url" placeholder="{{__trans('base_url')}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="unique_code" class="form-label">{{__trans('unique_code')}}</label>
                                <input type="text" name="unique_code" class="form-control" id="unique_code" placeholder="{{__trans('unique_code')}}">
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
</div>
