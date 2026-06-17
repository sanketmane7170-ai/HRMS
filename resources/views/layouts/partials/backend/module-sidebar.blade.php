{{-- Asset Module Routes Starts --}}
@if (isModuleEnabled('Asset'))
@if (hasPermission('Manage Asset Type') || hasPermission('Manage Asset Manufacturer') || hasPermission('Manage Asset'))
<li class="submenu">
    <a href="#">
        <i class="fas fa-list"></i>
        <span> {{ __trans('asset_management') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>

@can('Manage Asset')
<li>
    <a class="@if ($activeLink == 'assets') active @endif"
        href="{{ route('backend.asset.index') }}">{{ __trans('assets') }}</a>
</li>
@endcan

</ul>
</li>
@endcan
@endif
{{-- Asset Module Routes Ends --}}

{{-- Announcement Module Routes Starts --}}
@if (isModuleEnabled('Announcement'))
@if (hasPermission('Manage Announcement') || hasPermission('Manage Announcement Type'))
<li class="submenu">
    <a href="#">
        <i class="fas fa-bullhorn"></i>
        <span> {{ __trans('announcements') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        <li>
            <a class="@if ($activeLink == 'announcement-types') active @endif"
                href="{{ route('backend.announcement-types.index') }}">{{ __trans('announcement_types') }}</a>
        </li>

        <li>
            <a class="@if ($activeLink == 'announcements') active @endif"
                href="{{ route('backend.announcements.index') }}">{{ __trans('announcements') }}</a>
        </li>

    </ul>
</li>
@endcan
@endif
{{-- Announcement Module Routes Ends --}}

{{-- Warning Module Routes Starts --}}
@if (isModuleEnabled('FileManager'))
@if (hasPermission('Manage FileManager'))
<li class="{{ $activeLink == 'filemanager' ? 'active' : '' }}">
    <a href="/filemanager">
        <i class="fas fa-folder"></i>
        <span>{{ __trans('File Management') }}</span></a>
</li>
@endif
@endif
{{-- Warning Module Routes Ends --}}

{{-- Leave Module Routes Starts --}}
@if (isModuleEnabled('Leave'))
@can('Manage Leave')
<li class="submenu">
    <a href="#">
        <i class="fas fa-folder-open" aria-hidden="true"></i>
        <span> {{ __trans('leave_requests') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Leave Type')
        <li>
            <a class="@if ($activeLink == 'leave-types') active @endif"
                href="{{ route('backend.leave-types.index') }}">{{ __trans('leave_types') }}</a>
        </li>
        @endcan
        @can('Manage Leave')
        <li>
            <a class="@if ($activeLink == 'leaves') active @endif"
                href="{{ route('backend.leaves.index') }}">{{ __trans('leaves') }}</a>
        </li>
        @endcan
        @can('View Report Leave')
        <li>
            <a class="@if ($activeLink == 'leaves-report') active @endif"
                href="{{ route('backend.leaves.report') }}">{{ __trans('leaves_report') }}</a>
        </li>
        @endcan
        @can('Planner Leave')
        <li>
            <a class="@if ($activeLink == 'leaves-planner') active @endif"
                href="{{ route('backend.leaves.calender') }}">{{ __trans('leaves_planner') }}</a>
        </li>
        @endcan
        @can('Previous Year Report Leave')
        <li>
            <a class="@if ($activeLink == 'previousyear-leaves-report') active @endif"
                href="{{ route('backend.previousyear.leaves.report') }}">{{ __trans('previous_year_leaves_report') }}</a>
        </li>
        @endcan
    </ul>
</li>
@endcan
@endif
{{-- Leave Module Routes Ends --}}

{{-- Uniform Module Routes Starts --}}
@if (isModuleEnabled('Apparel'))
@can('Manage Apparel')
<li class="@if (request()->is('general-request')) active @endif">
    <a href="{{ route('backend.general_request') }}">
        <i class="fas fa-id-card" aria-hidden="true"></i>
        <span data-translate="true">General Request</span>
    </a>

</li>
@endcan
@endif
{{-- Air ticket Routes Starts --}}
@if(auth()->user()->hasRole(App\Models\User::ROLE_ADMIN) || auth()->user()->hasRole(App\Models\User::ROLE_SUPER_ADMIN))
<li class="@if (request()->is('get-air-ticket-request')) active @endif">
    <a href="{{route('backend.air-ticket.request')}}">
        <i class="fas fa-ticket-alt" aria-hidden="true"></i>
        <span data-translate="true">Air Ticket Request</span>
    </a>
</li>
@endif

{{-- Attendance Module Routes Starts --}}
@if (isModuleEnabled('Attendance'))
@if (hasPermission('Manage Attendance') || hasPermission('Manage Holiday'))
<li class="submenu">
    <a href="#">
        <i class="fas fa-calendar-alt"></i>
        <span> {{ __trans('attendances') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Holiday')
        <li>
            <a class="@if ($activeLink == 'holidays') active @endif"
                href="{{ route('backend.holidays.index') }}">{{ __trans('holidays') }}</a>
        </li>
        @endcan
        @can('Manage Attendance')
        <li>
            <a class="@if ($activeLink == 'marked-attendances') active @endif"
                href="{{ route('backend.attendances.index') }}">{{ __trans('marked_attendance') }}</a>
        </li>
        @endcan
        <li>
            <a class="@if ($activeLink == 'extra-work') active @endif"
                href="{{ route('backend.extra.show') }}">{{ __trans('extra_hours') }}</a>
        </li>
        @if(request()->getHost() === config('domain.specific_domain'))
        <li>
            <a class="@if ($activeLink == 'late-come') active @endif"
                href="{{ route('backend.late_come.show') }}">{{ __trans('Late Arrival Report') }}</a>
        </li>
        @endif
    </ul>
</li>
@endcan
@endif
{{-- Attendance Module Routes Ends --}}

{{-- Shift Scheduling Module Routes Starts --}}
@if (isModuleEnabled('Shift'))
@if (hasPermission('Manage Shift Shift') || hasPermission('Manage Scheduling Shift'))
<li class="submenu custom-css">
    <a href="#">
        <i class="fas fa-business-time"></i>
        <span> {{ __trans('shift_scheduler') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Shift Shift')
        <li>
            <a class="@if ($activeLink == 'create-shift') active @endif"
                href="{{ route('backend.shift.index') }}">{{ __trans('create_shift') }}</a>
        </li>
        @endcan
        @can('Manage Scheduling Shift')
        <li>
            <a class="@if ($activeLink == 'assign-shift') active @endif"
                href="{{ route('backend.assign_shift.index') }}">{{ __trans('assign_shift') }}</a>
        </li>
        @endcan
        @can('Manage Roster Roster')
        <li>
            <a class="@if ($activeLink == 'shift-roster') active @endif"
                href="{{ route('backend.shift.roster') }}">{{ __trans('shift_roster') }}</a>
        </li>
        @endcan
    </ul>
</li>
@endcan
@endif
{{-- Attendance Module Routes Ends --}}

{{-- Document Module Routes Starts --}}
@if (isModuleEnabled('Document'))
@if (hasPermission('Manage Document Type') || hasPermission('Manage Document Request'))
<li class="submenu">
    <a href="#">
        <i class="fas fa-list"></i>
        <span> {{ __trans('hr_service_request') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Document Type')
        <li>
            <a class="@if ($activeLink == 'document-types') active @endif"
                href="{{ route('backend.document-types.index') }}">{{ __trans('document_types') }}</a>
        </li>
        @endcan
        @can('Manage Document Request')
        <li>
            <a class="@if ($activeLink == 'document-requests') active @endif"
                href="{{ route('backend.document-requests.index') }}">{{ __trans('document_requests') }}</a>
        </li>
        @endcan

    </ul>
</li>
@endif
@endif
{{-- Document Module Routes Ends --}}

{{-- Warning Module Routes Starts --}}
@if (isModuleEnabled('Warning'))
@if (hasPermission('Manage Warning'))
<li class="{{ $activeLink == 'user-warnings' || $activeLink == 'user-appreciation' ? 'active' : '' }}">
    <a href="{{ route('backend.showReviews') }}">
        <i class="fas fa-comment-dots"></i>
        <span>{{ __trans('performance_reviews') }}</span></a>
</li>
@endif
@endif
{{-- Warning Module Routes Ends --}}


{{-- Expense Module Routes Starts --}}
@if (isModuleEnabled('Expense'))
@can('Manage Expense')
<li class="submenu">
    <a href="#">
        <i class="fas fa-wallet"></i>
        <span> {{ __trans('expense') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        @can('Manage Expense Type')
        <li>
            <a class="@if ($activeLink == 'expense-types') active @endif"
                href="{{ route('backend.expense-types.index') }}">{{ __trans('expense_types') }}</a>
        </li>
        @endcan

        <li>
            <a class="@if ($activeLink == 'expense') active @endif"
                href="{{ route('backend.expense.index') }}">{{ __trans('expense') }}</a>
        </li>

    </ul>
</li>
@endcan
@endif


{{-- Expense Module Routes Ends --}}




{{-- Company Document Module Routes Starts --}}
@if (isModuleEnabled('CompanyDocument'))
@can('Manage Company Document')
<li class="submenu">
    <a href="#">
        <i class="fas fa-wallet"></i>
        <span> {{ __trans('company_document') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>

        <li>
            <a class="@if ($activeLink == 'companydocument') active @endif"
                href="{{ route('backend.companydocument.index') }}">{{ __trans('company_document') }}</a>
        </li>

    </ul>
</li>
@endcan
@endif


{{-- Company Document Module Routes Ends --}}


{{-- Training Module Routes Starts --}}
@if (isModuleEnabled('Training'))
@can('Manage Training')
<li class="submenu">
    <a href="#">
        <i class="fas fa-video"></i>
        <span> {{ __trans('training') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        <li>
            <a class="@if ($activeLink == 'training') active @endif"
                href="{{ route('backend.training.index') }}">{{ __trans('training') }}</a>
        </li>

    </ul>
</li>
@endcan
@endif


{{-- Training Module Routes Ends --}}


{{-- Performance Module Routes Starts --}}
@if (isModuleEnabled('Performance'))
@can('Manage Performance')
<li class="submenu">
    <a href="#">
        <i class="fas fa-chart-line"></i> {{-- Use any FontAwesome icon --}}
        <span> {{ __trans('performance_management') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>
        <li>
            <a class="@if ($activeLink == 'appraisal_template') active @endif"
                href="{{ route('performance.template.index') }}">
                {{ __trans('appraisal_templates') }}
            </a>
        </li>

        <li>
            <a class="@if ($activeLink == 'performance') active @endif"
                href="{{ route('performance.index') }}">{{ __trans('performance_appraisals') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'performance') active @endif"
                href="{{ route('performance.monthly.report') }}">{{ __trans('performance_report') }}</a>
        </li>
    </ul>
</li>
@endcan
@endif
{{-- Performance Module Routes Ends --}}

{{-- Performance Review Module Routes Starts --}}
@if (isModuleEnabled('PerformanceReview'))
@can('Manage Performance Review')
<li class="submenu">
    <a href="#">
        <i class="fas fa-clipboard-check"></i>
        <span>{{ __trans('performance_review') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>


        <li>
            <a class="@if ($activeLink == 'review.duration') active @endif"
                href="{{ route('reviewduration.index') }}">{{ __trans('review_durations') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'question.set') active @endif"
                href="{{ route('questionset.index') }}">{{ __trans('question_sets') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'questions') active @endif"
                href="{{ route('question.index') }}">{{ __trans('questions') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'score.criteria') active @endif"
                href="{{ route('scorecriterion.index') }}">{{ __trans('score_criteria') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'increment-criteria') active @endif"
                href="{{ route('incrementcriteria.index') }}">{{ __trans('increment_criteria') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'kpi_score_levels') active @endif"
                href="{{ route('kpi.scorelevels.index') }}">
                {{ __trans('kpi_score_levels') }}
            </a>
        </li>

        <li>
            <a class="@if ($activeLink == 'Key Performance Indicator') active @endif"
                href="{{ route('kpi.index') }}">{{ __trans('KPI') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'KPI Assignments') active @endif" href="{{ route('kpi.assignments.index') }}">
                {{ __trans('KPI Assignments') }}
            </a>
        </li>


        <li>
            <a class="@if ($activeLink == 'performance-reviews') active @endif"
                href="{{ route('performancereview.index') }}">{{ __trans('review_list') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'reviewevaluations') active @endif"
                href="{{ route('evaluate.index') }}">{{ __trans('evaluate_reviews') }}</a>
        </li>

    </ul>
</li>
@endcan
@endif
{{-- Performance Review Module Routes Ends --}}

{{-- Task Module Routes Starts --}}

@if (isModuleEnabled('Task'))
@can('Manage Task')
<li class="submenu">
    <a href="#">
        <i class="fas fa-wallet"></i>
        <span> {{ __trans('Task Management') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul>

        @can('Manage Task')
        <li>
            <a class="@if ($activeLink == 'Task') active @endif"
                href="{{ route('backend.task.index') }}">{{ __trans('task_list') }}</a>
        </li>
        @endcan

    </ul>
</li>
@endcan
@endif
{{-- Task Module Routes Ends --}}

{{-- Recruitment Module Routes Starts --}}
@if (isModuleEnabled('Recruitment'))
@can('Manage Recruitment')
<li class="submenu {{ str_starts_with($activeLink, 'recruitment-') ? 'active' : '' }}">
    <a href="#" class="{{ str_starts_with($activeLink, 'recruitment-') ? 'active' : '' }}">
        <i class="fas fa-users"></i>
        <span> {{ __trans('recruitment') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul style="{{ str_starts_with($activeLink, 'recruitment-') ? 'display: block;' : '' }}">
        @can('Manage Recruitment')
        <li>
            <a class="@if ($activeLink == 'recruitment-dashboard') active @endif"
                href="{{ route('recruitment.dashboard') }}">{{ __trans('recruitment_dashboard') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'recruitment-jobs') active @endif"
                href="{{ route('recruitment.jobs.index') }}">{{ __trans('job_management') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'recruitment-applications') active @endif"
                href="{{ route('recruitment.applications.index') }}">{{ __trans('applications') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'recruitment-interviews') active @endif"
                href="{{ route('recruitment.interviews.index') }}">{{ __trans('interviews') }}</a>
        </li>
        <li>
            <a class="@if ($activeLink == 'recruitment-offers') active @endif"
                href="{{ route('recruitment.offers.index') }}">{{ __trans('offers') }}</a>
        </li>
        @endcan
    </ul>
</li>
@endcan
@endif
{{-- Recruitment Module Routes Ends --}}

{{-- Onboarding Module Routes Starts --}}
@if (isModuleEnabled('Onboarding'))
<li class="submenu {{ str_starts_with($activeLink, 'onboarding-') || $activeLink == 'probation-reviews' ? 'active' : '' }}">
    <a href="#" class="{{ str_starts_with($activeLink, 'onboarding-') || $activeLink == 'probation-reviews' ? 'active' : '' }}">
        <i class="fas fa-user-check"></i>
        <span> {{ __trans('Onboarding') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul style="{{ str_starts_with($activeLink, 'onboarding-') || $activeLink == 'probation-reviews' ? 'display: block;' : '' }}">
        <li>
            <a class="{{ $activeLink == 'onboarding-dashboard' ? 'active' : '' }}" 
               href="{{ route('onboarding.dashboard') }}">{{ __trans('Dashboard') }}</a>
        </li>
        <li>
            <a class="{{ $activeLink == 'onboarding-new-hires' ? 'active' : '' }}" 
               href="{{ route('onboarding.new-hires') }}">{{ __trans('New Hires') }}</a>
        </li>
        <li>
            <a class="{{ $activeLink == 'onboarding-tracker' ? 'active' : '' }}" 
               href="{{ route('onboarding.tracker.index') }}">{{ __trans('Workflow Tracker') }}</a>
        </li>
        <li>
            <a class="{{ $activeLink == 'probation-reviews' ? 'active' : '' }}" 
               href="{{ route('onboarding.probation.index') }}">{{ __trans('Probation Reviews') }}</a>
        </li>
    </ul>
</li>
@endif
{{-- Onboarding Module Routes Ends --}}

{{-- Resignation Module Routes Starts --}}
@if (isModuleEnabled('Resignation'))
<li class="submenu {{ str_starts_with($activeLink, 'resignation.') ? 'active' : '' }}">
    <a href="#" class="{{ str_starts_with($activeLink, 'resignation.') ? 'active' : '' }}">
        <i class="fas fa-sign-out-alt"></i>
        <span> {{ __trans('resignation') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul style="{{ str_starts_with($activeLink, 'resignation.') ? 'display: block;' : '' }}">
        @if(auth()->user()->hasRole(['admin', 'Admin', 'hr', 'HR', 'CEO', 'Super Admin', 'superadmin']))
        <li>
            <a class="@if ($activeLink == 'resignation.admin') active @endif"
                href="{{ route('resignation.admin') }}">{{ __trans('Admin Control') }}</a>
        </li>
        @endif

        @if(auth()->user()->hasRole(['admin', 'Admin', 'hr', 'HR', 'Manager', 'manager', 'CEO', 'Director', 'Super Admin', 'superadmin']))
        <li>
            <a class="@if ($activeLink == 'resignation.manager') active @endif"
                href="{{ route('resignation.manager') }}">{{ __trans('Manage Resignations') }}</a>
        </li>
        @endif

        @if(!auth()->user()->hasRole(['admin', 'Admin', 'Super Admin', 'superadmin']))
        <li>
            <a class="@if ($activeLink == 'resignation.employee') active @endif"
                href="{{ route('resignation.employee') }}">{{ __trans('Resignation Request') }}</a>
        </li>
        @endif
        {{-- @endrole --}}
    </ul>
</li>
@endif
{{-- Resignation Module Routes Ends --}}

{{-- IndianPayroll Module Routes Starts --}}
@if (isModuleEnabled('IndianPayroll') && auth()->user()->can('Manage Indian Payroll'))
<li class="submenu {{ str_starts_with($activeLink, 'indian-payroll') ? 'active' : '' }}">
    <a href="#" class="{{ str_starts_with($activeLink, 'indian-payroll') ? 'active' : '' }}">
        <i class="fas fa-rupee-sign"></i>
        <span> {{ __trans('payroll') }}</span>
        <span class="menu-arrow"></span>
    </a>
    <ul style="{{ str_starts_with($activeLink, 'indian-payroll') ? 'display: block;' : '' }}">
        <li><a class="@if ($activeLink == 'indian-payroll') active @endif" href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('dashboard') }}</a></li>
        <li><a class="@if ($activeLink == 'indian-payroll.employee-profiles') active @endif" href="{{ route('backend.indian-payroll.employee-profiles.index') }}">{{ __trans('employee_statutory_profiles') }}</a></li>
        <li><a class="@if ($activeLink == 'indian-payroll.salary-components') active @endif" href="{{ route('backend.indian-payroll.salary-components.index') }}">{{ __trans('salary_components') }}</a></li>
        <li><a class="@if ($activeLink == 'indian-payroll.salary-templates') active @endif" href="{{ route('backend.indian-payroll.salary-templates.index') }}">{{ __trans('ctc_structure_templates') }}</a></li>
        <li><a class="@if ($activeLink == 'indian-payroll.employee-salary-structures') active @endif" href="{{ route('backend.indian-payroll.employee-salary-structures.index') }}">{{ __trans('employee_salary_structures') }}</a></li>
        <li><a class="@if ($activeLink == 'indian-payroll.statutory-settings') active @endif" href="{{ route('backend.indian-payroll.statutory-settings.index') }}">{{ __trans('statutory_settings') }}</a></li>
        <li><a class="@if ($activeLink == 'indian-payroll.payroll-runs') active @endif" href="{{ route('backend.indian-payroll.payroll-runs.index') }}">{{ __trans('payroll_runs') }}</a></li>
        <li><a class="@if ($activeLink == 'indian-payroll.tax-declarations') active @endif" href="{{ route('backend.indian-payroll.tax-declarations.index') }}">{{ __trans('tax_declarations') }}</a></li>
        <li><a class="@if ($activeLink == 'indian-payroll.settlements') active @endif" href="{{ route('backend.indian-payroll.settlements.index') }}">{{ __trans('full_and_final_settlements') }}</a></li>
    </ul>
</li>
@endif
{{-- IndianPayroll Module Routes Ends --}}
