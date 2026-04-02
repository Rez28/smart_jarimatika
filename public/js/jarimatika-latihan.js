/**
 * JARIMATIKA BERHITUNG LATIHAN (5 LEVEL) + CAMERA TOGGLE
 * Updated Colors: #BBCB64 (Green), #FFE52A (Yellow), #F79A19 (Orange), #CF0F0F (Red)
 */

const CONFIG = {
    stepDelay: 4000, // Waktu jeda untuk user mempraktekkan (4 detik)
    questionsPerLevel: 5,
};

// STATE VARIABLES
let currentLevel = 1;
let questionIndex = 1;
let currentScore = 0;
let finalAnswerKey = 0;
let sequenceQueue = [];
let isCheckingAnswer = false;
let hasLessonStarted = false; // Flag Penanda Game Dimulai
let isCameraActive = false; // Flag Status Kamera
let wrongTimer = null; // Timer untuk toleransi jawaban salah

// DOM ELEMENTS
const uiLevel = document.getElementById("ui-level");
const uiLevelDesc = document.getElementById("level-desc");
const uiQNum = document.getElementById("ui-q-num");
const uiProgressBar = document.getElementById("progress-bar");

const elInstruction = document.getElementById("instruction-display");
const elStatus = document.getElementById("status-text");
const elUserResult = document.getElementById("user-result-display");
const elOverlay = document.getElementById("overlay-correct");
const elResultIcon = document.getElementById("result-icon");
const elFlash = document.getElementById("flash-effect");
const elModal = document.getElementById("result-modal");
const elStartOverlay = document.getElementById("start-overlay");
const btnNextLevel = document.getElementById("btn-next-level");
const btnDashboard = document.getElementById("btn-dashboard");
const uiPotentialXp = document.getElementById("ui-potential-xp");
const elGallery = document.getElementById("gallery-list");

// CAMERA CONTROLS
const btnCamToggle = document.getElementById("btn-camera-toggle");
const txtCamText = document.getElementById("cam-text");
const txtCamIcon = document.getElementById("cam-icon");

// AUDIO
const sfxCorrect = new Audio("/sounds/correct.mp3");
const sfxWrong = new Audio("/sounds/wrong.mp3");

// --- HELPER FUNCTIONS ---

function speak(text, callback) {
    if ("speechSynthesis" in window) {
        window.speechSynthesis.cancel();
        const utt = new SpeechSynthesisUtterance(text);
        utt.lang = "id-ID";
        utt.rate = 1.0;
        utt.onend = () => {
            if (callback) callback();
        };
        window.speechSynthesis.speak(utt);
    } else {
        if (callback) setTimeout(callback, 1000);
    }
}

async function sendGamificationReward(score, accuracy = 100, baseXp = null) {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!tokenMeta) return;

    try {
        const response = await fetch('/api/gamification/reward', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': tokenMeta.content,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                score,
                accuracy,
                base_xp: baseXp,
            }),
        });

        if (!response.ok) {
            const errorPayload = await response.json().catch(() => null);
            console.warn('Gamification reward error:', errorPayload);
            return;
        }

        const result = await response.json();
        console.log('Reward dikirim:', result.data || result);
    } catch (error) {
        console.error('Gagal mengirim reward:', error);
    }
}

