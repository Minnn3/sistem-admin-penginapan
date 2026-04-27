<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Hocky Guest House</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f1117;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        /* Animated background blobs */
        .bg-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: float 8s ease-in-out infinite;
        }
        .bg-blob-1 { width: 500px; height: 500px; background: #6366f1; top: -150px; left: -150px; animation-delay: 0s; }
        .bg-blob-2 { width: 400px; height: 400px; background: #8b5cf6; bottom: -100px; right: -100px; animation-delay: -4s; }
        .bg-blob-3 { width: 300px; height: 300px; background: #3b82f6; top: 50%; left: 50%; transform: translate(-50%,-50%); animation-delay: -2s; }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 0 16px;
        }

        .login-card {
            background: rgba(26, 29, 39, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.6);
        }

        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            padding: 4px;
            margin-bottom: 12px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 4px 20px rgba(99,102,241,0.3);
        }
        .brand-title {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #818cf8, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }

        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 500; color: #94a3b8; margin-bottom: 7px; }

        input {
            width: 100%;
            background: rgba(15,17,23,0.7);
            border: 1px solid #2e3350;
            border-radius: 10px;
            color: #e2e8f0;
            padding: 12px 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.22s;
        }
        input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.2); }
        input::placeholder { color: #4a5568; }
        input.is-invalid { border-color: #ef4444; }

        .error-msg { font-size: 12px; color: #ef4444; margin-top: 5px; }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .form-check input[type="checkbox"] { width: auto; }
        .form-check label { margin: 0; font-size: 13px; color: #64748b; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.22s;
            letter-spacing: 0.01em;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99,102,241,0.5);
        }
        .btn-login:active { transform: translateY(0); }

        .login-footer { text-align: center; margin-top: 20px; font-size: 12px; color: #475569; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25%       { transform: translateX(-6px); }
            75%       { transform: translateX(6px); }
        }
        .shake { animation: shake 0.4s ease; }
    </style>
</head>
<body>
<div class="bg-blob bg-blob-1"></div>
<div class="bg-blob bg-blob-2"></div>
<div class="bg-blob bg-blob-3"></div>

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

            <button type="submit" class="btn-login" id="loginBtn">Masuk</button>
        </form>

        <div class="login-footer">© {{ date('Y') }} Hocky Guest House. AMN.</div>
    </div>
</div>

<script>
    if (<?php echo $errors->any() ? 'true' : 'false'; ?>) {
        document.getElementById('loginCard').classList.add('shake');
    }
</script>
</body>
</html>
