/**
 * JARIMATIKA GAME - MODE BELAJAR (SEQUENTIAL UNLOCK)
 * Palette: Green #BBCB64, Yellow #FFE52A, Orange #F79A19, Red #CF0F0F
 * Fitur: Sequential Unlock 1-10, Hold Detection 2 Detik, Dynamic UI Update
 */

const GAME_CONFIG = {
    HOLD_DURATION: 2000,
    FEEDBACK_DELAY: 1500,
    HOLD_CHECK_INTERVAL: 50,
};

// ==========================================
// STATE MANAGEMENT - SEQUENTIAL UNLOCK
// ==========================================

let currentSelectedNumber = 1;       // Angka yang sedang dipilih (default: 1)
let highestUnlocked = 1;            // Angka tertinggi yang sudah dibuka (dari backend)
let isCameraActive = false;         // Apakah kamera sedang aktif
let isHolding = false;              // Apakah sedang menahan (hold counter)
let holdStartTime = 0;              // Waktu mulai hold
let isProcessing = false;           // Flag untuk mencegah double trigger

// Track evaluation data
window.evaluationData = {
    totalAttempts: 0,
    correctDetections: 0,
    logs: [],
};

// ==========================================
// DOM ELEMENTS
// ==========================================

// Header elements
const elUnlockedDisplay = document.getElementById('unlocked-display');

// Tutorial section (middle)
const elTutorialTitle = document.getElementById('tutorial-title');

// Camera section (right)
const elDetectedNumber = document.getElementById('detected-number');
const elUserCurrentAnswer = document.getElementById('user-current-answer');
const elTryBtn = document.getElementById('try-btn');
const elFlashEffect = document.getElementById('flash-effect');
const elFeedbackOverlay = document.getElementById('feedback-overlay');

// Sidebar navigation buttons
const navButtons = document.querySelectorAll('.nav-btn.unlocked');

// Start overlay
const elStartOverlay = document.getElementById('start-overlay');

// Audio SFX
const sfxCorrect = new Audio('/sounds/correct.mp3');

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('[BELAJAR] ✅ Mode Belajar initialized');

    // Ambil highestUnlocked dari global variable (passed dari Blade)
    highestUnlocked = window.unlockedNumber || 1;
    currentSelectedNumber = highestUnlocked; // Start dengan angka terbuka terakhir

    console.log(`[BELAJAR] 🔓 Highest Unlocked: ${highestUnlocked}`);
    console.log(`[BELAJAR] 📍 Current Selected: ${currentSelectedNumber}`);

    // Initialize event listeners
    initializeEventListeners();

    // Update tutorial title
    updateTutorialDisplay();

    // Start game loop
    startGameLoop();
});

// ==========================================
// EVENT LISTENERS SETUP
// ==========================================

function initializeEventListeners() {
    console.log('[BELAJAR] 🎮 Setting up event listeners...');

    // 1. SIDEBAR NAVIGATION BUTTONS (LEFT COLUMN)
    document.querySelectorAll('.nav-btn.unlocked').forEach(btn => {
        btn.addEventListener('click', function() {
            const number = parseInt(this.dataset.number);
            selectNumber(number);
        });
    });

    // 2. TRY BUTTON (RIGHT COLUMN - PURPLE FRAME)
    if (elTryBtn) {
        elTryBtn.addEventListener('click', function() {
            startCameraForPractice();
        });
    }

    // 3. CLOSE START OVERLAY
    const startBtn = document.querySelector('.start-btn');
    if (startBtn) {
        startBtn.addEventListener('click', function() {
            closeStartOverlay();
        });
    }

    console.log('[BELAJAR] ✅ Event listeners attached');
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function speak(text, callback) {
    if ("speechSynthesis" in window) {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = "id-ID";
        utterance.rate = 1.0;
        utterance.onend = () => {
            if (callback) callback();
        };
        window.speechSynthesis.speak(utterance);
    } else {
        if (callback) callback();
    }
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// ==========================================
// NUMBER SELECTION (SIDEBAR BUTTONS)
// ==========================================

/**
 * Saat user klik tombol angka di sidebar (yang tidak digembok)
 */
function selectNumber(number) {
    // Validasi: hanya bisa memilih angka yang sudah dibuka
    if (number > highestUnlocked) {
        console.warn(`[BELAJAR] ⚠️ Angka ${number} belum dibuka`);
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Bisa Dipilih',
            text: `Angka ${number} masih dikunci. Selesaikan angka ${highestUnlocked} terlebih dahulu!`,
            confirmButtonColor: '#F79A19',
        });
        return;
    }

    // Update state
    currentSelectedNumber = number;
    console.log(`[BELAJAR] 📍 Angka dipilih: ${currentSelectedNumber}`);

    // Update tutorial display
    updateTutorialDisplay();

    // Stop camera jika sedang aktif
    if (isCameraActive) {
        stopCamera();
    }

    // Reset hold state
    resetHoldState();
}

