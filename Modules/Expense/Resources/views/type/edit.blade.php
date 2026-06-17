
<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_expense_type')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.expense-types.update',$expenseType)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">

                    

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{__trans('name')}}</label>
                            <input type="text" name="name" class="form-control" placeholder="{{__trans('name')}}" value="{{$expenseType->name}}">
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

