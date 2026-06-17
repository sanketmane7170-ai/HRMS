<style>
.input-container {
  display: flex;
  align-items: center;
}

.addremove {
  width: 30px;
  height: 30px;
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
}

.loanmonth {
  width: 60px;
  text-align: center;
  font-size: 18px;
  margin: 0 5px;
}

</style>
<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('advance_salary_request')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.payroll.advance.updateAdvanceRequest', ['userid' => auth()->user()->id, 'id' => $adRequest->id]) }}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('POST')
            <div class="modal-body p-4">
                <div class="row">
                    @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN) || auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) || hasPermission('Manage Advance Salary Request'))
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="amount">Select User:</label>
                                <select name="user_id" id="userlist" class="form-control" required>
                                    @foreach ($userList as $user)
                                        <option value="{{ $user->id }}" {{ $user->id === $adRequest->user_id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="amount">Loan Amount:</label>
                            <input type="number" name="amount" value="{{ $adRequest->amount }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="loan_months">Loan Duration:</label>
                            <div class="input-container">
                                <button type="button" class="addremove" onclick="decrement()">-</button>
                                <input type="number" name="loan_months" class="loanmonth" id="numberInput" value="{{ $adRequest->loan_months }}" min="0" max="12" readonly />
                                <button type="button" class="addremove" onclick="increment()">+</button>
                              </div>
                            {{--  <select name="loan_months" class="form-control" required>
                                <option value="1">1 Month</option>
                                <option value="6">6 Months</option>
                                <option value="12">12 Months</option>
                            </select>  --}}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="type">Loan Type:</label>
                            <select name="type" class="form-control" required>
                                <option value="Salary Advance" {{ $adRequest->type === 'Salary Advance' ? 'selected' : '' }}>Advance Salary</option>
                                <option value="Loan" {{ $adRequest->type === 'Loan' ? 'selected' : '' }}>Loan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                        <label for="start_month">Loan Start Month:</label>
                        @php
                            $currentMonth = now()->month;
                            $currentYear = now()->year;
                        @endphp
                        
                        <select name="start_month" class="form-control" required>
                            @for ($i = 0; $i < 3; $i++) 
                                @php
                                    $month = ($currentMonth + $i - 1) % 12 + 1;
                                    $year = $currentYear + floor(($currentMonth + $i - 1) / 12);
                                @endphp
                                <option value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" {{ \Carbon\Carbon::create($year, $month)->format('Y-m') == \Carbon\Carbon::parse($adRequest->start_month)->format('Y-m') ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create($year, $month)->format('M Y') }}
                                </option>
                            @endfor
                        </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="reason">Reason:</label>
                            <textarea name="reason" class="form-control" required>{{ $adRequest->reason }}</textarea>

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
    loadAjaxSelect2()
    
    function increment() {
        let input = document.getElementById("numberInput");
        let max = parseInt(input.max);
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
        }
    }
    
    function decrement() {
        let input = document.getElementById("numberInput");
        let min = parseInt(input.min);
        if (parseInt(input.value) > min) {
            input.value = parseInt(input.value) - 1;
        }
    }
    $(document).ready(function(){
        $('#userlist').select2({
            placeholder: "Select a user",
            allowClear: true
        });
    });
</script>