@if(!auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN) && !auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) && !auth()->user()->hasRole(\App\Models\User::ROLE_HR))
<li class="submenu">
    <a href="#">
    <a href="#">
        <i class="fas fa-user"></i>
        <span> {{__trans('people')}}</span>
    </a>
    <ul>
        <li>
            <a class="@if($activeLink =='profile') active @endif"
                href="{{route('backend.employee.profile')}}">{{__trans('my_details')}}</a>
        </li>
        @can('Manage User Document')
        <li>
            <a class="@if($activeLink =='documents') active @endif"
                href="{{route('backend.employee.documents.index')}}">{{__trans('my_documents')}}</a>
        </li>
        @endcan

        @if(isModuleEnabled('Asset'))
        <li>
            <a class="@if($activeLink =='assets') active @endif"
                href="{{route('backend.employee.assets.index')}}">{{__trans('my_assets')}}</a>
        </li>
        @endif
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
@can('Manage Dependent')
<li class="@if($activeLink =='dependents') active @endif">
    <a href="{{route('backend.employee.dependents.index')}}"> <i class="fas fa-users"></i>
        <span>{{__trans('my_dependents')}}</span></a>
</li>
@endcan
@if(isModuleEnabled('Leave'))
<li class="@if($activeLink =='leaves') active @endif">
    <a href="{{route('backend.employee.leaves.index')}}">
        <i class="fas fa-calendar"></i> <span>{{__trans('my_leaves')}}</span>
    </a>
</li>
@endif
@if(isModuleEnabled('Apparel'))
<li class="@if(request()->is('my-apparel')) active @endif">
    <a href="{{route('backend.employee.my-apparel')}}">
        <i class="fa fa-tshirt"></i> <span>{{__trans('my_apparels')}}</span>
    </a>
</li>
@endif

{{-- Consolidated Career Center for Employees - Sanket --}}
{{-- Hide for users who have management access to Recruitment to avoid duplication --}}
@if(isModuleEnabled('Recruitment') && !auth()->user()->can('Manage Recruitment'))
<li class="submenu {{ (request()->routeIs('career.*') || request()->routeIs('backend.employee.jobs.*') || request()->routeIs('backend.employee.applications.*') || in_array($activeLink, ['career', 'employee-jobs', 'employee-applications'])) ? 'active' : '' }}">
    <a href="#" class="{{ (request()->routeIs('career.*') || request()->routeIs('backend.employee.jobs.*') || request()->routeIs('backend.employee.applications.*')) ? 'subdrop' : '' }}">
        <i class="fas fa-user-graduate"></i>
        <span> {{__trans('career_center')}}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul style="display: {{ (request()->routeIs('career.*') || request()->routeIs('backend.employee.jobs.*') || request()->routeIs('backend.employee.applications.*')) ? 'block' : 'none' }};">
        <li>
            <a class="@if($activeLink =='career') active @endif"
                href="{{route('career.index')}}">{{__trans('internal_jobs')}}</a>
        </li>
        <li>
            <a class="@if(request()->routeIs('backend.employee.jobs.*') || $activeLink == 'employee-jobs') active @endif"
                href="{{route('backend.employee.jobs.index')}}">{{__trans('hot_jobs')}}</a>
        </li>
        <li>
            <a class="@if(request()->routeIs('backend.employee.applications.*') || $activeLink == 'employee-applications') active @endif"
                href="{{route('backend.employee.applications.index')}}">{{__trans('my_applications')}}</a>
        </li>
        <li>
            <a class="@if($activeLink == 'my-offers' || request()->routeIs('backend.employee.offers.*')) active @endif"
                href="{{route('backend.employee.offers.index')}}">{{__trans('my_offers')}}</a>
        </li>
    </ul>
</li>
@endif
@if(isModuleEnabled('Attendance'))
<li class="@if($activeLink =='attendances') active @endif">
    <a href="{{route('backend.employee.attendances.index')}}">
        <i class="fas fa-clock"></i> <span>{{__trans('my_timesheet')}}</span>
    </a>
</li>
@endif
@if(isModuleEnabled('Document'))
<li class="@if($activeLink =='document-requests') active @endif">
    <a href="{{route('backend.employee.document-requests.index')}}">
        <i class="fa fa-file"></i> <span>{{__trans('hR_service_request')}}</span>
    </a>
