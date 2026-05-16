<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧠 Tebak Jari - Jarimatika</title>

    <!-- Gunakan Vite bawaan Laravel (Menghilangkan warning CDN Tailwind) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fredoka Font -->
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            font-family: 'Fredoka', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
            background-attachment: fixed;
            position: relative;
        }

        /* Polka dot pattern background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle, rgba(56, 189, 248, 0.1) 2px, transparent 2px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        .tebak-container {
            position: relative;
            z-index: 1;
        }

        /* Header styles */
        .header-badge {
            background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);
            border-bottom: 4px solid #4C1D95;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        /* Soal card styles */
        .soal-card {
            background: white;
            border-radius: 24px;
            border: 4px solid #38BDF8;
            box-shadow: 0 8px 24px rgba(56, 189, 248, 0.2);
            transition: all 0.3s ease;
        }

        .soal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(56, 189, 248, 0.3);
        }

        /* Button 3D Green */
        .btn-3d-green {
            background: linear-gradient(135deg, #BBCB64 0%, #9DB847 100%);
            border: 3px solid #7A9633;
            border-bottom-width: 6px;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.1s ease;
            box-shadow: 0 6px 0 rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .btn-3d-green:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 0 rgba(0, 0, 0, 0.2);
        }

        .btn-3d-green:active {
            transform: translateY(4px);
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.2);
        }

        .btn-3d-green:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Shake animation (untuk jawaban salah) */
        @keyframes shake-red {

            0%,
            100% {
                transform: translateX(0);
                background: linear-gradient(135deg, #BBCB64 0%, #9DB847 100%);
            }

            25% {
                transform: translateX(-8px);
                background: #EF4444;
            }

            50% {
                transform: translateX(8px);
                background: #DC2626;
            }

            75% {
                transform: translateX(-8px);
                background: #EF4444;
            }
        }

        .shake-red {
            animation: shake-red 0.5s ease !important;
        }

        /* Flash green animation (untuk jawaban benar) */
        @keyframes flash-green {
            0% {
                background-color: transparent;
            }

            50% {
                background-color: rgba(187, 203, 100, 0.6);
            }

            100% {
                background-color: transparent;
            }
        }

        .flash-green {
            animation: flash-green 0.6s ease;
        }

        /* Screen flash (full page flash for correct answer) */
        @keyframes screen-flash {
            0% {
                background-color: transparent;
            }

            50% {
                background-color: rgba(187, 203, 100, 0.3);
            }

            100% {
                background-color: transparent;
            }
        }

        body.screen-flash {
            animation: screen-flash 0.6s ease;
        }

        /* Lives and score display */
        .stat-badge {
            background: white;
            border: 3px solid #38BDF8;
            border-radius: 12px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            display: inline-block;
        }

        .stat-badge span {
            font-size: 1.25rem;
        }

        /* Soal text styles */
        #soal-tangan {
            color: #3B82F6;
            font-weight: 700;
            font-size: 1.25rem;
        }

        /* Slide in animation */
        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-in {
            animation: slide-in 0.5s ease;
        }
    </style>
</head>

