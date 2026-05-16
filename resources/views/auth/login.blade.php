<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Smart Jarimatika - Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, #EEF2FF 0%, transparent 35%),
                radial-gradient(circle at bottom right, #DCFCE7 0%, transparent 35%),
                #F8FAFC;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        .brand-text {
            font-family: 'Fredoka', sans-serif;
        }

        /* =========================
           MAIN CONTAINER
        ========================== */

        .container-login {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        @media (max-width: 900px) {
            .container-login {
                grid-template-columns: 1fr;
            }

            .left-section {
                display: none !important;
            }
        }

        /* =========================
           LEFT SECTION
        ========================== */

        .left-section {
            position: relative;
            padding: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .left-content {
            position: relative;
            z-index: 2;
            max-width: 520px;
        }

        .logo-badge {
            width: fit-content;
            padding: 10px 18px;
            background: rgba(79, 70, 229, 0.1);
            color: #4F46E5;
            border-radius: 999px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .hero-title {
            font-size: 56px;
            line-height: 1.1;
            color: #0F172A;
            margin-bottom: 24px;
            font-weight: 700;
        }

        .hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #64748B;
            margin-bottom: 40px;
        }

        /* Floating cards */

        .stats-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        .stats-card {
            background: white;
            border-radius: 24px;
            padding: 18px 22px;
            min-width: 180px;
            box-shadow:
                0 10px 30px rgba(15, 23, 42, 0.06);
            border: 1px solid #E2E8F0;
        }

        .stats-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .bg-purple {
            background: #EEF2FF;
        }

        .bg-green {
            background: #DCFCE7;
        }

        .bg-orange {
            background: #FEF3C7;
        }

        .stats-title {
            font-size: 15px;
            font-weight: 600;
            color: #64748B;
        }

        .stats-value {
            font-size: 22px;
            font-weight: 700;
            color: #0F172A;
        }

        /* Decorative shapes */

        .shape-1,
        .shape-2,
        .shape-3 {
            position: absolute;
            border-radius: 999px;
            opacity: 0.3;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            background: #C7D2FE;
            top: -100px;
            left: -100px;
        }

        .shape-2 {
            width: 220px;
            height: 220px;
            background: #BBF7D0;
            bottom: -60px;
            right: -60px;
        }

        .shape-3 {
            width: 120px;
            height: 120px;
            background: #FDE68A;
            top: 140px;
            right: 80px;
        }

        /* =========================
           RIGHT SECTION
        ========================== */

        .right-section {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            background: white;
            border-radius: 36px;
            padding: 42px;
            border: 1px solid #E2E8F0;
            box-shadow:
                0 20px 50px rgba(15, 23, 42, 0.08);
        }

        .login-header {
            text-align: center;
            margin-bottom: 38px;
        }

        .login-logo {
            width: 120px;
            margin-bottom: 22px;
        }

        .welcome-text {
            font-size: 36px;
            color: #0F172A;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #64748B;
            line-height: 1.7;
            font-size: 15px;
        }

        /* =========================
           FORM
        ========================== */

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
        }

        .input-field {
            width: 100%;
            padding: 17px 20px;
            border-radius: 18px;
            border: 1.5px solid #E2E8F0;
            background: #F8FAFC;
            font-size: 15px;
            font-weight: 500;
            transition: 0.25s ease;
            color: #0F172A;
        }

        .input-field::placeholder {
            color: #94A3B8;
        }

        .input-field:focus {
            outline: none;
            border-color: #4F46E5;
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        }

        /* =========================
           OPTIONS
        ========================== */

        .options-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            margin-bottom: 28px;
        }

        .remember-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .remember-wrapper input {
            width: 18px;
            height: 18px;
            accent-color: #4F46E5;
        }

        .remember-wrapper span {
            font-size: 14px;
            color: #64748B;
            font-weight: 500;
        }

        .forgot-password {
            text-decoration: none;
            color: #4F46E5;
            font-size: 14px;
            font-weight: 600;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        /* =========================
           BUTTON
        ========================== */

        .login-button {
            width: 100%;
            border: none;
            cursor: pointer;
            padding: 18px;
            border-radius: 18px;
            background: #4F46E5;
            color: white;
            font-size: 16px;
            font-weight: 700;
            transition: 0.25s ease;
            box-shadow:
                0 12px 24px rgba(79, 70, 229, 0.22);
        }

        .login-button:hover {
            transform: translateY(-2px);
            background: #4338CA;
        }

        /* =========================
           REGISTER
        ========================== */

        .register-wrapper {
            margin-top: 30px;
            text-align: center;
        }

        .register-text {
            color: #64748B;
            font-size: 15px;
        }

        .register-link {
            text-decoration: none;
            color: #4F46E5;
            font-weight: 700;
        }

        .register-link:hover {
            text-decoration: underline;
        }

        /* =========================
           ERROR
        ========================== */

        .error-message {
            margin-top: 8px;
            color: #DC2626;
            font-size: 13px;
            font-weight: 500;
        }

        .session-status {
            background: #DCFCE7;
            color: #166534;
            padding: 14px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="container-login">

        <!-- LEFT -->
        <div class="left-section">

            <div class="shape-1"></div>
            <div class="shape-2"></div>
            <div class="shape-3"></div>

            <div class="left-content">

                <div class="logo-badge">
                    Smart Learning Platform
                </div>

                <h1 class="hero-title">
                    Belajar Matematika Jadi Lebih Seru
                </h1>

                <p class="hero-description">
                    Tingkatkan kemampuan berhitung dengan metode jarimatika
                    yang interaktif, menyenangkan, dan mudah dipahami anak-anak.
                </p>

                <div class="stats-wrapper">

                    <div class="stats-card">
                        <div class="stats-top">
                            <div class="stats-icon bg-purple">
                                ⭐
                            </div>

                            <div>
                                <div class="stats-title">
                                    Level Belajar
                                </div>

                                <div class="stats-value">
                                    Explorer
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-top">
                            <div class="stats-icon bg-green">
                                🔥
                            </div>

                            <div>
                                <div class="stats-title">
                                    Streak Belajar
                                </div>

                                <div class="stats-value">
                                    7 Hari
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-top">
                            <div class="stats-icon bg-orange">
                                🧠
                            </div>

                            <div>
                                <div class="stats-title">
                                    Skill Utama
                                </div>

                                <div class="stats-value">
                                    Penjumlahan
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- RIGHT -->
        <div class="right-section">

            <div class="login-card">

                <div class="login-header">

                    <img src="{{ asset('images/logo.png') }}" alt="Smart Jarimatika" class="login-logo">

                    <h1 class="welcome-text">
                        Selamat Datang
                    </h1>

                    <p class="subtitle">
                        Masuk untuk melanjutkan perjalanan belajar
                        matematika bersama Smart Jarimatika.
                    </p>

                </div>

                @if (session('status'))
                    <div class="session-status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">

                    @csrf

                    <!-- EMAIL -->
                    <div class="form-group">

                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autofocus autocomplete="username" class="input-field" placeholder="Masukkan email anda">

                        @error('email')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- PASSWORD -->
                    <div class="form-group">

                        <label for="password" class="form-label">
                            Password
                        </label>

                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="input-field" placeholder="Masukkan password">

                        @error('password')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- OPTIONS -->
                    <div class="options-wrapper">

                        <label class="remember-wrapper">

                            <input type="checkbox" name="remember" id="remember_me">

                            <span>
                                Ingat saya
                            </span>

                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-password">

                                Lupa Password?
                            </a>
                        @endif

                    </div>

                    <!-- BUTTON -->
                    <button type="submit" class="login-button">
                        Masuk Sekarang
                    </button>

                </form>

                <!-- REGISTER -->
                <div class="register-wrapper">

                    <p class="register-text">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="register-link">

                            Daftar sekarang
                        </a>
                    </p>

                </div>

            </div>

        </div>

    </div>

</body>

</html>
