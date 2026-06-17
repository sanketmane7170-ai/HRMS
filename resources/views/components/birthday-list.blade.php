<div>
    <div class="card h-100 shadow-premium">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-shape-premium bg-danger-soft me-3">
                    <i class="fas fa-birthday-cake text-danger"></i>
                </div>
                <h5 class="card-title mb-0">{{__trans('birthdays_this_month')}}</h5>
            </div>
            <div class="col-auto">
                @if (isModuleEnabled('Analytic'))
                <a href="{{route('backend.analytic.birthday.list')}}" class="btn btn-sm btn-outline-primary">
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
                            <th>{{__trans('birthday_date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($birthdays as $user)
                        <tr>
                            <td>{{$user->name}}</td>
                            <td>{{formatDate($user->profile->date_of_birth,'birth_date_format')}}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">{{__trans('no_birthdays_this_month')}}</td>
                        </tr>
                        @endforelse
                </table>
            </div>
        </div>
    </div>
</div>
