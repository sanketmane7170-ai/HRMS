<div class="row align-items-center mb-3">
    <div class="col"></div>
    <div class="col-auto">
        @can('Assign Asset')
        <a href="{{route('backend.asset.assign-user',$user)}}" class="btn btn-primary btn-md edit-button">
            <i class="fas fa-plus"></i> {{__trans('assign_user')}}
        </a>
        @endcan
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card">
            <table class="table text-center table light">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{__trans('asset_brand')}}</th>
                        <th>{{__trans('asset_type')}}</th>
                        <th>{{__trans('asset_model')}}</th>
                        <th>{{__trans('asset_serial_number')}}</th>
                        <th>{{__trans('issue_date')}}</th>
                        <th>{{__trans('return_date')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($user->assignments()->with('asset','asset.type','asset.manufacturer')->orderByDesc('issue_date')->get() as $assignment)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$assignment->asset->manufacturer->name}}</td>
                        <td>{{$assignment->asset->type->name}}</td>
                        <td>{{$assignment->asset->model}}</td>
                        <td>{{$assignment->asset->unique_id}}</td>
                        <td>{{$assignment->issue_date}}</td>
                        <td>{{$assignment->return_date ?? 'N/A'}}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">{{__trans('no_asset_assigned')}}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
