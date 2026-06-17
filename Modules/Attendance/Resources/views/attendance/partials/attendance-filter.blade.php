<form action="" id="perPageForm" method="GET">
    <div class="att-filter-outer">
        <div class="att-filter-box">
            <div class="att-filter-box-inner">
                <div class="form-group">
                    <label><strong>{{ __trans('employee_name') }}:</strong></label>
                    <select name="employee[]" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.users') }}" multiple>
                        <option value="">{{ __trans('search_employee ...') }}</option>
                        @foreach ($filterEmployees as $employee)
                        <option value="{{ $employee->id }}" selected>{{ $employee->employee_id }}
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="att-filter-box">
            <div class="att-filter-box-inner">
                <div class="form-group">
                    <label><strong>{{ __trans('department') }}:</strong></label>
                    <select name="department" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.departments') }}">
                        <option value="">All</option>
                        @if ($filterDepartment)
                        <option value="{{ $filterDepartment->id }}" selected>
                            {{ $filterDepartment->name }}
                        </option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="att-filter-box">
            <div class="att-filter-box-inner">
                <div class="form-group">
                    <label><strong>{{ __trans('select_month') }}:</strong></label>
                    <select name="month" class="form-control select-search" id="selected_month_input">
                        @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" @if ($month==$i) selected @endif>
                            {{ date('F', strtotime(date('Y') . '-' . $i)) }}</option>
                            @endfor
                    </select>
                </div>
            </div>
        </div>
        <div class="att-filter-box">
            <div class="att-filter-box-inner">
                <div class="form-group">
                    <label><strong>{{ __trans('select_year') }}:</strong></label>
                    <input type="text" name="year" value="{{ $year }}" class="form-control" id="selected_year_input">
                </div>
            </div>
        </div>
        {{-- <div class="att-filter-box">
            <div class="att-filter-box-inner">
                <div class="form-group">
                    <label><strong>{{ __trans('select_start_date') }}:</strong></label>
        <input type="text" name="start_date" class="form-control datepicker" placeholder="{{__trans('select_start_date')}}">
    </div>
    </div>
    </div>
    <div class="att-filter-box">
        <div class="att-filter-box-inner">
            <div class="form-group">
                <label><strong>{{ __trans('select_end_date') }}:</strong></label>
                <input type="text" name="end_date" class="form-control datepicker" placeholder="{{__trans('select_end_date')}}">
            </div>
        </div>
    </div> --}}
    <div class="att-filter-box att-filter-box-btn-outer">
        <div class="att-filter-box-inner">
            <div class="form-group">
                <label>&nbsp; </label>
                <button type="submit" class="btn btn-primary w-100">{{ __trans('apply') }}</button>
            </div>
        </div>
    </div>
    </div>
    <div class="att-indicators mb-4 row align-items-center">
        <div class="col-lg-12">
            <ul>
                @foreach (\Modules\Attendance\Enums\AttendanceStatus::cases() as $case)
                <li>
                    <img src="{{ Module::asset('attendance:images/' . $case->value . '.svg') }}" @if($case->value == 'earlyout' || $case->value == 'halfday') style="height: 20px;" @endif @if($case->value == 'sickleave') style="height: 20px;" @endif class="" /> {{ __trans($case->value) }}
                </li>
                @endforeach
                <li>
                    <img src="{{ Module::asset('attendance:images/present_full.svg') }}" style="width:16px;" /> {{ __trans('Present (with checkout)') }}
                </li>
                <li>
                    <img src="{{ Module::asset('attendance:images/weekendWork.svg') }}" style="width:16px;" /> {{ __trans('Cancel Off') }}
                </li>
                <li>
                    <img src="{{ Module::asset('attendance:images/vacation.png') }}" style="width:16px;" /> {{ __trans('Vacation') }}
                </li>
                <li>
                    <img src="{{ Module::asset('attendance:images/PH.jpeg') }}" style="width:16px;" /> {{ __trans('PH') }}
                </li>
                <li>
                    <img src="{{ Module::asset('attendance:images/unpaid.jpeg') }}" style="width:16px;" /> {{ __trans('Unpaid') }}
                </li>
                <li>
                    <img src="{{ Module::asset('attendance:images/visit_in.jpeg') }}" style="width:16px;" /> {{ __trans('Visit') }}
                </li>
            </ul>
        </div>
        <div class="col-lg-12">
            @can('Export Attendance')
            <button formaction="{{ route('backend.showAttendanceReport') }}" class="btn btn-success">
                <i class="fa fa-info"></i> {{ __trans('show_report') }}
            </button>
            <button formaction="{{ route('backend.showUserVisitReport') }}" class="btn btn-success" style="background-color: #c6c93b; color:white;">
                <i class="fa fa-file"></i> {{ __trans('show_visit_report') }}
            </button>
            <button formaction="{{ route('backend.attendance.export-pdf') }}" class="btn btn-warning">
                <i class="fa fa-download"></i> {{ __trans('attendance-pdf') }}
            </button>

            <button formaction="{{ route('backend.download.bulk.attendance.csv') }}" class="btn" style="background-color: #582bc9; color:white;">
                <i class="fa fa-download"></i> {{ __trans('bulk_export') }}
            </button>
            @endcan
            @can('Edit Attendance')
            <a href="{{ route('backend.attendance.getBulkUserAttendance') }}" class="btn me-1 edit-button" style="background-color: #702c81; color:white;">
                <i class="fa fa-file-excel"></i> {{ __trans('bulk_mark_attendance') }}
            </a>
            @endcan

        </div>
    </div>
    <label for="per_page">Show:</label>
    <select name="per_page" id="per_page" onchange="document.getElementById('perPageForm').submit()">
        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
    </select>
    <input type="hidden" name="page" id="current_page_input" value="{{ request('page', 1) }}">
</form>