<div>
    <div class="card h-100 shadow-premium">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">
                <i class="fas fa-umbrella-beach text-primary me-2"></i>
                {{__trans('users_on_leave')}}
            </h5>
            <div class="col-auto">
                @can('Manage Leave')
                <a href="{{route('backend.analytic.leave.list')}}" class="btn btn-sm btn-outline-primary">
                    {{__trans('view_all')}}
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stripped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{__trans('name')}}</th>
                            <th>{{__trans('department_name')}}</th>
                            <th>{{__trans('start_date')}}</th>
                            <th>{{__trans('end_date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @forelse ($leavelist as $leave)
                        <tr>
                            <td>{{$leave->user->name}}</td>
                            <td>{{$leave->user->department->name}}</td>
                            <td>{{formatDate($leave->start_date,'birth_date_format')}}</td>
                            <td>{{formatDate($leave->end_date,'birth_date_format')}}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">{{__trans('no_one_in_leave')}}</td>
                        </tr>
                        @endforelse
                </table>
            </div>
        </div>
    </div>
</div>
