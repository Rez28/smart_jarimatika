@extends('layouts.app')

@section('content')
    @include('components.navbar')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            background-image: radial-gradient(#F79A19 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        /* Canvas & Video */
        .output_canvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: none !important;
        }

        .input_video {
            transform: scaleX(-1);
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Navigation Buttons - Left Column */
        .nav-btn {
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1.125rem;
            transition: all 0.2s ease;
            border-bottom: 4px solid;
            cursor: pointer;
            margin-bottom: 8px;
        }

        .nav-btn.unlocked {
            background-color: #BBCB64;
            border-bottom-color: #8fa040;
            color: white;
        }

        .nav-btn.unlocked:active {
            transform: translateY(4px);
            border-bottom-width: 0px;
        }

        .nav-btn.locked {
            background-color: #cbd5e1;
            border-bottom-color: #94a3b8;
            color: #64748b;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .nav-btn:hover:not(.locked) {
            transform: translateY(-2px);
        }

        /* Orange Frame (Tutorial) */
        .frame-orange {
            background: white;
            border: 6px solid #F79A19;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .frame-orange h2 {
            font-size: 1.875rem;
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 24px;
            text-align: center;
        }

        .frame-orange .tutorial-placeholder {
            flex-grow: 1;
            border: 3px dashed #cbd5e1;
            border-radius: 16px;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            min-height: 200px;
        }

        .frame-orange .tutorial-placeholder p {
            color: #94a3b8;
            font-weight: 500;
            text-align: center;
        }

        .frame-orange .instruction-text {
            text-align: center;
            color: #64748b;
            font-size: 1rem;
            line-height: 1.5;
        }

        /* Purple Frame (Camera) */
        .frame-purple {
            background: white;
            border: 6px solid #8b5cf6;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .frame-purple .camera-indicator {
            text-align: center;
            margin-bottom: 20px;
        }

        .frame-purple .camera-indicator p {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .frame-purple .detected-number {
            font-size: 4rem;
            font-weight: 900;
            color: #8b5cf6;
            text-align: center;
        }

        .frame-purple .camera-container {
            flex-grow: 1;
            background: #1e293b;
            border-radius: 16px;
            overflow: hidden;
            margin: 20px 0;
            position: relative;
            aspect-ratio: 4/3;
            border: 3px solid #334155;
        }

        .frame-purple .camera-container canvas,
        .frame-purple .camera-container video {
            width: 100%;
            height: 100%;
        }

        .frame-purple .try-btn {
            background-color: #8b5cf6;
            border-bottom: 6px solid #6d28d9;
            color: white;
            padding: 16px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .frame-purple .try-btn:active {
            transform: translateY(4px);
            border-bottom-width: 0px;
        }

        .frame-purple .try-btn:hover {
            transform: translateY(-2px);
        }

        /* Responsive Grid */
        @media (max-width: 768px) {
            .grid-3-col {
                grid-template-columns: 1fr !important;
            }

            .col-span-2 {
                grid-column: span 1 !important;
            }

            .col-span-5 {
                grid-column: span 1 !important;
            }

            .frame-orange,
            .frame-purple {
                min-height: 400px;
            }
        }

        /* START OVERLAY */
        .start-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }

        .start-card {
            background: white;
            padding: 40px;
            border-radius: 40px;
            border-bottom: 12px solid #BBCB64;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .start-card .emoji {
            font-size: 5rem;
            margin-bottom: 16px;
            display: inline-block;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .start-card h1 {
            font-size: 2.25rem;
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 16px;
        }

        .start-card p {
            font-size: 1.125rem;
            color: #475569;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .start-card button {
            background-color: #BBCB64;
            border-bottom: 6px solid #8fa040;
            color: white;
            padding: 16px 24px;
            font-size: 1.5rem;
            font-weight: 900;
            border-radius: 16px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .start-card button:active {
            transform: translateY(6px);
            border-bottom-width: 0px;
        }

        /* INTRO BUTTON */
        .guide-btn {
            width: 100%;
            padding: 14px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.2s ease;
            border-bottom: 4px solid;
            cursor: pointer;
            margin-bottom: 12px;
            background-color: #8b5cf6;
            border-bottom-color: #6d28d9;
            color: white;
        }

        .guide-btn:active {
            transform: translateY(4px);
            border-bottom-width: 0px;
        }

        .guide-btn:hover {
            transform: translateY(-2px);
        }

        /* INTRO MODAL */
        .intro-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 40;
        }

        .intro-modal.show {
            display: flex;
        }

        .intro-card {
            background: white;
            padding: 40px;
            border-radius: 32px;
            border-bottom: 12px solid #8b5cf6;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .intro-card h2 {
            font-size: 2rem;
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 20px;
            text-align: center;
        }

        .intro-card .section {
            margin-bottom: 24px;
            padding: 16px;
            background: #f1f5f9;
            border-radius: 16px;
            border-left: 4px solid #8b5cf6;
        }

        .intro-card .section h3 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .intro-card .section p {
            font-size: 1rem;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .intro-card .finger-list {
            background: white;
            padding: 12px;
            border-radius: 12px;
            margin-top: 8px;
        }

        .intro-card .finger-list li {
            margin: 8px 0;
            color: #475569;
            font-weight: 500;
        }

        .intro-card .close-intro {
            width: 100%;
            padding: 16px;
            background-color: #8b5cf6;
            border-bottom: 6px solid #6d28d9;
            color: white;
            font-weight: 900;
            border-radius: 16px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 24px;
            transition: all 0.2s ease;
        }

        .intro-card .close-intro:active {
            transform: translateY(6px);
            border-bottom-width: 0px;
        }

        /* INSTRUCTION TEXT */
        .instruction-section {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 12px;
            margin-top: 16px;
        }

        .instruction-section .label {
            font-size: 0.875rem;
            font-weight: 700;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .instruction-section .text {
            font-size: 1rem;
            color: #78350f;
            line-height: 1.5;
            font-weight: 600;
        }
    </style>

    <div class="jarimatika-container py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- HEADER -->
            <header class="mb-8 p-6 bg-white rounded-2xl border-b-2 border-[#FFE52A]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-4xl">🎓</span>
                        <div>
                            <h1 class="text-2xl font-black text-slate-800">MODE BELAJAR</h1>
                            <p class="text-sm text-slate-500">Sequential Unlock • Angka 1-10</p>
                        </div>
                    </div>
                    <div class="hidden md:flex items-center gap-2 bg-slate-100 px-4 py-2 rounded-xl">
                        <span class="text-2xl">🔓</span>
                        <div>
                            <p class="text-xs text-slate-500">Terbuka sampai</p>
                            <p class="text-xl font-black text-[#BBCB64]"><span
                                    id="unlocked-display">{{ $unlockedNumber }}</span></p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- 3 COLUMN GRID LAYOUT -->
            <div class="grid gap-4 grid-3-col" style="grid-template-columns: 200px 1fr 1fr;">

                <!-- LEFT COLUMN: NAVIGATION BUTTONS (col-span-2 equivalent) -->
                <div class="col-span-2" style="grid-column: span 1;">
                    <div
                        style="background: white; border-radius: 24px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-height: calc(100vh - 200px); overflow-y: auto;">
                        <h3
                            style="font-size: 0.875rem; font-weight: 700; color: #64748b; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.05em;">
                            Pilih Angka</h3>

                        <!-- Panduan Awal Button -->
                        <button class="guide-btn" id="guide-btn" onclick="openIntroModal()">
                            📖 Panduan Awal
                        </button>

                        @for ($i = 1; $i <= 10; $i++)
                            <button class="nav-btn @if ($i <= $unlockedNumber) unlocked @else locked @endif"
                                data-number="{{ $i }}" @if ($i > $unlockedNumber) disabled @endif>
                                @if ($i <= $unlockedNumber)
                                    {{ $i }}
                                @else
                                    {{ $i }} 🔒
                                @endif
                            </button>
                        @endfor
                    </div>
                </div>

                <!-- MIDDLE COLUMN: ORANGE TUTORIAL FRAME (col-span-5) -->
                <div class="col-span-5" style="grid-column: span 1;">
                    <div class="frame-orange">
                        <h2>
                            Tutorial Angka <span id="tutorial-title">{{ $unlockedNumber }}</span>
                        </h2>

                        <div class="tutorial-placeholder relative">
                            <video id="tutorial-video" class="w-full h-full object-cover rounded-xl" autoplay loop muted
                                playsinline>
                                <source src="{{ asset('videos/' . $unlockedNumber . '.mp4') }}" type="video/mp4">
                            </video>
                            <p id="video-fallback"
                                class="hidden text-slate-500 font-semibold absolute inset-0 flex items-center justify-center">
                                Video belum tersedia 😅</p>
                        </div>

                        <div class="instruction-text">
                            <p>
                                Tekuk jari sesuai arahan untuk membentuk angka target 👆
                            </p>
                        </div>

                        <!-- Dynamic Instruction Section -->
                        <div id="instruction-section" class="instruction-section" style="display: none;">
                            <div class="label">📋 Instruksi Angka</div>
                            <div id="instruction-text" class="text"></div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: PURPLE CAMERA FRAME (col-span-5) -->
                <div class="col-span-5" style="grid-column: span 1;">
                    <div class="frame-purple">
                        <div class="camera-indicator">
                            <p>Jari Terbaca</p>
                            <div class="detected-number" id="detected-number">0</div>
                        </div>

                        <div class="camera-container">
                            <video class="input_video" autoplay muted playsinline></video>
                            <canvas class="output_canvas" width="640" height="480"></canvas>

                            <!-- Flash Effect -->
                            <div id="flash-effect"
                                style="position: absolute; inset: 0; background: white; opacity: 0; transition: opacity 0.2s; pointer-events: none;">
                            </div>

                            <!-- Success Feedback -->
                            <div id="feedback-overlay"
                                style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 20;">
                                <div
                                    style="background: rgba(255, 255, 255, 0.9); padding: 24px; border-radius: 50%; border: 8px solid #BBCB64; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); transform: scale(1.5);">
                                    <span style="font-size: 4rem; display: block;">⭐</span>
                                </div>
                            </div>
                        </div>

                        <button class="try-btn" id="try-btn">
                            Coba Praktekkan! 📷
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <!-- START OVERLAY -->
        <div id="start-overlay" class="start-overlay" style="display: flex;">
            <div class="start-card">
                <div class="emoji">🚀</div>
                <h1>Selamat Datang!</h1>
                <p>
                    Angka 1 sudah terbuka. Sempurna dulu sebelum membuka angka berikutnya! 🔓
                </p>
                <button class="start-btn" onclick="closeStartOverlay()">
                    MULAI SEKARANG! 🎮
                </button>
            </div>
        </div>

    </div>

    <!-- INTRO MODAL -->
    <div id="intro-modal" class="intro-modal">
        <div class="intro-card">
            <h2>📖 Panduan Awal</h2>

            <img src="{{ asset('images/anatomi_jari.png') }}" alt="Anatomi Jari"
                class="w-full max-w-md mx-auto h-auto object-contain rounded-2xl mb-6 shadow-sm border-4 border-[#8B5CF6]">

            <div class="section">
                <h3>Nama-Nama Jari 👋</h3>
                <ul class="finger-list">
                    <li>🤞 <strong>Jari Telunjuk</strong> - jari kedua</li>
                    <li>🖐️ <strong>Jari Tengah</strong> - jari ketiga (terpanjang)</li>
                    <li>✋ <strong>Jari Manis</strong> - jari keempat</li>
                    <li>🤟 <strong>Jari Kelingking</strong> - jari kelima</li>
                </ul>
            </div>

            <div class="section">
                <h3>Konsep Jarimatika</h3>
                <p><strong>✊ Tangan Kanan = Satuan (1-9)</strong></p>
                <p style="margin-bottom: 12px;">Jari-jari tangan kanan mewakili angka dari 1 hingga 9.</p>
                <p><strong>✋ Tangan Kiri = Puluhan (10, 20, 30...)</strong></p>
                <p>Jari-jari tangan kiri mewakili puluhan. Setiap jari yang dibuka = 10 lebih banyak.</p>
            </div>

            <div class="section">
                <h3>Cara Bermain</h3>
                <p>1️⃣ Pilih angka dari menu di sebelah kiri</p>
                <p>2️⃣ Lihat video tutorial cara membentuk angka</p>
                <p>3️⃣ Ikuti instruksi yang diberikan</p>
                <p>4️⃣ Tunjukkan jarimu di depan kamera 📷</p>
                <p>5️⃣ Sistem akan mengenali dan memberi feedback ✅</p>
            </div>

            <button class="close-intro" onclick="closeIntroModal()">Mengerti! Mari Mulai 🚀</button>
        </div>
    </div>

    <!-- MediaPipe Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>

    <!-- Core & Game Logic -->
    <script src="{{ asset('js/jarimatika-core.js') }}"></script>
    <script src="{{ asset('js/jarimatika-game.js') }}"></script>

    <script>
        // Pass unlock data to JavaScript
        window.unlockedNumber = {{ $unlockedNumber }};
        window.updateProgressUrl = "{{ route('jarimatika.belajar.progress') }}";
        window.csrfToken = "{{ csrf_token() }}";

        // Data instruksi untuk setiap angka
        const numberInstructions = {
            1: "Tangan Kanan: Buka jari telunjuk, tutup jari lainnya. Telapak tangan menghadap ke depan.",
            2: "Tangan Kanan: Buka jari telunjuk dan jari tengah dalam bentuk 'V', tutup jari lainnya.",
            3: "Tangan Kanan: Buka jari telunjuk, jari tengah, dan jari manis. Tutup ibu jari dan kelingking.",
            4: "Tangan Kanan: Buka jari telunjuk, tengah, manis, dan kelingking. Tutup hanya ibu jari.",
            5: "Tangan Kanan: Buka semua jari (5 jari). Telapak tangan menghadap ke depan.",
            6: "Tangan Kiri: Buka jari telunjuk. Tangan Kanan: Buka semua jari. Total = 10 + 5 = 15... Awal angka puluhan!",
            7: "Tangan Kiri: Buka jari telunjuk. Tangan Kanan: Buka jari telunjuk dan tengah. Total = 10 + 2 = 12.",
            8: "Tangan Kiri: Buka jari telunjuk. Tangan Kanan: Buka jari telunjuk, tengah, dan manis. Total = 10 + 3 = 13.",
            9: "Tangan Kiri: Buka jari telunjuk. Tangan Kanan: Buka jari telunjuk, tengah, manis, dan kelingking. Total = 10 + 4 = 14.",
            10: "Tangan Kiri: Buka jari telunjuk dan tengah dalam bentuk 'V'. Tangan Kanan: Tutup semua jari (genggaman). Total = 20 + 0 = 20."
        };

        function closeStartOverlay() {
            const overlay = document.getElementById('start-overlay');
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.4s ease';
            setTimeout(() => overlay.style.display = 'none', 400);
        }

        function openIntroModal() {
            document.getElementById('intro-modal').classList.add('show');
        }

        function closeIntroModal() {
            document.getElementById('intro-modal').classList.remove('show');
        }

        // Update UI based on unlocked number
        document.addEventListener('DOMContentLoaded', function() {
            // Update detected number in real-time
            setInterval(function() {
                if (window.gameState && window.gameState.detectedNumber !== undefined) {
                    document.getElementById('detected-number').textContent = window.gameState
                        .detectedNumber;
                    document.getElementById('user-current-answer').textContent = window.gameState
                        .detectedNumber;
                }
            }, 150);

            // Navigation button click handlers
            document.querySelectorAll('.nav-btn.unlocked').forEach(btn => {
                btn.addEventListener('click', function() {
                    const number = this.dataset.number;
                    document.getElementById('tutorial-title').textContent = number;

                    // Update video source
                    const videoEl = document.getElementById('tutorial-video');
                    const fallbackEl = document.getElementById('video-fallback');
                    const instructionSection = document.getElementById('instruction-section');
                    const instructionText = document.getElementById('instruction-text');

                    videoEl.src = '/videos/' + number + '.mp4';
                    videoEl.load();

                    // Update instruction text
                    if (numberInstructions[number]) {
                        instructionText.textContent = numberInstructions[number];
                        instructionSection.style.display = 'block';
                    } else {
                        instructionSection.style.display = 'none';
                    }

                    // Handle video error (file not found)
                    videoEl.onerror = function() {
                        videoEl.classList.add('hidden');
                        fallbackEl.classList.remove('hidden');
                    };

                    // Handle video loaded successfully
                    videoEl.onloadeddata = function() {
                        videoEl.classList.remove('hidden');
                        fallbackEl.classList.add('hidden');
                        videoEl.play().catch(function(err) {
                            console.warn('Video play failed:', err);
                        });
                    };
                });
            });

            // Try button handler
            document.getElementById('try-btn').addEventListener('click', function() {
                if (typeof window.startCameraSystem === 'function') {
                    window.startCameraSystem();
                }
            });

            // Close intro modal when clicking outside
            document.getElementById('intro-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeIntroModal();
                }
            });
        });

        /**
         * Call this when user completes a number
         */
        async function completeNumber(number) {
            const response = await fetch(window.updateProgressUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                },
                body: JSON.stringify({
                    completed_number: number,
                }),
            });

            const data = await response.json();

            if (data.success) {
                window.unlockedNumber = data.highest_number_unlocked;
                document.getElementById('unlocked-display').textContent = data.highest_number_unlocked;

                // Update UI
                const nextBtn = document.querySelector(`[data-number="${data.highest_number_unlocked}"]`);
                if (nextBtn) {
                    nextBtn.classList.remove('locked');
                    nextBtn.classList.add('unlocked');
                    nextBtn.removeAttribute('disabled');
                    nextBtn.innerHTML = data.highest_number_unlocked;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Angka Terbuka! 🔓',
                    html: `<h2 style="font-size: 2rem; margin: 20px 0;">Angka ${data.highest_number_unlocked} Terbuka!</h2>`,
                    confirmButtonColor: '#BBCB64',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Bisa Skip!',
                    text: data.message,
                    confirmButtonColor: '#CF0F0F',
                });
            }
        }
    </script>

    <!-- MediaPipe Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>

    <!-- Core & Game Logic -->
    <script src="{{ asset('js/jarimatika-core.js') }}"></script>
    <script src="{{ asset('js/jarimatika-game.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startOverlay = document.getElementById('start-overlay');
            const startBtn = startOverlay.querySelector('.start-btn');
            const userAnswer = document.getElementById('user-current-answer');
            const feedbackOverlay = document.getElementById('feedback-overlay');
            const btnNextLevel = document.getElementById('btn-next-level');
            const camPlaceholder = document.getElementById('camera-off-placeholder');
            const btnCamToggle = document.getElementById('btn-camera-toggle');
            const elLessonContext = document.getElementById('lesson-context');
            const elLessonHint = document.getElementById('lesson-hint');

            // Close start overlay
            startBtn.addEventListener('click', function() {
                startOverlay.style.opacity = '0';
                startOverlay.style.transition = 'opacity 0.4s ease';
                setTimeout(() => startOverlay.classList.add('hidden'), 400);

                if (typeof speak === 'function') {
                    speak('Selamat datang di Mode Belajar!');
                }
            });

            // Hide camera placeholder when toggled
            btnCamToggle.addEventListener('click', function() {
                if (camPlaceholder) camPlaceholder.style.display = 'none';
            });

            // Snapshot Photo function
            window.snapPhoto = function(numberLabel) {
                const imageData = typeof window.captureHandSmart === 'function' ? window.captureHandSmart() :
                    null;
                if (!imageData) return;

                // Flash effect
                const elFlash = document.getElementById('flash-effect');
                if (elFlash) {
                    elFlash.style.opacity = 0.8;
                    setTimeout(() => elFlash.style.opacity = 0, 300);
                }

                // Feedback
                if (feedbackOverlay) {
                    feedbackOverlay.style.opacity = 1;
                    setTimeout(() => feedbackOverlay.style.opacity = 0, 800);
                }
            };

            // Real-time UI updates
            setInterval(function() {
                if (window.gameState) {
                    userAnswer.textContent = window.gameState.detectedNumber ?? 0;
                }
            }, 150);
        });
    </script>
@endsection
