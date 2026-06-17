@if(hasPermission('Manage Asset Type') || hasPermission('Manage Asset Manufacturer') || hasPermission('Manage Asset'))
<li class="submenu">
    <a href="#">
        <i class="fa fa-list"></i>
        <span> {{__trans('asset_management')}}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        {{-- @can('Manage Asset Type')
        <li>
            <a class="@if($activeLink =='asset-types') active @endif" href="{{route('backend.asset-types.index')}}">{{__trans('asset_types')}}</a>
        </li>
        @endcan
        @can('Manage Asset Manufacturer')
        <li>
            <a class="@if($activeLink =='asset-manufacturers') active @endif" href="{{route('backend.asset-manufacturers.index')}}">{{__trans('asset_manufacturer')}}</a>
        </li>
        @endcan --}}
        @can('Manage Asset')
        <li>
            <a class="@if($activeLink =='assets') active @endif" href="{{route('backend.asset.index')}}">{{__trans('assets')}}</a>
        </li>
        @endcan

    </ul>
</li>
@endcan