</li>
@endif
<li class="@if($activeLink =='training') active @endif">
    <a href="{{route('backend.employee.traininglist')}}">
        <i class="fas fa-video"></i> <span>{{__trans('training')}}</span>
    </a>
</li>

<li class="@if($activeLink == 'employee-performance') active @endif">
    <a href="{{route('backend.employee.performancelist')}}">
        <i class="fas fa-chart-line"></i> <span>{{__trans('employee_performance_appraisals')}}</span>
    </a>
</li>

@if(isModuleEnabled('Task'))
<li class="@if($activeLink =='Task') active @endif">
    <a href="{{route('backend.employee.task.my_task')}}">
        <i class="fas fa-calendar"></i> <span>{{__trans('my_tasks')}}</span>
    </a>
</li>
@endif
@if(!auth()->user()->hasRole(App\Models\User::ROLE_ADMIN))
<li class="@if($activeLink =='Task') active @endif">
    <a href="{{route('backend.employee.air-ticket.index')}}">
        <i class="fas fa-ticket-alt"></i> <span>{{__trans('my_air_ticket')}}</span>
    </a>
</li>
@endif
<li class="submenu">
    <a href="#">
        <i class="fas fa-chart-line" aria-hidden="true"></i>
        <span> {{__trans('performance_review')}}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        <li>
            <a class="@if ($activeLink == 'KPI Assignments') active @endif"
                href="{{ route('kpi.assignments.index') }}">
                {{ __trans('KPI Assignments') }}
            </a>
        </li>
        <li>
            <a class="@if($activeLink =='KPI Response') active @endif"
                href="{{route('backend.employee.kpiresponse')}}">{{__trans('kpi_response')}}</a>
        </li>
        <li>
            <a class="@if($activeLink =='Review Response') active @endif"
                href="{{route('backend.employee.reviewresponse')}}">{{__trans('review_response')}}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'reviewevaluations') active @endif"
                href="{{ route('evaluate.index') }}">{{ __trans('evaluate_reviews') }}</a>
        </li>
    </ul>
</li>
<li class="submenu">
    <a href="#">
        <i class="fas fa-money-check-alt"></i>
        <span> {{__trans('pay_roll')}}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        <li>
            <a class="@if($activeLink =='salaries') active @endif"
                href="{{route('backend.my-salary.getViewSalary')}}">{{__trans('salary')}}</a>
        </li>
        <li>
            <a class="@if($activeLink =='payslip') active @endif"
                href="{{route('backend.my-salary.getViewPayslip')}}">{{__trans('pay_slip')}}</a>
        </li>
        <li>
            <a class="@if($activeLink =='advance-request') active @endif"
                href="{{route('backend.payroll.advance-request.index')}}">{{__trans('advance_salary')}}</a>
        </li>
        @if(isModuleEnabled('Payroll'))
        @can('Set Salary and PayRoll Payroll')
        <li>
            <a class="@if($activeLink =='salaries') active @endif"
                href="{{route('backend.payroll.user-salaries.index')}}">{{__trans('set_salary')}}</a>
        </li>
        <li>
            <a class="@if($activeLink =='payslip') active @endif"
                href="{{route('backend.payslip.user-payslip.index')}}">{{__trans('run_payroll')}}</a>
        </li>

        @endcan
        @endif
    </ul>
</li>
@endif

@if(isModuleEnabled('IndianPayroll') && auth()->user()->indianPayrollProfile()->exists())
<li class="submenu">
    <a href="#">
        <i class="fas fa-rupee-sign"></i>
        <span> {{ __trans('payroll') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        <li>
            <a class="@if($activeLink =='my-indian-payroll.payslips') active @endif"
                href="{{ route('backend.my-indian-payroll.payslips.index') }}">{{ __trans('my_payslips') }}</a>
        </li>
        <li>
            <a class="@if($activeLink =='my-indian-payroll.tax-declaration') active @endif"
                href="{{ route('backend.my-indian-payroll.tax-declaration.index') }}">{{ __trans('my_tax_declaration') }}</a>
        </li>
    </ul>
</li>
@endif

