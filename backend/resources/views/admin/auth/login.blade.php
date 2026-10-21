<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — لوحة تحكم Zadex</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600&family=Sora:wght@600;700&family=Tajawal:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #3565eb;
            --primary-hover: #264ecc;
            --ink: #11151c;
            --muted: #5a6474;
            --line: #e2e6ed;
            --bg: #f8fafc;
            --card: #ffffff;
            --danger: #ef4444;
            --font-ar: 'Tajawal', sans-serif;
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
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            width: 100%;
            max-width: 420px;
            padding: 36px 32px;
            box-shadow: 0 15px 35px rgba(27, 42, 69, 0.05);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-logo {
            font-family: var(--font-brand);
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--ink);
            text-decoration: none;
        }

        .brand-dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            display: inline-block;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--muted);
            margin-top: 6px;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: #fdfdfd;
            color: var(--ink);
            direction: ltr;
            text-align: right;
            transition: all 0.15s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(53, 101, 235, 0.1);
            background: #fff;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            cursor: pointer;
            font-weight: normal;
            margin-bottom: 0;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: 0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        .credentials-hint {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
        }

        .cred-code {
            font-family: var(--font-mono);
            font-weight: 600;
            color: var(--primary);
            direction: ltr;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-header">
            <a href="/" class="brand-logo">Zadex<span class="brand-dot"></span></a>
            <p class="brand-subtitle">لوحة تحكم الدعم الفني وإدارة التذاكر</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email', 'admin@zadex.com') }}" 
                    required 
                    autofocus
                    placeholder="admin@zadex.com"
                >
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="••••••••"
                >
            </div>

            <div class="remember-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" value="1" checked>
                    <span>تذكرني على هذا الجهاز</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                تسجيل الدخول
            </button>
        </form>

        <div class="credentials-hint">
            <strong>بيانات تسجيل الدخول التجريبية (Admin Credentials):</strong><br>
            البريد: <span class="cred-code">admin@zadex.com</span><br>
            كلمة المرور: <span class="cred-code">zadex@2026</span>
        </div>
    </div>
</body>
</html>
