@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('kpi_score_level') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('kpi_score_level') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <form id="kpiSettingsForm" action="{{ route('kpi.scorelevels.store') }}" method="POST">
            @csrf

            @foreach ($permission_roles as $r_id => $r_name)
            <div class="role-container p-3 mb-4 border rounded">
                <div class="mb-3">
                    <span class="fw-bold text-black">For {{ __trans($r_name) }} Role</span>
                </div>

                <div class="approval-steps" data-role-id="{{ $r_id }}">
                    @isset($steps[$r_id])
                    @foreach ($steps[$r_id] as $index => $step)
                    @php
                        $stepIndex = $loop->index;
                        //dd(json_decode($step['approvers'][0]));
                        //$approversArray = json_decode($step['approvers'][0], true);
                        //$approversArray = $step['approvers'][0];
                        $approversArray = is_array($step['approvers']) ? $step['approvers'] : json_decode($step['approvers'], true);


                    @endphp

                    <div class="step-container p-4 border rounded-lg mb-4 bg-white row align-items-center">
                        <div class="col-auto">
                            <span class="fw-bold text-black">Level {{ $stepIndex + 1 }}</span>
                        </div>
                        <div class="col">
                            <div class="approver-group d-flex flex-wrap gap-2">
                                @foreach ($approversArray as $approver)
                                <div class="d-flex gap-2 align-items-center w-auto">
                                    <select name="steps[{{ $r_id }}][{{ $stepIndex }}][approvers][]" class="form-select bg-white w-auto">
                                        <option value="">Select Role</option>
                                        <option value="Report To User" {{ $approver == 'Report To User' ? 'selected' : '' }}>Report To User</option>
                                        @foreach ($roles as $id => $role)
                                            <option value="{{ $role }}" {{ $approver == $role ? 'selected' : '' }}>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-danger remove-approver">X</button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-auto">
                            <!-- <button type="button" class="btn btn-success add-approver">+ Add Approver</button> -->
                            <button type="button" class="btn btn-danger remove-step">Remove Level</button>
                        </div>
                    </div>
                    @endforeach
                    @endisset
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-primary add-step" data-role-id="{{ $r_id }}">+ Add Level</button>
                </div>
            </div>
            @endforeach

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    $(".add-step").click(function () {
        let roleId = $(this).data("role-id");
        let stepsContainer = $('.approval-steps[data-role-id="' + roleId + '"]');
        let newStepIndex = stepsContainer.find(".step-container").length;

        let stepHtml = `
        <div class="step-container p-4 border rounded-lg mb-4 bg-white row align-items-center">
            <div class="col-auto">
                <span class="fw-bold text-black">Level ${newStepIndex + 1}</span>
            </div>
            <div class="col">
                <div class="approver-group d-flex flex-wrap gap-2">
                    <div class="d-flex gap-2 align-items-center w-auto">
                        <select name="steps[${roleId}][${newStepIndex}][approvers][]" class="form-select bg-white w-auto">
                            <option value="">Select Role</option>
                            <option value="Report To User">Report To User</option>
                            @foreach ($roles as $id => $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-danger remove-approver">X</button>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger remove-step">Remove Level</button>
            </div>
        </div>`;
        stepsContainer.append(stepHtml);
    });

    $(document).on("click", ".remove-step", function () {
        $(this).closest(".step-container").remove();
    });

    $(document).on("click", ".add-approver", function () {
        let stepContainer = $(this).closest(".step-container");
        let approverGroup = stepContainer.find(".approver-group");
        let roleId = $(this).closest(".approval-steps").data("role-id");
        let stepIndex = stepContainer.index();

        let approverHtml = `
        <div class="d-flex gap-2 align-items-center w-auto">
            <select name="steps[${roleId}][${stepIndex}][approvers][]" class="form-select bg-white w-auto">
                <option value="">Select Role</option>
                <option value="Report To User">Report To User</option>
                @foreach ($roles as $id => $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-danger remove-approver">X</button>
        </div>`;
        approverGroup.append(approverHtml);
    });

    $(document).on("click", ".remove-approver", function () {
        $(this).closest("div.w-auto").remove();
    });

    $("#kpiSettingsForm").submit(function (e) {
        // e.preventDefault(); // Uncomment this if you want AJAX submission
        // Add AJAX logic here if needed
    });
});
</script>
@endsection
