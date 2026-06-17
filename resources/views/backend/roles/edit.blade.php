@extends('layouts.backend')

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <form action="{{route('backend.roles.update',$role)}}" method="POST" class="ajax-form-submit">
            @csrf
            @method('PUT')
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{__trans('edit_role_permission')}}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a
                                    href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                            </li>
                            <li class="breadcrumb-item active">{{__trans('edit_role_permission')}}</li>
                        </ul>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">{{__trans('update_permissions')}}</button>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body ">
                            <div class="col-md-12 p-4 row">
                                <div class="col-md-9 mb-3">
                                    <label for="edit-field-1" class="form-label">{{__trans('role_title')}}</label>
                                    <input type="text" name="name" value="{{$role->name}}" class="form-control"
                                        id="edit-field-1" placeholder="Admin">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="edit-field-1" class="form-label">{{__trans('priority')}}</label>
                                    <input type="number" name="priority" value="{{$role->priority}}"
                                        class="form-control" id="priority" placeholder="1">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table text-center table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><input type="checkbox" class="form-check-input select-all"></th>
                                            <th>{{__trans('Module')}}</th>
                                            <th colspan="2">{{__trans('Permissions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                        @foreach (config('default.permissions') as $module => $permissions)

                                        <tr>
                                            <td><input type="checkbox" class="form-check-input manage-permission"
                                                    data-selector="{{$module}}"></td>
                                            <td>{{$module}}</td>
                                            <!-- <td>
                                                @foreach ($permissions as $permission)

                                                <?php
                                                $name = "$permission $module";

                                                ?>
                                                <span class="me-4">
                                                    <label for="">
                                                        <input type="checkbox" value="{{$name}}" name="permissions[]"
                                                            class="form-check-input" @if($role->hasPermissionTo($name))
                                                        checked @endif> {{$permission}}
                                                    </label>
                                                </span>
                                                @endforeach
                                            </td> -->
                                            <td>
                                                <div class="row text-start">
                                                    @foreach ($permissions as $permission)
                                                    <?php $name = "$permission $module"; ?>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="d-flex align-items-center gap-2">
                                                            <input type="checkbox" value="{{$name}}" name="permissions[]"
                                                                class="form-check-input"
                                                                @if($role->hasPermissionTo($name)) checked @endif>
                                                            {{$permission}}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
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
        </form>
    </div>
</div>
@endsection


@push('scripts')
<script>
    $('.select-all').on('change', function() {
        if ($(this).is(':checked')) {
            $('[type="checkbox"]').prop('checked', true);
        } else {
            $('[type="checkbox"]').prop('checked', false);
        }
    });

    $('.manage-permission').on('change', function() {
        var selector = $(this).data('selector');
        console.log(selector);
        if ($(this).is(':checked')) {
            $('[value$="' + selector + '"]').prop('checked', true);
        } else {
            $('[value$="' + selector + '"]').prop('checked', false);
        }
    });
</script>
@endpush
