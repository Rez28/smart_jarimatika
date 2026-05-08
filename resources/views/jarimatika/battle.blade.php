@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            background-image: radial-gradient(#CF0F0F 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        /* === FIX: Canvas tidak di-mirror, hanya video yang mirror === */
        .output_canvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: none !important;
        }

        .input_video {
            transform: scaleX(-1);
            /* Video cermin alami */
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* =========================================== */

        /* Game Cards & 3D Buttons */
        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .btn-3d-green {
            background-color: #BBCB64;
            border-bottom: 6px solid #8fa040;
            transition: all 0.15s ease;
        }

        .btn-3d-green:active:not(:disabled) {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .btn-3d-green:disabled {
            background-color: #cbd5e1;
            border-bottom: 6px solid #94a3b8;
            cursor: not-allowed;
        }

        .btn-3d-red {
            background-color: #EF4444;
            /* Warna merah (Tailwind red-500) */
            border-bottom: 6px solid #B91C1C;
            /* Warna merah gelap (Tailwind red-700) */
            transition: all 0.15s ease;
        }

        .btn-3d-red:active {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .btn-3d-orange {
            background-color: #F79A19;
            border-bottom: 6px solid #c8790f;
            transition: all 0.15s ease;
        }

        .btn-3d-orange:active:not(:disabled) {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .btn-3d-orange:disabled {
            background-color: #cbd5e1;
            border-bottom: 6px solid #94a3b8;
            cursor: not-allowed;
        }

        #battle-log::-webkit-scrollbar {
            width: 8px;
        }

        #battle-log::-webkit-scrollbar-thumb {
            background: #38BDF8;
            border-radius: 10px;
        }

        /* Auto-Confirm System Styles */
        .btn-holding {
            background-color: #FBBF24 !important;
            border-bottom: 6px solid #D97706 !important;
            animation: pulse 0.6s ease-in-out infinite;
        }

        .btn-correct {
            background-color: #4ADE80 !important;
            border-bottom: 6px solid #22C55E !important;
            animation: bounce 0.4s ease-out;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        @keyframes bounce {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>

    <div id="battle-screen" data-game-id="{{ $gameId }}" data-user-id="{{ auth()->id() }}"
        data-user-name="{{ auth()->user()->name }}" data-pusher-key="{{ config('broadcasting.connections.pusher.key', '') }}"
        data-pusher-cluster="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}"
        data-score-url="{{ route('jarimatika.battle.score') }}" class="jarimatika-container py-6 px-4 sm:px-6 relative">

        <!-- Big Countdown Overlay: Full-screen backdrop dengan animasi -->
        <div id="big-countdown-overlay"
            class="fixed inset-0 hidden flex items-center justify-center z-[9999] pointer-events-auto">
            <!-- Dark backdrop dengan blur -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300"></div>

            <!-- Countdown text -->
            <div class="relative z-[10000] text-center">
                <p id="big-countdown-text"
                    class="text-9xl md:text-10xl font-black text-white drop-shadow-[0_8px_16px_rgba(0,0,0,0.4)] animate-pulse">
                    3
                </p>
            </div>
        </div>

        <div class="max-w-[1400px] mx-auto">

            <div
                class="game-card mb-6 px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-[8px] border-[#CF0F0F]">
                <div class="flex items-center gap-3">
                    <span
                        class="bg-[#CF0F0F] text-white px-4 py-2 rounded-xl text-lg font-bold uppercase tracking-wider shadow-sm">
                        🔥 Arena Battle
                    </span>
                    <span class="text-slate-600 font-bold text-lg">Room: #{{ $gameId }}</span>
                </div>
                <div class="flex items-center gap-2 bg-slate-100 px-4 py-2 rounded-xl border-2 border-slate-200">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-bold text-slate-600 text-sm">Real-Time Server</span>
                </div>
                <button id="btn-camera-switch" type="button"
                    class="ml-3 px-4 py-2 bg-[#38BDF8] text-white rounded-xl font-bold hover:bg-[#0ea5e9] transition-all">Nyalakan
                    Kamera</button>
            </div>

            <main class="grid grid-cols-1 lg:grid-cols-[1fr_auto_1fr] gap-6 items-start">

                <section class="flex flex-col gap-6">
                    <div class="game-card p-4 md:p-6 border-b-[8px] border-[#38BDF8] relative flex flex-col">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-black text-[#38BDF8]">Kamera Kamu</h2>
                            <span class="bg-[#38BDF8] text-white text-xs font-bold px-3 py-1 rounded-full">Player 1</span>
                        </div>

                        <!-- PERBAIKAN: Video disembunyikan pakai opacity-0 HANYA sebagai source -->
                        <div
                            class="relative w-full bg-slate-900 rounded-2xl overflow-hidden border-4 border-[#38BDF8] aspect-video shadow-inner">
                            <video class="input_video absolute inset-0 opacity-0 pointer-events-none" autoplay muted
                                playsinline></video>
                            <canvas class="output_canvas w-full h-full object-contain absolute inset-0"></canvas>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 border-2 border-slate-200 rounded-2xl p-4 text-center">
                                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">Jari Terbaca</p>
                                <p id="detected-number" class="text-5xl font-black text-[#38BDF8] drop-shadow-sm mt-1">0</p>
                            </div>
                            <div class="bg-[#E0F2FE] border-2 border-[#BAE6FD] rounded-2xl p-4 text-center">
                                <p class="text-[#0369A1] font-bold uppercase tracking-widest text-xs">Skor Kamu</p>
                                <p id="user-score" class="text-5xl font-black text-[#0284C7] drop-shadow-sm mt-1">0</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="flex flex-col gap-4 lg:min-w-[300px] z-10 relative">
                    <div
                        class="game-card p-6 border-b-[8px] border-[#FFE52A] text-center bg-white shadow-xl relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-2 bg-[#FFE52A]"></div>
                        <p class="text-slate-500 font-bold uppercase tracking-widest text-sm mb-2">Target Angka</p>
                        <p id="target-number" class="text-[5rem] leading-none font-black text-slate-800 drop-shadow-md">?
                        </p>
                    </div>

                    <div class="game-card p-4 border-b-[6px] border-slate-800 text-center bg-slate-800">
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs mb-1">Sisa Waktu</p>
                        <p id="battle-timer" class="text-4xl font-black text-[#FFE52A] drop-shadow-md animate-pulse">20s</p>
                    </div>

                    <!-- Status Ready Indicator -->
                    <div class="game-card p-3 border-b-[4px] border-blue-300 text-center bg-blue-50">
                        <p class="text-slate-600 font-bold uppercase tracking-widest text-xs mb-1">Status Pemain</p>
                        <p id="ready-status" class="text-lg font-black text-blue-600">0/2 Siap</p>
                    </div>

                    <div class="flex flex-col gap-3 mt-2">
                        <button id="btn-start-battle" type="button"
                            class="btn-3d-green w-full py-4 text-xl font-bold text-white rounded-2xl uppercase tracking-wider">
                            ✋ Ready
                        </button>
                        <button id="btn-answer" type="button" disabled
                            class="btn-3d-orange w-full py-4 text-xl font-bold text-white rounded-2xl uppercase tracking-wider hidden">
                            Konfirmasi Jari
                        </button>
                    </div>
                </section>

                <section class="flex flex-col gap-6">
                    <div class="game-card p-4 md:p-6 border-b-[8px] border-[#F79A19] relative flex flex-col">
                        <div class="flex justify-between items-center mb-4">
                            <span class="bg-[#F79A19] text-white text-xs font-bold px-3 py-1 rounded-full">Player 2</span>
                            <h2 id="opponent-name-display" class="text-2xl font-black text-[#F79A19]">Lawanmu</h2>
                        </div>

                        <div
                            class="relative w-full bg-slate-100 rounded-2xl border-4 border-slate-200 aspect-video flex flex-col items-center justify-center shadow-inner overflow-hidden">
                            <div class="absolute inset-0 opacity-10"
                                style="background-image: radial-gradient(#F79A19 2px, transparent 2px); background-size: 15px 15px;">
                            </div>

                            <video id="opponent-video" muted autoplay playsinline
                                class="absolute inset-0 w-full h-full object-cover hidden z-20"></video>

                            <div id="opponent-placeholder"
                                class="text-[5rem] relative z-10 transform transition-transform hover:scale-110">🤖</div>

                            <!-- PERBAIKAN: Tulisan Mencari Lawan sekarang punya ID khusus -->
                            <p id="opponent-status"
                                class="mt-4 text-lg font-bold text-slate-500 uppercase tracking-widest bg-white px-4 py-1 rounded-full shadow-sm relative z-10">
                                Menunggu lawan...</p>
                        </div>

                        <div class="mt-4 bg-[#FFEDD5] border-2 border-[#FED7AA] rounded-2xl p-4 text-center">
                            <p class="text-[#C2410C] font-bold uppercase tracking-widest text-xs">Skor Lawan</p>
                            <p id="opponent-score" class="text-5xl font-black text-[#EA580C] drop-shadow-sm mt-1">0</p>
                        </div>
                    </div>
                </section>
            </main>

            <!-- BALAPAN POIN (Tidak diubah) -->
            <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6 mt-6">
                <div class="game-card p-6 border-b-[8px] border-slate-300">
                    <h3 class="text-slate-500 font-bold uppercase tracking-widest text-sm mb-6 flex items-center gap-2">
                        <span>🏁</span> Balapan Poin
                    </h3>
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-[#38BDF8] uppercase tracking-wide">Kamu</span>
                            <span id="user-progress-text" class="font-bold text-slate-600">0 Poin</span>
                        </div>
                        <div class="h-6 bg-slate-100 rounded-full border-2 border-slate-200 overflow-hidden">
                            <div id="user-progress"
                                class="h-full bg-[#38BDF8] w-0 transition-all duration-500 rounded-full"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-[#F79A19] uppercase tracking-wide">Lawan</span>
                            <span id="opponent-progress-text" class="font-bold text-slate-600">0 Poin</span>
                        </div>
                        <div class="h-6 bg-slate-100 rounded-full border-2 border-slate-200 overflow-hidden">
                            <div id="opponent-progress"
                                class="h-full bg-[#F79A19] w-0 transition-all duration-500 rounded-full"></div>
                        </div>
                    </div>
                </div>

                <div class="game-card p-6 border-b-[8px] border-slate-300 flex flex-col">
                    <h3 class="text-slate-500 font-bold uppercase tracking-widest text-sm mb-4 flex items-center gap-2">
                        <span>📜</span> Log Pertandingan
                    </h3>
                    <div id="battle-log"
                        class="flex-grow h-40 overflow-y-auto bg-slate-50 border-2 border-slate-200 rounded-2xl p-4 text-sm font-medium text-slate-600 space-y-2">
                        <div class="text-slate-400 italic">Menunggu pertandingan dimulai...</div>
                    </div>
                    <p id="battle-result" class="mt-4 text-center text-xl font-black text-slate-800"></p>
                </div>
            </div>
            <button id="btn-exit-room" type="button"
                class="btn-3d-red w-full py-4 text-xl font-bold text-white rounded-2xl uppercase tracking-wider transition-all">
                Keluar
            </button>
        </div>
    </div>

    <!-- Modal Hasil Pertandingan -->
    <div id="battle-result-modal" class="hidden fixed inset-0 z-[100] bg-slate-900/80 flex items-center justify-center">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <h2 class="text-3xl font-black text-center text-slate-800 mb-6">Pertandingan Selesai!</h2>
            <p id="modal-final-score"
                class="text-center text-2xl font-bold text-slate-600 mb-8 py-4 bg-slate-50 rounded-2xl border-2 border-slate-200">
            </p>
            <div class="flex flex-col gap-4">
                <button id="btn-rematch" type="button"
                    class="btn-3d-green w-full py-3 text-lg font-bold text-white rounded-2xl uppercase tracking-wider">Main
                    Lagi</button>
                <a href="/dashboard"
                    class="btn-3d-orange w-full py-3 text-lg font-bold text-white rounded-2xl uppercase tracking-wider text-center hover:no-underline">Kembali
                    ke Lobi</a>
            </div>
        </div>
    </div>

    <!-- SCRIPT LIBRARY -->
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>

    <!-- SCRIPT APLIKASI -->
    <script src="{{ asset('js/jarimatika-core.js') }}"></script>
    <script src="{{ asset('js/jarimatika-battle-new.js') }}"></script>
@endsection
