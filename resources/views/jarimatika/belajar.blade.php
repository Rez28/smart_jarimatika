@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap');

        .jarimatika-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            background-image: radial-gradient(#FFE52A 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        /* === FIX: Canvas tidak di-mirror, hanya video === */
        .output_canvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: none !important; /* Pastikan tidak ada flip CSS */
        }

        .input_video {
            transform: scaleX(-1); /* Hanya video yang mirror untuk natural movement */
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        /* =========================================== */

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

        .snapshot-card {
            border-radius: 16px;
            overflow: hidden;
            border: 4px solid #FFE52A;
            width: 100px;
            height: 100px;
            background-color: #f8fafc;
            flex-shrink: 0;
        }

        .snapshot-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Debug Panel */
        .debug-panel {
            position: fixed;
            bottom: 16px;
            right: 16px;
            background: rgba(0, 0, 0, 0.85);
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            font-family: monospace;
            font-size: 12px;
            z-index: 100;
            line-height: 1.6;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .debug-panel .label { color: #94a3b8; }
        .debug-panel .value-fps { color: #FFE52A; font-weight: bold; }
        .debug-panel .value-acc { color: #BBCB64; font-weight: bold; }
        .debug-panel .value-det { color: #38BDF8; font-weight: bold; }
        .debug-panel .value-low { color: #CF0F0F; }
    </style>

    <div class="jarimatika-container py-8 px-4 sm:px-6">
        <div class="max-w-6xl mx-auto">

            <header class="game-card mb-8 px-8 py-5 flex items-center justify-between border-b-[8px] border-[#FFE52A]">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFE52A] p-3 rounded-full shadow-sm text-3xl">🎓</div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800 tracking-wide">PENGENALAN ANGKA</h1>
                        <p class="text-slate-500 font-medium">Mari belajar bentuk jari 1 sampai 10!</p>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-3 bg-slate-100 px-5 py-2 rounded-2xl border-2 border-slate-200">
                    <span class="text-slate-500 font-semibold">Status:</span>
                    <span id="sub-instruction" class="font-bold text-[#F79A19]">Menunggu...</span>
                </div>
            </header>

            <main class="grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] gap-8">

                <section class="flex flex-col gap-6">
                    <div class="game-card p-4 border-b-[8px] border-[#BBCB64]">
                        <div
                            class="relative w-full aspect-[4/3] bg-slate-800 rounded-2xl overflow-hidden border-4 border-slate-700 shadow-inner">
                            <video class="input_video absolute inset-0 opacity-0 pointer-events-none" autoplay muted
                                playsinline></video>
                            <canvas class="output_canvas w-full h-full object-cover" width="640" height="480"></canvas>

                            <div id="flash-effect"
                                class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-200 z-10">
                            </div>

                            <div id="feedback-overlay"
                                class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300 z-20">
                                <div class="bg-white/90 p-6 rounded-full border-8 border-[#BBCB64] shadow-2xl scale-150">
                                    <span class="text-6xl">🌟</span>
                                </div>
                            </div>

                            <div id="camera-off-placeholder"
                                class="absolute inset-0 flex flex-col items-center justify-center bg-slate-800 text-slate-400 z-0">
                                <span class="text-6xl mb-4">📷</span>
                                <p class="font-semibold text-lg">Kamera belum menyala</p>
                            </div>
                        </div>

                        <div
                            class="mt-6 flex items-center justify-between bg-slate-50 p-4 rounded-2xl border-2 border-slate-100">
                            <button id="btn-camera-toggle"
                                class="btn-3d-orange px-6 py-3 rounded-2xl text-white font-bold flex items-center gap-2">
                                <span id="cam-icon" class="text-xl">🚫</span>
                                <span id="cam-text">Nyalakan Kamera</span>
                            </button>

                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Jari Terbaca</p>
                                <p class="text-4xl font-black text-[#BBCB64]" id="user-current-answer">0</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="flex flex-col gap-6">
                    <div
                        class="game-card p-8 border-b-[8px] border-[#38BDF8] text-center flex flex-col items-center justify-center min-h-[250px]">
                        <span
                            class="bg-[#38BDF8] text-white px-4 py-1 rounded-full text-sm font-bold uppercase tracking-widest mb-4 inline-block">Instruksi</span>
                        <h2 id="question-text" class="text-2xl font-bold text-slate-700 mb-2">Tunjukkan Angka</h2>
                        <div id="target-number" class="text-[6rem] leading-none font-black text-[#38BDF8] drop-shadow-md">
                            ?
                        </div>
                    </div>

                    <div class="game-card p-6 border-b-[8px] border-slate-200 flex-grow flex flex-col">
                        <h3 class="text-lg font-bold text-slate-700 mb-4 flex items-center gap-2">
                            <span>📸</span> Koleksi Foto Jarimu
                        </h3>

                        <div id="gallery-list"
                            class="flex gap-4 overflow-x-auto pb-4 mb-4 flex-grow items-start min-h-[120px]">
                            <div id="gallery-placeholder" class="w-full text-center text-slate-400 py-8 font-medium">
                                Foto angka yang benar akan muncul di sini!
                            </div>
                        </div>

                        <a href="{{ route('jarimatika.latihan') }}" id="btn-next-level"
                            class="hidden btn-3d-green w-full block text-center text-white text-xl font-bold rounded-2xl py-4 mt-auto">
                            Mulai Mode Latihan ➡️
                        </a>
                    </div>
                </section>
            </main>
        </div>

        <div id="start-overlay"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-md px-4">
            <div
                class="bg-white p-10 rounded-[40px] border-b-[12px] border-[#BBCB64] max-w-lg w-full text-center shadow-2xl relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#FFE52A] rounded-full opacity-50"></div>
                <div class="absolute -bottom-10 -left-10 w-24 h-24 bg-[#38BDF8] rounded-full opacity-50"></div>

                <div class="text-[5rem] mb-2 animate-bounce relative z-10">👋</div>
                <h1 class="text-4xl font-black text-slate-800 mb-4 relative z-10">Halo Teman!</h1>
                <p class="text-lg text-slate-600 font-medium mb-8 relative z-10">Di mode ini, kita akan belajar bagaimana
                    bentuk jari untuk angka 1 sampai 10. Sudah siap?</p>

                <button class="start-btn btn-3d-green w-full py-4 text-2xl font-bold text-white rounded-2xl relative z-10">
                    SIAP, MULAI! 🚀
                </button>
            </div>
        </div>

        <!-- [BARU] Debug Panel untuk Monitoring FPS & Akurasi -->
        <div class="debug-panel">
            <div><span class="label">🎬 FPS:</span> <span id="ui-fps" class="value-fps">0</span></div>
            <div><span class="label">🎯 Akurasi:</span> <span id="ui-acc" class="value-acc">0%</span></div>
            <div><span class="label">🔢 Deteksi:</span> <span id="ui-det" class="value-det">0</span></div>
            <div class="mt-2 pt-2 border-t border-white/20">
                <span class="label">💡 Tip:</span> Buka console untuk lihat log evaluasi
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>

    <script src="{{ asset('js/jarimatika-core.js') }}"></script>
    <script src="{{ asset('js/jarimatika-game.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startOverlay = document.getElementById('start-overlay');
            const startBtn = startOverlay.querySelector('.start-btn');
            const userAnswer = document.getElementById('user-current-answer');
            const galleryPlaceholder = document.getElementById('gallery-placeholder');
            const galleryList = document.getElementById('gallery-list');
            const feedbackOverlay = document.getElementById('feedback-overlay');
            const btnNextLevel = document.getElementById('btn-next-level');
            const camPlaceholder = document.getElementById('camera-off-placeholder');
            const btnCamToggle = document.getElementById('btn-camera-toggle');

            // [BARU] Elemen Debug Panel
            const elUiFps = document.getElementById('ui-fps');
            const elUiAcc = document.getElementById('ui-acc');
            const elUiDet = document.getElementById('ui-det');

            // Logic Tutup Overlay Mulai
            startBtn.addEventListener('click', function() {
                startOverlay.style.opacity = '0';
                startOverlay.style.transition = 'opacity 0.4s ease';
                setTimeout(() => startOverlay.classList.add('hidden'), 400);

                if (typeof speak === 'function') {
                    speak('Selamat datang! Silakan klik tombol nyalakan kamera.');
                }
            });

            // Sembunyikan placeholder kamera saat tombol kamera di-klik
            btnCamToggle.addEventListener('click', function() {
                if (camPlaceholder) camPlaceholder.style.display = 'none';
            });

            // Fungsi Simulasi Jepret Foto
            window.snapPhoto = function(numberLabel) {
                const imageData = typeof window.captureHandSmart === 'function' ? window.captureHandSmart() :
                    null;
                if (!imageData) return;

                // 1. Efek Flash Terang
                const elFlash = document.getElementById('flash-effect');
                elFlash.style.opacity = 0.8;
                setTimeout(() => elFlash.style.opacity = 0, 300);

                // 2. Hilangkan text placeholder
                if (galleryPlaceholder) galleryPlaceholder.style.display = 'none';

                // 3. Buat Kartu Foto Baru
                const card = document.createElement('div');
                card.className = 'snapshot-card relative group';
                card.innerHTML = `
                <img src="${imageData}" class="snapshot-img">
                <div class="absolute bottom-1 right-1 bg-[#38BDF8] text-white font-black text-sm w-6 h-6 flex items-center justify-center rounded-full border-2 border-white">
                    ${numberLabel}
                </div>
            `;
                galleryList.prepend(card);

                // 4. Efek Bintang Besar
                if (feedbackOverlay) {
                    feedbackOverlay.style.opacity = 1;
                    setTimeout(() => feedbackOverlay.style.opacity = 0, 800);
                }

                // 5. Tampilkan Tombol Lanjut
                if (btnNextLevel) {
                    btnNextLevel.classList.remove('hidden');
                }
            };

            // Loop untuk update UI secara real-time
            setInterval(function() {
                if (window.gameState) {
                    // Update angka deteksi
                    userAnswer.textContent = window.gameState.detectedNumber ?? 0;

                    // [BARU] Update Debug Panel - FPS
                    if (elUiFps && window.gameState.currentFps !== undefined) {
                        elUiFps.innerText = window.gameState.currentFps;
                        // Warning visual jika FPS rendah
                        if (window.gameState.currentFps < 20) {
                            elUiFps.classList.add('value-low');
                        } else {
                            elUiFps.classList.remove('value-low');
                        }
                    }

                    // [BARU] Update Debug Panel - Akurasi
                    if (elUiAcc && typeof window.evaluationData !== 'undefined') {
                        if (window.evaluationData.totalAttempts > 0) {
                            const acc = ((window.evaluationData.correctDetections / window.evaluationData
                                .totalAttempts) * 100).toFixed(1);
                            elUiAcc.innerText = acc + '%';
                        } else {
                            elUiAcc.innerText = '0%';
                        }
                    }

                    // [BARU] Update Debug Panel - Deteksi realtime
                    if (elUiDet) {
                        elUiDet.innerText = window.gameState.detectedNumber ?? 0;
                    }
                }
            }, 150);
        });
    </script>
@endsection
