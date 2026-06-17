<div class="row align-items-center mb-3">
    <div class="col"></div>
    <div class="col-auto">
        {{-- Add button if needed later --}}
        {{-- <a href="{{ route('backend.user-promotion-letters.create', $user) }}" class="btn btn-primary btn-md">
        <i class="fas fa-plus"></i> {{ __trans('add_service_history') }}
        </a> --}}
    </div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card">
            <table class="table text-center table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __trans('old_position') }}</th>
                        <th>{{ __trans('new_position') }}</th>
                        <th>{{ __trans('old_salary') }}</th>
                        <th>{{ __trans('new_salary') }}</th>
                        <th>{{ __trans('department') }}</th>
                        <th>{{ __trans('Reason') }}</th>
                        <th>{{ __trans('effective_date') }}</th>
                        <th>{{ __trans('position_length') }}</th>
                        <th>{{ __trans('remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    use App\Models\UserPromotionLetter;
                    use Carbon\Carbon;
                    $joiningDate = $user->workDetail?->joining_date;
                    $serviceHistory = UserPromotionLetter::where('user_id', $user->id)
                    ->with(['type', 'oldPosition', 'newPosition', 'oldDepartment', 'newDepartment'])
                    ->orderBy('date', 'asc') // important for service calculation
                    ->get();

                    $previousDate = $joiningDate ? Carbon::parse($joiningDate) : null;
                    @endphp


                    @php
                    if ($joiningDate) {
                    $startDate = Carbon::parse($joiningDate);
                    $endDate = Carbon::now();

                    $diff = $startDate->diff($endDate);

                    $totalYears = $diff->y;
                    $totalMonths = $diff->m;
                    $totalDays = $diff->d;

                    $totalService = "{$totalYears}y {$totalMonths}m {$totalDays}d";
                    $totalDaysAll = $startDate->diffInDays($endDate);
                    } else {
                    $totalService = 'N/A';
                    $totalDaysAll = 'N/A';
                    }
                    @endphp


                    @forelse ($serviceHistory as $index => $letter)
                    @php
                    $currentDate = $letter->date ? Carbon::parse($letter->date) : null;

                    if ($currentDate && $previousDate) {
                    $years = $previousDate->diffInYears($currentDate);
                    $months = $previousDate->copy()->addYears($years)->diffInMonths($currentDate);
                    $serviceLength = "{$years}y {$months}m";
                    } else {
                    $serviceLength = 'N/A';
                    }

                    $previousDate = $currentDate;
                    @endphp

                    @if ($currentDate && $previousDate)
                    @php
                     $previousDate = $letter->date ? Carbon::parse($letter->date) : null;
                      $currentDate = Carbon::now();
                    $diff = $previousDate->diff($currentDate);

                    $years = $diff->y;
                    $months = $diff->m;
                    $days = $diff->d;

                    $serviceLength = "{$years}y {$months}m";

                    // Add days only if greater than 0
                    if ($days > 0) {
                    $serviceLength .= " {$days}d";
                    }
                    $serviceLength .= " {$days}d";

                    @endphp
                    @else
                    @php
                    $serviceLength = 'N/A';
                    @endphp
                    @endif

                    <tr style="color:white;">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $letter->oldPosition->name ?? 'N/A' }}</td>
                        <td>{{ $letter->newPosition->name ?? 'N/A' }}</td>
                        <td>{{ $letter->old_salary ?? 'N/A' }}</td>
                        <td>{{ $letter->user_basic_salary ?? 'N/A' }}</td>
                        <td>{{ $letter->newDepartment->name ?? 'N/A' }}</td>
                        <td>{{ $letter->reason ?? 'N/A' }}</td>
                        <td>{{ $letter->date ? date('d M Y', strtotime($letter->date)) : 'N/A' }}</td>
                        <td>{{ $serviceLength }}</td>
                        <td>{{ $letter->remarks ?? 'N/A' }}</td>

                    </tr>
                  
                    <!-- <tr>
                        <td colspan="10" class="text-center font-weight-bold">
                            {{ __trans('total_service_length') }} - {{ $totalService }}
                        </td>

                    </tr> -->
                    <tr>
                        <td colspan="10" class="text-center font-weight-bold">
                            {{ __trans('total_service_length') }} -
                            {{ $totalService }}
                            ({{ $totalDaysAll }} Days)
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="5">{{ __trans('no_records_found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
