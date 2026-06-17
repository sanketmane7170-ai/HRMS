<div>
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <div class="icon-shape-premium bg-warning-soft me-3">
                <i class="fas fa-sign-out-alt text-warning"></i>
            </div>
            <h5 class="card-title mb-0">{{ \Illuminate\Support\Str::title(__trans('employee_serving_notice_period')) }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-stripped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{__trans('name')}}</th>
                            <th>{{__trans('last_working_day')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($noticePeriodList as $resignation)
                        <tr>
                            <td>{{ $resignation->employee->name }}</td>
                            <td>{{ $resignation->approved_last_working_date ? formatDate($resignation->approved_last_working_date, 'birth_date_format') : __trans('not_set') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center">{{__trans('no_data_found')}}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
