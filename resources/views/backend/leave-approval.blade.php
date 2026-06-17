@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('leave_approval') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('leave_approval') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <form id="leaveSettingsForm" action="{{ route('backend.leave-approval.store') }}" method="POST">
            @csrf

            @foreach ($permission_roles as $role_id => $role_name)
                <div class="role-container p-3 mb-4 border rounded">
                    <label class="form-label fw-bold text-black">{{ __trans($role_name) }} Role</label>
                    <select name="levels[{{ $role_id }}]" class="form-select w-auto">
                        <option value="">Select Level</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ isset($levels[$role_id]) && $levels[$role_id] == $i ? 'selected' : '' }}>
                                Level {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
            @endforeach

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
