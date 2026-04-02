<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        /* Memaksa background body/layout berubah menjadi tema game kita */
        body {
            font-family: 'Fredoka', sans-serif !important;
            background-color: #FFFBEB !important;
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px) !important;
            background-size: 30px 30px !important;
        }

        /* Kartu Login 3D */
        .login-card {
            background-color: white;
            border-radius: 40px;
            border-bottom: 12px solid #F79A19;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        /* Input Form Bergaya Game */
        .game-input {
            border: 4px solid #e2e8f0;
            border-radius: 20px;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #1e293b;
            width: 100%;
            transition: all 0.2s ease;
        }

        .game-input:focus {
            border-color: #38BDF8;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.2);
            outline: none;
        }

        /* Tombol 3D */
        .btn-3d-green {
            background-color: #BBCB64;
            border-bottom: 8px solid #8fa040;
            border-radius: 24px;
            color: white;
            transition: all 0.15s ease;
        }

        .btn-3d-green:active {
            border-bottom-width: 0px;
            transform: translateY(8px);
        }
    </style>

    <div class="flex flex-col items-center justify-center min-h-screen px-4 pb-12 pt-6">

        <div class="text-[6rem] mb-[-40px] z-10 animate-bounce relative drop-shadow-md">👋</div>

        <div class="login-card w-full max-w-md px-8 py-10 relative z-0">

            <div class="text-center mb-8 pt-4">
                <span
                    class="bg-[#FFE52A] text-slate-800 px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest border-2 border-white shadow-sm">
                    Smart Jarimatika
                </span>
                <h1 class="mt-4 text-3xl font-black text-slate-800">Masuk & Bermain!</h1>
                <p class="mt-2 text-slate-500 font-medium">Ayo kumpulkan XP dan Koinmu hari ini.</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-slate-600 font-bold ml-2 mb-2">Email Pemain</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username" class="game-input" placeholder="Masukkan emailmu...">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 font-bold" />
                </div>

                <div>
                    <label for="password" class="block text-slate-600 font-bold ml-2 mb-2">Kata Sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="game-input" placeholder="Rahasia!">
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 font-bold" />
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-2">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox"
                            class="w-5 h-5 rounded border-2 border-slate-300 text-[#38BDF8] focus:ring-[#38BDF8] cursor-pointer"
                            name="remember">
                        <span class="ml-2 text-sm font-bold text-slate-500 group-hover:text-slate-700 transition">Ingat
                            saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm font-bold text-[#F79A19] hover:text-[#d97706] transition"
                            href="{{ route('password.request') }}">
                            Lupa sandi?
                        </a>
                    @endif
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="btn-3d-green w-full py-4 text-xl font-black uppercase tracking-wider flex justify-center items-center gap-2">
                        <span>🚀</span> MULAI PETUALANGAN
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center bg-slate-50 p-4 rounded-2xl border-2 border-slate-100">
                <p class="text-sm font-bold text-slate-500">
                    Belum punya akun? <br>
                    <a href="{{ route('register') }}"
                        class="text-[#38BDF8] hover:text-[#0284C7] font-black text-lg underline decoration-2 underline-offset-4 mt-1 inline-block">
                        Daftar Karakter Baru!
                    </a>
                </p>
            </div>

        </div>
    </div>
</x-guest-layout>
