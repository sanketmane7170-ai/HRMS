<div id="addResourceModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__trans('add_role')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('backend.roles.store')}}" datatable="true" method="POST"
                class="ajax-form-submit role-permission-form">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('role_title')}}</label>
                                <input type="text" name="name" class="form-control" id="field-1" placeholder="Admin">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="priority" class="form-label">{{__trans('priority')}}</label>
                                <input type="number" name="priority" class="form-control" id="priority" placeholder="1">
                            </div>
                        </div>
                    </div>

                    <label class="form-label">{{__trans('roles_permission')}}</label>
                    <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" class="form-check-input select-all">
                                    </th>
                                    <th style="width: 180px;">{{__trans('Module')}}</th>
                                    <th>{{__trans('Permissions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (config('default.permissions') as $module => $permissions)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input manage-permission"
                                            data-selector="{{$module}}">
                                    </td>
                                    <td>{{$module}}</td>
                                    <td>
                                        <div class="row text-start">
                                            @foreach ($permissions as $permission)
                                            <?php $name = "$permission $module"; ?>
                                            <div class="col-md-3 mb-2">
                                                <label class="d-flex align-items-center gap-2">
                                                    <input type="checkbox" value="{{$name}}" name="permissions[]"
                                                        class="form-check-input">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect"
                        data-bs-dismiss="modal">{{__trans('close')}}</button>
                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Scope the toggles to this form so they don't touch checkboxes elsewhere
    // on the page (e.g. the DataTable or other modals).
    (function () {
        var $form = $('#addResourceModal .role-permission-form');

        $form.on('change', '.select-all', function () {
            $form.find('input[type="checkbox"]').prop('checked', $(this).is(':checked'));
        });

        // Tick/untick every permission belonging to one module row.
        $form.on('change', '.manage-permission', function () {
            var $row = $(this).closest('tr');
            $row.find('input[name="permissions[]"]').prop('checked', $(this).is(':checked'));
        });
    })();
</script>
@endpush
