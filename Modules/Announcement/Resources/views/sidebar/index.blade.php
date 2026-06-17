@if(hasPermission('Manage Announcement') || hasPermission('Manage Announcement Type'))
<li class="submenu">
    <a href="#">
        <i class="fa fa-bullhorn"></i>
        <span> {{__trans('announcements')}}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Announcement Type')
        <li class="d-none">
            <a class="@if($activeLink =='announcement-types') active @endif" href="{{route('backend.announcement-types.index')}}">{{__trans('announcement_types')}}</a>
        </li>
        @endcan
        @can('Manage Announcement')
        <li>
            <a class="@if($activeLink =='announcements') active @endif" href="{{route('backend.announcements.index')}}">{{__trans('announcements')}}</a>
        </li>
        @endcan

    </ul>
</li>
@endcan
