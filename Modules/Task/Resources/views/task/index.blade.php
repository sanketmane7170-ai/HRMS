@extends('layouts.backend')
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('task_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('task_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create Task')
                    <a href="{{route('backend.task.create')}}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        @php
        $statusColors = [
        'PENDING' => '#042356',
        'IN_PROGRESS' => '#ffbc34 !important',
        'COMPLETED' => '#22C68C',
        'ON_HOLD' => '#FF0000',
        ];

        $priorityColors = [
        'LOW' => '#042356',
        'MEDIUM' => '#ffbc34',
        'HIGH' => '#FF0000',
        'URGENT' => '#343a40',
        ];

        // Calculate contrast text color
        function getTextColor($hexColor) {
        $hex = str_replace('#', '', $hexColor);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $luminance = ($r * 299 + $g * 587 + $b * 114) / 1000;
        return $luminance > 150 ? 'black' : 'white';
        }
        @endphp

        <div class="row">
            <div class="col-md-12">
                <div class="card-body light">

                    {{-- Status cards row --}}
                    <div class="row justify-content-center mb-3">
                        @foreach($statusCounts as $status => $count)
                        @php
                        $key = strtoupper($status);
                        $bgColor = $statusColors[$key] ?? '#f8f9fa';
                        $textColor = getTextColor($bgColor);
                        @endphp
                        <div class="col-md-3 col-6 mb-3">
                            <div class="card shadow-sm border"
                                style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase fw-bold">{{ __trans($status) }}</h6>
                                    <h4 class="mb-0">{{ $count }}</h4>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Priority cards row --}}
                    <div class="row justify-content-center">
                        @foreach($priorityCounts as $priority => $count)
                        @php
                        $key = strtoupper($priority);
                        $bgColor = $priorityColors[$key] ?? '#f8f9fa';
                        $textColor = getTextColor($bgColor);
                        @endphp
                        <div class="col-md-3 col-6 mb-3">
                            <div class="card shadow-sm border"
                                style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase fw-bold">{{ __trans($priority) }}</h6>
                                    <h4 class="mb-0">{{ $count }}</h4>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{__trans('title')}}</th>
                                        <th>{{__trans('description')}}</th>
                                        <th>{{__trans('assigned_to')}}</th>
                                        <th>{{__trans('assigned_by')}}</th>
                                        <th>{{__trans('priority')}}</th>
                                        <th>{{__trans('status')}}</th>
                                        <th>{{__trans('start_date')}}</th>
                                        <th>{{__trans('end_date')}}</th>
                                        <th>{{__trans('actions')}}</th>
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
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [
            [0, 'desc']
        ],
        ajax: {
            url: "{{route('backend.task.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },

            {
                data: 'title',
            },
            {
                data: 'description',
            },
            {
                data: 'assigned_to_user',
                name: 'assigned_to_user'
            },
            {
                data: 'assigned_by_user',
                name: 'assigned_by_user'
            },
            {
                data: 'priority',
            },
            // {
            //     data: 'status',
            // },
            {
                data: 'status',
                render: function(data) {
                    if (!data) return '';
                    return data
                        .toLowerCase()
                        .replace(/_/g, ' ')
                        .replace(/\b\w/g, c => c.toUpperCase());
                }
            },

            {
                data: 'start_date',
            },
            {
                data: 'end_date',
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            },
        ]
    });
</script>
@endpush