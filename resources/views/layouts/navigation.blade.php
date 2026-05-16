@if (
    !request()->is('admin*') &&
        !request()->is('jarimatika/battle*') &&
        !request()->is('jarimatika/match*') &&
        !request()->is('jarimatika/room*'))
    <nav x-data="{ open: false }"
        class="fixed left-0 right-0 top-8 z-50 font-['Fredoka'] flex justify-center px-4 pointer-events-none">
        <!-- Floating Pill Navbar Container -->
        <div class="bg-white rounded-3xl shadow-2xl border-2 border-slate-100 backdrop-blur-md pointer-events-auto"
            style="box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.15);">

            <style>
                @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

                .nav-hud {
                    font-family: 'Fredoka', sans-serif;
                }

                .floating-nav-link {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .floating-nav-link:hover {
                    transform: translateY(-2px);
                }

                .floating-nav-link.active {
                    background: linear-gradient(135deg, rgba(56, 189, 248, 0.1), rgba(56, 189, 248, 0.05));
                }
            </style>

            <div class="px-6 py-3 nav-hud flex items-center justify-between gap-4">
                <!-- Logo & Title -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-2 transform hover:scale-110 transition-transform">
                        <div
                            class="bg-[#FFE52A] text-slate-800 text-xl p-1.5 rounded-lg border-b-3 border-[#ccb622] font-black leading-none">
                            ✌️
                        </div>
                        <span
                            class="text-lg font-black text-[#38BDF8] hidden md:block drop-shadow-sm tracking-wide">Jarimatika</span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}"
                        class="floating-nav-link px-4 py-2 rounded-2xl text-sm font-bold transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-sky-100 text-[#0284C7]' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                        🏠 Lobi
                    </a>
                    <a href="{{ route('jarimatika.belajar') }}"
                        class="floating-nav-link px-4 py-2 rounded-2xl text-sm font-bold transition-colors duration-200 {{ request()->routeIs('jarimatika.belajar') ? 'bg-lime-100 text-[#5e692a]' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                        📚 Belajar
                    </a>
                    <a href="{{ route('jarimatika.latihan') }}"
                        class="floating-nav-link px-4 py-2 rounded-2xl text-sm font-bold transition-colors duration-200 {{ request()->routeIs('jarimatika.latihan') ? 'bg-orange-100 text-[#c8790f]' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                        🎯 Latihan
                    </a>

                    <!-- Battle Link with Level Check -->
                    @auth
                        @if (auth()->user()->level >= 5)
                            <a href="{{ route('jarimatika.match') }}"
                                class="floating-nav-link px-4 py-2 rounded-2xl text-sm font-bold transition-colors duration-200 relative {{ request()->routeIs('jarimatika.match') ? 'bg-red-100 text-red-600' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                                ⚔️ Battle
                                <span class="absolute top-1 right-1 flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                </span>
                            </a>
                        @else
                            <div class="px-4 py-2 rounded-2xl text-sm font-bold text-gray-400 cursor-not-allowed flex items-center gap-1"
                                title="Level 5+ diperlukan untuk membuka Battle">
                                🔒 Battle
                            </div>
                        @endif
                    @else
                        <a href="{{ route('jarimatika.match') }}"
                            class="floating-nav-link px-4 py-2 rounded-2xl text-sm font-bold transition-colors duration-200 relative {{ request()->routeIs('jarimatika.match') ? 'bg-red-100 text-red-600' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                            ⚔️ Battle
                        </a>
                    @endauth

                    <a href="{{ route('reward.leaderboard') }}"
                        class="floating-nav-link px-4 py-2 rounded-2xl text-sm font-bold transition-colors duration-200 {{ request()->routeIs('reward.leaderboard') ? 'bg-yellow-100 text-yellow-700' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                        🏆 Peringkat
                    </a>
                </div>

                <!-- Stats (Desktop) -->
                @auth
                    <div class="hidden lg:flex items-center gap-2">
                        <div
                            class="bg-[#BBCB64] text-white px-2.5 py-1 rounded-lg text-xs font-black border-b-2 border-[#8fa040] flex items-center gap-1 shadow-sm">
                            <span class="opacity-80">Lv.</span> {{ auth()->user()->level ?? 1 }}
                        </div>
                        <div
                            class="bg-[#FFE52A] text-slate-800 px-2.5 py-1 rounded-lg text-xs font-black border-b-2 border-[#ccb622] flex items-center gap-1 shadow-sm">
                            ⭐ {{ auth()->user()->total_xp ?? 0 }}
                        </div>
                        <div
                            class="bg-[#F79A19] text-white px-2.5 py-1 rounded-lg text-xs font-black border-b-2 border-[#c8790f] flex items-center gap-1 shadow-sm">
                            💰 {{ auth()->user()->koin ?? 0 }}
                        </div>
                    </div>
                @endauth

                <!-- User Dropdown (Desktop) -->
                @auth
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border-2 border-slate-200 text-sm font-bold rounded-2xl text-slate-600 bg-white hover:bg-slate-50 hover:text-slate-900 focus:outline-none transition ease-in-out duration-150">
                                    <div class="hidden sm:inline">{{ auth()->user()->name }}</div>
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
                    <div class="hidden sm:flex sm:items-center sm:space-x-2">
                        <a href="{{ route('login') }}"
                            class="text-sm font-bold text-slate-500 hover:text-[#38BDF8] px-2">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="rounded-xl border-b-2 border-[#8fa040] bg-[#BBCB64] px-3 py-1.5 text-xs font-black text-white hover:bg-[#aab95b] transition-all active:border-b-0 active:translate-y-0.5">Daftar</a>
                        @endif
                    </div>
                @endauth

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none transition duration-150 ease-in-out border-2 border-slate-200">
                        <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div :class="{ 'block': open, 'hidden': !open }"
                class="hidden md:hidden border-t-2 border-slate-100 bg-white rounded-b-3xl">
                <div class="px-4 py-3 space-y-2">
                    @auth
                        <div
                            class="flex justify-around items-center mb-3 bg-slate-50 p-2 rounded-xl border-2 border-slate-200">
                            <div class="text-center">
                                <p class="text-xs font-bold text-slate-400">Level</p>
                                <p class="font-black text-[#BBCB64]">{{ auth()->user()->level ?? 1 }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-bold text-slate-400">XP</p>
                                <p class="font-black text-[#FFE52A]">⭐ {{ auth()->user()->total_xp ?? 0 }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-bold text-slate-400">Koin</p>
                                <p class="font-black text-[#F79A19]">💰 {{ auth()->user()->koin ?? 0 }}</p>
                            </div>
                        </div>
                    @endauth

                    <a href="{{ route('dashboard') }}"
                        class="block w-full px-3 py-2 text-sm font-bold text-slate-600 hover:bg-sky-50 hover:text-[#38BDF8] rounded-lg {{ request()->routeIs('dashboard') ? 'bg-sky-100 text-[#38BDF8]' : '' }}">🏠
                        Lobi</a>
                    <a href="{{ route('jarimatika.belajar') }}"
                        class="block w-full px-3 py-2 text-sm font-bold text-slate-600 hover:bg-lime-50 hover:text-[#BBCB64] rounded-lg {{ request()->routeIs('jarimatika.belajar') ? 'bg-lime-100 text-[#BBCB64]' : '' }}">📚
                        Belajar</a>
                    <a href="{{ route('jarimatika.latihan') }}"
                        class="block w-full px-3 py-2 text-sm font-bold text-slate-600 hover:bg-orange-50 hover:text-[#F79A19] rounded-lg {{ request()->routeIs('jarimatika.latihan') ? 'bg-orange-100 text-[#F79A19]' : '' }}">🎯
                        Latihan</a>

                    <!-- Mobile Battle Link with Level Check -->
                    @auth
                        @if (auth()->user()->level >= 5)
                            <a href="{{ route('jarimatika.match') }}"
                                class="block w-full px-3 py-2 text-sm font-bold text-slate-600 hover:bg-red-50 hover:text-red-500 rounded-lg {{ request()->routeIs('jarimatika.match') ? 'bg-red-100 text-red-600' : '' }}">⚔️
                                Battle</a>
                        @else
                            <div class="block w-full px-3 py-2 text-sm font-bold text-gray-400 cursor-not-allowed rounded-lg flex items-center gap-2"
                                title="Level 5+ diperlukan">
                                🔒 Battle
                            </div>
                        @endif
                    @else
                        <a href="{{ route('jarimatika.match') }}"
                            class="block w-full px-3 py-2 text-sm font-bold text-slate-600 hover:bg-red-50 hover:text-red-500 rounded-lg {{ request()->routeIs('jarimatika.match') ? 'bg-red-100 text-red-600' : '' }}">⚔️
                            Battle</a>
                    @endauth

                    <a href="{{ route('reward.leaderboard') }}"
                        class="block w-full px-3 py-2 text-sm font-bold text-slate-600 hover:bg-yellow-50 hover:text-yellow-600 rounded-lg {{ request()->routeIs('reward.leaderboard') ? 'bg-yellow-100 text-yellow-600' : '' }}">🏆
                        Peringkat</a>
                </div>

                <div class="pt-2 pb-3 border-t-2 border-slate-100 bg-slate-50 rounded-b-3xl px-4">
                    @auth
                        <div class="mb-3">
                            <div class="font-black text-sm text-slate-800">{{ auth()->user()->name }}</div>
                            <div class="font-medium text-xs text-slate-500">{{ auth()->user()->email }}</div>
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('profile.edit') }}"
                                class="block w-full px-3 py-2 text-xs font-bold text-slate-600 hover:text-[#38BDF8] hover:bg-slate-100 rounded-lg">👤
                                Profile
                                Setting</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="block w-full px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 rounded-lg">🚪
                                    Keluar</a>
                            </form>
                        </div>
                    @else
                        <div class="space-y-2">
                            <a href="{{ route('login') }}"
                                class="block text-center w-full bg-white border-2 border-slate-200 py-2 rounded-lg text-xs font-bold text-slate-600">Masuk</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="block text-center w-full bg-[#BBCB64] border-b-2 border-[#8fa040] py-2 rounded-lg text-xs font-black text-white active:translate-y-0.5 active:border-b-0">Daftar</a>
                            @endif
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
@endif