/**
 * Update tampilan tutorial saat angka berubah
 */
function updateTutorialDisplay() {
    if (elTutorialTitle) {
        elTutorialTitle.textContent = currentSelectedNumber;
    }
    console.log(`[BELAJAR] 📚 Tutorial diupdate untuk angka: ${currentSelectedNumber}`);
}

// ==========================================
// CAMERA CONTROL
// ==========================================

/**
 * Saat user klik tombol "Coba Praktekkan! 📷"
 */
function startCameraForPractice() {
    if (isCameraActive) {
        console.log('[BELAJAR] 📷 Kamera sudah aktif');
        return;
    }

    console.log(`[BELAJAR] 📷 Memulai kamera untuk angka: ${currentSelectedNumber}`);

    // Jalankan startCameraSystem dari jarimatika-core.js
    if (typeof window.startCameraSystem === 'function') {
        window.startCameraSystem();
        isCameraActive = true;
        console.log('[BELAJAR] ✅ Sistem kamera dimulai');
    } else {
        console.error('[BELAJAR] ❌ Fungsi startCameraSystem tidak ditemukan');
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal menghubungkan ke sistem kamera',
            confirmButtonColor: '#CF0F0F',
        });
    }
}

/**
 * Stop camera
 */
function stopCamera() {
    console.log('[BELAJAR] 📷 Menghentikan kamera');

    if (typeof window.stopCameraSystem === 'function') {
        window.stopCameraSystem();
        isCameraActive = false;
        console.log('[BELAJAR] ✅ Kamera dihentikan');
    }
}

// ==========================================
// HOLD STATE MANAGEMENT
// ==========================================

/**
 * Mulai count hold time
 */
function startHold() {
    if (isHolding) return;

    isHolding = true;
    holdStartTime = performance.now();
    console.log(`[BELAJAR] ⏱️ Hold dimulai untuk angka: ${currentSelectedNumber}`);
}

/**
 * Cek apakah hold sudah cukup lama (2 detik)
 */
function checkHoldDuration(currentTime) {
    if (!isHolding) return false;

    const elapsedTime = currentTime - holdStartTime;

    // Jika sudah 2 detik
    if (elapsedTime >= GAME_CONFIG.HOLD_DURATION) {
        return true; // Hold berhasil!
    }

    return false;
}

/**
 * Reset hold state
 */
function resetHoldState() {
    isHolding = false;
    holdStartTime = 0;
}

// ==========================================
// SUCCESS HANDLING
// ==========================================

/**
 * Saat user berhasil menahan angka selama 2 detik
 */
async function onCorrectAnswer() {
    if (isProcessing) return; // Prevent double trigger
    isProcessing = true;

    console.log(`[BELAJAR] ✅ JAWABAN BENAR untuk angka: ${currentSelectedNumber}`);

    // Update evaluation data
    window.evaluationData.totalAttempts++;
    window.evaluationData.correctDetections++;

    // 1. Play success sound
    playSuccessSound();

    // 2. Show visual feedback
    showSuccessFeedback();

    // 3. Stop camera otomatis
    stopCamera();

    // 4. Reset hold state
    resetHoldState();

    // 5. Delay sebelum lanjut (untuk UX yang lebih baik)
    await sleep(GAME_CONFIG.FEEDBACK_DELAY);

    // 6. Jika currentSelectedNumber == highestUnlocked, unlock next number
    if (currentSelectedNumber === highestUnlocked) {
        console.log(`[BELAJAR] 🔓 Mencoba membuka angka berikutnya...`);
        await unlockNextNumber(currentSelectedNumber);
    } else {
        // Jika belum unlock, hanya tampilkan congratulations
        showCongratulations(currentSelectedNumber);
    }

    isProcessing = false;
}

