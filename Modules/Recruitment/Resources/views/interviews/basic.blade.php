@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Interview Schedule (Basic HTML)</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Interview Schedule</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div class="alert alert-success">
            <strong>✅ SUCCESS!</strong> Found {{ $interviews->count() }} interviews in the database.
        </div>

        <!-- Interview Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Interview Schedule ({{ $interviews->count() }} interviews)</h4>
                    </div>
                    <div class="card-body">
                        @if($interviews->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Candidate</th>
                                            <th>Job Title</th>
                                            <th>Interviewer</th>
                                            <th>Scheduled Date</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($interviews as $index => $interview)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($interview->application && $interview->application->user)
                                                    {{ $interview->application->user->name }}
                                                    <small class="text-muted">(User ID: {{ $interview->application->user->id }})</small>
                                                @elseif($interview->application && $interview->application->candidate_name)
                                                    {{ $interview->application->candidate_name }}
                                                    <small class="text-muted">(External)</small>
                                                @else
                                                    <span class="text-danger">No Candidate Data</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($interview->application && $interview->application->job)
                                                    {{ $interview->application->job->title }}
                                                @else
                                                    <span class="text-danger">No Job</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($interview->interviewer)
                                                    {{ $interview->interviewer->name }}
                                                @else
                                                    <span class="text-danger">No Interviewer</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($interview->scheduled_at)
                                                    {{ $interview->scheduled_at->format('M d, Y H:i') }}
                                                @else
                                                    <span class="text-danger">Not Scheduled</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $interview->type ? ucfirst(str_replace('_', ' ', $interview->type)) : 'Phone (Default)' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $interview->status === 'scheduled' ? 'warning' : ($interview->status === 'completed' ? 'success' : 'secondary') }}">
                                                    {{ ucfirst($interview->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $interview->duration_minutes ?? 60 }} minutes</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <strong>⚠️ No interviews found!</strong> The interviews table exists but contains no data.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug Info -->
        <div class="row mt-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Debug Information</h4>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li><strong>Total Interviews:</strong> {{ $interviews->count() }}</li>
                            <li><strong>User Authenticated:</strong> {{ auth()->check() ? 'Yes (' . auth()->user()->name . ')' : 'No' }}</li>
                            <li><strong>Current Time:</strong> {{ now()->format('M d, Y H:i:s') }}</li>
                        </ul>
                        
                        <h5>Test Links:</h5>
                        <ul>
                            <li><a href="{{ route('recruitment.simple-interviews.index') }}" target="_blank">Simple Interviews (DataTable)</a></li>
                            <li><a href="{{ route('recruitment.simple-interviews.data') }}" target="_blank">AJAX Data Endpoint</a></li>
                            <li><a href="{{ route('recruitment.debug.json') }}" target="_blank">JSON Debug Data</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection