/**
 * JARIMATIKA GAME LOGIC (PLAY MODE)
 * Palette: Green #BBCB64, Yellow #FFE52A, Orange #F79A19, Red #CF0F0F
 */

const GAME_CONFIG = {
    holdDuration: 2000,
    hintDelay: 4000,
};

const lessonsList = [
    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 20, 30, 40, 50, 60, 70, 80, 90,
];

let lessonIndex = 0;
let holdStartTime = 0;
let lastHintTime = 0;
let isHolding = false;
let isSpeaking = false;
let isTransitioning = false;
let isFinished = false;
let hasLessonStarted = false;

// DOM Elements
const elQuestionText = document.getElementById("question-text");
const elTargetNum = document.getElementById("target-number");
const elSubInstruction = document.getElementById("sub-instruction");
const elFeedback = document.getElementById("feedback-overlay");
const btnNextLevel = document.getElementById("btn-next-level");
const elStartOverlay = document.getElementById("start-overlay");

// Camera Buttons
const btnCamToggle = document.getElementById("btn-camera-toggle");
const txtCamText = document.getElementById("cam-text");
const txtCamIcon = document.getElementById("cam-icon");

const sfxCorrect = new Audio("/sounds/correct.mp3");

const specificHints = {
    1: "Telunjuk.",
    2: "Telunjuk dan Tengah.",
    3: "Telunjuk, Tengah, Manis.",
    4: "Empat jari.",
    5: "Jempol.",
    6: "Jempol dan Telunjuk.",
    7: "Jempol, Telunjuk, Tengah.",
    8: "Jempol - Manis.",
    9: "Semua jari.",
};

function speak(text, callback) {
    if ("speechSynthesis" in window) {
        window.speechSynthesis.cancel();
        isSpeaking = true;
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = "id-ID";
        utterance.rate = 1.0;
        utterance.onend = () => {
            isSpeaking = false;
            if (callback) callback();
        };
        window.speechSynthesis.speak(utterance);
    } else {
        if (callback) callback();
    }
}

// NOTE: Fungsi snapPhoto ada di file blade (dioverride)

function startLesson() {
    isTransitioning = false;
    isHolding = false;
    lastHintTime = Date.now() + 2000;

    if (lessonIndex >= lessonsList.length) {
        finishTutorial();
        return;
    }

    const currentTarget = lessonsList[lessonIndex];
    const isPuluhan = currentTarget >= 10;

    // UPDATE WARNA TARGET (Default Merah/Oranye)
    elTargetNum.style.color = "#CF0F0F"; // Merah
    elFeedback.style.opacity = "0";

    elTargetNum.innerText = currentTarget;
    let handSide = isPuluhan ? "Tangan KIRI" : "Tangan KANAN";
    let baseNum = isPuluhan ? currentTarget / 10 : currentTarget;
    let hintText = specificHints[baseNum] || "";

    elQuestionText.innerText = "Tunjukkan Angka:";
    elSubInstruction.innerText = `Gunakan ${handSide}`;
    elSubInstruction.style.color = "#F79A19"; // Oranye

    speak(
        `Tunjukkan jari ${hintText} untuk angka ${currentTarget}. Gunakan ${handSide}.`
    );
}

function nextLevel() {
    isTransitioning = true;

    // UPDATE WARNA SUKSES (Hijau)
    elTargetNum.style.color = "#BBCB64";
    elFeedback.style.opacity = "1";
    sfxCorrect.play().catch(() => {});

    const currentTarget = lessonsList[lessonIndex];

    // Panggil snapPhoto (di Blade)
    if (window.snapPhoto) window.snapPhoto(currentTarget);

    speak(`Hebat! Angka ${currentTarget} benar. Selanjutnya...`, () => {
        lessonIndex++;
        setTimeout(startLesson, 1500);
    });
}

