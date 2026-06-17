@props(['user','leave_balance'])
<div class="row">
    <div class="col-md-12">
    <table class="table light">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">{{__trans('leave_type')}}</th>
                <th scope="col">{{__trans('previous_year_leave_balance')}}</th>
                <th scope="col">{{__trans('comment')}}</th>
            </tr>
        </thead>
        <tbody id="table-body-custom">
            @foreach(\Modules\Leave\Entities\LeaveType::get(['id', 'name','days']) as $index => $type)
                <tr>
                    <th scope="row">{{ $index + 1 }}</th>
                    <td>{{ $type->name }}</td>
                    <td>
                        <input type="hidden" name="leave_balance[{{ $index }}][leave_type_id]" value="{{ $type->id }}">
                            @php
                                $leave = $leave_balance->firstWhere('leave_type_id', $type->id);
                                // $days = $leave ? $leave->days : 0;
                            @endphp
                        <input type="text" name="leave_balance[{{ $index }}][days]" value="{{  $leave->days ?? '0' }}" @if($user->is_previous_leave != '1') disabled @endif>
                    </td>
                    <td>
                        <input type="text" name="leave_balance[{{ $index }}][comment]" value="{{$leave->comment ?? ''}}" @if($user->is_previous_leave != '1') disabled @endif>
                    </td>
                </tr>
            @endforeach
        </tbody>
        </table>
    </div>
</div>
<script>
        document.getElementById('toggle-edit').addEventListener('change', function() {
            let isChecked = this.checked;
            let inputs = document.querySelectorAll('#table-body-custom input');
            let submitButton = document.getElementById('submit-button');

            inputs.forEach(input => {
                input.disabled = !isChecked;
            });

            if (isChecked) {
                submitButton.style.display = 'inline-block';
            } else {
                submitButton.style.display = 'none';
            }
        });
    </script>
