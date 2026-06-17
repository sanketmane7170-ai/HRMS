@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('ctc_structure_templates') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('indian_payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('ctc_structure_templates') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.salary-templates.create') }}" class="btn btn-primary edit-button">
                        <i class="fas fa-plus"></i> {{ __trans('add_template') }}
                    </a>
                </div>
            </div>
        </div>

        @foreach ($templates as $template)
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5>{{ $template->name }} <span class="badge badge-{{ $template->is_active ? 'success' : 'secondary' }}">{{ $template->is_active ? __trans('active') : __trans('inactive') }}</span></h5>
                    <div>
                        <a href="{{ route('backend.indian-payroll.salary-templates.edit', $template) }}" class="btn btn-sm btn-warning edit-button"><i class="fa fa-edit"></i></a>
                        <a href="{{ route('backend.indian-payroll.salary-templates.destroy', $template) }}" method="DELETE" class="btn btn-sm btn-danger action-button" data-alert="{{ __trans('delete_template_confirm') }}"><i class="fa fa-trash"></i></a>
                    </div>
                </div>
                <p class="text-muted">{{ $template->description }}</p>
                <table class="table table-sm">
                    <thead><tr><th>{{ __trans('component') }}</th><th>{{ __trans('calculation') }}</th><th>{{ __trans('value') }}</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($template->components as $tc)
                        <tr>
                            <td>{{ $tc->component->name }}</td>
                            <td>{{ str_replace('_', ' ', $tc->calculation_type) }}</td>
                            <td>{{ $tc->calculation_type === 'flat' ? number_format($tc->value, 2) : $tc->value.'%' }}</td>
                            <td>
                                <a href="{{ route('backend.indian-payroll.salary-templates.components.remove', [$template, $tc]) }}" method="DELETE" class="btn btn-sm btn-link text-danger action-button" data-alert="{{ __trans('remove_component_confirm') }}"><i class="fa fa-times"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <form method="POST" action="{{ route('backend.indian-payroll.salary-templates.components.add', $template) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <select name="salary_component_id" class="form-control" required>
                            <option value="">{{ __trans('select_component') }}</option>
                            @foreach (\Modules\IndianPayroll\Entities\SalaryComponent::where('type', 'earning')->where('is_active', true)->get() as $sc)
                                <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="calculation_type" class="form-control" required>
                            <option value="flat">{{ __trans('flat_amount') }}</option>
                            <option value="percentage_of_basic">{{ __trans('percentage_of_basic') }}</option>
                            <option value="percentage_of_ctc">{{ __trans('percentage_of_ctc') }}</option>
                            <option value="remainder_of_ctc">{{ __trans('remainder_of_ctc') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="value" class="form-control" placeholder="{{ __trans('value') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">{{ __trans('add') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
<div id="editModal" class="modal"></div>
@endsection
