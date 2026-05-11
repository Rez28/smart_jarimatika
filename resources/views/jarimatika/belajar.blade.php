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

                        <div class="tutorial-placeholder">
                            <p style="font-size: 1.25rem;">
                                Animasi Jari akan muncul di sini ✨
                            </p>
                        </div>

                        <div class="instruction-text">
                            <p>
                                Tekuk jari sesuai arahan untuk membentuk angka target 👆
                            </p>
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

        function closeStartOverlay() {
            const overlay = document.getElementById('start-overlay');
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.4s ease';
            setTimeout(() => overlay.style.display = 'none', 400);
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
                    // Add logic to update tutorial content
                });
            });

            // Try button handler
            document.getElementById('try-btn').addEventListener('click', function() {
                if (typeof window.startCameraSystem === 'function') {
                    window.startCameraSystem();
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
