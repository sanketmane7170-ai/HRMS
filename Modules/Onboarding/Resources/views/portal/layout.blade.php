<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>@yield('title') | {{ getSetting('site_title') ?? 'Onboarding Portal' }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/backend/img/favicon.png') }}">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/backend/css/bootstrap.min.css') }}">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/backend/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/backend/plugins/fontawesome/css/all.min.css') }}">
    
    <!-- Main CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/backend/css/style.css') }}"> --}}
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #2563eb;
            --primary-light: #60a5fa;
            --primary-soft: rgba(37, 99, 235, 0.05);
            --dark: #0f172a;
            --surface: #ffffff;
            --bg-app: #f8fafc;
            --border-soft: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-xl: 32px;
        }
        
        body {
            background-color: var(--bg-app);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Modern UI - Rounded Corners */
        .card { border-radius: var(--radius-lg); border: 1px solid var(--border-soft); background: #fff; }
        .btn { border-radius: var(--radius-md); font-weight: 600; }
        .form-control { border-radius: var(--radius-sm); border: 1px solid var(--border-soft); }
        .badge { border-radius: 6px; font-weight: 700; padding: 6px 12px; }
        
        /* Premium Shadows */
        .shadow-premium {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: var(--transition);
        }
        
        .shadow-premium:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            transform: translateY(-2px);
        }

        .portal-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(241, 245, 249, 0.8);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .portal-logo {
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--dark);
            text-decoration: none !important;
            letter-spacing: -0.03em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logo-dot {
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 50%;
        }
        
        .portal-logo span {
            color: var(--primary);
        }
        
        .user-dropdown-btn {
            font-weight: 600;
            color: #334155;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--gray-100);
            border-radius: var(--radius-md);
            transition: var(--transition);
        }
        
        .user-dropdown-btn:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            color: #fff;
            padding: 12px 28px;
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.3);
            transition: var(--transition);
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            transform: translateY(-1px);
        }

        /* Interactive Animation Base */
        .fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .footer {
            margin-top: 8rem;
            padding: 4rem 0;
            background: transparent;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.02em;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Header -->
    <header class="portal-header">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('portal.dashboard') }}" class="portal-logo">
                <img src="{{ getLogo() }}" alt="{{ getSetting('site_title') }}" style="height: 32px; width: auto;">
            </a>
            
            @if(Auth::check())
                <div class="dropdown">
                    <button class="btn btn-link user-dropdown-btn text-decoration-none dropdown-toggle border-0" type="button" data-bs-toggle="dropdown">
                        <i class="far fa-user-circle mr-1"></i> {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2">
                        <li>
                            <form action="{{ route('portal.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 small">
                                    <i class="fas fa-sign-out-alt mr-2 text-muted"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} {{ getSetting('site_title') }}. All rights reserved. | v1.0.1</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('assets/backend/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/backend/js/bootstrap.bundle.min.js') }}"></script>
    @yield('scripts')
</body>
</html>
