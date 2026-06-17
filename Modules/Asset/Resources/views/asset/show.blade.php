@extends('layouts.backend')
@section('content')
<style>
    table tr td{
        color: white;
    }
</style>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('asset')}} : {{$asset->unique_id}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('asset_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">

                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped ">
                            <tr>
                                <td><b>{{__trans('asset_brand')}}</b> : {{$asset->manufacturer->name}}</td>
                                <td><b>{{__trans('asset_type')}}</b> : {{$asset->type->name}}</td>
                            </tr>
                            <tr>
                                <td><b>{{__trans('asset_model')}}</b> : {{$asset->model}}</td>
                                <td><b>{{__trans('asset_serial_number')}}</b> : {{$asset->unique_id}}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="color: white;"><b>{{__trans('asset_status')}}</b> : {!! $asset->status->getHtml() !!}</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p> <strong>{{__trans('asset_description')}}</strong> :</p>
                                    {{$asset->description}}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <strong>{{__trans('asset_assignment_list')}}</strong>
                    </div>
                    <div class="card-body">
                        <table class="table text-center table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{__trans('asset_brand')}}</th>
                                    <th>{{__trans('asset_type')}}</th>
                                    <th>{{__trans('asset_model')}}</th>
                                    <th>{{__trans('assign_user')}}</th>
                                    <th>{{__trans('asset_serial_number')}}</th>
                                    <th>{{__trans('issue_date')}}</th>
                                    <th>{{__trans('return_date')}}</th>
                                    <th>{{__trans('comment')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($asset->assignments()->with('asset','asset.type','asset.manufacturer')->orderByDesc('issue_date')->get() as $assignment)
                                <tr style="color: white;">
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$assignment->asset->manufacturer->name}}</td>
                                    <td>{{$assignment->asset->type->name}}</td>
                                    <td>{{$assignment->asset->model}}</td>
                                    <td>{{$assignment->user->name}}</td>
                                    <td>{{$assignment->asset->unique_id}}</td>
                                    <td>{{$assignment->issue_date}}</td>
                                    <td>{{$assignment->return_date ?? 'N/A'}}</td>
                                    <td>{{$assignment->comment ?? 'N/A'}}</td>
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
<!-- /Page Wrapper -->


@endsection
