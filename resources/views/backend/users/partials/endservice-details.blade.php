@if($user->settlement_status == 0)
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card light">
            <div class="card-body">
                <h5>{{__trans('end_of_service_calculator')}}</h5>
                <hr>
                


                <div class="row">
                    <h6>{{__trans('1.service_information')}}</h6>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Hire Date</label>
                            <input type="text" class="form-control" name="hire_date" id="hire_date"
                                value="{{$user->workDetail && $user->workDetail?->joining_date ? $user->workDetail?->joining_date->format('d/m/Y') : ''}}"
                                disabled>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Departure Date</label>
                            <input type="date" value="{{ $offboard && $offboard->departure_date ? \Carbon\Carbon::parse($offboard->departure_date)->format('Y-m-d') : '' }}" class="form-control datepickers flatpickr-input" id="departure_date" name="departure_date">
                            <!-- Error Message -->
                            <div id="error_message1" style="color: red;"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Reason for departure</label>
                            <select name="reason" id="reason" class="form-control select-search"
                                id="reason">
                                <option value="">Select Reason</option>
                                @foreach ($reasons as $reason)
                                <option value="{{$reason->id}}" @if($offboard && $offboard->departure_reason_id == $reason->id) selected @endif >{{$reason->name}}</option>
                                @endforeach
                            </select>
                            <div id="error_message3" style="color: red;"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Contract Type</label>
                            <select name="contract_type" id="contract_type" class="form-control select-search select2-hidden-accessible"
                                data-select2-id="4" tabindex="-1" aria-hidden="true">
                                <option value="">Select A Option</option>
                                <option>Limited</option>
                                <option>Unlimited</option>
                            </select>
                            <div id="error_message2" style="color: red;"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Basic Salary for departure month</label>
                            <input type="text" class="form-control" id="basic_salary" name="basic_salary" value="{{ $user->salary ? $user->salary?->basic : '' }}" disabled>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Gross Salary per month</label>
                            <input type="text" class="form-control" id="gross_salary" name="gross_salary" value="{{ $gross_value }}" disabled>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Total Service Duration</label>
                            <input type="text" class="form-control" name="total_service_duration" id="total_service_duration" value="NA" disabled>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Add absent days</label>
                            <input type="text" class="form-control" name="absent_days" value="{{ $offboard?->absent_days ?? $absent_count }}" id="absent_days" >
                        </div>
                    </div>
                    @if($user->settlement_status == 0)
                        <div class="col-lg-12" style="text-align:center">
                            <div class="form-group">
                                <button id="calculateBtn" class="btn btn-info waves-effect waves-light text-white">
                                Settlement Data <i class="fa fa-handshake" aria-hidden="true"></i> 
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="col-lg-12" style="text-align:center">
                            <div class="form-group">
                            <a href="{{route('backend.settlement.transaction')}}" class="btn btn-info waves-effect waves-light text-white">
                                View Transaction <i class="fa fa-history" aria-hidden="true"></i>
                            </a>
                            </div>
                        </div>
                    @endif
                </div>

                <x-final-settlement :user="$user" :salary="$salary" :offboard="$offboard"/>
            </div>
        </div>
    </div>
</div>
@else
@include('backend.users.partials.layout-settlement')
@endif
