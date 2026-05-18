<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Hocky Guest House</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #FAFAF8; /* var(--bg-base) */
            color: #1A1A1A; /* var(--text-primary) */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Optional subtle noise texture can be added here if desired via background-image */
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 0 20px;
        }

        .login-card {
            background: #FFFFFF;
            border: 1px solid #E5E4E0;
            border-radius: 14px;
            padding: 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(0,0,0,0.06);
        }

        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 50%;
            background: transparent;
            margin-bottom: 16px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 700;
            color: #1A1A1A;
            letter-spacing: -0.02em;
        }
        .brand-subtitle { font-size: 13px; color: #A3A3A3; margin-top: 4px; }

        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 500; color: #525252; margin-bottom: 6px; }

        input {
            width: 100%;
            background: #FAFAF8;
            border: 1px solid #E5E4E0;
            border-radius: 6px;
            color: #1A1A1A;
            padding: 10px 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.12s ease;
        }
        input:focus { outline: none; border-color: #1A1A1A; box-shadow: 0 0 0 2px rgba(0,0,0,0.06); }
        input::placeholder { color: #A3A3A3; }
        input.is-invalid { border-color: #DC2626; }

        .error-msg { font-size: 12px; color: #DC2626; margin-top: 4px; }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .form-check input[type="checkbox"] { width: auto; }
        .form-check label { margin: 0; font-size: 13px; color: #525252; }

        .btn-login {
            width: 100%;
            padding: 10px 18px;
            background: #1A1A1A;
            border: none;
            border-radius: 6px;
            color: #FFFFFF;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-login:hover {
            background: #333333;
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); }

        .login-footer { text-align: center; margin-top: 24px; font-size: 11px; color: #A3A3A3; }

    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card" id="loginCard">
        <div class="login-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Hocky Guest House" class="login-logo">
            <div class="brand-title">Hocky Guest House</div>
            <div class="brand-subtitle">Sistem Manajemen Penginapan</div>
        </div>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan Email"
                    autocomplete="email"
                    autofocus
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                >
                @error('email')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                >
                @error('password')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn-login">Masuk</button>
        </form>

        <div class="login-footer">© {{ date('Y') }} Hocky Guest House.</div>
    </div>
</div>

</body>
</html>
