<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_employee_overtime')}} : {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.user.user-salaries.storeovertime',$user)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="overtime_option" class="form-label">{{__trans('overtime_type')}}</label>
                            <select name="overtime_type" class="form-control select-search" id="overtime_type" onChange="onChangeOvertime(this)">
                                <option value="" selected>Select Type</option>
                                @php
                                    $types = [
                                        'ot1' => 'OT1',
                                        'ot2' => 'OT2',
                                        'ot3' => 'OT3',
                                        'ot4' => 'OT4'
                                    ];
                                @endphp
                                @foreach($types as $key => $type)
                                    <option value="{{ $key }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{__trans('rate_per_hour')}}</label>
                            <input type="text" name="rate" class="form-control" id="rate" placeholder="{{__trans('rate')}}" disabled>
                            <input type="text" name="rate_per_hour" class="form-control" id="rateperhour" hidden>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="hours" class="form-label">{{__trans('hours')}}</label>
                            <input type="number" step="0.01"  name="hours" class="form-control" id="hours" placeholder="{{__trans('hours')}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @if(isset($monthyear))
                    <input type="text" name="hidden_my" value="{{ $monthyear }}" hidden>
                @else
                    <input type="text" name="hidden_my" value="NA" hidden>
                @endif
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