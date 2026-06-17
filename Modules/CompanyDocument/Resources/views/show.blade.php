@extends('layouts.backend')

@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('company_document_view')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.companydocument.index')}}">{{__trans('my_companydocuments')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('companydocument_view')}}</li>
                    </ul>
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
                                    <td> <strong>{{__trans('legal_trade_name')}}</strong> </td>
                                    <td> {{$companydocument->legal_trade_name}}</td>
                                </tr>

                                 <tr>
                                    <td> <strong>{{__trans('short_name')}}</strong> </td>
                                    <td> {{$companydocument->short_name}}</td>
                                </tr>

                                <tr>
                                    <td> <strong>{{__trans('license_expiry')}}</strong> </td>
                                    <td> {{$companydocument->license_expiry}}</td>
                                </tr>

                                <tr>
                                    <td> <strong>{{__trans('mol_code')}}</strong> </td>
                                    <td> {{$companydocument->mol_code}}</td>
                                </tr>

                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{__trans('license_number')}}</strong> </td>
                                    <td> {{$companydocument->license_number}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('added_date')}}</strong> </td>
                                    <td> {{$companydocument->added_date}}</td>
                                </tr>

                            </table>
                        </div>




                        <div class="col-md-12 mt-2 p-4">
                            <div class="mb-3">
                                <h6>{{__trans('Uploaded Documents')}}</h6>
                            </div>
                            <div class="col-md-6 mt-6">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ asset('uploads/companydocument/'. $companydocument->document) }}" target="_blank">
                                        {{ $companydocument->document }}
                                    </a>
                                </li>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="editModal">

</div>


@endsection