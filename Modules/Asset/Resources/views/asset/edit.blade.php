<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_asset')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.asset.update',$asset)}}" datatable="true" method="POST"
            class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="unique_id" class="form-label">{{__trans('serial_number')}}</label>
                            <input type="text" name="unique_id" class="form-control" id="unique_id"
                                placeholder="{{__trans('serial_number')}}" value="{{$asset->unique_id}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="model" class="form-label">{{__trans('model')}}</label>
                            <input type="text" name="model" class="form-control" id="model"
                                placeholder="{{__trans('model')}}" value="{{$asset->model}}">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>{{__trans('asset_type')}}</label>
                            <select name="asset_type_id" class="ajax-select2" id="asset_type_id"
                                data-target="{{route('ajax.select2.fetch.asset-types')}}">
                                <option value="{{$asset->asset_type_id}}">{{$asset->type->name}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>{{__trans('asset_manufacturer')}}</label>
                            <select name="asset_manufacturer_id" class="ajax-select2" id="asset_manufacturer_id"
                                data-target="{{route('ajax.select2.fetch.asset-manufacturers')}}">
                                <option value="{{$asset->asset_manufacturer_id}}">{{$asset->manufacturer->name}}
                                </option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="purchase_date" class="form-label">{{__trans('purchase_date')}}</label>
                            <input type="text" value="{{$asset->purchase_date}}" name="purchase_date"
                                class="form-control datetime" placeholder="{{__trans('purchase_date')}}">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>{{__trans('asset_description')}}</label>
                            <textarea name="description" id="description" cols="30" rows="6"
                                class="form-control">{{$asset->description}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>

<script>
loadAjaxSelect2()
flatpickr("input.datetime", {
    enableTime: true,
    // minDate: "today",
    dateFormat: "Y-m-d",
});
</script>