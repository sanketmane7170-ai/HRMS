<div>
    <div class="card h-100 shadow-premium">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-shape-premium bg-success-soft me-3">
                    <i class="fas fa-award text-success"></i>
                </div>
                <h5 class="card-title mb-0">{{__trans('upcoming_work_anniversary')}}</h5>
            </div>
            <div class="col-auto">
                @if (isModuleEnabled('Analytic'))
                <a href="{{route('backend.analytic.anniversary.list')}}" class="btn btn-sm btn-outline-primary">
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
                            <th>{{__trans('anniversary_date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($anniversaries as $user)
                        <tr>
                            <td>{{$user->name}}</td>
                            <td>{{formatDate($user->workDetail?->joining_date,'birth_date_format')}}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">{{__trans('no_data_found')}}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
