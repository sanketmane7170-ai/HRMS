@extends('layouts.backend')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('leave_view')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.leaves.index')}}">{{__trans('my_leaves')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('leave_view')}}</li>
                    </ul>
                </div>
                <div class="col-auto">

                    @if(
                    (
                    $leave->status->value == \Modules\Leave\Enums\LeaveStatus::Pending->value
                    && $leave_approval_auth
                    )
                    ||
                    (
                    (
                    auth()->user()->hasRole(App\Models\User::ROLE_ADMIN)
                    || auth()->user()->hasRole(App\Models\User::ROLE_SUPER_ADMIN)
                    )
                    && $leave->status->value == \Modules\Leave\Enums\LeaveStatus::Pending->value
                    )
                    )

                    <a class="btn btn-success action-button"
                        href="{{ route('backend.leaves.action', [$leave, 'approve', $level]) }}" method="POST"
                        data-alert="{{ __trans('are_you_sure_want_to_apporve_leave_request?') }}" redirect>
                        <i class="fas fa-check"></i> {{ __trans('approve') }}
                    </a>
                    <a class="btn btn-danger edit-button"
                        href="{{ route('backend.leaves.action', [$leave, 'reject']) }}" method="POST">
                        <i class="fas fa-times"></i> {{ __trans('reject') }}
                    </a>
                    @endif
                    @if (
                    $leave->status->value == \Modules\Leave\Enums\LeaveStatus::Approved->value ||
                    $leave->status->value == \Modules\Leave\Enums\LeaveStatus::Rejected->value)
                    <a class="btn btn-danger" href="{{ route('backend.leaves.pdfexport', $leave->id) }}">
                        <i class="fa fa-file-pdf"></i> {{ __trans('export-pdf') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @php
        $authUser =$leave->user;
        @endphp
        <x-user-leave-balance :user=$authUser />
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{__trans('start_date')}}</strong> </td>
                                    <td> {{formatDate($leave->start_date)}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('leave_type')}}</strong> </td>
                                    <td> {{$leave->type->name}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('total_leave_days')}}</strong> </td>
                                    <td> {{$leave->total_leave_days}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('created_by')}}</strong> </td>
                                    <td> {{$leave->user->name}} ({{$leave->user->employee_id}})</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{__trans('end_date')}}</strong> </td>
                                    <td> {{formatDate($leave->end_date)}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('is_half_day')}}</strong> </td>
                                    <td> {{$leave->is_half_day ?'Yes' :'No'}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('status')}}</strong> </td>
                                    <td> {!! $leave->status->getHtml()!!}
                                        {{ !empty($level) ? 'by Level ' . $level : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('created_at')}}</strong> </td>
                                    <td> {!! formatDate($leave->created_at)!!}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-12 mt-2 p-4">
                            <label for="reason"> <strong>{{__trans('reason')}}</strong></label>
                            <p>
                                {{$leave->reason}}
                            </p>
                            @if($leave->file_path)
                            <a href="{{asset($leave->file_path)}}" target="_blank">{{__trans('view_document')}}</a>
                            @endif
                        </div>

                        @if ($leave->remark)
                        <div class="col-md-12 mt-2 p-4">
                            <label for="reason"> <strong>{{__trans('remark')}}</strong></label>
                            <p>
                                {{$leave->remark}}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="editModal">

</div>

@endsection