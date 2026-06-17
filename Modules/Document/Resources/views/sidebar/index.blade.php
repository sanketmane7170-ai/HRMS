@if(hasPermission('Manage Document Type') || hasPermission('Manage Document Request'))
<li class="submenu">
    <a href="#">
        <i class="fa fa-list"></i>
        <span> {{__trans('hr_service_request')}}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Document Type')
        <li>
            <a class="@if($activeLink =='document-types') active @endif" href="{{route('backend.document-types.index')}}">{{__trans('document_types')}}</a>
        </li>
        @endcan
        @can('Manage Document Request')
        <li>
            <a class="@if($activeLink =='document-requests') active @endif" href="{{route('backend.document-requests.index')}}">{{__trans('document_requests')}}</a>
        </li>
        @endcan

    </ul>
</li>
@endif
