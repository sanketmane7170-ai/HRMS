<div class="row" style="display:none;" id="finalSettlement">
    <h6>2.Final Settlement Data</h6>
     <div class="tableOuter">
        <table class="table table-bordered"  id="leaveTable">
            <thead>
                <tr>
                    <th>Additions</th>
                    <th>Remarks</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Gratuity</b></td>
                    <td>5 years on 21 days</td>
                    <td id="first_gratuity"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>after 5 years on 30 days</td>
                    <td id="second_gratuity"></td>
                </tr>
                <tr>
                    <td><b>Leave encashments  </b><label>  Add more leave:  </label>
                        @php
                            $types = Modules\Leave\Entities\LeaveType::get(['id', 'name', 'days']);
                        @endphp
                        @foreach ($types as $type)
                            @php
                                $userId = $user;
                                $balance = Modules\Leave\Entities\LeaveBalance::where(
                                    [
                                        'user_id' => $userId->id,
                                        'year' => date('Y'),
                                        'leave_type_id' => $type->id
                                    ],
                                )->first();
                                $leaveBalance = $balance ? $balance->available : 0
                            @endphp
                            {{--  @if($type->name !== 'Vacation' )  --}}
                                <label> 
                                    {{ $type->name }} : <input type="checkbox"class="addleave" data-leave-id="{{ $type->id }}" data-leave-name="{{ $type->name }}" data-leave-balance="{{ $leaveBalance }}"> ,
                                </label>
                            {{--  @endif  --}}
                        @endforeach
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><b>Addition List</b></td>
                    <td></td>
                    <td></td>
                </tr>
            <hr>
                <tr>
                    <td><b>Deduction List</b></td>
                    <td></td>
                    <td></td>
                </tr>
                @php
                    // $user is passed as prop
                    // Author: Sanket - Fix null pointer error
                    $workdetails = $user->workDetail()->first();
                @endphp
                @if ($workdetails && $workdetails->attendance_base == 'no') 
                @if(isset($offboard) && isset($offboard->salary_month) && $offboard->salary_month != null)
                <tr>
                    <td><b>Selected month for salary</b>
                        @php
                            if(isset($offboard) && isset($offboard->salary_month) && $offboard->salary_month != null){
                                $months = explode(',', $offboard->salary_month);
                            }
                            if(isset($offboard) && isset($offboard->salary_month_day) && $offboard->salary_month_day != null){
                                $salaryData = json_decode($offboard->salary_month_day, true);
                            }
                            
                        @endphp
                        @foreach ($months as $month)
                            @php
                                $day = 0;
                                $isChecked = '';
                                if(isset($offboard->salary_month_day) && $offboard->salary_month_day != null){
                                    $entry = collect($salaryData)->firstWhere('month', (string) $month);
                                    $isChecked = !is_null($entry);
                                    $day = $entry['day'] ?? '';
                                }
                            @endphp
                            <label> {{ Carbon\Carbon::create()->month($month)->format('F') }} : <input type="checkbox" class="addmonthday"
                                data-month-id="{{ $month }}"
                                data-month-name="{{ \Carbon\Carbon::create()->month($month)->format('F') }}"
                                data-day="{{ $day }}"
                                {{ $isChecked ? 'checked' : '' }}> ,
                            </label>
                        @endforeach
                    </td>
                </tr>
                @endif
                @endif
                <tr id="salaryRow">
                    <td><b>Salary</b></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <h5>Total Amount</h5>
                    </td>
                    <td>
                        <h5 id="total_amount"></h5>
                    </td>
                </tr>
        </table>
        <form id="myForm">
            <div class="d-flex justify-content-center mt-3">
                <button type="button" class="btn btn-info waves-effect waves-light text-white" onclick="submitReviewForm()">
                    Review and Submit Transaction
                </button>
            </div>
        </form>
    </div>
</div>
