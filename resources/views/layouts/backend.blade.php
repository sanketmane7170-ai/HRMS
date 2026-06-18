<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Added all css files -->
    @include('layouts.partials.header-files')



</head>

<body class="no-class">
    @include('layouts.partials.loader')
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Added Admin Header -->
        @include('layouts.partials.backend.header')
        <!-- Added Admin Sidebar -->
        @include('layouts.partials.backend.sidebar')

        @yield('content')

        {{--  <a href="javascript:void(0);" id="chat-icon" class="chat-icon">
            <i class="fas fa-comments"></i>
            <span class="badge" id="unread-count" style="display:none;"></span>
        </a>  --}}
        {{--  <div id="chat-floating-button" class="chat-floating-button">
            <i class="fas fa-comments"></i>
        </div>  --}}

        <!-- Main Dashboard Footer -->
        <footer class="main-footer" style="text-align: center; padding: 15px; font-size: 14px; background: var(--card, #fff); border-top: 1px solid var(--border, #eaeaea); color: var(--text-secondary, #666); margin-top: 2rem;">
            <p style="margin: 0;">
                &copy; 2026 SR Global.
                <strong>Developed By Innozia</strong> |
                Version: 2.5.0
            </p>
        </footer>
    </div>
    @include('components.chat_window')

    <!-- /Main Wrapper -->
    <!-- Added all script files -->
    @include('layouts.partials.footer-files')
    @include('layouts.partials.alert')
    @stack('scripts')
</body>

</html>
