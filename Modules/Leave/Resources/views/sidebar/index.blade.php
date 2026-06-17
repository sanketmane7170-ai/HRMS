@can('Manage Leave')
<li class="submenu">
    <a href="#">
        <i class="fa fa-folder-open" aria-hidden="true"></i>
        <span> {{__trans('leave_requests')}}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Leave Type')
        <li>
            <a class="@if($activeLink =='leave-types') active @endif" href="{{route('backend.leave-types.index')}}">{{__trans('leave_types')}}</a>
        </li>
        @endcan
        <li>
            <a class="@if($activeLink =='leaves') active @endif" href="{{route('backend.leaves.index')}}">{{__trans('leaves')}}</a>
        </li>
    </ul>
</li>
@endcan
