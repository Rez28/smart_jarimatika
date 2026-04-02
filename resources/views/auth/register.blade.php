<x-guest-layout>
    <div
        class="mx-auto w-full max-w-lg rounded-[40px] border-4 border-[#BBCB64] bg-[#f3f7d3] p-8 shadow-[0_20px_0_rgba(187,203,100,0.18)]">
        <div class="text-center">
            <p class="text-sm uppercase tracking-[0.35em] text-[#6b7280]">Buat Akun Baru</p>
            <h1 class="mt-4 text-4xl font-black text-slate-900">Gabung Petualangan Hitung</h1>
            <p class="mt-3 text-slate-600">Daftar untuk menyimpan XP, koin, level, dan naik rank di leaderboard.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <x-input-label for="name" :value="__('Name')" class="text-slate-700" />
                <x-text-input id="name"
                    class="mt-2 w-full rounded-[32px] border border-[#d6d6d0] bg-white px-4 py-3 text-slate-900 shadow-[0_10px_0_rgba(0,0,0,0.06)] focus:border-[#BBCB64] focus:ring-[#BBCB64]/30"
                    type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" class="text-slate-700" />
                <x-text-input id="email"
                    class="mt-2 w-full rounded-[32px] border border-[#d6d6d0] bg-white px-4 py-3 text-slate-900 shadow-[0_10px_0_rgba(0,0,0,0.06)] focus:border-[#BBCB64] focus:ring-[#BBCB64]/30"
                    type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" class="text-slate-700" />
                <x-text-input id="password"
                    class="mt-2 w-full rounded-[32px] border border-[#d6d6d0] bg-white px-4 py-3 text-slate-900 shadow-[0_10px_0_rgba(0,0,0,0.06)] focus:border-[#BBCB64] focus:ring-[#BBCB64]/30"
                    type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-slate-700" />
                <x-text-input id="password_confirmation"
                    class="mt-2 w-full rounded-[32px] border border-[#d6d6d0] bg-white px-4 py-3 text-slate-900 shadow-[0_10px_0_rgba(0,0,0,0.06)] focus:border-[#BBCB64] focus:ring-[#BBCB64]/30"
                    type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div>
                <button type="submit"
                    class="btn-3d w-full rounded-full bg-[#FFE52A] px-5 py-3 text-base font-bold text-slate-900 transition hover:bg-[#ffe88a]">{{ __('Register') }}</button>
            </div>
        </form>

        <div class="mt-8 text-center text-sm text-slate-700">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-[#F79A19] hover:text-[#ea580c]">Masuk
                sekarang</a>
        </div>
    </div>
</x-guest-layout>
