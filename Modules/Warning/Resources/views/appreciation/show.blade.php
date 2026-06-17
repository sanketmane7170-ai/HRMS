@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('appreciation_view') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.user-appreciation') }}">{{ __trans('user_appreciation') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('appreciation_view') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    {{--  <a href="{{ route('backend.user-warning.generate.document', $UserAppreciation) }}" class="btn btn-primary" target="_blank">{{ __trans('download_document') }}</a>  --}}
                    @php
                        $path = "/uploads/users/$UserAppreciation->user_id/appreciation/$UserAppreciation->document";
                    @endphp
                    @if($UserAppreciation->document)
                        <a href="{{$path}}" class="btn btn-primary" target="_blank">{{ __trans('download_document') }}</a>
                    @endif
                    {{--  @can('Delete Warning')  --}}
                    <a href="{{ route('backend.user-appreciation.mydelete', $UserAppreciation) }}" class="btn btn-danger"><i class='fa fa-trash'></i> Delete</a>
                            {{--  {!! createActionButton(
                                route('backend.user-appreciation.mydelete', $UserAppreciation),
                                'Delete',
                                'btn-danger action-button',
                                'fa fa-trash',
                                'redirect',
                            ) !!}  --}}
                    {{--  @endcan  --}}
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
                                    <td> <strong>{{ __trans('date') }}</strong> </td>
                                    <td> {{ formatDate($UserAppreciation->date) }}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('employee') }}</strong> </td>
                                    <td> {{ $UserAppreciation->user->name }}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('department') }}</strong> </td>
                                    <td> {{ $UserAppreciation->user->department->name }}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('document') }}</strong> </td>
                                    <td> {{ $UserAppreciation->document }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{ __trans('type') }}</strong> </td>
                                    <td> {{ $UserAppreciation->type }}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('employee_id') }}</strong> </td>
                                    <td> {{ $UserAppreciation->user->employee_id }}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{ __trans('acknowledgement') }}</strong> </td>
                                    <td> {{ $UserAppreciation->acknowledgement }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-12 mt-2 p-4">
                            <label for="details"> <strong style="color: white">{{ __trans('details') }}</strong></label>
                            <p>
                                {!! $UserAppreciation->detail !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection