<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        * {
            font-family: 'Fredoka', sans-serif !important;
        }

        body {
            background: linear-gradient(135deg, #FFFBEB 0%, #F3E8FF 100%);
            min-height: 100vh;
        }

        /* ==================== SPLIT SCREEN CONTAINER ==================== */
        .split-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            min-height: 100vh;
            align-items: stretch;
        }

        @media (max-width: 768px) {
            .split-container {
                grid-template-columns: 1fr;
                gap: 0;
                min-height: auto;
            }
        }

        /* ==================== LEFT SIDE: MASCOT & MOTIVATION ==================== */
        .mascot-section {
            background: linear-gradient(135deg, #38BDF8 0%, #0284C7 50%, #10B981 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .mascot-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .mascot-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .mascot-content {
            position: relative;
            z-index: 10;
            text-align: center;
            color: white;
        }

        .mascot-emoji {
            font-size: 5rem;
            margin-bottom: 1rem;
            display: block;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .mascot-title {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }

        .mascot-quotes {
            font-size: 1.25rem;
            font-weight: 600;
            line-height: 1.6;
            margin-bottom: 2rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .motivation-badge {
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 1rem 1.5rem;
            font-weight: 700;
            backdrop-filter: blur(10px);
            display: inline-block;
        }

        /* ==================== RIGHT SIDE: LOGIN FORM ==================== */
        .form-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background-color: white;
        }

        @media (max-width: 768px) {
            .form-section {
                padding: 1.5rem;
            }

            .mascot-section {
                padding: 1.5rem;
                min-height: 300px;
            }

            .mascot-emoji {
                font-size: 3rem;
            }

            .mascot-title {
                font-size: 1.5rem;
            }

            .mascot-quotes {
                font-size: 1rem;
            }
        }

        .login-card {
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-badge {
            background: linear-gradient(135deg, #FFE52A 0%, #F79A19 100%);
            color: #1e293b;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 1rem;
            box-shadow: 0 6px 15px rgba(255, 165, 0, 0.2);
        }

        .login-title {
            font-size: 2.2rem;
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            font-size: 1rem;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        /* ==================== CHUNKY INPUT FIELDS ==================== */
        .input-wrapper {
            margin-bottom: 1.5rem;
        }

        .input-label {
            display: block;
            color: #334155;
            font-weight: 700;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .chunky-input {
            width: 100%;
            padding: 1rem 1.75rem;
            border: 4px solid #e2e8f0;
            border-radius: 24px;
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            background-color: #f8fafc;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .chunky-input::placeholder {
            color: #cbd5e1;
        }

        .chunky-input:focus {
            outline: none;
            border-color: #38BDF8;
            background-color: #f0f9ff;
            box-shadow: 0 0 0 6px rgba(56, 189, 248, 0.15), 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .chunky-input:hover:not(:focus) {
            border-color: #cbd5e1;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        /* ==================== SUBMIT BUTTON WITH BOUNCE ==================== */
        .btn-adventure {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, #BBCB64 0%, #8fa040 100%);
            color: white;
            border: 4px solid #8fa040;
            border-radius: 24px;
            font-size: 1.1rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            margin-top: 0.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-adventure:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: bounce 0.6s ease;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(-4px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .btn-adventure:active {
            transform: translateY(0px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* ==================== REMEMBER & FORGOT PASSWORD ==================== */
        .remember-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
            font-size: 0.95rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chunky-checkbox {
            width: 24px;
            height: 24px;
            cursor: pointer;
            accent-color: #38BDF8;
        }

        .forgot-link {
            color: #F79A19;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            color: #EA580C;
            text-decoration: underline;
        }

        /* ==================== REGISTER LINK ==================== */
        .register-prompt {
            background: linear-gradient(135deg, #F0F9FF 0%, #F0FDF4 100%);
            border: 3px solid #e0e7ff;
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            margin-top: 2rem;
        }

        .register-text {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.75rem;
        }

        .register-link {
            color: #38BDF8;
            font-weight: 900;
            text-decoration: none;
            font-size: 1.05rem;
            transition: all 0.3s ease;
        }

        .register-link:hover {
            color: #0284C7;
            text-decoration: underline;
            text-decoration-thickness: 3px;
            text-underline-offset: 6px;
        }

        /* ==================== ERROR MESSAGES ==================== */
        .error-message {
            color: #DC2626;
            font-weight: 700;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            padding: 0.75rem;
            background-color: #FEE2E2;
            border-radius: 12px;
            border-left: 4px solid #DC2626;
        }
    </style>

    <div class="split-container">
        <!-- LEFT SIDE: MASCOT & MOTIVATION -->
        <div class="mascot-section hidden md:flex">
            <div class="mascot-content">
                <span class="mascot-emoji">✌️</span>
                <h2 class="mascot-title">Jari Matika</h2>
                <p class="mascot-quotes">
                    "Setiap jari adalah alat hitung yang ajaib!<br>
                    Mari bermain sambil belajar matematika."
                </p>
                <div class="motivation-badge">
                    🎯 Kalahkan pemain lain & Naik Level!
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: LOGIN FORM -->
        <div class="form-section">
            <div class="login-card">
                <!-- Header -->
                <div class="login-header">
                    <div class="brand-badge">⚡ Smart Jarimatika</div>
                    <h1 class="login-title">Masuk & Bermain!</h1>
                    <p class="login-subtitle">Kumpulkan XP, Koin, dan Naik ke Level Tertinggi</p>
                </div>

                <!-- Status Message -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Field -->
                    <div class="input-wrapper">
                        <label for="email" class="input-label">📧 Email Pemain</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autofocus autocomplete="username" class="chunky-input" placeholder="nama@email.com">
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="input-wrapper">
                        <label for="password" class="input-label">🔐 Kata Sandi</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="chunky-input" placeholder="Masukkan kata sandi...">
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="remember-section">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" id="remember_me" name="remember" class="chunky-checkbox">
                            <span class="text-slate-600 font-bold">Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">Lupa sandi?</a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-adventure">
                        <span>🚀</span> MULAI PETUALANGAN
                    </button>
                </form>

                <!-- Register Link -->
                <div class="register-prompt">
                    <p class="register-text">Belum punya akun?</p>
                    <a href="{{ route('register') }}" class="register-link">Daftar Karakter Baru →</a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
