<div>
    <div class="user-checkin-button p-2">
        @if(!config('attendance.multi_checkins_allowed'))
        @if(!isUserCheckedIn(auth()->id()))
        <form action="{{route('backend.employee.checkin')}}" datatable class="ajax-form-submit" method="POST" html="{{$selector ?? '.user-checkin-checkout' }}">
            @csrf
            <button class="btn btn-success ">
                <i class="fas fa-user-clock"></i> {{__trans('clock_in')}}
            </button>
        </form>
        @elseif(!isUserCheckedIn(auth()->id(),\Modules\Attendance\Enums\CheckinType::OUT))
        <a href="{{route('backend.employee.checkin')}}" class="btn btn-danger action-button" method="POST" datatable data-alert="{{__trans('are_you_sure_want_to_clock_out?')}}" html=.user-checkin-button>
            <i class="fas fa-user-clock"></i> {{__trans('clock_out')}}
        </a>
        @else
        <p>
            <span class="badge badge-danger ">{{__trans('you_have_clocked_out_for_today_successfully')}}</span>
        </p>
        @endif
        @else
        <?php
        $hasLogOut =  isUserCheckedIn(auth()->id(), \Modules\Attendance\Enums\CheckinType::OUT)
        ?>
        <form action="{{route('backend.employee.checkin')}}" datatable class="ajax-form-submit" method="POST" html="{{$selector ?? '.user-checkin-checkout' }}">
            @csrf
            <button class="btn btn-{{$hasLogOut ?'success':'danger'}} ">
                <i class="fas fa-user-clock"></i> {{ $hasLogOut ? __trans('clock_in') : __trans('clock_out') }}
            </button>
        </form>
        @endif
    </div>
</div>
