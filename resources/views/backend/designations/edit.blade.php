<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_designation')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.designations.update',$designation)}}" datatable="true" method="POST" class="ajax-form-submit">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('name')}}</label>
                            <input type="text" name="name" value="{{$designation->name}}" class="form-control" id="edit-field-1" placeholder="Admin">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="code" class="form-label">{{__trans('code')}}</label>
                            <input type="text" name="code" class="form-control" id="code" value="{{$designation->code}}" placeholder="{{__trans('code')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="grade" class="form-label">{{__trans('grade')}}</label>
                            <input type="text" name="grade" class="form-control" id="grade" value="{{$designation->grade}}" placeholder="{{__trans('grade')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                            <div class="mb-3">
                                <label for="department" class="form-label">{{__trans('branch')}}</label>
                                <select name="department_id" id="department_id" class="ajax-select2" data-target="{{route('ajax.select2.fetch.departmentswithall')}}">
                                <option value="{{$designation->department_id ? $designation->department_id : 0}}">{{$designation->department? $designation->department->name : 'All'}}</option>
                                </select>
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
