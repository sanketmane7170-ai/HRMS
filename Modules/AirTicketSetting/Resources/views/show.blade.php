@extends('layouts.backend')

@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('air_Ticket_Setting_view')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.settings.air-ticket-setting.index')}}">{{__trans('airticketsettings')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('air_Ticket_Setting_view')}}</li>
                    </ul>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div class="modal-body p-4">
                        <div class="container my-5">
                            <div class="mb-4">
                                <label for="name" class="form-label fw-bold">{{__trans('policy_name')}}</label>
                                <input type="text" name="policy_name" class="form-control"
                                   readonly value="{{$airticketsetting->policy_name}}" placeholder="{{__trans('policy_name')}}">
                            </div>

                            <!-- Allowance Limit Per Cycle -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Allowance Limit Per Cycle</label>
                                <div class="row g-3">
                                    <!-- Currency Dropdown -->
                                    <div class="col-md-6">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select disabled name="allowance_currency" class="form-control select-search"
                                            id="allowance_currency">
                                            @foreach ($currencies as $code => $name)
                                            <option @if($airticketsetting->allowance_currency == $code ) selected @endif value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Allowance Amount -->
                                    <div class="col-md-6">
                                        <label for="allowance-amount" class="form-label">Allowance Amount</label>
                                        <input type="text" id="allowance_amount" name="allowance_amount" class="form-control"
                                        readonly value="{{$airticketsetting->allowance_amount}}" placeholder="{{__trans('allowance_amount')}}" onchange="updateEncashment()">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Employees can request an air ticket after</label>
                                <div class="row g-3">
                                    <!-- Currency Dropdown -->
                                    <div class="col-md-6">
                                        <label for="currency" class="form-label">No. of Months</label>
                                        <input type="text" name="request_after_months" class="form-control"
                                        readonly  value="{{$airticketsetting->request_after_months}}" placeholder="{{__trans('No. of Months')}}">
                                    </div>
                                    <!-- Allowance Amount -->
                                    <div class="col-md-6">
                                        <label for="request_after_months_date"
                                            class="form-label">{{__trans('from')}}</label>
                                        <select disabled name="request_after_from" class="form-control select-search"
                                            id="request_after_from">
                                            <option @if($airticketsetting->request_after_from == "hiring_date" ) selected @endif value="hiring_date">Hiring Date</option>
                                            <option @if($airticketsetting->request_after_from == "probation_date" ) selected @endif value="probation_date">Probation Date</option>

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Air ticket policy renewal occurs in </label>
                                <div class="row g-3">
                                    <!-- Currency Dropdown -->
                                    <div class="col-md-12">
                                        <label for="currency" class="form-label">No. of Months</label>
                                        <input type="text" name="policy_renewal_months" class="form-control"
                                        readonly  value="{{$airticketsetting->policy_renewal_months}}" placeholder="{{__trans('No. of Months')}}">
                                    </div>
                                    <!-- Allowance Amount -->

                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">No. of requests employee can make every cycle</label>
                                <div class="row g-3">
                                    <!-- Currency Dropdown -->
                                    <div class="col-md-12">
                                        <label for="currency" class="form-label">Quantity</label>
                                        <input type="text" name="request_limit_per_cycle" class="form-control"
                                        readonly value="{{$airticketsetting->request_limit_per_cycle}}" placeholder="{{__trans('Quantity')}}">
                                    </div>
                                    <!-- Allowance Amount -->

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input disabled class="form-check-input" type="checkbox" id="allow_reimbursement"
                                        name="allow_reimbursement" value="{{$airticketsetting->request_limit_per_cycle}}" {{$airticketsetting->request_limit_per_cycle==1 ? "checked" : "unchecked"}}>
                                    <label class="form-check-label" for="allow_reimbursement">
                                        <strong>Reimbursement</strong><br>
                                        <span class="text-muted">Employees can request a reimbursement of an air ticket they
                                            purchased</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input disabled class="form-check-input" type="checkbox" id="allow_encashment"
                                        name="allow_encashment" value="{{$airticketsetting->allow_encashment}}" {{$airticketsetting->allow_encashment==1 ? "checked" : "unchecked"}}>
                                    <label class="form-check-label" for="allow_encashment">
                                        <strong>Encashment</strong><br>
                                        <span class="text-muted">Employees can request for an encashment of their allowed air
                                            ticket
                                            amount - <strong id="encashment-amount">{{$airticketsetting->allowance_currency}} {{$airticketsetting->allowance_amount}}</strong></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input disabled class="form-check-input" type="checkbox" id="allow_ticket_booking"
                                        name="allow_ticket_booking" value="{{$airticketsetting->allow_ticket_booking}}" {{$airticketsetting->allow_ticket_booking==1  ? "checked" : "unchecked"}}>
                                    <label class="form-check-label" for="allow_ticket_booking">
                                        <strong>Air Ticket Booking</strong><br>
                                        <span class="text-muted">Employees can request the company to book an air ticket for
                                            them</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="editModal">

</div>


@endsection