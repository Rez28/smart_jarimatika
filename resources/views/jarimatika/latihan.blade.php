@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            background-image: radial-gradient(#F79A19 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        /* FIX: Canvas tidak di-mirror lagi, video saja yang mirror */
        .output_canvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: none !important; /* Pastikan tidak ada flip */
        }

        .input_video {
            transform: scaleX(-1); /* Hanya video yang mirror untuk natural movement */
        }

        /* Game Cards & 3D Buttons */
        .game-card {
            background: white;
            border-radius: 24px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .btn-3d-green {
            background-color: #BBCB64;
            border-bottom: 6px solid #8fa040;
            transition: all 0.15s ease;
        }

        .btn-3d-green:active {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .btn-3d-orange {
            background-color: #F79A19;
            border-bottom: 6px solid #c8790f;
            transition: all 0.15s ease;
        }

        .btn-3d-orange:active {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .btn-3d-blue {
            background-color: #38BDF8;
            border-bottom: 6px solid #0284C7;
            transition: all 0.15s ease;
        }

        .btn-3d-blue:active {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        /* Smooth animation untuk instruksi */
        .fade-in-text {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="jarimatika-container py-6 px-4 sm:px-6">
        <div class="max-w-[1400px] mx-auto">
            <div
                class="game-card mb-6 px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-[8px] border-[#38BDF8]">
                <div class="flex items-center gap-3">
                    <span class="bg-[#38BDF8] text-white px-4 py-2 rounded-xl text-lg font-bold uppercase tracking-wider">
                        Tahap <span id="ui-level">1</span>
                    </span>
                    <span id="level-desc" class="text-slate-600 font-bold text-lg">Penjumlahan Satuan</span>
                </div>

                <div class="flex-1 max-w-md mx-auto md:mx-0 flex items-center gap-4">
                    <div class="flex-1 h-6 bg-slate-200 rounded-full border-2 border-slate-300 overflow-hidden relative">
                        <div id="progress-bar"
                            class="absolute top-0 left-0 h-full bg-[#BBCB64] transition-all duration-500 w-0"></div>
                    </div>
                    <span class="font-black text-slate-700 text-xl whitespace-nowrap">
                        Soal <span id="ui-q-num" class="text-[#F79A19]">1</span>/5
                    </span>
                </div>
            </div>

            <main class="grid grid-cols-1 xl:grid-cols-[4fr_6fr] gap-6">

                <section class="flex flex-col gap-6">
                    <div
                        class="game-card p-6 md:p-10 border-b-[8px] border-[#F79A19] text-center flex-grow flex flex-col items-center justify-center min-h-[250px] relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-4 bg-[#F79A19]"></div>
                        <p class="text-slate-400 font-bold uppercase tracking-widest mb-2">Instruksi Guru</p>

                        <div id="instruction-display"
                            class="text-5xl md:text-6xl font-black text-slate-800 leading-tight mb-2 drop-shadow-sm">
                            Persiapan...
                        </div>

                        <p id="status-text" class="text-[#38BDF8] text-xl font-bold animate-pulse mt-4">
                            Menunggu Kamera...
                        </p>
                    </div>

                    <div class="game-card p-6 border-b-[8px] border-slate-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-slate-500 font-bold uppercase tracking-widest text-sm">Jari Terbaca:</h3>
                            <p class="text-sm text-slate-400">Pastikan masuk ke dalam frame</p>
                        </div>
                        <div id="user-result-display"
                            class="text-7xl font-black text-[#BBCB64] drop-shadow-md transition-all">
                            0
                        </div>
                    </div>
                </section>

                <section class="flex flex-col gap-6">
                    <div class="game-card p-4 md:p-6 border-b-[8px] border-[#BBCB64] relative flex-grow flex flex-col">

                        <div
                            class="relative w-full flex-grow bg-slate-900 rounded-2xl overflow-hidden border-4 border-slate-700 aspect-video shadow-inner">

                            <video class="input_video absolute inset-0 opacity-0 pointer-events-none" autoplay muted
                                playsinline></video>

                            <canvas class="output_canvas w-full h-full object-contain absolute inset-0"></canvas>

                            <div id="flash-effect"
                                class="absolute inset-0 bg-white pointer-events-none opacity-0 transition-opacity duration-200 z-10">
                            </div>

                            <div id="overlay-correct"
                                class="absolute inset-0 flex items-center justify-center bg-[#BBCB64]/80 backdrop-blur-sm opacity-0 transition-opacity pointer-events-none z-20">
                                <span class="text-[8rem] filter drop-shadow-lg" id="result-icon">✅</span>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                            <button id="btn-camera-toggle"
                                class="btn-3d-blue w-full md:w-auto px-8 py-3 rounded-2xl flex items-center justify-center gap-3">
                                <span id="cam-icon" class="text-2xl">🚫</span>
                                <span id="cam-text" class="font-bold text-white text-lg tracking-wide">Nyalakan
                                    Kamera</span>
                            </button>

                            <div id="gallery-list"
                                class="flex gap-2 overflow-x-auto h-16 w-full md:w-1/2 items-center justify-end">
                                <div class="text-slate-400 text-sm font-semibold italic">Riwayat Foto...</div>
                            </div>
                        </div>
                    </div>
                </section>

            </main>
        </div>

        <div id="start-overlay"
            class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-50 flex items-center justify-center px-4 cursor-pointer">
            <div
                class="bg-white p-10 rounded-[40px] border-b-[12px] border-[#38BDF8] max-w-lg w-full text-center shadow-2xl">
                <div class="text-[5rem] mb-4 animate-bounce">🧠</div>
                <h1 class="text-4xl font-black text-slate-800 mb-2">LATIHAN BERHITUNG</h1>
                <p class="text-lg text-slate-500 font-medium mb-8">Nyalakan kamera, dengarkan instruksi soalnya, dan jawab
                    dengan jarimu!</p>
                <div class="btn-3d-blue w-full py-4 text-2xl font-bold text-white rounded-2xl inline-block">
                    KLIK UNTUK MULAI
                </div>
            </div>
        </div>

        <div id="result-modal"
            class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-50 hidden items-center justify-center px-4">
            <div
                class="bg-white p-10 rounded-[40px] border-b-[12px] border-[#FFE52A] max-w-md w-full text-center shadow-2xl relative">
                <div class="text-[6rem] mb-2 absolute -top-16 left-1/2 transform -translate-x-1/2 animate-bounce">🏆</div>
                <h2 class="text-3xl font-black text-slate-800 mt-12 mb-2">Level Selesai!</h2>
                <p class="text-slate-500 font-bold uppercase tracking-widest mb-4">Total Skor Kamu</p>

                <div id="final-score" class="text-7xl font-black text-[#F79A19] mb-8 drop-shadow-md">
                    0
                </div>

                <div class="flex flex-col gap-4">
                    <button onclick="nextLevelAction()" id="btn-next-level"
                        class="btn-3d-green w-full py-4 text-xl font-bold text-white rounded-2xl">
                        Lanjut Level Berikutnya ➡️
                    </button>
                    <button onclick="restartLevel()"
                        class="btn-3d-orange w-full py-4 text-xl font-bold text-white rounded-2xl">
                        🔄 Ulangi Level Ini
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>

    <script src="{{ asset('js/jarimatika-core.js') }}"></script>
    <script src="{{ asset('js/jarimatika-latihan.js') }}"></script>
@endsection