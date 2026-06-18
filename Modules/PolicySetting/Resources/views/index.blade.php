@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="row">

            @can('Leave Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> 
                                    <a class="@if($activeLink =='leavesPolicy') active @endif edit-button" href="{{route('backend.settings.leavesPolicy')}}">{{__trans('leaves_policy')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Attendance Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a
                                        class="@if($activeLink =='attendance_policy') active @endif edit-button"
                                        href="{{route('backend.settings.attendance_policy')}}">{{__trans('attendance_policy')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Late Comers Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a
                                        class="@if($activeLink =='late_comers_policy') active @endif edit-button"
                                        href="{{route('backend.settings.late_comers_policy')}}">{{__trans('late_comers_policy')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Early Comers Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a
                                        class="@if($activeLink =='early_comers_policy') active @endif edit-button"
                                        href="{{route('backend.settings.early_comers_policy')}}">{{__trans('early_comers_policy')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan

            @can('Overtime Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a
                                        class="@if($activeLink =='overtime_policy') active @endif edit-button"
                                        href="{{route('backend.settings.overtime_policy')}}">{{__trans('overtime_policy')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> 
                                    <a class="@if($activeLink =='overtime_policy') active @endif" href="{{route('backend.settings.endOfServicePolicy')}}">{{__trans('end_of_service_policy')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> 
                                    <a class="@if($activeLink =='shift_policy') active @endif edit-button" href="{{route('backend.settings.shiftPolicy')}}">{{__trans('Shift_policy')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> 
                                    <a class="@if($activeLink =='payrollPolicy') active @endif edit-button" href="{{route('backend.settings.payrollPolicy')}}">{{__trans('payroll_policy')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


    </div>
</div>
<!-- /Page Wrapper -->
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        $(document).on('change', '.autoadd', function() {
            let isChecked = this.checked; // true or false
            let url = this.dataset.url; // The route URL from the data attribute
            let token = this.dataset.token; // CSRF token for the request

            // Send the request to the server
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ allow: isChecked })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Error updating status.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
        });
    });
</script>

<script type="text/javascript">

    $(document).on('change', '.leaveallow', function() {
        let isChecked = this.checked; // true or false
        let url = this.dataset.url; // The route URL from the data attribute
        let token = this.dataset.token; // CSRF token for the request

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Policy setting updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });

    $(document).on('change', '.month2leave', function() {

        let isChecked = this.checked; // true or false
        let url = this.dataset.url; // The route URL from the data attribute
        let token = this.dataset.token; // CSRF token for the request

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Policy setting updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });

    $(document).on('change', '.year2leave', function() {

        let isChecked = this.checked; // true or false
        let url = this.dataset.url; // The route URL from the data attribute
        let token = this.dataset.token; // CSRF token for the request

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Policy setting updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });


    $(document).on('change', '.monthwise', function() {

        let isChecked = this.checked; // true or false
        let url = this.dataset.url; // The route URL from the data attribute
        let token = this.dataset.token; // CSRF token for the request

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Policy setting updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });
</script>
@endpush

