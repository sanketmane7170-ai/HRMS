<!-- Header -->
<div class="header" style="display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; height: 60px; background: var(--card, #fff);">

    <!-- LEFT SECTION -->
    <div style="display: flex; align-items: center; gap: 1rem;">
        <!-- Sidebar Toggle -->
        <a href="javascript:void(0);" id="toggle_btn" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 8px; color: var(--text-color);">
            <i class="fas fa-bars"></i>
        </a>
        
        <!-- Mobile Menu Toggle -->
        <a class="mobile_btn d-md-none" id="mobile_btn" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 8px; color: var(--text-color);">
            <i class="fas fa-bars"></i>
        </a>
    </div>

    <!-- CENTER SECTION -->
    @php
        $currentStatus = auth()->user()->work_status ?? \Modules\Attendance\Enums\WorkStatus::AVAILABLE;
    @endphp
    <div class="nav-item dropdown main-drop d-none d-md-block" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 500;">
        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" id="statusPickerBtn" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text-color);">
            <span class="status-indicator" style="background-color: {{ $currentStatus->color() ?? '#28a745' }}; width: 8px; height: 8px; border-radius: 50%;"></span>
            <span class="status-label" style="color: var(--text-primary);">{{ $currentStatus->label() ?? 'Available' }}</span>
            <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: var(--text-secondary);"></i>
            <div class="status-spinner" style="display:none;"></div>
        </a>
        <div class="dropdown-menu shadow-lg border-0" style="min-width: 240px; border-radius: 12px; margin-top: 10px;">
            <div class="p-3 border-bottom mb-2">
                <h6 class="mb-0 font-weight-bold" style="font-size: 0.9rem;">Set Your Status</h6>
                <p class="text-muted small mb-0">How are you working now?</p>
            </div>
            @foreach(\Modules\Attendance\Enums\WorkStatus::cases() as $status)
                <a class="dropdown-item status-drop-item" href="javascript:void(0);" 
                   onclick="updateWorkStatus('{{ $status->value }}')"
                   data-status-value="{{ $status->value }}" style="display: flex; align-items: center; gap: 10px;">
                    <i class="{{ $status->icon() }}" style="color: {{ $status->color() }}; width: 20px;"></i>
                    <span>{{ $status->label() }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- RIGHT SECTION -->
    <ul class="nav user-menu" style="display: flex; align-items: center; gap: 1.25rem; flex-direction: row; margin-bottom: 0;">

        <!-- Theme Toggle -->
        <li class="nav-item" style="display: flex; align-items: center;">
            <a href="javascript:void(0);" class="nav-link" id="theme-toggle-react-link" style="padding: 0;">
                <div id="theme-toggle-react"></div>
            </a>
        </li>

        @if(Route::has('backend.languages.index'))
        <!-- Flag -->
        @include('multilingual::language-dropdown')
        @endif

        <!-- Notifications -->
        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
            $recentNotifications = auth()->user()->unreadNotifications()->take(5)->get();
        @endphp
        <li class="nav-item dropdown" style="display: flex; align-items: center;">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" style="display: flex; align-items: center; color: var(--text-secondary);">
                <i data-feather="bell" style="width: 20px; height: 20px;"></i>
                @if($unreadCount != 0)
                <span class="notification-increase" id="notification-count" style="position: absolute; top: -5px; right: -5px;">{{ $unreadCount }}</span>
                @endif
            </a>

            <div class="dropdown-menu notifications dropdown-menu-end">
                <div class="topnav-dropdown-header">
                    <span class="notification-title">{{__trans('unread_notifications')}} ({{ $unreadCount }})</span>
                    @if($unreadCount != 0)
                    <a href="javascript:void(0)" id="readallButton" class="clear-noti"> {{__trans('read_all')}}</a>
                    @endif
                </div>
                <div class="noti-content">
                    <ul class="list-group">
                        @forelse($recentNotifications as $notification)
                        <a href="{{ $notification->data['route'] ?? '#' }}" class="notification-link" data-notification-id="{{ $notification->id }}">
                            <li class="list-group-item {{ $notification->read_at ? 'list-group-item-light' : 'list-group-item-info' }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $notification->data['name'] ?? $notification->data['title'] ?? 'Notification' }}</h6>
                                    <small>{{ $notification->data['time'] ?? $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">{{ $notification->data['message'] ?? 'No message' }}</p>
                            </li>
                        </a>
                        @empty
                        <li class="list-group-item">Notification not found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </li>

        <!-- Profile Menu -->
        <li class="nav-item dropdown has-arrow main-drop" style="display: flex; align-items: center;">
            <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown" style="display: flex; align-items: center; gap: 0.5rem; padding: 0;">
                <img src="{{ auth()->user()->getProfileImage() }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                <span style="font-size: 0.875rem; font-weight: 500; color: var(--text-primary); margin-top: 0; margin-bottom: 0;">Hi, {{ explode(' ', trim(auth()->user()->name))[0] }}!</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{route('backend.account')}}">
                    <img src="{{asset('assets/backend/img/icon-user.svg')}}" class="img-fluid me-2" /> {{__trans('profile')}}
                </a>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <img src="{{asset('assets/backend/img/icon-logout.svg')}}" class="img-fluid me-2" /> {{__trans('logout')}}
                </a>
            </div>
        </li>

    </ul>
</div>
<!-- /Header -->er -->
