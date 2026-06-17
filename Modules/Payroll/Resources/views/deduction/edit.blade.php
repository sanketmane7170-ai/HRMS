<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_employee_deduction')}} : {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.user.user-salaries.updatededuction',[$user,$deduction])}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{__trans('title')}}</label>
                            <select name="title" class="form-control select-search" id="title">
                                <option value="" selected>Select deduction</option>
                                @foreach ($fixedDeduction as $item)
                                    <option value="{{ $item->name }}" @if ($deduction->title == $item->name) selected @endif>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="deduction_option" class="form-label">{{__trans('Type')}}</label>
                            <select name="deduction_type" class="form-control select-search" id="deduction_type">
                                <option value="" selected>Select Type</option>
                                @php
                                    $types = [
                                        'fixed' => 'Fixed',
                                        'percentage' => 'Percentage'
                                    ];
                                @endphp
                                @foreach($types as $key => $type)
                                    @if($deduction->deduction_type == $key)
                                        <option value="{{ $key }}" selected>{{ $type }}</option>
                                    @else 
                                        <option value="{{ $key }}">{{ $type }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="amount" class="form-label">{{__trans('amount')}}</label>
                            <input type="number" step="0.01"  value="{{ $deduction->amount }}" name="amount" class="form-control" id="amount" placeholder="{{__trans('amount')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="is_recurring">
                                <input type="checkbox" value="{{ $deduction->is_fixed_for_current_month }}" class="form-check-input" name="is_fixed_for_current_month" id="is_fixed_for_current_month" @if($deduction->is_fixed_for_current_month == 1) checked @endif>  for this month only?
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remark" class="form-label">{{__trans('remark')}}</label>
                            <textarea name="remark" class="form-control" id="remark" placeholder="{{__trans('remark')}}">{{ $deduction->remark }}</textarea>
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
<script>
    $( document ).ready(function() {
        let text = $('#overtime_type :selected').text();
        switch(text){
            case 'OT1':
                $('#rate , #rateperhour').val('1.25');
            break;
            case 'OT2':
                $('#rate , #rateperhour').val('1.25');
            break;
            case 'OT3':
                $('#rate , #rateperhour').val('1.50');
            break;
            case 'OT4':
                $('#rate , #rateperhour').val('1.50');
            break;
            default:
                $('#rate , #rateperhour').val('0');        
        }
    });
    function onChangeOvertime(data) {
        var type = '';
        type = data.options[data.selectedIndex].text;
        switch(type){
            case 'OT1':
                $('#rate , #rateperhour').val('1.25');
            break;
            case 'OT2':
                $('#rate , #rateperhour').val('1.25');
            break;
            case 'OT3':
                $('#rate , #rateperhour').val('1.50');
            break;
            case 'OT4':
                $('#rate , #rateperhour').val('1.50');
            break;
            default:
                $('#rate , #rateperhour').val('0');        
        }
    }
</script>