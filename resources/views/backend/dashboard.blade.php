@extends('layouts.backend')
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
<style>
    #userBarChart {
        max-height: 300px;
        /* Adjust height */
        max-width: 500px;
        /* Optional: Adjust width */
        margin: auto;
    }
</style>
<style>
    #branchChart,
    #departmentPieChart,
    #roleChart,
    #designationChart {
        max-width: 500px;
        max-height: 300px;
        margin: auto;
    }

    .new-year-banner img {
        border: 2px solid red;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        max-width: 100%;
        /* Adjust width as needed */
    }
</style>

@endpush
@section('content')
<?php
    $totalEmployee       = employeeCount();
    $femaleEmployeeCount = femaleEmployeeCount();
    $maleEmployeeCount   = maleEmployeeCount();
    $countryUserList     = getCountryUserList();
    $alldepartments      = getalldepartments();
?>
<!-- Page Wrapper -->
<div class="page-wrapper dashboard-page">
    <div class="content container-fluid">
        @if(date("m")==1 && date("d") < 15 )
            <style>
            .new-year-banner img {
            border: 2px solid red;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            max-width: 100%;
            /* Adjust width as needed */
            }
            </style>
            <div class="new-year-banner" style="text-align: center;">
                <img src="{{ asset('uploads/assets/happy_new_year_0_1.png') }}" alt="Happy New Year" style="max-width: 100%; height: auto;">
            </div>
            <br>
            @endif
            <div class="kpi-row">
                <div class="kpi-tile">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <img src="{{asset('assets/backend/img/top-stat-1.svg')}}" class="img-fluid" />
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <a href="{{ route('backend.users.index') }}"><h3>{{$totalEmployee}}</h3></a>
                                    </div>
                                    <div class="dash-title">
                                        <a href="{{ route('backend.users.index') }}"><p>{{__trans('total_number_of_employees')}}</p></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($alldepartments as $department)
                <div class="kpi-tile">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header d-flex align-items-center justify-content-between">

                                {{-- Left block (icon + count + title) --}}
                                <div class="d-flex align-items-center">
                                    <span class="dash-widget-icon me-3">
                                        <img src="{{ asset('assets/backend/img/top-stat-3.svg') }}" class="img-fluid" />
                                    </span>
                                    <div class="dash-count">
                                        <div class="dash-counts">
                                            <a href="{{ route('backend.users.index', ['department_id' => $department->id]) }}"><h3>{{ $department->user_count->count() }}</h3></a>
                                        </div>
                                        <div class="dash-title">
                                            <a href="{{ route('backend.users.index', ['department_id' => $department->id]) }}"><p class="mb-0 text-muted">{{ $department->name }}</p></a>
                                        </div>
                                    </div>
                                </div>

                                {{-- Logo on far right --}}
                                @if($department->logo && Storage::disk('public')->exists($department->logo))
                                <img src="{{ asset('storage/' . $department->logo) }}"
                                    alt="{{ $department->name }} Logo"
                                    style="height:80px; width:auto; object-fit:contain;">
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="row ">

                <div class="col-md-6 ">
                    <x-document-expired-list />
                </div>


                <div class="col-md-6">
                    <x-filemanager-doc-expired-list />
                </div>

                <div class="col-md-6">
                    <x-checkin-list />
                </div>

                <div class="col-md-6">
                    <x-announcements />
                </div>

                <div class="col-md-6">
                    <x-new-joiner />
                </div>

                <!-- Pending Requests In Queue moved here -->
                <div class="col-md-6">
                    <x-requests-queue />
                </div>


            </div>
            <div class="row">
                <div class="col-lg-6 col-12">
                    <x-document-expiring-list />
                </div>
                <div class="col-lg-6 col-12">
                    <x-birthday-list />
                </div>
                <div class="col-lg-6 col-12">
                    <x-anniversary-list />
                </div>
                <div class="col-lg-6 col-12">
                    <x-probation-end-list />
                </div>
                {{--  <div class="col-lg-6 col-12">
                    <x-feature-list />
                </div>  --}}
                <div class="col-lg-6 col-12">
                    <x-pic-list />
                </div>
                <div class="col-lg-6 col-12">
                    <x-UpcomingLeaveList />
                </div>
                <div class="col-lg-6 col-12">
                    <x-UpcomingAirTicketList />
                </div>
                <!-- NOTICE PERIOD WIDGET moved here -->
                <div class="col-lg-6 col-12">
                    <x-notice-period-list />
                </div>
            </div>
            <div class="row">

                <div class="col-lg-6 col-12">

                    <div class="col-xl-15 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="">
                                    <h5 class="card-title">{{__trans('users_by_branch')}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <canvas id="branchChart" width="300" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12">

                    <div class="col-xl-15 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="">
                                    <h5 class="card-title">{{__trans('users_by_department')}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <canvas id="departmentPieChart" width="300" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12">

                    <div class="col-xl-15 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="">
                                    <h5 class="card-title">{{__trans('users_by_designation')}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <canvas id="designationChart" width="300" height="400"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12">

                    <div class="col-xl-15 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="">
                                    <h5 class="card-title">{{__trans('users_by_role')}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <canvas id="roleChart" width="300" height="400"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="col-xl-15 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="">
                                    <h5 class="card-title">{{__trans('users_by_joinning_and_probation')}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <canvas id="userBarChart" width="300" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>



            </div>
            <div class="row">

                <div class="col-lg-6 col-12">
                    <div class="col-xl-15 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="">
                                    <h5 class="card-title">{{__trans('employees_login_chart')}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 col-12">
                                        <form action="{{route('backend.reports.attendance_report_search')}}"
                                            enctype="multipart/form-data" method="POST">

                                            <div class="row">
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <input placeholder="Select Month" type="text"
                                                            class="form-control datepicker" id="user_checkin_date"
                                                            name="date">
                                                    </div>
                                                </div>

                                                <div class="col-sm-4">
                                                    <div class="form-group ">
                                                        <button type="button" id="user_checkin_submit" name="button"
                                                            value="submit" class="btn btn-primary">
                                                            <i class="fa fa-search mr-2" style="display: inline"></i>Search
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-lg-12 col-12">
                                        <div id="container" style="width: 100%; height: 400px;"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-6 col-12">
                    <div class="col-xl-15 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="">
                                    <h5 class="card-title">{{__trans('employees_by_nationality')}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-12">
                                        <div id="regions_div" style="width: 100%; height: 400px;"></div>
                                    </div>
                                    <div class="col-lg-6 col-12">
                                        <div class="map-table">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>{{__trans('country')}}</th>
                                                            <th class="text-end"> {{__trans('users')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($countryUserList as $country)
                                                        @php
                                                        $percentage= $totalEmployee > 0 ? (($country->users_count/$totalEmployee)*100) : 0;
                                                        $style= "style=width:$percentage%";
                                                        @endphp
                                                        <tr>
                                                            <td>{{$country->name}}</td>
                                                            <td class="text-end">{{$country->users_count}}</td>
                                                        </tr>
                                                        <tr>

                                                            <td colspan="2">
                                                                <div class="progress progress-xs">
                                                                    <div class="progress-bar bg-success" role="progressbar"
                                                                        {{$style}} aria-valuenow="{{$percentage}}"
                                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @if (isModuleEnabled('Analytic'))
                                            <div class="map-table-btn-outer text-end mt-3">
                                                <a href="{{route('backend.analytic.country.user.list')}}"
                                                    class="btn-right btn btn-sm btn-outline-primary">
                                                    {{__trans('view_all')}}</a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>


    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <img style="margin-left: 87px;" src="{{asset('assets/backend/img/feature.png')}}" class="img-fluid" /> -->
                <!-- <img style="" src="{{asset('assets/backend/img/feature_7.jpg')}}" class="img-fluid" /> -->
                <img style="float:left; width:50%" src="{{asset('assets/backend/img/feature_1.jpg')}}"
                    class="img-fluid" />
                <img style="float:right; width:50%" src="{{asset('assets/backend/img/feature_2.jpg')}}"
                    class="img-fluid" />
                <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
            </div>
            <div class="modal-body">
                <table class="table table-stripped table-hover" id="feature">
                    <thead class="thead-light">

                    </thead>
                    <tbody>
                        @forelse ($features as $feature)
                        <tr>
                            <!-- <td>{{$feature->date}}</td> -->
                            <td>
                                <a
                                    href="{{$feature->url}}"><span>{{ \Illuminate\Support\Str::title($feature->feature) }}({{$feature->date}})</span></a>

                            </td>
                        </tr>
                        @empty
                        <!-- <tr>
                            <td colspan="2">{{__trans('no_features_this_month')}}</td>
                        </tr> -->
                        @endforelse
                </table>
            </div>
            <div class="modal-footer">
                <!-- <img style="float:left;" src="{{asset('assets/backend/img/feature.png')}}" class="img-fluid" /> -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
            </div>
        </div>
    </div>
</div>

<!-- /Page Wrapper -->
@endsection
@push('scripts')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    $(window).on('load', function() {
        var delayMs = 1000; // delay in milliseconds

        var rowCount = $('#feature tr').length;
        // alert(rowCount);
        if (rowCount > 0) {
            setTimeout(function() {
                $('#exampleModal').modal('show');
            }, delayMs);
        }


    });

    $(document).ready(function() {
        google.charts.load('current', {
            'packages': ['geochart'],
        });
        google.charts.setOnLoadCallback(drawRegionsMap);

        function drawRegionsMap() {
            var data = google.visualization.arrayToDataTable([
                ['Country', 'Popularity'],
                <?php
                    foreach ($countryUserList as $row) {
                        echo "['" . $row->name . "', " . $row->users_count . "],";
                    }
                ?>
            ]);
            var options = {};
            var chart = new google.visualization.GeoChart(document.getElementById('regions_div'));
            chart.draw(data, options);
        }
    });
</script>

<script>
    $(function() {

        $(".progress.progress-round").each(function() {

            var value = $(this).attr('data-value');
            var left = $(this).find('.progress-left .progress-bar');
            var right = $(this).find('.progress-right .progress-bar');

            if (value > 0) {
                if (value <= 50) {
                    right.css('transform', 'rotate(' + percentageToDegrees(value) + 'deg)')
                } else {
                    right.css('transform', 'rotate(180deg)')
                    left.css('transform', 'rotate(' + percentageToDegrees(value - 50) + 'deg)')
                }
            }

        })

        function percentageToDegrees(percentage) {

            return percentage / 100 * 360

        }

    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/11.4.8/highcharts.min.js"></script>
<script type="text/javascript">
    var userloginCount = <?php echo json_encode($userloginCount) ?>;
    var userLoginDates = <?php echo json_encode($userLoginDates) ?>;
    var leaveUsersCount = <?php echo json_encode($leaveUsersCount) ?>;
    var absentUsersCount = <?php echo json_encode($absentUsersCount) ?>;
    var weekendUsersCount = <?php echo json_encode($weekendUsersCount) ?>;
    usercheckin_chart(userloginCount, userLoginDates, leaveUsersCount, absentUsersCount, weekendUsersCount);


    function usercheckin_chart(userloginCount, userLoginDates, leaveUsersCount, absentUsersCount, weekendUsersCount) {
        Highcharts.chart('container', {
            title: {
                text: 'User Check-in Activity (Past 7 Days)'
            },
            xAxis: {
                categories: userLoginDates
            },
            yAxis: {
                title: {
                    text: 'User Count'
                }
            },
            accessibility: {
                enabled: false
            },
            series: [{
                name: 'Check In Users',
                data: userloginCount,
                color: '#3B82F6',
                type: 'column'
            }, {
                name: 'On Leave',
                data: leaveUsersCount,
                color: '#28a745',
                type: 'column'
            }, {
                name: 'Day Off',
                data: weekendUsersCount,
                color: '#ffc107',
                type: 'column'
            }, {
                name: 'Absent',
                data: absentUsersCount,
                color: '#dc3545',
                type: 'column'
            }]
        });
    }


    // const chart = Highcharts.chart('container', {
    //     title: {
    //         text: 'Check In Users'
    //     },

    //     credits: {
    //         enabled: false
    //     },

    //     xAxis: {
    //         categories: userLoginDates
    //     },
    //     yAxis: {
    //         title: {
    //             text: 'Number of max login Users'
    //         }
    //     },
    //     legend: {
    //         layout: 'vertical',
    //         align: 'right',
    //         verticalAlign: 'middle'
    //     },

    //     series: [{
    //         type: 'column',
    //         name: 'Login Users',
    //         data: userloginCount
    //     }]
    // });

    $(document).on('click', '#user_checkin_submit', function(e) {
        // e.preventDefault();
        var user_checkin_date = $("#user_checkin_date").val();
        if (user_checkin_date == "") {
            toastr.error('Please select date');
        } else {
            $.ajax({
                url: "{{ route('backend.dashboard.user_checkin') }}",
                method: 'POST',
                data: {
                    date: user_checkin_date,
                },
                success: function(response) {
                    console.log("1");
                    console.log(response.userLoginDates);
                    console.log(response.userLoginDates);
                    usercheckin_chart(response.userloginCount, response.userLoginDates)
                },
                error: function(e) {
                    if (showFormError) {
                        showFormError(e, form);
                    } else {
                        toastr.error('An error occurred while processing your request.');
                    }
                }
            });
        }




    });
</script>
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>

<script>
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
        enableTime: false

    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<script>
    // Some of these charts (e.g. "Users By Department") have no underlying
    // records in a fresh install, which previously just rendered a blank
    // canvas with no explanation. Show the same "no data" treatment the rest
    // of the dashboard's list widgets already use instead.
    function chartHasData(canvasId, dataArr) {
        if (dataArr && dataArr.length && dataArr.some(v => v)) {
            return true;
        }
        const canvas = document.getElementById(canvasId);
        canvas.style.display = 'none';
        const empty = document.createElement('div');
        empty.className = 'text-center text-muted py-5';
        empty.innerHTML = '<i class="fas fa-chart-pie fa-2x mb-2 d-block" style="opacity:.25"></i>No data available';
        canvas.parentElement.appendChild(empty);
        return false;
    }
</script>

<script>
    const chartData = @json($departmentChartData);

    const ctx = document.getElementById('branchChart').getContext('2d');
    const labels = chartData.map(item => item.department.toUpperCase()); // Department names
    const data = chartData.map(item => item.count); // User counts

    if (chartHasData('branchChart', data)) {
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                // backgroundColor: [
                //     '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                // ],
            }],
        },
        options: {
            plugins: {
                datalabels: {
                    color: '#fff', // Keep data labels white for contrast on colored slices
                    font: {
                        weight: 'bold',
                        size: 14,
                    },
                    formatter: (value, context) => {
                        const label = context.chart.data.labels[context.dataIndex];
                        return `${value}`; // Show department and count
                    },
                },
                legend: {
                    labels: {
                    color: '#64748B' // Unified secondary text color
                    }
                }
            },
        },
        plugins: [ChartDataLabels], // Enable the Data Labels plugin
    });
    }
</script>
<script>
    const departmentChartData = @json($divisionsChartData);

    const departmentCtx = document.getElementById('departmentPieChart').getContext('2d');
    const delabels = departmentChartData.map(item => item.divisionNames.toUpperCase());
    const dedata = departmentChartData.map(item => item.employeeCounts);

    if (chartHasData('departmentPieChart', dedata)) {
    new Chart(departmentCtx, {
        type: 'pie',
        data: {
            labels: delabels,
            datasets: [{
                data: dedata,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#00A36C', '#C71585', '#008080'
                ],
            }],
        },
        options: {
            plugins: {
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 14,
                    },
                    formatter: (value, context) => {
                        const label = context.chart.data.labels[context.dataIndex];
                        return `${value}`;
                    },
                },
                legend: {
                    labels: {
                        color: '#64748B'
                    }
                }
            },
        },
        plugins: [ChartDataLabels],
    });
    }
</script>

<script>
    const designationchartData = @json($designationChartData);

    const designationChart = document.getElementById('designationChart').getContext('2d');
    const designation_labels = designationchartData.map(item => item.designation.toUpperCase()); // Department names
    const designation_data = designationchartData.map(item => item.count); // User counts

    if (chartHasData('designationChart', designation_data)) {
    new Chart(designationChart, {
        type: 'pie',
        data: {
            labels: designation_labels,
            datasets: [{
                data: designation_data,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                ],
            }],
        },
        options: {
            plugins: {
                datalabels: {
                    color: '#fff', // Label color
                    font: {
                        weight: 'bold',
                        size: 14,
                    },
                    formatter: (value, context) => {
                        const label = context.chart.data.labels[context.dataIndex];
                        return `${value}`; // Show department and count
                    },
                },
                legend: {
                    labels: {
                        color: '#64748B' // Unified secondary text color
                    }
                }
            },
        },
        plugins: [ChartDataLabels], // Enable the Data Labels plugin
    });
    }
</script>
<script>
    const roleData = @json($roleChartData);

    const role_name = roleData.map(item => item.role.toUpperCase()); // Role names

    const role_count = roleData.map(item => item.count); // User counts
    const ctx1 = document.getElementById('roleChart').getContext('2d');

    if (chartHasData('roleChart', role_count)) {
    new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: role_name,
            datasets: [{
                data: role_count,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                ],
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 11,
                    },
                    formatter: (value, context) => {
                        const label = context.chart.data.labels[context.dataIndex];
                        return `${value}`;
                    },
                },
                legend: {
                    labels: {
                        color: '#64748B' // Set the color of the labels here (e.g., dark blue)
                    }
                }
            },

        },
        plugins: [ChartDataLabels],
    });
    }