/**
 * Play success sound / TTS
 */
function playSuccessSound() {
    const praises = [
        "Hebat! 🌟",
        "Bagus sekali! 💪",
        "Luar biasa! 🎉",
        "Sempurna! 🏆",
        "Pintar! 👏",
    ];

    const randomPraise = praises[Math.floor(Math.random() * praises.length)];
    console.log(`[BELAJAR] 🔊 Pujian: ${randomPraise}`);

    // If speak function exists (TTS)
    if (typeof speak === 'function') {
        speak(randomPraise);
    }

    // Play SFX
    if (sfxCorrect) {
        sfxCorrect.play().catch(() => {});
    }
}

/**
 * Show visual feedback (flash + star)
 */
function showSuccessFeedback() {
    // Flash effect
    if (elFlashEffect) {
        elFlashEffect.style.opacity = '0.8';
        setTimeout(() => {
            elFlashEffect.style.opacity = '0';
        }, 300);
    }

    // Star feedback
    if (elFeedbackOverlay) {
        elFeedbackOverlay.style.opacity = '1';
        setTimeout(() => {
            elFeedbackOverlay.style.opacity = '0';
        }, 800);
    }

    console.log('[BELAJAR] ✨ Visual feedback ditampilkan');
}

/**
 * Show congratulations alert (SweetAlert2)
 */
function showCongratulations(number) {
    const praises = [
        "Hebat! 🌟",
        "Bagus sekali! 💪",
        "Luar biasa! 🎉",
        "Sempurna! 🏆",
    ];

    const randomPraise = praises[Math.floor(Math.random() * praises.length)];

    Swal.fire({
        icon: 'success',
        title: randomPraise,
        html: `<h2 style="font-size: 2.5rem; margin: 20px 0; color: #BBCB64;">Angka ${number}</h2>
               <p style="font-size: 1.125rem; color: #64748b;">Sempurna! Kamu bisa melanjutkan.</p>`,
        confirmButtonColor: '#BBCB64',
        confirmButtonText: 'OK',
    });

    console.log(`[BELAJAR] 🎉 Congratulations ditampilkan untuk angka: ${number}`);
}

/**
 * Unlock next number via API
 */
async function unlockNextNumber(completedNumber) {
    console.log(`[BELAJAR] 📡 Mengirim permintaan unlock untuk angka: ${completedNumber}`);

    try {
        const response = await fetch(window.updateProgressUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
            },
            body: JSON.stringify({
                completed_number: completedNumber,
            }),
        });

        const data = await response.json();

        if (data.success) {
            console.log(`[BELAJAR] ✅ Unlock berhasil! Angka berikutnya: ${data.highest_number_unlocked}`);

            // Update state
            highestUnlocked = data.highest_number_unlocked;
            window.unlockedNumber = data.highest_number_unlocked;

            // Update UI
            updateUnlockUI(data.highest_number_unlocked);

            // Show unlock alert
            showUnlockAlert(data.highest_number_unlocked);
        } else {
            console.error('[BELAJAR] ❌ Unlock gagal:', data.message);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Membuka',
                text: data.message,
                confirmButtonColor: '#CF0F0F',
            });
        }
    } catch (error) {
        console.error('[BELAJAR] ❌ Error unlock:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal menghubungi server',
            confirmButtonColor: '#CF0F0F',
        });
    }
}

/**
 * Update UI saat angka baru dibuka
 */
function updateUnlockUI(nextNumber) {
    console.log(`[BELAJAR] 🎨 Update UI untuk angka berikutnya: ${nextNumber}`);

    // Update header "Terbuka sampai"
    if (elUnlockedDisplay) {
        elUnlockedDisplay.textContent = nextNumber;
    }

    // Update sidebar button: ubah dari locked ke unlocked
    const nextBtn = document.querySelector(`[data-number="${nextNumber}"]`);
    if (nextBtn) {
        // Remove locked class
        nextBtn.classList.remove('locked');

        // Add unlocked class
        nextBtn.classList.add('unlocked');

        // Remove disabled attribute
        nextBtn.removeAttribute('disabled');

        // Update button text (remove lock icon)
        nextBtn.innerHTML = `${nextNumber}`;

        // Add click handler
        nextBtn.addEventListener('click', function() {
            selectNumber(nextNumber);
        });

        console.log(`[BELAJAR] ✅ Tombol ${nextNumber} dibuka di UI`);
    }
}

