
<div class="row">
    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="col-sm-12 col-md-12">
        <div class="card light">
            <div class="card-body">
                <h5>{{__trans('off_boarding')}}</h5>
                <hr>
                <form action="{{ route('backend.storeOffBoarding') }}" method="POST" id="offBoardingForm">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Departure Date</label>
                                <input type="date" value="{{ $offboard && $offboard->departure_date ? \Carbon\Carbon::parse($offboard->departure_date)->format('Y-m-d') : '' }}" class="form-control datepickers flatpickr-input" id="departureDate" name="departure_date">
                                <!-- Error Message -->
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Reason for departure</label>
                                <select name="departure_reason_id" id="departure_reason_id" class="form-control select-search" id="reason">
                                    <option value="">Select Reason</option>
                                    @foreach ($reasons as $reason)
                                        <option value="{{$reason->id}}" @if($offboard && $offboard->departure_reason_id == $reason->id) selected @endif >{{$reason->name}}</option>
                                    @endforeach
                                </select>
                                @if($offboard && $offboard->resignation_reason)
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Employee Note:</strong> {{ $offboard->resignation_reason }}
                                    </small>
                                    <input type="hidden" name="resignation_reason" value="{{ $offboard->resignation_reason }}">
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Settlement Type</label>
                                <select name="settlement_type" id="settlement_type" class="form-control select-search select2-hidden-accessible"
                                    data-select2-id="4" tabindex="-1" aria-hidden="true">
                                    <option value="">Select A Option</option>
                                    <option value="in_payroll" @if($offboard && $offboard->settlement_type == 'in_payroll') selected @endif >Pay in Payroll</option>
                                    <option value="settlement" @if($offboard && $offboard->settlement_type == 'settlement') selected @endif >Final Settlement</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Select month for salary</label>
                                <select name="salary_month[]" class="form-control select2" id="month_select" multiple style="height: 30%;">
                                    @php
                                        $currentMonth = now()->subMonth()->month;
                                        $selectedMonths = [];
                                        if ($offboard && $offboard->salary_month) {
                                            $selectedMonths = explode(',', $offboard->salary_month);
                                        }
                                    @endphp
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ in_array($i, $selectedMonths) ? 'selected' : '' }} >
                                            {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12" style="text-align:center">
                            <div class="form-group">
                                <button class="btn btn-info waves-effect waves-light text-white" type="submit">
                                    Save Data <i class="fa fa-save" aria-hidden="true"></i> 
                                </button>
                                @if($offboard)
                                    <a class="btn btn-info waves-effect waves-light text-white" href="{{ route('backend.rehire', $user->id) }}" onclick="return confirm('Are you sure you want to rehire this user?')">
                                        Rehire <i class="fa fa-refresh" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