</script>

<script>
    const workDetailsData = @json($workDetailsData);

    const year_labels = workDetailsData.map(item => item.year); // Years
    const joiningCounts = workDetailsData.map(item => item.joining_count); // Joining user counts
    const probationCounts = workDetailsData.map(item => item.probation_count); // Probation user counts

    const userBarChart = document.getElementById('userBarChart').getContext('2d');

    new Chart(userBarChart, {
        type: 'bar',
        data: {
            labels: year_labels, // Year labels
            datasets: [{
                    label: 'Joining Users',
                    data: joiningCounts, // Joining users count per year
                    backgroundColor: 'rgba(54, 162, 235, 0.7)', // Blue color
                },
                {
                    label: 'Probation Users',
                    data: probationCounts, // Probation users count per year
                    backgroundColor: 'rgba(255, 99, 132, 0.7)', // Red color
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Year',
                        color: '#64748B', // Set the Year title color to white
                    },
                    ticks: {
                        color: '#64748B', // Set the year labels (ticks) color to white
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Count',
                        color: '#64748B', // Set the Year title color to white
                    },
                    ticks: {
                        color: '#64748B', // Set the year labels (ticks) color to white
                    },
                    beginAtZero: true,
                },
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#64748B', // Legend label text color
                    }
                }
            }
        },
    });
</script>
@endpush
