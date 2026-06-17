@extends('layouts.backend')

@section('title', 'Employee Live Status Board')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Live Presence Pulse</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Live Status</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <button class="btn btn-primary" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Refresh Board</button>
                </div>
            </div>
        </div>

        <div id="live-status-container">
            <!-- Summary Stats -->
            <div class="row">
                @foreach(\Modules\Attendance\Enums\WorkStatus::cases() as $status)
                    @if($status !== \Modules\Attendance\Enums\WorkStatus::OFFLINE)
                    <div class="col-md-6 col-sm-6 col-lg-6 col-xl-2">
                        <div class="card dash-widget">
                            <div class="card-body">
                                <span class="dash-widget-icon" style="background-color: {{ $status->color() }}1a">
                                    <i class="{{ $status->icon() }}" style="color: {{ $status->color() }}"></i>
                                </span>
                                <div class="dash-widget-info" style="text-align: right;">
                                    <h3 style="color: var(--theme-heading-color, #1f1f1f); font-weight: bold;">{{ $users->where('work_status', $status)->count() }}</h3>
                                    <span style="color: var(--theme-text-color, #6c757d); font-weight: 500;">{{ $status->label() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <!-- Pulse Grid -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card pulse-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Employee Real-Time Status</h4>
                            <span class="badge badge-success"><i class="fas fa-satellite-dish pulse-icon"></i> Live Connection Active</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover custom-table mb-0 datatable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Current Status</th>
                                            <th>Duration</th>
                                            <th>Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                        <tr>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="javascript:void(0);" class="avatar">
                                                        <img src="{{ $user->getProfileImage() }}" alt="">
                                                    </a>
                                                    <a href="javascript:void(0);">{{ $user->name }} <span>{{ $user->employee_id }}</span></a>
                                                </h2>
                                            </td>
                                            <td>{{ $user->department->name ?? 'N/A' }}</td>
                                            <td>
                                                @php $status = $user->work_status ?? \Modules\Attendance\Enums\WorkStatus::AVAILABLE; @endphp
                                                <div class="d-flex align-items-center">
                                                    <span class="status-indicator me-2" style="background-color: {{ $status->color() }}; width: 12px; height: 12px; border-radius: 50%;"></span>
                                                    <span class="badge" style="background-color: {{ $status->color() }}1a; color: {{ $status->color() }}; border: 1px solid {{ $status->color() }}33;">
                                                        {{ $status->label() }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($user->status_updated_at)
                                                    {{ $user->status_updated_at->diffForHumans(null, true) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $user->status_updated_at ? $user->status_updated_at->format('h:i A') : 'N/A' }}</td>
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
</div>

@push('scripts')
<script>
    // Realtime AJAX Fetcher for Live Status Board
    $(document).ready(function() {
        setInterval(function() {
            $.ajax({
                url: window.location.href,
                type: 'GET',
                success: function(response) {
                    var newContent = $(response).find('#live-status-container').html();
                    if(newContent) {
                        $('#live-status-container').html(newContent);
                        // Re-initialize any plugins if needed (e.g. Datatables)
                        if ($.fn.DataTable && $('.datatable').length > 0) {
                            $('.datatable').DataTable({
                                destroy: true,
                                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
                            });
                        }
                    }
                },
                error: function() {
                    console.error("Live Board: Connection failed. Retrying...");
                }
            });
        }, 15000); // 15 Seconds Pulse
    });
</script>
@endpush

@push('styles')
<style>
    .status-indicator {
        border-radius: 50%;
        display: inline-block;
    }
    .custom-table tr td {
        padding: 15px 10px !important;
    }
</style>
@endpush
@endsection