<body>
    <div class="tebak-container min-h-screen py-10 px-4 sm:px-6">
        <div class="max-w-2xl mx-auto">

            <!-- ==================== HEADER ==================== -->
            <div class="text-center mb-8">
                <div
                    class="header-badge inline-block text-white px-6 py-3 rounded-full text-2xl font-bold uppercase tracking-widest shadow-lg mb-4">
                    🧠 Tebak Jari
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-800 drop-shadow-sm mb-6">Mode Single Player</h1>

                <!-- Stats Row -->
                <div class="flex gap-4 justify-center flex-wrap">
                    <div class="stat-badge">
                        ❤️ Nyawa: <span id="lives">❤️❤️❤️</span>
                    </div>
                    <div class="stat-badge">
                        ⭐ Skor: <span id="score">0</span>
                    </div>
                </div>
            </div>

            <!-- ==================== SOAL CARD ==================== -->
            <div class="soal-card p-8 md:p-12 mb-8 text-center slide-in">
                <div id="soal-tangan" class="text-blue-500 font-bold text-xl mb-4">
                    Tebak angka...
                </div>
                <div id="soal-images" class="flex justify-center items-center gap-2 md:gap-4 slide-in">
                    <img id="img-kiri" src=""
                        class="w-32 h-32 md:w-48 md:h-48 object-contain bg-white/60 rounded-2xl border-2 border-slate-200 drop-shadow-md transition-transform transform hover:scale-105 hidden">
                    <img id="img-kanan" src=""
                        class="w-32 h-32 md:w-48 md:h-48 object-contain bg-white/60 rounded-2xl border-2 border-slate-200 drop-shadow-md transition-transform transform hover:scale-105 hidden">
                </div>

                <!-- ==================== ANSWER GRID (2x2) ==================== -->
                <div class="grid grid-cols-2 gap-4">
                    <button class="btn-3d-green" id="btn-0" onclick="cekJawaban(this.textContent)">
                        1
                    </button>
                    <button class="btn-3d-green" id="btn-1" onclick="cekJawaban(this.textContent)">
                        2
                    </button>
                    <button class="btn-3d-green" id="btn-2" onclick="cekJawaban(this.textContent)">
                        3
                    </button>
                    <button class="btn-3d-green" id="btn-3" onclick="cekJawaban(this.textContent)">
                        4
                    </button>
                </div>

            </div>
        </div>

        <!-- ==================== JAVASCRIPT LOGIC ==================== -->
        <script>
            // ==========================================
            // DATABASE JARI (Finger Database)
            // ==========================================
            const databaseJari = [];

            for (let i = 1; i <= 99; i++) {
                let jenisTangan = "";
                if (i >= 1 && i <= 9) {
                    jenisTangan = "Tangan Kanan (Satuan)";
                } else if (i % 10 === 0) {
                    jenisTangan = "Tangan Kiri (Puluhan)";
                } else if (i >= 11 && i <= 19) {
                    jenisTangan = "Dua Tangan (Belasan)";
                } else {
                    jenisTangan = "Dua Tangan (Puluhan & Satuan)";
                }

                databaseJari.push({
                    angka: i,
                    tangan: jenisTangan
                });
            }

            // ==========================================
            // STATE MANAGEMENT
            // ==========================================
            let currentScore = 0;
            let currentLives = 3;
            let maxLives = 3;
            let currentQuestion = null;
            let currentAnswers = [];
            let gameOver = false;

            // ==========================================
            // UTILITY FUNCTIONS
            // ==========================================

            /**
             * Update lives display
             */
            function updateLives() {
                const livesElement = document.getElementById('lives');
                let livesDisplay = '';
                for (let i = 0; i < currentLives; i++) {
                    livesDisplay += '❤️';
                }
                for (let i = currentLives; i < maxLives; i++) {
                    livesDisplay += '🖤';
                }
                livesElement.textContent = livesDisplay;
            }

            /**
             * Update score display
             */
            function updateScore() {
                document.getElementById('score').textContent = currentScore;
            }

            /**
             * Get random element from array
             */
            function getRandomElement(arr) {
                return arr[Math.floor(Math.random() * arr.length)];
            }

            /**
             * Generate random unique numbers
             */
            function getUniqueRandomNumbers(exclude, count, min = 1, max = 99) {
                const numbers = [];
                while (numbers.length < count) {
                    const num = Math.floor(Math.random() * (max - min + 1)) + min;
                    if (num !== exclude && !numbers.includes(num)) {
                        numbers.push(num);
                    }
                }
                return numbers;
            }

            /**
             * Shuffle array
             */
            function shuffleArray(arr) {
                return arr.sort(() => Math.random() - 0.5);
            }

            // ==========================================
            // GAME LOGIC FUNCTIONS
            // ==========================================

            /**
             * Generate soal (question)
             */
            function generateSoal() {
                if (gameOver) return;

                // Ambil 1 soal acak sebagai jawaban benar
                currentQuestion = getRandomElement(databaseJari);

                // Generate 3 angka salah
                const wrongAnswers = getUniqueRandomNumbers(currentQuestion.angka, 3, 1, 99);

                // Gabung dan acak posisi di 4 tombol
                currentAnswers = shuffleArray([currentQuestion.angka, ...wrongAnswers]);

                // Update UI dengan soal
                document.getElementById('soal-tangan').textContent = currentQuestion.tangan;

                // Update images
                const imgKiri = document.getElementById('img-kiri');
                const imgKanan = document.getElementById('img-kanan');
                imgKiri.classList.add('hidden');
                imgKanan.classList.add('hidden');

                const angka = currentQuestion.angka;

                if (angka >= 1 && angka <= 9) {
                    // Hanya satuan (Kanan)
                    imgKanan.src = '/images/jari/' + angka + '.png';
                    imgKanan.classList.remove('hidden');
                } else if (angka % 10 === 0) {
                    // Hanya puluhan (Kiri)
                    imgKiri.src = '/images/jari/' + angka + '.png';
                    imgKiri.classList.remove('hidden');
                } else {
                    // Gabungan Puluhan dan Satuan
                    const puluhan = Math.floor(angka / 10) * 10;
                    const satuan = angka % 10;

                    imgKiri.src = '/images/jari/' + puluhan + '.png';
                    imgKiri.classList.remove('hidden');

                    imgKanan.src = '/images/jari/' + satuan + '.png';
                    imgKanan.classList.remove('hidden');
                }

                // Update button texts
                document.getElementById('btn-0').textContent = currentAnswers[0];
                document.getElementById('btn-1').textContent = currentAnswers[1];
                document.getElementById('btn-2').textContent = currentAnswers[2];
                document.getElementById('btn-3').textContent = currentAnswers[3];

                // Reset button styles
                document.querySelectorAll('.btn-3d-green').forEach(btn => {
                    btn.classList.remove('shake-red', 'flash-green');
                    btn.disabled = false;
                });
            }

            /**
             * Check jawaban (answer)
             */
            function cekJawaban(jawaban) {
                if (gameOver || !currentQuestion) return;

                const jawabanAngka = parseInt(jawaban);
                const isCorrect = jawabanAngka === currentQuestion.angka;

                if (isCorrect) {
                    // ✅ JAWABAN BENAR
                    currentScore += 10;
                    updateScore();

                    // Flash green effect
                    document.body.classList.add('screen-flash');
                    setTimeout(() => {
                        document.body.classList.remove('screen-flash');
                    }, 600);

                    // Disable buttons
                    document.querySelectorAll('.btn-3d-green').forEach(btn => {
                        btn.disabled = true;
                    });

                    // Generate soal baru setelah delay
                    setTimeout(() => {
                        generateSoal();
                    }, 800);

                } else {
                    // ❌ JAWABAN SALAH
                    currentLives--;
                    updateLives();

                    // Shake animation pada tombol yang diklik
                    const buttons = document.querySelectorAll('.btn-3d-green');
                    buttons.forEach(btn => {
                        if (parseInt(btn.textContent) === jawabanAngka) {
                            btn.classList.add('shake-red');
                        }
                    });

                    // Remove animation class setelah selesai
                    setTimeout(() => {
                        buttons.forEach(btn => btn.classList.remove('shake-red'));
                    }, 500);

                    // Check game over
                    if (currentLives <= 0) {
                        endGame();
                    }
                }
            }

            /**
             * End game
             */
            function endGame() {
                gameOver = true;
                document.querySelectorAll('.btn-3d-green').forEach(btn => {
                    btn.disabled = true;
                });

                // Show SweetAlert2
                setTimeout(() => {
                    Swal.fire({
                        title: '🎮 Game Over!',
                        html: `<div class="text-2xl font-bold text-slate-800">
                        Total Skor: <span class="text-4xl text-purple-600">${currentScore}</span>
                    </div>`,
                        icon: 'warning',
                        confirmButtonText: '🏠 Kembali ke Dasbor',
                        confirmButtonColor: '#8B5CF6',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/dashboard';
                        }
                    });
                }, 500);
            }

            // ==========================================
            // INITIALIZATION
            // ==========================================
            document.addEventListener('DOMContentLoaded', () => {
                updateLives();
                updateScore();
                generateSoal();
                console.log('[TEBAK JARI] Game initialized with', databaseJari.length, 'questions');
            });
        </script>
</body>

</html>
