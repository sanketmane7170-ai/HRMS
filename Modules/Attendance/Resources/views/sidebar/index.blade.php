@if(hasPermission('Manage Attendance') || hasPermission('Manage Holiday'))
<li class="submenu">
    <a href="#">
        <i class="fas fa-calendar-alt"></i>
        <span> {{__trans('attendances')}}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Holiday')
        <li>
            <a class="@if($activeLink =='holidays') active @endif" href="{{route('backend.holidays.index')}}">{{__trans('holidays')}}</a>
        </li>
        @endcan
        @can('Manage Attendance')
        <li>
            <a class="@if($activeLink =='marked-attendances') active @endif" href="{{route('backend.attendances.index')}}">{{__trans('marked_attendance')}}</a>
        </li>
        @endcan
    </ul>
</li>
@endcan
