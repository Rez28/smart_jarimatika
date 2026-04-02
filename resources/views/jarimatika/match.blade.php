@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            /* Krem terang */
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        /* Game Cards */
        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        /* Tombol 3D Batal (Merah) */
        .btn-3d-red {
            background-color: #CF0F0F;
            border-bottom: 6px solid #900b0b;
            transition: all 0.15s ease;
        }

        .btn-3d-red:active {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        /* Animasi Radar Pencarian */
        .radar-pulse {
            animation: ping-large 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes ping-large {

            75%,
            100% {
                transform: scale(1.8);
                opacity: 0;
            }
        }
    </style>

    <div class="jarimatika-container py-10 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto">

            <div class="text-center mb-10">
                <div
                    class="inline-block bg-[#F79A19] text-white px-6 py-2 rounded-full text-xl font-bold uppercase tracking-widest border-b-4 border-[#c8790f] mb-4 shadow-sm">
                    ⚔️ Mode Battle 1 vs 1
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-800 drop-shadow-sm">Mencari Penantang...</h1>
            </div>

            <div
                class="game-card border-b-[12px] border-[#38BDF8] p-8 md:p-12 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8 mb-8 z-10">

                <div class="flex-1 text-center w-full relative z-20">
                    <div
                        class="w-32 h-32 mx-auto bg-sky-100 rounded-full border-[8px] border-[#38BDF8] flex items-center justify-center text-6xl shadow-inner mb-4 relative z-20">
                        👦
                    </div>
                    <h2 class="text-3xl font-black text-slate-800">{{ $user->name }}</h2>
                    <p class="text-[#38BDF8] font-bold uppercase tracking-widest mt-1">Siap Bertanding</p>
                </div>

                <div class="flex-shrink-0 relative z-30 my-4 md:my-0">
                    <div
                        class="bg-[#FFE52A] text-slate-800 text-5xl font-black w-24 h-24 rounded-full flex items-center justify-center border-[6px] border-white shadow-xl rotate-12 transform hover:scale-110 transition-transform">
                        VS
                    </div>
                </div>

                <div class="flex-1 text-center w-full relative z-20">
                    <div
                        class="w-32 h-32 mx-auto bg-slate-100 rounded-full border-[8px] border-slate-300 flex items-center justify-center text-6xl shadow-inner mb-4 relative">
                        <span class="animate-bounce">❓</span>
                        <div class="absolute inset-0 rounded-full border-4 border-[#F79A19] opacity-75 radar-pulse z-[-1]">
                        </div>
                    </div>
                    <h2 id="queue-opponent" class="text-3xl font-black text-slate-400">Belum ada</h2>
                    <p id="queue-status" class="text-[#F79A19] font-bold uppercase tracking-widest mt-1 animate-pulse">
                        Menunggu lawan...</p>
                </div>

                <div class="absolute inset-0 opacity-5 pointer-events-none z-0"
                    style="background-image: repeating-linear-gradient(45deg, #000 0, #000 2px, transparent 2px, transparent 10px);">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="game-card p-6 border-b-[8px] border-[#BBCB64]">
                    <h3
                        class="text-slate-400 font-bold uppercase tracking-widest mb-4 border-b-2 border-slate-100 pb-2 flex items-center gap-2">
                        <span>📡</span> Status Sistem
                    </h3>
                    <div class="space-y-4">
                        <div
                            class="flex justify-between items-center bg-slate-50 p-3 rounded-2xl border-2 border-slate-100">
                            <span class="text-slate-600 font-semibold">Koneksi Server</span>
                            <span id="queue-connection"
                                class="bg-[#BBCB64] text-white px-3 py-1 rounded-xl text-sm font-bold shadow-sm">Terhubung</span>
                        </div>
                        <div
                            class="flex justify-between items-center bg-slate-50 p-3 rounded-2xl border-2 border-slate-100">
                            <span class="text-slate-600 font-semibold">Game ID</span>
                            <span id="queue-game-id"
                                class="font-mono font-bold text-slate-800 bg-slate-200 px-3 py-1 rounded-xl">-</span>
                        </div>
                        <div
                            class="flex justify-between items-center bg-slate-50 p-3 rounded-2xl border-2 border-slate-100">
                            <span class="text-slate-600 font-semibold">Info Antrean</span>
                            <span id="queue-hint" class="font-bold text-[#F79A19]">Menunggu...</span>
                        </div>
                    </div>
                </div>

                <div class="game-card p-6 border-b-[8px] border-slate-200 flex flex-col justify-center text-center">
                    <div class="text-4xl mb-4">⏱️</div>
                    <p class="text-slate-500 font-semibold mb-6">Sistem sedang mencocokkan kamu dengan pemain lain secara
                        otomatis. Harap tunggu sebentar...</p>
                    <button id="btn-cancel-search" type="button"
                        class="btn-3d-red w-full py-4 text-xl font-bold text-white rounded-2xl flex justify-center items-center gap-2">
                        <span>❌</span> Batal Mencari
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script src="{{ asset('js/jarimatika-matchmaking.js') }}"></script>
@endsection
