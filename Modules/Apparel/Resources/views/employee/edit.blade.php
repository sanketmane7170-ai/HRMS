<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Update Uniform Request</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.employee.myApparel.update',$requestApp->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>Uniform:</strong></label>
                            <select name="apparel_id" class="select-search">
                                @foreach ($apparel as $type)
                                    @php
                                        $total = Modules\Apparel\Entities\ApparelRequest::where('apparel_id',$type->id)->where('status',1)->sum('number_of_apparel');
                                        $limit = $type->number_of_given - $total
                                    @endphp
                                    <option value="{{$type->id}}" @if($requestApp->apparel_id == $type->id) selected @endif>{{$type->name}} - ({{ $limit }} Limit)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>How many Uniform:</strong></label>
                            <input type="text" name="number_of_apparel" value="{{ $requestApp->number_of_apparel }}" class="form-control" placeholder="How many Uniform">
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
    flatpickr("input.datetime", {
        enableTime: false,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });
</script>
