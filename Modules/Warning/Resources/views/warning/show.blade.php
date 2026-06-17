@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('warning_view') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.user-warnings.index') }}">{{ __trans('user_warnings') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('warning_view') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.user-warning.generate.document', $userWarning) }}"
                        class="btn btn-primary" target="_blank">{{ __trans('download_document') }}</a>
                    @php
                    $path = "/uploads/users/$userWarning->user_id/warnings/$userWarning->document";
                    @endphp
                    @if($userWarning->document)
                    <a href="{{$path}}"
                        class="btn btn-primary" target="_blank">{{ __trans('download_attechment') }}</a>
                    @endif
                    @php
                    $path = "/uploads/users/$userWarning->user_id/warnings/$userWarning->ack_document";
                    @endphp
                    @if($userWarning->ack_document)
                    <a href="{{$path}}"
                        class="btn btn-primary" target="_blank">{{ __trans('download_acknowledgement') }}</a>
                    @endif
                    @can('Delete Warning')
                    <a href="{{ route('backend.user-warnings.mydelete', $userWarning) }}" class="btn btn-danger"><i class='fa fa-trash'></i> Delete</a>
                    {{--- {!! createActionButton(
                                route('backend.user-warnings.mydelete', $userWarning),
                                'Delete',
                                'btn-danger action-button',
                                'fa fa-trash',
                                'redirect',
                            ) !!} ---}}
                    @endcan
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

                                <tr>
                                    <td> <strong>{{ __trans('acknowledgement') }}</strong> </td>
                                    <td> {{ $userWarning->acknowledgement }}</td>
                                </tr>

                                <tr>
                                    <td> <strong>{{ __trans('ack_datetime') }}</strong> </td>
                                    <td> {{ $userWarning->ack_datetime }}</td>
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
                                <tr>
                                    <td> <strong>{{ __trans('document') }}</strong> </td>
                                    <td> {{ $userWarning->document}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('ack_document') }}</strong> </td>
                                    <td> {{ $userWarning->ack_document }}</td>
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