function rnd(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function getLevelName(lv) {
    switch (lv) {
        case 1:
            return "Penjumlahan Satuan (Kanan)";
        case 2:
            return "Penjumlahan Puluhan (Kiri)";
        case 3:
            return "Pengurangan Satuan";
        case 4:
            return "Pengurangan Puluhan";
        case 5:
            return "Operasi Campuran";
        default:
            return "";
    }
}

// ==========================================
// 1. GENERATOR SOAL BERANTAI (RNG)
// ==========================================
function generateSequence() {
    sequenceQueue = [];
    let currentVal = 0;

    // Update UI Header
    uiLevel.innerText = currentLevel;
    uiQNum.innerText = questionIndex;
    if (uiPotentialXp)
        uiPotentialXp.innerText = (CONFIG.questionsPerLevel - questionIndex + 1) * 10;
    if (uiLevelDesc) uiLevelDesc.innerText = getLevelName(currentLevel);

    let progress = ((questionIndex - 1) / CONFIG.questionsPerLevel) * 100;
    uiProgressBar.style.width = `${progress}%`;

    // Reset Tampilan Status
    elUserResult.style.opacity = "0";
    elOverlay.style.opacity = "0";

    // Warna Kuning Palet (#FFE52A) untuk status standby
    elStatus.innerText = "Dengarkan...";
    elStatus.className = "text-[#FFE52A] text-xl font-bold animate-pulse";

    // --- LOGIKA LEVEL ---
    let startNum = 0;

    // STEP 1: INISIALISASI ANGKA AWAL
    if (currentLevel === 1) startNum = rnd(1, 4);
    else if (currentLevel === 2) startNum = rnd(1, 4) * 10;
    else if (currentLevel === 3) startNum = rnd(6, 9);
    else if (currentLevel === 4) startNum = rnd(6, 9) * 10;
    else if (currentLevel === 5) startNum = rnd(5, 8);

    currentVal = startNum;

    let handHint =
        currentLevel === 2 || currentLevel === 4
            ? "Tangan Kiri"
            : "Tangan Kanan";
    if (currentLevel === 5) handHint = "Siapkan dua tangan";

    sequenceQueue.push({
        text: `Buka jari ${startNum}. (${handHint})`,
        val: currentVal,
    });

    // STEP 2 & 3: OPERASI MATEMATIKA
    for (let i = 0; i < 2; i++) {
        let op = "+";
        let val = 0;

        if (currentLevel <= 2) op = "+";
        else if (currentLevel <= 4) op = "-";
        else op = Math.random() > 0.5 ? "+" : "-";

        if (currentLevel === 1 || currentLevel === 3) val = rnd(1, 3);
        else if (currentLevel === 2 || currentLevel === 4) val = rnd(1, 3) * 10;
        else val = Math.random() > 0.5 ? rnd(1, 3) : rnd(1, 3) * 10;

        // Constraint Check (Cek Batasan 0 - 99)
        if (op === "+") {
            if (
                currentVal + val > 99 ||
                (currentLevel === 1 && currentVal + val > 9)
            ) {
                // Jika melanggar batas atas
                if (currentLevel === 5) {
                    op = "-";
                    currentVal -= val;
                } else {
                    val = 0; // Skip
                }
            } else {
                currentVal += val;
            }
        } else {
            // Operator Kurang
            if (currentVal - val < 0) {
                // Jika melanggar batas bawah (minus)
                op = "+";
                currentVal += val;
            } else {
                currentVal -= val;
            }
        }

        if (val > 0) {
            let opText = op === "+" ? "Tambahkan" : "Kurangi";
            sequenceQueue.push({
                text: `${opText} ${val} jari.`,
                val: currentVal,
            });
        }
    }

    finalAnswerKey = currentVal;

    // Step Akhir
    sequenceQueue.push({
        text: "Berapa totalnya? Tahan jarimu.",
        type: "final",
    });

    processSequence();
}

// ==========================================
// 2. EKSEKUTOR INSTRUKSI
// ==========================================
async function processSequence() {
    isCheckingAnswer = false;

    for (let i = 0; i < sequenceQueue.length; i++) {
        // Pause instruksi jika kamera dimatikan user
        while (!isCameraActive) {
            await new Promise((resolve) => setTimeout(resolve, 500));
        }

        const step = sequenceQueue[i];

        // Tampilkan Teks dengan animasi
        elInstruction.innerText = step.text;
        elInstruction.classList.remove("fade-in-text");
        void elInstruction.offsetWidth;
        elInstruction.classList.add("fade-in-text");

        // Suara TTS
        await new Promise((resolve) => speak(step.text, resolve));

        if (step.type === "final") {
            elStatus.innerText = "Mendeteksi jawaban...";
            // Warna Oranye Palet (#F79A19) saat mendeteksi
            elStatus.className =
                "text-[#F79A19] text-xl font-bold animate-pulse";
            isCheckingAnswer = true;
            return;
        }

        // Jeda waktu berfikir (4 Detik)
        await new Promise((resolve) => setTimeout(resolve, CONFIG.stepDelay));
    }
}

// ==========================================
// 3. VALIDASI JAWABAN & SNAPSHOT
// ==========================================
function checkFinalAnswer() {
    if (!isCheckingAnswer || !window.gameState.isSystemReady) return;

    const detected = window.gameState.detectedNumber;

    // Tampilkan angka real-time kecil
    if (detected > 0) elStatus.innerText = `Terdeteksi: ${detected}`;

    if (detected === finalAnswerKey) {
        snapAndFinish(true);
    } else {
        // Logika Timeout jika salah terus selama 6 detik
        if (!wrongTimer) {
            wrongTimer = setTimeout(() => {
                if (isCheckingAnswer) snapAndFinish(false);
                wrongTimer = null;
            }, 6000);
        }
    }
}

function snapAndFinish(isCorrect) {
    isCheckingAnswer = false;
    clearTimeout(wrongTimer);
    wrongTimer = null;

    // --- FITUR PHOTOBOOTH (Hanya jika benar) ---
    if (isCorrect) {
        // Panggil fungsi crop smart dari core.js
        const imageData = window.captureHandSmart
            ? window.captureHandSmart()
            : null;

        if (imageData) {
            // Masukkan foto ke galeri strip di bawah
            const img = document.createElement("img");
            img.src = imageData;
            img.className =
                "h-full w-auto rounded-lg border-2 border-[#BBCB64] shadow-sm object-cover bg-white";

            // Hapus placeholder jika ada, lalu masukkan foto baru paling depan
            if (
                elGallery.children.length > 0 &&
                elGallery.children[0].innerText.includes("Hasil")
            ) {
                elGallery.innerHTML = "";
            }
            elGallery.prepend(img);
        }
    }

    // Flash Effect
    elFlash.style.opacity = "0.8";
    setTimeout(() => (elFlash.style.opacity = "0"), 150);

    // Tampilkan Angka Besar
    elUserResult.innerText = window.gameState.detectedNumber;
    elUserResult.style.opacity = "1";

    if (isCorrect) {
        // --- JAWABAN BENAR ---
        currentScore += 20;
        sendGamificationReward(10, 100, 10);
        sfxCorrect.play().catch(() => {});
        elResultIcon.innerText = "✅";

        elStatus.innerText = "BENAR! HEBAT!";
        // Warna Hijau Palet (#BBCB64)
        elStatus.className =
            "text-[#BBCB64] font-black text-3xl drop-shadow-sm";

        speak(`Benar! Jawabannya ${finalAnswerKey}.`);
    } else {
        // --- JAWABAN SALAH ---
        sfxWrong.play().catch(() => {});
        elResultIcon.innerText = "❌";

        elStatus.innerText = `SALAH! (Harusnya ${finalAnswerKey})`;
        // Warna Merah Palet (#CF0F0F)
        elStatus.className =
            "text-[#CF0F0F] font-black text-3xl drop-shadow-sm";

        speak(`Kurang tepat. Jawabannya adalah ${finalAnswerKey}.`);
    }

    elOverlay.style.opacity = "1";

    // Lanjut ke soal berikutnya setelah 4 detik
    setTimeout(() => {
        if (questionIndex >= CONFIG.questionsPerLevel) {
            finishLevel();
        } else {
            questionIndex++;
            generateSequence();
        }
    }, 4000);
}

// ==========================================
// 4. SISTEM LEVELING
// ==========================================
function finishLevel() {
    elModal.classList.remove("hidden");
    document.getElementById("final-level").innerText =
        currentLevel < 5 ? currentLevel + 1 : currentLevel;
    document.getElementById("final-xp").innerText = "+50";
    document.getElementById("final-coins").innerText = "+10";

    let praise = currentScore >= 80 ? "Hebat Sekali!" : "Latihan Lagi Ya!";
    speak(`Selesai Level ${currentLevel}. ${praise}`);

    btnDashboard.onclick = () => {
        window.location.href = "/dashboard";
    };

    if (currentLevel >= 5) {
        btnNextLevel.innerText = "Tamat (Menu Utama)";
        btnNextLevel.onclick = () => {
            window.location.href = "/dashboard";
        };
    } else {
        btnNextLevel.innerText = "Lanjut Level " + (currentLevel + 1);
        btnNextLevel.onclick = nextLevelAction;
    }
}

function nextLevelAction() {
    if (currentLevel >= 5) return;
    currentLevel++;
    restartGameParams();
    elModal.classList.add("hidden");
    generateSequence();
}

function restartLevel() {
    restartGameParams();
    elModal.classList.add("hidden");
    generateSequence();
}

function restartGameParams() {
    questionIndex = 1;
    currentScore = 0;
    uiProgressBar.style.width = "0%";
    if (uiLevelDesc) uiLevelDesc.innerText = getLevelName(currentLevel);
}

// ==========================================
// 5. MAIN LOOP & CONTROLS
// ==========================================

function gameLoop() {
    requestAnimationFrame(gameLoop);
    // Hanya proses deteksi jika kamera aktif
    if (isCameraActive) {
        checkFinalAnswer();
    }
}

// --- LOGIKA TOMBOL KAMERA (SAKLAR) ---
if (btnCamToggle) {
    // INIT STATE: OFF (MERAH #CF0F0F)
    btnCamToggle.classList.add("off");
    btnCamToggle.style.borderColor = "#CF0F0F";
    txtCamText.innerText = "KAMERA: OFF";
    txtCamText.style.color = "#CF0F0F";
    txtCamIcon.innerText = "🚫";

    btnCamToggle.addEventListener("click", () => {
        if (isCameraActive) {
            // --- MATIKAN KAMERA ---
            if (window.stopCameraSystem) window.stopCameraSystem();

            btnCamToggle.classList.remove("on");
            btnCamToggle.classList.add("off");

            // UI Merah
            btnCamToggle.style.borderColor = "#CF0F0F";
            txtCamText.innerText = "KAMERA: OFF";
            txtCamText.style.color = "#CF0F0F";
            txtCamIcon.innerText = "🚫";

            elStatus.innerText = "Kamera Nonaktif";
            elStatus.className = "text-gray-400 text-lg font-bold";
            isCameraActive = false;
        } else {
            // --- NYALAKAN KAMERA ---
            if (window.startCameraSystem) window.startCameraSystem();

            btnCamToggle.classList.remove("off");
            btnCamToggle.classList.add("on");

            // UI Hijau (#BBCB64) untuk tanda ON
            btnCamToggle.style.borderColor = "#BBCB64";
            txtCamText.innerText = "KAMERA: ON";
            txtCamText.style.color = "#BBCB64";
            txtCamIcon.innerText = "📹";

            elStatus.innerText = "Menunggu...";
            elStatus.className =
                "text-[#FFE52A] text-xl font-bold animate-pulse";
            isCameraActive = true;

            // TRIGGER MULAI GAME (Hanya saat pertama kali dinyalakan)
            if (!hasLessonStarted) {
                hasLessonStarted = true;
                setTimeout(() => {
                    speak(
                        "Kamera siap. Mari kita mulai latihan berhitung!",
                        () => {
                            generateSequence(); // Generate Soal No 1
                            gameLoop(); // Mulai Loop Deteksi
                        }
                    );
                }, 1500);
            } else {
                speak("Kamera aktif kembali.");
            }
        }
    });
}

// --- LOGIKA START OVERLAY ---
if (elStartOverlay) {
    elStartOverlay.addEventListener("click", () => {
        // Hilangkan Overlay Hitam
        elStartOverlay.style.opacity = "0";
        setTimeout(() => (elStartOverlay.style.display = "none"), 500);

        // Reset teks instruksi
        elInstruction.innerText = "Persiapan";
        elStatus.innerText = "Silakan Nyalakan Kamera...";

        // Animasi kedip pada tombol kamera agar user sadar
        if (btnCamToggle) {
            btnCamToggle.style.animation = "pulse 1.5s infinite";
            setTimeout(() => {
                btnCamToggle.style.animation = "";
            }, 5000);
        }

        speak("Selamat datang! Silakan tekan tombol kamera untuk memulai.");
    });
}
