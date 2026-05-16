<x-app-layout>
    @include('components.navbar')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-lobby {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            /* Krem terang */
            background-image: radial-gradient(#FFE52A 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: calc(100vh - 65px);
            /* Asumsi ada navbar bawaan app-layout */
        }

        /* Kartu Timbul ala Game */
        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        /* Mode Buttons 3D */
        .mode-btn {
            border-radius: 32px;
            transition: all 0.15s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem 1rem;
            cursor: pointer;
        }

        .mode-btn:active {
            border-bottom-width: 0px !important;
            transform: translateY(8px);
        }

        .mode-belajar {
            background-color: #38BDF8;
            border-bottom: 8px solid #0284C7;
        }

        .mode-latihan {
            background-color: #BBCB64;
            border-bottom: 8px solid #8fa040;
        }

        .mode-battle {
            background-color: #F79A19;
            border-bottom: 8px solid #c8790f;
        }

        .btn-3d-yellow {
            background-color: #FFE52A;
            border-bottom: 6px solid #ccb622;
            transition: all 0.15s ease;
        }

        .btn-3d-yellow:active {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }
    </style>

    <div class="jarimatika-lobby py-8 px-4 sm:px-6">
        <div class="max-w-[1200px] mx-auto grid grid-cols-1 xl:grid-cols-[1fr_350px] gap-8">

            <div class="flex flex-col gap-8">

                <div
                    class="game-card p-8 md:p-10 border-b-[10px] border-[#38BDF8] relative overflow-hidden bg-white z-10">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-[#E0F2FE] rounded-full z-0 opacity-50"></div>

                    <div class="relative z-10 flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
                        <div class="shrink-0">
                            @php
                                $avatar = auth()->user()->active_avatar;
                                $isEmoji = $avatar && strlen($avatar) < 10 && !str_contains($avatar, '/');
                            @endphp
                            @if ($avatar && !$isEmoji)
                                <img src="{{ asset($avatar) }}"
                                    class="rounded-full w-24 h-24 border-4 border-white shadow-lg object-cover mb-4">
                            @elseif ($avatar && $isEmoji)
                                <div
                                    class="w-24 h-24 rounded-full bg-white border-4 border-[#38BDF8] shadow-lg flex items-center justify-center text-6xl mb-4">
                                    {{ $avatar }}
                                </div>
                            @else
                                <div
                                    class="w-24 h-24 rounded-full bg-[#BBCB64] border-4 border-white shadow-lg flex items-center justify-center text-4xl font-black text-white mb-4">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <div class="flex-grow w-full">
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">Selamat Datang
                                Kembali,
                            </p>
                            <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-4">{{ auth()->user()->name }}!
                                👋</h1>

                            <div class="bg-slate-50 p-4 rounded-2xl border-2 border-slate-100">
                                @php
                                    $userLevel = auth()->user()->level ?? 1;
                                    $userTotalXp = auth()->user()->total_xp ?? 0;
                                    $maxXpForLevel = $userLevel * 500;
                                    $xpProgress = min(100, ($userTotalXp / $maxXpForLevel) * 100);
                                    $xpNeeded = max(0, $maxXpForLevel - $userTotalXp);
                                @endphp
                                <div class="flex justify-between items-end mb-2">
                                    <span class="font-bold text-slate-600">Menuju Level
                                        {{ $userLevel + 1 }}</span>
                                    <span class="text-sm font-bold text-[#BBCB64]">Kurang {{ intval($xpNeeded) }}
                                        XP</span>
                                </div>
                                <div
                                    class="h-6 bg-slate-200 rounded-full border-2 border-slate-300 overflow-hidden relative">
                                    <div class="absolute top-0 left-0 h-full bg-[#BBCB64] rounded-full transition-all duration-500"
                                        style="width: {{ $xpProgress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2
                        class="text-slate-500 font-bold uppercase tracking-widest text-sm mb-4 border-b-2 border-slate-200 pb-2">
                        🎮 Pilih Mode Permainan</h2>
                    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">

                        <a href="{{ route('jarimatika.belajar') }}" class="mode-btn mode-belajar group">
                            <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">📚</div>
                            <h3 class="text-xl font-black text-white drop-shadow-sm">Belajar</h3>
                            <p class="text-sky-100 font-medium text-xs mt-1 px-2">Kenali bentuk jari</p>
                        </a>

                        <a href="{{ route('jarimatika.latihan') }}" class="mode-btn mode-latihan group">
                            <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🎯</div>
                            <h3 class="text-xl font-black text-white drop-shadow-sm">Latihan</h3>
                            <p class="text-lime-100 font-medium text-xs mt-1 px-2">Kumpulkan XP</p>
                        </a>

                        <a href="{{ route('jarimatika.tebak') }}" class="mode-btn group"
                            style="background-color: #8B5CF6; border-bottom: 8px solid #6d28d9;">
                            <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🧠</div>
                            <h3 class="text-xl font-black text-white drop-shadow-sm">Tebak Jari</h3>
                            <p class="text-purple-100 font-medium text-xs mt-1 px-2">Single Player (Tanpa Kamera)</p>
                        </a>

                        @php
                            $userLevel = auth()->user()->level ?? 1;
                            $canBattle = $userLevel >= 5;
                        @endphp

                        @if ($canBattle)
                            <a href="{{ route('jarimatika.match') }}"
                                class="mode-btn mode-battle group relative overflow-hidden hover:shadow-lg transition-all">
                                <div
                                    class="absolute top-2 right-[-20px] bg-red-600 text-white text-xs font-black uppercase tracking-wider py-1 px-8 rotate-45 shadow-md">
                                    HOT!</div>

                                <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">⚔️</div>
                                <h3 class="text-xl font-black text-white drop-shadow-sm">Battle</h3>
                                <p class="text-orange-100 font-medium text-xs mt-1 px-2">1 vs 1 Matchmaking</p>
                            </a>

                            <a href="{{ route('jarimatika.match.hitung') }}"
                                class="mode-btn group relative overflow-hidden hover:shadow-lg transition-all bg-gradient-to-br from-purple-500 to-purple-600">
                                <div
                                    class="absolute top-2 right-[-20px] bg-purple-700 text-white text-xs font-black uppercase tracking-wider py-1 px-8 rotate-45 shadow-md">
                                    NEW!</div>

                                <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🧮</div>
                                <h3 class="text-xl font-black text-white drop-shadow-sm">Battle Hitung</h3>
                                <p class="text-purple-100 font-medium text-xs mt-1 px-2">Math Mode 1 vs 1</p>
                            </a>
                        @else
                            <button disabled
                                class="mode-btn bg-gray-400 cursor-not-allowed opacity-60 relative overflow-hidden transform hover:scale-100">
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-3xl">🔒
                                </div>

                                <div class="flex flex-col items-center justify-center h-full">
                                    <div class="text-3xl mb-2">⚔️</div>
                                    <h3 class="text-sm font-black text-white drop-shadow-sm">Battle</h3>
                                    <p class="text-gray-100 font-bold text-xs mt-1 px-1">Terbuka Level 5</p>
                                </div>
                            </button>

                            <button disabled
                                class="mode-btn bg-gray-400 cursor-not-allowed opacity-60 relative overflow-hidden transform hover:scale-100">
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-3xl">🔒
                                </div>

                                <div class="flex flex-col items-center justify-center h-full">
                                    <div class="text-3xl mb-2">🧮</div>
                                    <h3 class="text-sm font-black text-white drop-shadow-sm">Battle Hitung</h3>
                                    <p class="text-gray-100 font-bold text-xs mt-1 px-1">Terbuka Level 5</p>
                                </div>
                            </button>
                        @endif

                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">

                <div class="game-card p-6 border-b-[8px] border-[#FFE52A]">
                    <h2 class="text-slate-500 font-bold uppercase tracking-widest text-sm mb-4 flex items-center gap-2">
                        <span>📊</span> Status Kamu
                    </h2>

                    <div class="space-y-4">
                        <div
                            class="bg-slate-50 rounded-2xl p-4 flex items-center justify-between border-2 border-slate-100">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Level Saat Ini</p>
                                <p class="text-2xl font-black text-slate-800 mt-1">Lv. {{ auth()->user()->level ?? 1 }}
                                </p>
                            </div>
                            <div class="text-4xl">🎖️</div>
                        </div>

                        <div
                            class="bg-[#F4FCE3] rounded-2xl p-4 flex items-center justify-between border-2 border-[#E4F7C5]">
                            <div>
                                <p class="text-xs font-bold text-[#8fa040] uppercase tracking-widest">Total XP</p>
                                <p class="text-2xl font-black text-[#5e692a] mt-1">{{ auth()->user()->total_xp ?? 0 }}
                                </p>
                            </div>
                            <div class="text-4xl animate-pulse">⭐</div>
                        </div>

                        <div
                            class="bg-[#FFF9E6] rounded-2xl p-4 flex items-center justify-between border-2 border-[#FFEDAA]">
                            <div>
                                <p class="text-xs font-bold text-[#c8790f] uppercase tracking-widest">Saldo Koin</p>
                                <p class="text-2xl font-black text-[#9a5c0b] mt-1">{{ auth()->user()->koin ?? 0 }}</p>
                            </div>
                            <div class="text-4xl">💰</div>
                        </div>

                        <div
                            class="bg-[#FDF2F8] rounded-2xl p-4 flex items-center justify-between border-2 border-[#FBCFE8]">
                            <div>
                                <p class="text-xs font-bold text-[#9d174d] uppercase tracking-widest">🏆 Piala (Trophy)
                                </p>
                                <p class="text-2xl font-black text-[#be185d] mt-1">{{ auth()->user()->piala ?? 0 }}</p>
                            </div>
                            <div class="text-4xl animate-bounce">🏆</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <a href="{{ route('reward.leaderboard') }}"
                        class="btn-3d-yellow w-full block text-center py-4 rounded-2xl text-slate-800 font-bold text-lg flex justify-center items-center gap-2">
                        <span>🏆</span> Papan Peringkat
                    </a>

                    <a href="{{ route('shop') }}"
                        class="btn-3d-yellow w-full block text-center py-4 rounded-2xl text-slate-800 font-bold text-lg flex justify-center items-center gap-2 transition hover:shadow-lg active:border-b-0 active:translate-y-[6px]">
                        <span>🛒</span> Toko Avatar
                    </a>
                </div>

            </div>
        </div>
    </div>

</x-app-layout>
