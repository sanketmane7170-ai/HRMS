@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('branch_budget_report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('branch_wise_budget_report')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <label for="month">Select Month:</label>
                    <select name="month" class="form-select" id="monthFilter" required>
                        <option value=""> select month</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                            @endfor
                    </select><br>
                    <a href="{{ route('backend.reports.generateBranchBudgetReport',['month' => $month]) }}" class="btn btn-warning me-1">
                        <i class="fas fa-history"></i> Export to Excel
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="border-left: solid;border-bottom: solid !important;border-bottom-color: #cccccc61 !important;">#</th>
                                        <th style="border-left: solid;border-bottom: solid !important;border-bottom-color: #cccccc61 !important;">{{__trans('branch_name')}}</th>
                                        <th style="border-left: solid;border-bottom: solid !important;border-bottom-color: #cccccc61 !important;">{{__trans('monthly_budget')}}</th>
                                        <th style="border-left: solid;border-bottom: solid !important;border-bottom-color: #cccccc61 !important;">{{__trans('active_employees')}}</th>
                                        <th style="border-left: solid;border-bottom: solid !important;border-bottom-color: #cccccc61 !important;">{{__trans('total_spent')}}</th>
                                        <th style="border-left: solid;border-bottom: solid !important;border-bottom-color: #cccccc61 !important;">{{__trans('status')}}</th>
                                        <th style="border-left: solid;border-bottom: solid !important;border-bottom-color: #cccccc61 !important;">{{__trans('amount')}}</th>
                                        <th style="border-left: solid;border-bottom: solid !important;border-bottom-color: #cccccc61 !important;">{{__trans('month')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departments as $department)
                                    @php
                                    $total_user_salary = 0;
                                    $month = $month;
                                    $users = App\Models\User::whereDoesntHave('roles', function ($query) {
                                    $query->whereIn('name', [App\Models\User::ROLE_ADMIN, App\Models\User::ROLE_SUPER_ADMIN]);
                                    })->where('status', 'active')
                                    ->where('department_id', $department->id)
                                    ->get();

                                    foreach ($users as $user) {
                                    $year = date('Y');
                                    $start_date = date('Y-m-01', strtotime("{$year}-{$month}-01"));
                                    $end_date = date('Y-m-t', strtotime("{$year}-{$month}-01"));
                                    $total_net_salary = getUserTotalNetSalary($user, $month, $year, $start_date,$end_date);
                                    $total_user_salary += $total_net_salary;
                                    }
                                    $is_over_budget = $total_user_salary > $department->budget ? 'Over Budget' : 'Within Budget';
                                    if(str_contains(getSetting('currency'), 'AED')){
                                            $AEDCurrency = '<img src="'. asset("assets/currency/aedb.png") .'" alt="AED" style="width:18px; height:18px; vertical-align:middle;">';
                                    } else {
                                        $AEDCurrency = getSetting('currency');
                                    }
                                    @endphp
                                    <tr>
                                        <td style="border-right: 1px solid #dee2e6;">{{ $loop->iteration }}</td>
                                        <td style="border-right: 1px solid #dee2e6;">{{ $department->name }}</td>
                                        <td style="border-right: 1px solid #dee2e6;">{{ $department->budget > 0 ? $department->budget . ' ' . $AEDCurrency : '' }}</td>
                                        <td style="border-right: 1px solid #dee2e6;">{{ $users->count() }}</td>
                                        <td style="border-right: 1px solid #dee2e6;">{{ $total_user_salary > 0 ? number_format($total_user_salary, 2) . ' ' . $AEDCurrency : '' }}</td>
                                        <td style="border-right: 1px solid #dee2e6;">{{ $is_over_budget }}</td>
                                        <td style="border-right: 1px solid #dee2e6;">{{ abs($total_user_salary - $department->budget) > 0 ? abs($total_user_salary - $department->budget) . ' ' . $AEDCurrency : '' }}</td>
                                        <td style="border-right: 1px solid #dee2e6;">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->

</div>
@endsection

@push('scripts')

<script>
    document.getElementById('monthFilter').addEventListener('change', function() {
        let selectedMonth = this.value;
        let url = new URL(window.location.href);
        url.searchParams.set('month', selectedMonth);
        window.location.href = url.toString();
    });
</script>
@endpush
