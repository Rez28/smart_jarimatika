<nav x-data="{ open: false }"
    class="bg-white border-b-[6px] border-slate-200 sticky top-0 z-50 font-['Fredoka'] shadow-sm">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .nav-hud {
            font-family: 'Fredoka', sans-serif;
        }
    </style>

    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 nav-hud">
        <div class="flex justify-between h-20">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-2 transform hover:scale-105 transition-transform">
                        <div
                            class="bg-[#FFE52A] text-slate-800 text-2xl p-2 rounded-xl border-b-4 border-[#ccb622] font-black leading-none">
                            ✌️
                        </div>
                        <span
                            class="text-2xl font-black text-[#38BDF8] hidden md:block drop-shadow-sm tracking-wide">Jarimatika</span>
                    </a>
                </div>

                <div class="hidden space-x-2 sm:-my-px sm:ms-10 sm:flex">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center px-4 pt-1 border-b-[6px] text-lg font-black transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'border-[#38BDF8] text-[#0284C7]' : 'border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-300' }}">
                        🏠 Lobi
                    </a>
                    <a href="{{ route('jarimatika.belajar') }}"
                        class="inline-flex items-center px-4 pt-1 border-b-[6px] text-lg font-black transition-colors duration-200 {{ request()->routeIs('jarimatika.belajar') ? 'border-[#BBCB64] text-[#5e692a]' : 'border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-300' }}">
                        📚 Belajar
                    </a>
                    <a href="{{ route('jarimatika.latihan') }}"
                        class="inline-flex items-center px-4 pt-1 border-b-[6px] text-lg font-black transition-colors duration-200 {{ request()->routeIs('jarimatika.latihan') ? 'border-[#F79A19] text-[#c8790f]' : 'border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-300' }}">
                        🎯 Latihan
                    </a>
                    <a href="{{ route('jarimatika.match') }}"
                        class="inline-flex items-center px-4 pt-1 border-b-[6px] text-lg font-black transition-colors duration-200 relative {{ request()->routeIs('jarimatika.match') ? 'border-[#CF0F0F] text-[#900b0b]' : 'border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-300' }}">
                        ⚔️ Battle
                        <span class="absolute top-5 right-2 flex h-3 w-3">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                    </a>
                    <a href="{{ route('leaderboard') }}"
                        class="inline-flex items-center px-4 pt-1 border-b-[6px] text-lg font-black transition-colors duration-200 {{ request()->routeIs('leaderboard') ? 'border-[#FFE52A] text-slate-800' : 'border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-300' }}">
                        🏆 Peringkat
                    </a>
                </div>
            </div>

            @auth
                <div class="hidden sm:flex sm:items-center gap-4 sm:ms-6">

                    <div
                        class="hidden lg:flex items-center gap-2 bg-slate-50 p-1.5 rounded-2xl border-2 border-slate-200 shadow-inner">
                        <div
                            class="bg-[#BBCB64] text-white px-3 py-1.5 rounded-xl text-sm font-black border-b-4 border-[#8fa040] flex items-center gap-1 shadow-sm">
                            <span class="opacity-80 text-xs">Lv.</span> {{ auth()->user()->level ?? 1 }}
                        </div>
                        <div
                            class="bg-[#FFE52A] text-slate-800 px-3 py-1.5 rounded-xl text-sm font-black border-b-4 border-[#ccb622] flex items-center gap-1 shadow-sm">
                            ⭐ {{ auth()->user()->total_xp ?? 0 }}
                        </div>
                        <div
                            class="bg-[#F79A19] text-white px-3 py-1.5 rounded-xl text-sm font-black border-b-4 border-[#c8790f] flex items-center gap-1 shadow-sm">
                            💰 {{ auth()->user()->koin ?? 0 }}
                        </div>
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-4 py-2 border-2 border-slate-200 text-base font-bold rounded-2xl text-slate-600 bg-white hover:bg-slate-50 hover:text-slate-900 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ auth()->user()->name }}</div>
                                <div class="ms-2">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')" class="font-bold text-slate-600 hover:text-[#38BDF8]">
                                👤 {{ __('Profile Setting') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="font-bold text-red-500 hover:text-red-700">
                                    🚪 {{ __('Keluar Game') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <div class="hidden sm:flex sm:items-center sm:space-x-3 sm:ms-6">
                    <a href="{{ route('login') }}"
                        class="text-base font-bold text-slate-500 hover:text-[#38BDF8]">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="rounded-2xl border-b-4 border-[#8fa040] bg-[#BBCB64] px-6 py-2 text-base font-black text-white hover:bg-[#aab95b] transition-all active:border-b-0 active:translate-y-1">Daftar</a>
                    @endif
                </div>
            @endauth

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-3 rounded-2xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none transition duration-150 ease-in-out border-2 border-slate-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden border-t-4 border-slate-200 bg-white">
        <div class="pt-2 pb-3 space-y-1 px-4">
            @auth
                <div class="flex justify-between items-center mb-4 bg-slate-50 p-3 rounded-2xl border-2 border-slate-200">
                    <div class="text-center">
                        <p class="text-xs font-bold text-slate-400 uppercase">Level</p>
                        <p class="font-black text-[#BBCB64]">{{ auth()->user()->level ?? 1 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-bold text-slate-400 uppercase">XP</p>
                        <p class="font-black text-[#FFE52A]">⭐ {{ auth()->user()->total_xp ?? 0 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-bold text-slate-400 uppercase">Koin</p>
                        <p class="font-black text-[#F79A19]">💰 {{ auth()->user()->koin ?? 0 }}</p>
                    </div>
                </div>
            @endauth

            <a href="{{ route('dashboard') }}"
                class="block w-full px-4 py-3 text-lg font-black text-slate-600 hover:bg-sky-50 hover:text-[#38BDF8] rounded-xl {{ request()->routeIs('dashboard') ? 'bg-sky-100 text-[#38BDF8]' : '' }}">🏠
                Lobi</a>
            <a href="{{ route('jarimatika.belajar') }}"
                class="block w-full px-4 py-3 text-lg font-black text-slate-600 hover:bg-lime-50 hover:text-[#BBCB64] rounded-xl {{ request()->routeIs('jarimatika.belajar') ? 'bg-lime-100 text-[#BBCB64]' : '' }}">📚
                Belajar</a>
            <a href="{{ route('jarimatika.latihan') }}"
                class="block w-full px-4 py-3 text-lg font-black text-slate-600 hover:bg-orange-50 hover:text-[#F79A19] rounded-xl {{ request()->routeIs('jarimatika.latihan') ? 'bg-orange-100 text-[#F79A19]' : '' }}">🎯
                Latihan</a>
            <a href="{{ route('jarimatika.match') }}"
                class="block w-full px-4 py-3 text-lg font-black text-slate-600 hover:bg-red-50 hover:text-red-500 rounded-xl {{ request()->routeIs('jarimatika.match') ? 'bg-red-100 text-red-600' : '' }}">⚔️
                Battle</a>
            <a href="{{ route('leaderboard') }}"
                class="block w-full px-4 py-3 text-lg font-black text-slate-600 hover:bg-yellow-50 hover:text-yellow-600 rounded-xl {{ request()->routeIs('leaderboard') ? 'bg-yellow-100 text-yellow-600' : '' }}">🏆
                Peringkat</a>
        </div>

        <div class="pt-4 pb-4 border-t-2 border-slate-100 bg-slate-50">
            @auth
                <div class="px-6">
                    <div class="font-black text-lg text-slate-800">{{ auth()->user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ auth()->user()->email }}</div>
                </div>
                <div class="mt-4 space-y-2 px-4">
                    <a href="{{ route('profile.edit') }}"
                        class="block w-full px-4 py-2 font-bold text-slate-600 hover:text-[#38BDF8]">👤 Profile Setting</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                            class="block w-full px-4 py-2 font-bold text-red-500 hover:bg-red-50 rounded-xl">🚪 Keluar
                            Game</a>
                    </form>
                </div>
            @else
                <div class="px-6 space-y-3">
                    <a href="{{ route('login') }}"
                        class="block text-center w-full bg-white border-2 border-slate-200 py-3 rounded-2xl font-bold text-slate-600">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="block text-center w-full bg-[#BBCB64] border-b-4 border-[#8fa040] py-3 rounded-2xl font-black text-white active:translate-y-1 active:border-b-0">Daftar
                            Akun Baru</a>
                    @endif
                </div>
            @endauth
        </div>
    </div>
</nav>
