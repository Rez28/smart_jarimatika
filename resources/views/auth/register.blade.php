<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Smart Jarimatika - Register</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
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
           MAIN
        ========================== */

        .container-register {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        @media (max-width: 900px) {
            .container-register {
                grid-template-columns: 1fr;
            }

            .left-section {
                display: none !important;
            }
        }

        /* =========================
           LEFT
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

        .badge-top {
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

        /* Cards */

        .feature-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        .feature-card {
            background: white;
            border-radius: 24px;
            padding: 18px 22px;
            min-width: 180px;

            border: 1px solid #E2E8F0;

            box-shadow:
                0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .feature-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .feature-icon {
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

        .feature-title {
            font-size: 15px;
            font-weight: 600;
            color: #64748B;
        }

        .feature-value {
            font-size: 22px;
            font-weight: 700;
            color: #0F172A;
        }

        /* Shapes */

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
           RIGHT
        ========================== */

        .right-section {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .register-card {
            width: 100%;
            max-width: 480px;

            background: white;

            border-radius: 36px;

            padding: 42px;

            border: 1px solid #E2E8F0;

            box-shadow:
                0 20px 50px rgba(15, 23, 42, 0.08);
        }

        .register-header {
            text-align: center;
            margin-bottom: 34px;
        }

        .register-logo {
            width: 120px;
            margin-bottom: 22px;
        }

        .register-title {
            font-size: 36px;
            color: #0F172A;
            margin-bottom: 10px;
        }

        .register-subtitle {
            color: #64748B;
            line-height: 1.7;
            font-size: 15px;
        }

        /* =========================
           FORM
        ========================== */

        .form-group {
            margin-bottom: 20px;
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

            box-shadow:
                0 0 0 4px rgba(79, 70, 229, 0.12);
        }

        /* =========================
           BUTTON
        ========================== */

        .register-button {
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

            margin-top: 10px;
        }

        .register-button:hover {
            transform: translateY(-2px);
            background: #4338CA;
        }

        /* =========================
           LOGIN
        ========================== */

        .login-wrapper {
            margin-top: 28px;
            text-align: center;
        }

        .login-text {
            color: #64748B;
            font-size: 15px;
        }

        .login-link {
            text-decoration: none;
            color: #4F46E5;
            font-weight: 700;
        }

        .login-link:hover {
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
    </style>
</head>

<body>

    <div class="container-register">

        <!-- LEFT -->
        <div class="left-section">

            <div class="shape-1"></div>
            <div class="shape-2"></div>
            <div class="shape-3"></div>

            <div class="left-content">

                <div class="badge-top">
                    Smart Learning Platform
                </div>

                <h1 class="hero-title">
                    Mulai Perjalanan Belajarmu Hari Ini
                </h1>

                <p class="hero-description">
                    Buat akun baru dan nikmati pengalaman belajar matematika
                    yang lebih menyenangkan, interaktif, dan penuh tantangan.
                </p>

                <div class="feature-wrapper">

                    <div class="feature-card">

                        <div class="feature-top">

                            <div class="feature-icon bg-purple">
                                🎯
                            </div>

                            <div>
                                <div class="feature-title">
                                    Misi Harian
                                </div>

                                <div class="feature-value">
                                    XP Reward
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="feature-card">

                        <div class="feature-top">

                            <div class="feature-icon bg-green">
                                🏆
                            </div>

                            <div>
                                <div class="feature-title">
                                    Peringkat
                                </div>

                                <div class="feature-value">
                                    Top Player
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="feature-card">

                        <div class="feature-top">

                            <div class="feature-icon bg-orange">
                                🧠
                            </div>

                            <div>
                                <div class="feature-title">
                                    Materi
                                </div>

                                <div class="feature-value">
                                    Interaktif
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- RIGHT -->
        <div class="right-section">

            <div class="register-card">

                <div class="register-header">

                    <img src="{{ asset('images/logo.png') }}" alt="Smart Jarimatika" class="register-logo">

                    <h1 class="register-title">
                        Buat Akun Baru
                    </h1>

                    <p class="register-subtitle">
                        Daftar dan mulai belajar matematika
                        bersama Smart Jarimatika.
                    </p>

                </div>

                <!-- FORM -->
                <form method="POST" action="{{ route('register') }}">

                    @csrf

                    <!-- NAME -->
                    <div class="form-group">

                        <label for="name" class="form-label">
                            Nama Lengkap
                        </label>

                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                            autofocus autocomplete="name" class="input-field" placeholder="Masukkan nama lengkap">

                        @error('name')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- EMAIL -->
                    <div class="form-group">

                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email" class="input-field" placeholder="Masukkan email">

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

                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="input-field" placeholder="Masukkan password">

                        @error('password')
                            <div class="error-message">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- CONFIRM -->
                    <div class="form-group">

                        <label for="password_confirmation" class="form-label">
                            Konfirmasi Password
                        </label>

                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            autocomplete="new-password" class="input-field" placeholder="Ulangi password">

                    </div>

                    <!-- BUTTON -->
                    <button type="submit" class="register-button">
                        Daftar Sekarang
                    </button>

                </form>

                <!-- LOGIN -->
                <div class="login-wrapper">

                    <p class="login-text">
                        Sudah punya akun?

                        <a href="{{ route('login') }}" class="login-link">

                            Masuk sekarang
                        </a>
                    </p>

                </div>

            </div>

        </div>

    </div>

</body>

</html>
