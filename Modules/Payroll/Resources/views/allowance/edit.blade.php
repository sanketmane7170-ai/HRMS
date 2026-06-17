<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@if(request()->getHttpHost()=="cakesocial.momdigital.io"){{__trans('edit_employee_addition')}} @else{{__trans('edit_employee_allowance')}} @endif  : {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.user.user-salaries.updateallowance',[$user,$allowance])}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                     <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{__trans('title')}}</label>
                            <select name="title" class="form-control select-search" id="title">
                                <option value="" selected>Select allowance</option>
                                @foreach ($fixedAllowance as $item)
                                    <option value="{{ $item->name }}" @if ($allowance->title == $item->name) selected @endif>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="allowance_option" class="form-label">{{__trans('Type')}}</label>
                            <select name="allowance_type" value="{{ $allowance->allowance_type }}" class="form-control select-search" id="allowance_type">
                                <option value="">Select Type</option>
                                <option value="fixed" @if($allowance->allowance_type == 'fixed') selected @endif>Fixed</option>
                                <option value="percentage" @if($allowance->allowance_type == 'percentage') selected @endif>Percentage</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="amount" class="form-label">{{__trans('amount')}}</label>
                            <input type="number" step="0.01"  name="amount" class="form-control" id="amount" value="{{ $allowance->amount }}" placeholder="{{__trans('amount')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="is_recurring">
                                <input type="checkbox" value="{{ $allowance->is_fixed_for_current_month }}" class="form-check-input" name="is_fixed_for_current_month" id="is_fixed_for_current_month" @if($allowance->is_fixed_for_current_month == 1) checked @endif>  for this month only?
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remark" class="form-label">{{__trans('remark')}}</label>
                            <textarea name="remark" class="form-control" id="remark" placeholder="{{__trans('remark')}}">{{ $allowance->remark }}</textarea>
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

<script>
    loadAjaxSelect2();
    initselect2search();
</script>
