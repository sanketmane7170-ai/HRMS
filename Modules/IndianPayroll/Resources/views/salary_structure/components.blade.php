@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('salary_components') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('indian_payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('salary_components') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.salary-components.create') }}" class="btn btn-primary edit-button">
                        <i class="fas fa-plus"></i> {{ __trans('add_component') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __trans('code') }}</th>
                                    <th>{{ __trans('name') }}</th>
                                    <th>{{ __trans('type') }}</th>
                                    <th>{{ __trans('taxable') }}</th>
                                    <th>{{ __trans('statutory') }}</th>
                                    <th>{{ __trans('active') }}</th>
                                    <th>{{ __trans('action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($components as $component)
                                <tr>
                                    <td><code>{{ $component->code }}</code></td>
                                    <td>{{ $component->name }}</td>
                                    <td><span class="badge badge-info">{{ $component->type }}</span></td>
                                    <td>{!! $component->is_taxable ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</td>
                                    <td>{!! $component->is_statutory ? '<i class="fa fa-check text-success"></i>' : '-' !!}</td>
                                    <td>{!! $component->is_active ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</td>
                                    <td>
                                        @if(!$component->is_statutory)
                                        <a href="{{ route('backend.indian-payroll.salary-components.edit', $component) }}" class="btn btn-sm btn-warning edit-button"><i class="fa fa-edit"></i></a>
                                        @endif
                                    </td>
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
<div id="editModal" class="modal"></div>
@endsection
