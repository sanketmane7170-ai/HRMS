@extends('layouts.backend')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('document_request_view')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.employee.document-requests.index')}}">{{__trans('document_request_list')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('document_request_view')}}</li>
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
                                    <td> <strong>{{__trans('request_on')}}</strong> </td>
                                    <td> {{formatDate($documentRequest->created_at)}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('documentRequest_type')}}</strong> </td>
                                    <td> {{$documentRequest->type->name}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{__trans('created_by')}}</strong> </td>
                                    <td> {{$documentRequest->user->name}} ({{$documentRequest->user->employee_id}})</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('status')}}</strong> </td>
                                    <td> {!! $documentRequest->status->getHtml()!!}</td>
                                </tr>
                            </table>
                        </div>
                        @if ($documentRequest->type->name == 'salary certificate' || $documentRequest->type->name == 'Salary Certificate' || $documentRequest->type->name == 'SALARY CERTIFICATE')
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{__trans('letter_addressed_to')}}</strong> </td>
                                    <td>{{$documentRequest->letter_addressed_to}}</td>
                                </tr>
                            </table>
                        </div>
                        @endif
                        <div class="col-md-12 mt-2 p-4">
                            <label for="reason"> <strong>{{__trans('reason')}}</strong></label>
                            <p>
                                {{$documentRequest->reason}}
                            </p>
                        </div>
                        @if ($documentRequest->remark)
                        <div class="col-md-12 mt-2 p-4">
                            <label for="reason"> <strong>{{__trans('remark')}}</strong></label>
                            <p>
                                {{$documentRequest->remark}}
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
