<!-- Sidebar -->

<div class="sidebar  " id="sidebar">
    <div class="sidebar-inner slimscroll">

        <!-- Logo -->
        <div class=" header-left ">
            <a href="{{route('backend.dashboard')}}" class="logo">
                <img src="{{getSmallLogo()}}" alt="Logo">
            </a>
        </div>
        <!-- /Logo -->

        <div id="sidebar-menu" class="sidebar-menu ">
            <ul>
                <li class="{{ ($activeLink ?? '') == 'dashboard' ? 'active':''}}">
                    <a href="{{route('backend.dashboard')}}"><i class="fas fa-home"></i>
                        <span>{{__trans('dashboard')}}</span></a>
                </li>

                @unless(auth()->user()->hasRole(\App\Models\User::ROLE_PAYROLL_MANAGER))
                <li class="{{ ($activeLink ?? '') == 'live-board' ? 'active' : '' }}">
                    <a href="{{ route('backend.work-status.live-board') }}">
                        <i class="fas fa-satellite-dish pulse-icon"></i>
                        <span>Live Presence Board</span>
                    </a>
                </li>

                {{-- Sanket v2.0 - AgenticAI sidebar link integrated from Gastronaut branch --}}
                <li class="{{ $activeLink == 'agentic-ai' ? 'active':''}}">
                    <a href="/agenticai">
                        <i class="fas fa-microchip"></i>
                        <span>WorkPilot AI</span>
                    </a>
                </li>
                @endunless

                @unless(auth()->user()->hasRole(\App\Models\User::ROLE_PAYROLL_MANAGER))
                <li class="{{ $activeLink == 'features' ? 'active':''}}">
                    <a href="{{route('backend.features.index')}}"><i class="fas fa-bullhorn"></i>
                        <span>{{__trans('features')}}</span></a>
                </li>
                @endunless
                @include('layouts.partials.backend.employee-sidebar')
                @can('Manage User')
                <li class="submenu">
                    <a href="#">
                        <i class="fa fa-users"></i>
                        <span> {{__trans('peoples')}}</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        <li>
                            <a class="@if($activeLink =='users') active @endif"
                                href="{{route('backend.users.index')}}">{{__trans('peoples')}}</a>
                        </li>
                        @can('Manage Role')
                        <li>
                            <a class="@if($activeLink =='roles') active @endif"
                                href="{{route('backend.roles.index')}}">{{__trans('roles')}}</a>
                        </li>
                        @endcan
                         @can('Teams User')
                        <li>
                            <a class="@if($activeLink =='teams') active @endif"
                                href="{{route('backend.teams')}}">{{__trans('teams')}}</a>
                        </li>
                         @endcan
                         @can('Hierarchy User')
                        <li>
                            <a class="@if($activeLink =='hierarchy') active @endif"
                                href="{{route('backend.employee.hierarchy')}}">{{__trans('hierarchy')}}</a>
                        </li>
                         @endcan
                         @can('Hierarchy1 User')
                        <li>
                            <a class="@if($activeLink =='hierarchy1') active @endif"
                                href="{{route('backend.employee.hierarchy1')}}">{{__trans('hierarchy1')}}</a>
                        </li>
                         @endcan

                    </ul>
                </li>
                @endcan
                @if(hasPermission('Manage Department') || hasPermission('Manage Designation'))
                <li class="submenu">
                    <a href="#">
                        <i class="fa fa-archive" aria-hidden="true"></i>
                        <span> {{__trans('branch_settings')}}</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        @can('Manage Department')
                        <li>
                            <a class="@if($activeLink =='branches') active @endif" href="{{route('backend.departments.index')}}">{{__trans('branch')}}</a>
                        </li>
                        @endcan
                        @can('Manage Division')
                        <li>
                            <a class="@if($activeLink =='department') active @endif" href="{{route('backend.divisions.index')}}">{{__trans('department')}}</a>
                        </li>
                        @endcan
                        @can('Manage Designation')
                        <li>
                            <a class="@if($activeLink =='designations') active @endif"
                                href="{{route('backend.designations.index')}}">{{__trans('designation')}}</a>
                        </li>
                        @endcan
                        @can('Manage Leave')
                        <li>
                            <a class="@if($activeLink =='leave_approval') active @endif"
                                href="{{route('backend.leave-approval.index')}}">{{__trans('leave_approval')}}</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @include('layouts.partials.backend.module-sidebar')

                @if(auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) || auth()->user()->can('Manage International Payroll'))
                <li class="submenu">
                    <a href="#">
                        <i class="fas fa-money-check-alt"></i>
                        <span> {{__trans('international_payroll')}}</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        <li>
                            <a class="@if(request()->segment(2) == 'set-allowance-deduction') active @endif"
                                href="{{route('backend.payroll.user.allowance_deduction')}}">{{__trans('set_allowance_&_deduction')}}</a>
                        </li>
                        <li>
                            <a class="@if(request()->segment(2) == 'user-salaries') active @endif"
                                href="{{route('backend.payroll.user-salaries.index')}}">{{__trans('set_salary')}}</a>
                        </li>
                        <li>
                            <a class="@if($activeLink =='advance-request') active @endif"
                                href="{{route('backend.payroll.advance-request.index')}}">{{__trans('advance_salary')}}</a>
                        </li>
                        @can('Manage Role')
                        <li>
                            <a class="@if($activeLink =='payslip') active @endif"
                                href="{{route('backend.payslip.user-payslip.index')}}">{{__trans('run_payroll')}}</a>
                        </li>
                        @endcan
                        <li>
                            <a class="@if($activeLink =='transaction') active @endif"
                                href="{{route('backend.settlement.transaction')}}">{{__trans('transaction')}}</a>
                        </li>
                    </ul>
                </li>
                @endif
                @if(hasPermission('General Settings') || hasPermission('Smtp Settings') || hasPermission('Advance
                Settings'))
                <li class="submenu">
                    <a href="#">
                        <i class="fas fa-cogs"></i>
                        <span> {{__trans('settings')}}</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        @can('General Settings')
                        <li>
                            <a class="@if($activeLink =='setting-general') active @endif"
                                href="{{route('backend.settings.general')}}">{{__trans('general_settings')}}</a>
                        </li>
                        @endcan
                        @if(request()->getHost() === config('domain.specific_domain'))
                        @can('Smtp Settings')
                        <li>
                            <a class="@if($activeLink =='setting-smtp') active @endif"
                                href="{{route('backend.settings.smtp')}}">{{__trans('smpt_settings')}}</a>
                        </li>
                        @endcan
                        @endif
                        @if(request()->getHost() === config('domain.specific_domain'))
                        <li>
                            <a class="@if($activeLink =='portal-management') active @endif"
                                href="{{route('backend.settings.portals.info')}}">{{__trans('portal_management')}}</a>
                        </li>
                        @endif
                        <li>
                            <a class="@if($activeLink =='setting-notifications') active @endif"
                                href="{{route('backend.notification.manager.index')}}">{{__trans('notification_settings')}}</a>
                        </li>

                        <li>
                            <a class="@if($activeLink =='airticketsetting') active @endif"
                                href="{{route('backend.settings.air-ticket-setting.index')}}">{{__trans('air_ticket_settings')}}</a>
                        </li>

                        <li>
                            <a class="@if($activeLink =='policysetting') active @endif"
                                href="{{route('backend.settings.policysetting.index')}}">{{__trans('policy_settings')}}</a>
                        </li>
                    </ul>
                </li>
                @endcan
                @if (auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) || auth()->user()->can('Manage Company Policy'))
                    <li class="submenu">
                        <a href="#">
                            <i class="fas fa-shield-alt"></i>
                            <span> {{__trans('company_policy')}}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="@if($activeLink =='Company-Policy') active @endif"
                                    href="{{route('backend.getCompanyPolicy')}}">{{__trans('company_policy')}}</a>
                            </li>
                            <li>
                                <a class="@if($activeLink =='User-Company-Policy') active @endif"
                                    href="{{route('backend.userCompanyPolicy')}}">{{__trans('user_company_policy')}}</a>
                            </li>

                        </ul>
                    </li>
                @endif
                @if (!auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) && !auth()->user()->can('Manage Company Policy'))
                <li class="submenu">
                    <a href="#">
                        <i class="fas fa-shield-alt"></i>
                        <span> {{__trans('company_policy')}}</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        <li>
                            <a class="@if($activeLink =='Company-Policy') active @endif"
                                href="{{route('backend.employee.showCompanyPolicy')}}">{{__trans('company_policy')}}</a>
                        </li>
                        
                    </ul>
                </li>
                @endif
                <li class="{{ $activeLink == 'reports' ? 'active':''}}">
                    <a href="{{route('backend.reports')}}"><i class="fas fa-list"></i>
                        <span>{{__trans('reports')}}</span></a>
                </li>


            </ul>


        </div>
    </div>
</div>
<!-- /Sidebar -->
