@extends('layouts.app')

@section('content')
    @php
        $users = [
            ['rank' => 1, 'name' => 'Alya', 'xp' => 2560],
            ['rank' => 2, 'name' => 'Raka', 'xp' => 2320],
            ['rank' => 3, 'name' => 'Nina', 'xp' => 2175],
            ['rank' => 4, 'name' => 'Fajar', 'xp' => 1950],
            ['rank' => 5, 'name' => 'Dian', 'xp' => 1880],
            ['rank' => 6, 'name' => 'Bima', 'xp' => 1750],
            ['rank' => 7, 'name' => 'Sari', 'xp' => 1630],
            ['rank' => 8, 'name' => 'Tio', 'xp' => 1505],
            ['rank' => 9, 'name' => 'Maya', 'xp' => 1420],
            ['rank' => 10, 'name' => 'Rina', 'xp' => 1310],
        ];

        $podium = collect($users)
            ->whereIn('rank', [1, 2, 3])
            ->sortBy('rank');
        $currentUser = auth()->user();
    @endphp

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-leaderboard {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            /* Krem terang */
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
            padding-bottom: 100px;
            /* Ruang untuk sticky footer */
        }

        /* Game Cards Dasar */
        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        /* Kartu List Peringkat */
        .rank-row {
            background: white;
            border-radius: 20px;
            border-bottom: 6px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .rank-row:hover {
            transform: scale(1.02);
            border-color: #BBCB64;
            /* Highlight hijau saat hover */
        }
    </style>

    <div class="jarimatika-leaderboard py-8 px-4 sm:px-6">
        <div class="max-w-[1000px] mx-auto">

            <div class="text-center mb-12">
                <div
                    class="inline-block bg-[#FFE52A] text-slate-800 px-6 py-2 rounded-full text-lg font-black uppercase tracking-widest border-b-4 border-[#ccb622] mb-4 shadow-sm">
                    🏆 Hall of Fame
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-800 drop-shadow-sm mb-2">Papan Peringkat Global</h1>
                <p class="text-slate-500 font-semibold">Berkompetisi dengan {{ count($users) }} pemain lainnya!</p>
            </div>

            <div class="flex flex-col md:flex-row justify-center items-end gap-4 md:gap-6 mb-12 px-2">

                <div
                    class="order-2 md:order-1 w-full md:w-1/3 game-card border-b-[8px] border-slate-300 bg-slate-50 p-6 text-center relative md:h-56 flex flex-col justify-end">
                    <div
                        class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-200 text-slate-600 font-black text-xl w-12 h-12 flex items-center justify-center rounded-full border-4 border-white shadow-md">
                        2
                    </div>
                    <div class="text-5xl mb-2">🥈</div>
                    <h3 class="text-2xl font-black text-slate-800 truncate">{{ $podium->where('rank', 2)->first()['name'] }}
                    </h3>
                    <p class="text-[#38BDF8] font-bold text-lg mt-1">{{ $podium->where('rank', 2)->first()['xp'] }} XP</p>
                </div>

                <div
                    class="order-1 md:order-2 w-full md:w-1/3 game-card border-b-[12px] border-[#F79A19] bg-[#FFE52A] p-6 text-center relative z-10 md:h-72 flex flex-col justify-end transform md:-translate-y-4">
                    <div class="absolute -top-12 left-1/2 -translate-x-1/2 text-5xl animate-bounce">
                        👑
                    </div>
                    <div
                        class="absolute -top-4 left-1/2 -translate-x-1/2 bg-[#F79A19] text-white font-black text-2xl w-14 h-14 flex items-center justify-center rounded-full border-4 border-white shadow-lg z-20">
                        1
                    </div>
                    <div class="text-7xl mb-4">🥇</div>
                    <h3 class="text-3xl font-black text-slate-800 truncate">{{ $podium->where('rank', 1)->first()['name'] }}
                    </h3>
                    <p class="text-[#c8790f] font-black text-xl mt-1">{{ $podium->where('rank', 1)->first()['xp'] }} XP</p>
                </div>

                <div
                    class="order-3 md:order-3 w-full md:w-1/3 game-card border-b-[8px] border-orange-300 bg-orange-50 p-6 text-center relative md:h-48 flex flex-col justify-end">
                    <div
                        class="absolute -top-6 left-1/2 -translate-x-1/2 bg-orange-200 text-orange-700 font-black text-xl w-12 h-12 flex items-center justify-center rounded-full border-4 border-white shadow-md">
                        3
                    </div>
                    <div class="text-4xl mb-2">🥉</div>
                    <h3 class="text-xl font-black text-slate-800 truncate">{{ $podium->where('rank', 3)->first()['name'] }}
                    </h3>
                    <p class="text-[#F79A19] font-bold text-lg mt-1">{{ $podium->where('rank', 3)->first()['xp'] }} XP</p>
                </div>

            </div>

            <div class="game-card p-6 md:p-8 bg-white/90 backdrop-blur-sm">
                <div class="flex items-center justify-between mb-6 border-b-2 border-slate-100 pb-4">
                    <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                        <span>📜</span> Peringkat Selanjutnya
                    </h2>
                    <span
                        class="bg-slate-100 text-slate-500 font-bold px-4 py-1 rounded-full text-sm uppercase tracking-widest">Rank
                        4 - 10</span>
                </div>

                <div class="space-y-4">
                    @foreach ($users as $user)
                        @if ($user['rank'] > 3)
                            <div class="rank-row flex items-center justify-between p-4 md:px-6">
                                <div class="flex items-center gap-4 md:gap-6">
                                    <div
                                        class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-black text-slate-400 text-lg">
                                        #{{ $user['rank'] }}
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-full bg-[#38BDF8] text-white flex items-center justify-center font-black text-xl border-2 border-sky-200 hidden sm:flex">
                                            {{ strtoupper(substr($user['name'], 0, 1)) }}
                                        </div>
                                        <p class="font-bold text-slate-800 text-xl">{{ $user['name'] }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[#BBCB64] font-black text-xl md:text-2xl">{{ $user['xp'] }} <span
                                            class="text-sm text-slate-400">XP</span></p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <div
        class="fixed bottom-0 left-0 right-0 z-50 bg-[#38BDF8] border-t-[8px] border-[#0284C7] rounded-t-[32px] px-6 py-4 text-white shadow-[0_-10px_40px_rgba(2,132,199,0.3)]">
        <div class="mx-auto flex max-w-5xl flex-col sm:flex-row items-center justify-between gap-4 font-family-fredoka">

            <div class="flex items-center gap-4 text-center sm:text-left">
                <div
                    class="w-12 h-12 rounded-full bg-white text-[#38BDF8] flex items-center justify-center font-black text-xl border-4 border-[#8cd7f9]">
                    {{ strtoupper(substr($currentUser?->name ?? 'G', 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm text-sky-100 font-semibold uppercase tracking-widest">Posisi Kamu Saat Ini</p>
                    <p class="text-2xl font-black drop-shadow-sm">
                        {{ $currentUser?->name ?? 'Guest' }}
                        <span class="text-[#FFE52A] ml-2">
                            @if ($currentUser)
                                (#{{ $currentUser->rank ?? '–' }})
                            @endif
                        </span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="bg-white/20 px-4 py-2 rounded-2xl border-2 border-white/30 backdrop-blur-sm flex items-center gap-2">
                    <span class="text-2xl">⭐</span>
                    <span class="font-black text-xl">{{ $currentUser?->total_xp ?? 0 }} XP</span>
                </div>
                <div
                    class="bg-[#BBCB64] border-b-4 border-[#8fa040] px-4 py-2 rounded-2xl flex items-center gap-2 shadow-sm">
                    <span class="font-black text-lg">Level {{ $currentUser?->level ?? 1 }}</span>
                </div>
            </div>

        </div>
    </div>
@endsection
