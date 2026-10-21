<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم بالتذاكر') — Zadex Support</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&family=Sora:wght@600;700&family=Tajawal:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3565eb;
            --primary-hover: #264ecc;
            --primary-light: #ebf0fe;
            --ink: #11151c;
            --muted: #5a6474;
            --line: #e2e6ed;
            --line-light: #f0f3f7;
            --bg: #f8fafc;
            --card: #ffffff;
            --success: #10b981;
            --success-light: #ecfdf5;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --info: #06b6d4;
            --font-ar: 'Tajawal', sans-serif;
            --font-en: 'Public Sans', sans-serif;
            --font-brand: 'Sora', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-ar);
            background: var(--bg);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
        }

        /* Top Header */
        .topbar {
            background: var(--card);
            border-bottom: 1px solid var(--line);
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .brand-area {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--ink);
        }

        .brand-logo {
            font-family: var(--font-brand);
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .brand-dot {
            width: 7px;
            height: 7px;
            background: var(--primary);
            border-radius: 50%;
            display: inline-block;
        }

        .badge-tickets {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 999px;
            letter-spacing: 0.5px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: var(--card);
            border-color: var(--line);
            color: var(--ink);
        }
        .btn-secondary:hover {
            background: var(--bg);
            border-color: #cbd5e1;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-danger {
            background: var(--danger-light);
            color: var(--danger);
            border-color: #fecaca;
        }
        .btn-danger:hover {
            background: var(--danger);
            color: white;
        }

        /* Container */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px;
            width: 100%;
            flex: 1;
        }

        /* Flash Message */
        .alert-success {
            background: var(--success-light);
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        .badge-open {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .badge-in_progress {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }
        .badge-resolved {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .badge-closed {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        .badge-priority-low {
            background: #f3f4f6;
            color: #4b5563;
        }
        .badge-priority-medium {
            background: #e0f2fe;
            color: #0369a1;
        }
        .badge-priority-high {
            background: #ffedd5;
            color: #c2410c;
        }
        .badge-priority-urgent {
            background: #fee2e2;
            color: #b91c1c;
            font-weight: 700;
        }

        .code-tag {
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            background: var(--primary-light);
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Footer */
        .footer {
            border-top: 1px solid var(--line);
            padding: 20px 24px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            background: var(--card);
        }

        @media (max-width: 768px) {
            .topbar {
                padding: 0 16px;
            }
            .container {
                padding: 20px 16px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <header class="topbar">
        <a href="{{ route('admin.tickets.index') }}" class="brand-area">
            <span class="brand-logo">Zadex<span class="brand-dot"></span></span>
            <span class="badge-tickets">مركز التذاكر الدعم</span>
        </a>
        
        <div class="topbar-actions">
            @auth
                <span style="font-size: 13px; color: var(--muted); font-weight: 500;">
                    مرحباً، <strong>{{ auth()->user()->name }}</strong>
                </span>
            @endauth
            <a href="http://localhost:5173" target="_blank" class="btn btn-secondary btn-sm" title="الذهاب إلى الموقع الرئيسي">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                <span>الموقع الرئيسي</span>
            </a>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-primary btn-sm">
                <span>قائمة التذاكر</span>
            </a>
            @auth
                <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" title="تسجيل الخروج">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        <span>خروج</span>
                    </button>
                </form>
            @endauth
        </div>
    </header>

    <main class="container">
        @if(session('success'))
            <div class="alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <p>نظام تذاكر الدعم الفني لـ <strong>Zadex</strong> — مدعوم بواسطة Laravel & SQLite</p>
    </footer>

    @yield('scripts')
</body>
</html>