function finishTutorial() {
    isFinished = true;
    elTargetNum.innerText = "🎉";
    elQuestionText.innerText = "SELESAI!";
    elSubInstruction.innerText = "Kamu Luar Biasa!";
    btnNextLevel.classList.remove("hidden");
    speak("Luar biasa! Kamu sudah menyelesaikan semua angka.");
}

function analyzeMistake() {
    const now = Date.now();
    if (now - lastHintTime < GAME_CONFIG.hintDelay) return;
    lastHintTime = now;
    // ... Logika hint tetap sama ...
}

// GAME LOOP
function gameLoop() {
    requestAnimationFrame(gameLoop);
    if (!hasLessonStarted || !isCameraActive) return;

    if (
        !window.gameState ||
        !window.gameState.isSystemReady ||
        isTransitioning ||
        isFinished
    )
        return;

    const currentTarget = lessonsList[lessonIndex];
    const detected = window.gameState.detectedNumber;

    if (detected === currentTarget) {
        if (!isHolding) {
            holdStartTime = Date.now();
            isHolding = true;
            // Instruksi Tahan (Kuning)
            elSubInstruction.innerText = "Tahan sebentar... 📸";
            elSubInstruction.style.color = "#FFE52A";
            lastHintTime = Date.now() + 99999;
        } else {
            const elapsed = Date.now() - holdStartTime;
            if (elapsed >= GAME_CONFIG.holdDuration) nextLevel();
        }
    } else {
        isHolding = false;
        // if (!isSpeaking) analyzeMistake(); // Aktifkan jika butuh cerewet
    }
}

// === LOGIKA KAMERA & TOMBOL (Update Warna) ===
let isCameraActive = false;

// Default OFF (Merah)
if (btnCamToggle) {
    btnCamToggle.classList.add("off");
    btnCamToggle.style.borderColor = "#CF0F0F";
    btnCamToggle.style.color = "#CF0F0F";
    txtCamText.innerText = "KAMERA: OFF";
    txtCamIcon.innerText = "🚫";
}

btnCamToggle.addEventListener("click", () => {
    if (isCameraActive) {
        // MATIKAN (Merah)
        if (window.stopCameraSystem) window.stopCameraSystem();

        btnCamToggle.classList.remove("on");
        btnCamToggle.classList.add("off");

        btnCamToggle.style.borderColor = "#CF0F0F";
        btnCamToggle.style.color = "#CF0F0F";
        btnCamToggle.style.background = "#fff";

        txtCamText.innerText = "KAMERA: OFF";
        txtCamIcon.innerText = "🚫";
        elSubInstruction.innerText = "Kamera Nonaktif";
        isCameraActive = false;
    } else {
        // NYALAKAN (Hijau)
        if (window.startCameraSystem) window.startCameraSystem();

        btnCamToggle.classList.remove("off");
        btnCamToggle.classList.add("on");

        btnCamToggle.style.borderColor = "#BBCB64";
        btnCamToggle.style.color = "#fff";
        btnCamToggle.style.background = "#BBCB64";

        txtCamText.innerText = "KAMERA: ON";
        txtCamIcon.innerText = "📹";
        elSubInstruction.innerText = "Kamera Aktif";
        isCameraActive = true;

        if (!hasLessonStarted) {
            hasLessonStarted = true;
            setTimeout(() => {
                speak("Kamera siap. Ayo mulai!", () => {
                    startLesson();
                    gameLoop();
                });
            }, 1000);
        }
    }
});

// START OVERLAY
elStartOverlay.addEventListener("click", () => {
    elStartOverlay.style.opacity = "0";
    setTimeout(() => (elStartOverlay.style.display = "none"), 500);
    elQuestionText.innerText = "Persiapan";
    elSubInstruction.innerText = "Silakan Nyalakan Kamera";
    speak("Selamat datang! Tekan tombol kamera untuk mulai belajar.");

    // Animasi tombol kamera
    btnCamToggle.style.animation = "pulse 1s infinite";
    setTimeout(() => {
        btnCamToggle.style.animation = "";
    }, 3000);
});
