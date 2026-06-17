@extends('layouts.backend')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('leave_view')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.employee.leaves.index')}}">{{__trans('my_leaves')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('leave_view')}}</li>
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
                                    <td> {!! $leave->status->getHtml()!!}</td>
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
@endsection
