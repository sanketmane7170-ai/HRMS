<div class="row align-items-center mb-3">
    <div class="col"></div>
    <div class="col-auto">
        @can('Create Dependent')
        <a href="{{route('backend.user-dependent.create',$user)}}" class="btn btn-sm btn-success edit-button">{{__trans('add_dependent')}}</a>
        @endcan
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card">
            <table class="table text-center light">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{__trans('name')}}</th>
                        <th>{{__trans('contact')}}</th>
                        <th>{{__trans('relation')}}</th>
                        <th>{{__trans('gender')}}</th>
                        <th>{{__trans('nationality')}}</th>
                        <th>{{__trans('actions')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($user->dependents()->get() as $dependent)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$dependent->name}}</td>
                        <td>{{$dependent->contact}}</td>
                        <td>{{$dependent->relation->name}}</td>
                        <td>{{$dependent->gender}}</td>
                        <td>{{$dependent->nationality}}</td>
                        <td>
                            <?php
                            // echo  createActionButton(route('backend.user-dependent.edit', $dependent), 'Show', 'btn-success eye-button', 'fa fa-eye');
                            echo  createActionButton(route('backend.user-dependent.show', $dependent), 'Show', 'btn-primary edit-button', 'fa fa-eye');
                            ?>
                            @can('Edit Dependent')
                            <?php
                            echo  createActionButton(route('backend.user-dependent.edit', $dependent), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                            ?>
                            @endcan
                            @can('Delete Dependent')
                            <?php
                            echo  createActionButton(route('backend.user-dependent.destroy', $dependent), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'html="#dependent-details"');
                            ?>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">{{__trans('no_dependent_added_yet')}}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