/**
 * Show unlock alert (SweetAlert2)
 */
function showUnlockAlert(nextNumber) {
    Swal.fire({
        icon: 'success',
        title: 'Angka Terbuka! 🔓',
        html: `<h2 style="font-size: 2.5rem; margin: 20px 0; color: #BBCB64;">Angka ${nextNumber}</h2>
               <p style="font-size: 1.125rem; color: #64748b;">Berhasil dibuka! Silakan lanjut ke angka berikutnya.</p>`,
        confirmButtonColor: '#BBCB64',
        confirmButtonText: 'Lanjutkan',
    }).then((result) => {
        if (result.isConfirmed) {
            // Automatically select next number
            selectNumber(nextNumber);
        }
    });

    console.log(`[BELAJAR] 🎉 Alert unlock ditampilkan untuk angka: ${nextNumber}`);
}

// ==========================================
// GAME LOOP (requestAnimationFrame)
// ==========================================

/**
 * Main game loop - deteksi tangan secara real-time
 */
function startGameLoop() {
    console.log('[BELAJAR] 🎮 Game loop dimulai');

    function gameLoop() {
        // Pastikan kamera aktif dan sudah ready
        if (!isCameraActive || !window.gameState || !window.gameState.isSystemReady) {
            requestAnimationFrame(gameLoop);
            return;
        }

        // Deteksi tangan saat ini
        const detectedNumber = window.gameState.detectedNumber || 0;

        // Update UI detected number secara real-time
        if (elDetectedNumber) {
            elDetectedNumber.textContent = detectedNumber;
        }
        if (elUserCurrentAnswer) {
            elUserCurrentAnswer.textContent = detectedNumber;
        }

        // Jika user sudah memulai hold
        if (isHolding && detectedNumber === currentSelectedNumber) {
            // Terus check apakah hold sudah cukup lama
            const currentTime = performance.now();
            if (checkHoldDuration(currentTime)) {
                // Hold berhasil!
                onCorrectAnswer();
                resetHoldState();
            }
        } else if (detectedNumber === currentSelectedNumber && !isHolding) {
            // Baru pertama kali detect angka yang benar
            console.log(`[BELAJAR] 🖐️ Deteksi angka yang benar: ${currentSelectedNumber}`);
            startHold();
        } else if (detectedNumber !== currentSelectedNumber) {
            // User menggerakkan jari (tidak sesuai)
            if (isHolding) {
                console.log(`[BELAJAR] ❌ Hold terputus. Diharapkan: ${currentSelectedNumber}, Terdeteksi: ${detectedNumber}`);
                resetHoldState();
            }
        }

        // Continue loop
        requestAnimationFrame(gameLoop);
    }

    // Start loop
    gameLoop();
}

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

/**
 * Close start overlay
 */
function closeStartOverlay() {
    const overlay = document.getElementById('start-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.4s ease';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 400);
    }

    console.log('[BELAJAR] 🎮 Start overlay ditutup');
}

// ==========================================
// LOGGING & EXPORT
// ==========================================

/**
 * Export evaluation data untuk logging
 */
window.exportEvaluationData = function() {
    if (window.evaluationData.totalAttempts > 0) {
        const accuracy = (
            (window.evaluationData.correctDetections / window.evaluationData.totalAttempts) * 100
        ).toFixed(2);
        console.log('[BELAJAR] 📊 DATA EVALUASI:', {
            totalAttempts: window.evaluationData.totalAttempts,
            correctDetections: window.evaluationData.correctDetections,
            accuracy: `${accuracy}%`,
        });
        return {
            totalAttempts: window.evaluationData.totalAttempts,
            correctDetections: window.evaluationData.correctDetections,
            accuracy,
        };
    }
};

console.log('[BELAJAR] ✅ jarimatika-game.js berhasil dimuat');
