<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('update_deducation')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.user.update.allowance_deduction',$deducation->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{__trans('title')}}</label>
                            <input type="text" name="name" class="form-control" value="{{ $deducation->name }}">
                            <input type="hidden" name="type" value="2">
                        </div>
                    </div>
                    {{--  <div class="col-md-12">
                        <div class="mb-3">
                            <label for="amount" class="form-label">{{__trans('amount')}}</label>
                            <input type="number" name="amount" class="form-control" value="{{ $deducation->amount }}" id="amount" placeholder="{{__trans('amount')}}">
                        </div>
                    </div>  --}}
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
    loadAjaxSelect2();
    initselect2search();
</script>
<script>
    
</script>