@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('warning_view') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.employee.user-warnings.index') }}">{{ __trans('user_warnings') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('warning_view') }}</li>
                    </ul>
                </div>
                <div class="col-auto">

                </div>
            </div>
        </div>
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{ __trans('incident_date') }}</strong> </td>
                                    <td> {{ formatDate($userWarning->date) }}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('employee') }}</strong> </td>
                                    <td> {{ $userWarning->user->name }}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('department') }}</strong> </td>
                                    <td> {{ $userWarning->user->department->name }}</td>
                                </tr>

                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{ __trans('type') }}</strong> </td>
                                    <td> {!! $userWarning->type->getHtml() !!}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('employee_id') }}</strong> </td>
                                    <td> {{ $userWarning->user->employee_id }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-12 mt-2 p-4">
                            <label for="details"> <strong>{{ __trans('details') }}</strong></label>
                            <p>
                                {!! $userWarning->detail !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
