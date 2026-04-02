@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-shop {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            /* Krem terang */
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        /* Game Cards Dasar */
        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        /* 3D Buttons */
        .btn-3d-green {
            background-color: #BBCB64;
            border-bottom: 6px solid #8fa040;
            transition: all 0.15s ease;
        }

        .btn-3d-green:active:not(:disabled) {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .btn-3d-disabled {
            background-color: #cbd5e1;
            border-bottom: 6px solid #94a3b8;
            color: #64748b;
            cursor: not-allowed;
            transition: all 0.15s ease;
        }
    </style>

    <div class="jarimatika-shop py-8 px-4 sm:px-6">
        <div class="max-w-[1200px] mx-auto grid gap-8 xl:grid-cols-[350px_1fr] items-start">

            <aside
                class="game-card p-6 md:p-8 border-b-[8px] border-[#38BDF8] flex flex-col items-center text-center sticky top-8">

                <div
                    class="w-32 h-32 rounded-full bg-[#FFE52A] border-[6px] border-white shadow-lg flex items-center justify-center text-6xl font-black text-slate-800 mb-4 transform hover:scale-105 transition-transform">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <p class="text-sm font-bold uppercase tracking-widest text-[#38BDF8] mb-1">Penjelajah Angka</p>
                <h2 class="text-3xl font-black text-slate-800 mb-6">{{ $user->name }}</h2>

                <div class="w-full space-y-4 text-left">
                    <div class="bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 flex justify-between items-center">
                        <span class="text-slate-400 font-bold text-xs uppercase tracking-widest">Level Saat Ini</span>
                        <span class="text-2xl font-black text-slate-800">{{ $user->level }}</span>
                    </div>

                    <div class="bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 flex justify-between items-center">
                        <span class="text-slate-400 font-bold text-xs uppercase tracking-widest">Total XP</span>
                        <span class="text-2xl font-black text-[#BBCB64]">⭐ {{ $user->total_xp }}</span>
                    </div>

                    <div
                        class="bg-[#FFF9E6] border-2 border-[#FFE52A] rounded-2xl p-5 flex justify-between items-center transform scale-105 shadow-sm mt-6">
                        <span class="text-[#c8790f] font-bold text-sm uppercase tracking-widest">Dompet Koin</span>
                        <span class="text-3xl font-black text-[#F79A19]">💰 {{ $user->koin }}</span>
                    </div>
                </div>
            </aside>

            <main class="space-y-6">

                <section
                    class="game-card p-6 md:p-8 border-b-[8px] border-[#FFE52A] flex flex-col sm:flex-row items-center justify-between gap-6 bg-white overflow-hidden relative">
                    <div class="absolute -right-10 -bottom-10 text-[10rem] opacity-10 pointer-events-none">🛍️</div>
                    <div class="relative z-10 text-center sm:text-left">
                        <p class="text-sm font-bold uppercase tracking-widest text-[#F79A19] mb-1">Toko Virtual</p>
                        <h1 class="text-3xl md:text-4xl font-black text-slate-800">Etalase Item</h1>
                        <p class="mt-2 text-slate-500 font-medium">Beli avatar, bingkai, atau warna tema khusus menggunakan
                            koin hasil latihanmu!</p>
                    </div>
                </section>

                <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2">
                    @foreach ($items as $item)
                        @php
                            // Cek apakah user punya cukup koin
                            $canBuy = $user->koin >= $item['price'];
                        @endphp

                        <div
                            class="game-card p-6 border-b-[8px] {{ $canBuy ? 'border-[#BBCB64]' : 'border-slate-300 bg-slate-50' }} flex flex-col text-center relative group">

                            <div
                                class="absolute top-4 right-4 bg-[#FFE52A] text-slate-800 font-black px-4 py-1 rounded-full border-2 border-white shadow-sm z-10 flex items-center gap-1">
                                <span>💰</span> {{ $item['price'] }}
                            </div>

                            <div
                                class="h-32 flex items-center justify-center text-[5rem] mb-2 transform group-hover:scale-110 transition-transform duration-300 drop-shadow-md">
                                {{ $item['icon'] }}
                            </div>

                            <h2 class="text-2xl font-black text-slate-800 mb-2">{{ $item['name'] }}</h2>
                            <p class="text-sm text-slate-500 font-medium mb-6 flex-grow leading-relaxed">
                                {{ $item['description'] }}</p>

                            <button type="button"
                                class="w-full py-4 text-lg font-black rounded-2xl tracking-wide {{ $canBuy ? 'btn-3d-green text-white' : 'btn-3d-disabled' }}"
                                {{ $canBuy ? '' : 'disabled' }}>
                                {{ $canBuy ? 'Beli Item' : 'Koin Tidak Cukup' }}
                            </button>

                        </div>
                    @endforeach
                </section>

            </main>
        </div>
    </div>
@endsection
