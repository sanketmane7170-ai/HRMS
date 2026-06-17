<div>
    <div class="card h-100 shadow-premium">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-shape-premium bg-primary-soft me-3">
                    <i class="fas fa-user-plus text-primary"></i>
                </div>
                <h5 class="card-title mb-0">{{__trans('new_joiner')}}</h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stripped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{__trans('name')}}</th>
                            <th>{{__trans('department_name')}}</th>
                            <th>{{__trans('join_date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            use Carbon\Carbon;
                            $newJoiners = \App\Models\User::where('status', \App\Models\User::STATUS_ACTIVE)
                                ->with('workDetail')
                                ->whereHas('workDetail', function ($query) {
                                    $query->whereMonth('joining_date', Carbon::now()->month)
                                        ->whereYear('joining_date', Carbon::now()->year);
                                })
                                ->orderBy(function ($query) {
                                    $query->select('joining_date')
                                        ->from('user_work_details')
                                        ->whereColumn('users.id', 'user_work_details.user_id')
                                        ->limit(1);
                                })
                                ->get();
                        @endphp
                        @forelse ($newJoiners as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->department?->name ?? 'NA' ?? '-' }}</td>
                                <td>{{ formatDate($user->workDetail?->joining_date ?? null, 'birth_date_format') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">{{ __trans('no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
