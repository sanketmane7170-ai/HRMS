<div id="addResourceModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__trans('add_department')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('backend.divisions.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
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
                                <label for="code" class="form-label">{{__trans('code')}}</label>
                                <input type="text" name="code" class="form-control" id="code" placeholder="{{__trans('code')}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <select name="branch_id" id="branch_id" class="form-control select">
                                    <option value="">{{__trans('select_branch')}}</option>
                                     <option value="0">{{__trans('all')}}</option>
                                    @foreach (\App\Models\Department::all() as $branch)
                                        <option  value="{{$branch->id}}">{{$branch->name}}</option>
                                    @endforeach
                                </select>
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
