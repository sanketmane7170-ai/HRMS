@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('my_attendance')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('my_attendance')}}</li>
                    </ul>
                </div>
                <!-- <div class="col-auto user-checkin-checkout">
                    <x-attendance::checkin selector=".user-checkin-checkout" />
                </div> -->
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
                                        <th>#</th>
                                        <th>{{__trans('date')}}</th>
                                        <th>{{__trans('status')}}</th>
                                        <th>{{__trans('clock_in')}}</th>
                                        <th>{{__trans('clock_out')}}</th>
                                        <th>{{__trans('actual_time')}}</th>
                                    </tr>
                                </thead>
                            </table>
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
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.employee.attendances.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'date',
            },
            {
                data: 'status',
            },
            {
                data: 'clock_in',
            }, {
                data: 'clock_out',
            },
            {
                data: 'timediff',
                /*render: function(data, type, row) {
                    if (data !== null) {
                        // Replace &quot; with single quotes and then parse the JSON data
                        var cleanedData = data.replace(/&quot;/g, '"');
                        try {
                            // Attempt to parse cleanedData as JSON
                            var parsedData = JSON.parse(cleanedData);
                            // Assuming parsedData.actualTime exists   
                            
                            return parsedData.actualTime;
                        } catch (e) {
                            // If parsing fails, return original data (as plain text)
                            return data;

                        }
                    } else {
                        // Handle case where data is null (if needed)
                        return ''; // Or any other default value
                    }
                }*/
            },

        ]
    });
</script>
@endpush