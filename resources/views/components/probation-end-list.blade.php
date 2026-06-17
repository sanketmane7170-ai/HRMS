<div>
    <div class="card h-100 shadow-premium">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-shape-premium bg-primary-soft me-3">
                    <i class="fas fa-user-clock text-primary"></i>
                </div>
                <h5 class="card-title mb-0">{{__trans('probation_ending_soon')}}</h5>
            </div>
            <div class="col-auto">
                @if (isModuleEnabled('Analytic'))
                <a href="{{route('backend.analytic.probation.upcoming.list')}}" class="btn btn-sm btn-outline-primary">
                    {{__trans('view_all')}}
                </a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stripped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{__trans('name')}}</th>
                            <th>{{__trans('probation_end_date')}}</th>
                            <th>{{__trans('confirm')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($probationEndList as $user)
                        <tr>
                            <td>{{$user->name}}</td>
                            <td>{{formatDate($user->workDetail->probation_end_date,'date_format')}}</td>
                            <td>
                                <a href="{{route('backend.analytic.probation.upcoming.list')}}" class="btn btn-sm btn-outline-primary">
                                    {{__trans('confirm')}}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">{{__trans('no_records_found')}}</td>
                        </tr>
                        @endforelse
                </table>
            </div>
        </div>
    </div>
</div>
