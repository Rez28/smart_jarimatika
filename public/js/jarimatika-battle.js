const battleArea = document.getElementById("battle-screen");
const userScoreDisplay = document.getElementById("user-score");
const opponentScoreDisplay = document.getElementById("opponent-score");
const userProgress = document.getElementById("user-progress");
const opponentProgress = document.getElementById("opponent-progress");
const userProgressText = document.getElementById("user-progress-text");
const opponentProgressText = document.getElementById("opponent-progress-text");
const targetNumberEl = document.getElementById("target-number");
const detectedNumberEl = document.getElementById("detected-number");
const statusText = document.getElementById("opponent-status");
const resultText = document.getElementById("battle-result");
const battleLog = document.getElementById("battle-log");
const answerButton = document.getElementById("btn-answer");
const startButton = document.getElementById("btn-start-battle");
const timerEl = document.getElementById("battle-timer");
const flashOverlay = document.getElementById("battle-flash");
const btnCameraSwitch = document.getElementById("btn-camera-switch");
const opponentVideo = document.getElementById("opponent-video");
const opponentPlaceholder = document.getElementById("opponent-placeholder");

const BATTLE_DURATION = 20; // 20 detik

let gameId = battleArea?.dataset.gameId || "demo";
let userScore = 0;
let opponentScore = 0;
let currentTarget = 0;
let countdown = BATTLE_DURATION;
let timerInterval = null;
let pusher = null;
let channel = null;
let pusherSocketId = null;
const scoreUrl = battleArea?.dataset.scoreUrl || "/jarimatika/battle/score";
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function logBattle(message) {
    const line = document.createElement("div");
    line.className = "text-slate-300";
    line.textContent = message;
    battleLog.prepend(line);
}

function createTarget() {
    currentTarget = Math.floor(Math.random() * 9) + 1;
    targetNumberEl.textContent = currentTarget;
    logBattle(`Target baru: ${currentTarget}`);
}

function updateProgress() {
    const total = Math.max(1, userScore + opponentScore, 10);
    const userPercent = Math.min(100, Math.round((userScore / total) * 100));
    const opponentPercent = Math.min(
        100,
        Math.round((opponentScore / total) * 100),
    );

    userProgress.style.width = `${userPercent}%`;
    opponentProgress.style.width = `${opponentPercent}%`;
    userProgressText.textContent = userScore;
    opponentProgressText.textContent = opponentScore;
}

function setOpponentStatus(message, accent = "#ef4444") {
    statusText.textContent = message;
    statusText.style.color = accent;
}

function flashResult(success) {
    if (!flashOverlay) return;
    flashOverlay.style.background = success
        ? "rgba(56, 189, 248, 0.3)"
        : "rgba(239, 68, 68, 0.3)";
    flashOverlay.style.opacity = 1;
    setTimeout(() => {
        flashOverlay.style.opacity = 0;
    }, 180);
}

function endBattle() {
    answerButton.disabled = true;
    startButton.disabled = false;
    const winner =
        userScore === opponentScore
            ? "Seri"
            : userScore > opponentScore
              ? "Kamu Menang!"
              : "Lawan Menang";
    resultText.textContent = `Waktu habis — ${winner} (Kamu: ${userScore} vs Lawan: ${opponentScore})`;
    logBattle("Battle selesai. " + winner);
    setOpponentStatus("Battle selesai", "#94a3b8");
    if (timerInterval) clearInterval(timerInterval);
}

function startTimer() {
    countdown = BATTLE_DURATION;
    timerEl.textContent = `${countdown}s`;
    if (timerInterval) clearInterval(timerInterval);

    timerInterval = setInterval(() => {
        countdown -= 1;
        timerEl.textContent = `${countdown}s`;
        if (countdown <= 0) {
            endBattle();
        }
    }, 1000);
}

function bindPusher() {
    const pusherKey = battleArea?.dataset.pusherKey || "";
    const pusherCluster = battleArea?.dataset.pusherCluster || "mt1";
    if (!pusherKey) {
        setOpponentStatus(
            "Pusher tidak dikonfigurasi. Sinkronisasi real-time nonaktif.",
            "#f59e0b",
        );
        logBattle(
            "Pusher tidak tersedia pada environment. Jalankan backend broadcast untuk realtime.",
        );
        logBattle(
            "Jika sudah set env, jalankan: php artisan config:clear && php artisan config:cache.",
        );
        return;
    }

    Pusher.logToConsole = false;
    pusher = new Pusher(pusherKey, {
        cluster: pusherCluster,
        forceTLS: true,
    });

    channel = pusher.subscribe(`game.${gameId}`);

    channel.bind("pusher:subscription_succeeded", () => {
        pusherSocketId = pusher.connection.socket_id;
        setOpponentStatus("Terkoneksi ke channel lawan.", "#38bdf8");
        logBattle("Tersambung ke Pusher channel game.");
    });

    channel.bind("OpponentScored", (data) => {
        const points = Number(data.points || 1);
        opponentScore += points;
        setOpponentStatus(`Lawan berhasil menjawab +${points}`);
        updateProgress();
        logBattle(`Event OpponentScored diterima: +${points} poin.`);
    });

    channel.bind("pusher:subscription_error", (err) => {
        setOpponentStatus("Gagal menyambung ke Pusher.", "#f87171");
        logBattle(`Error Pusher: ${JSON.stringify(err)}`);
    });
}

function sendScoreToServer(points) {
    if (!scoreUrl || !csrfToken) {
        logBattle("Endpoint score tidak tersedia atau CSRF token hilang.");
        return;
    }

    fetch(scoreUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            gameId,
            points,
            socket_id: pusherSocketId,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                logBattle(
                    `Score submit gagal: ${data.message || "unknown error"}`,
                );
            }
        })
        .catch((error) => {
            logBattle(`Score submit error: ${error.message}`);
        });
}

function refreshDetectedNumber() {
    if (
        window.gameState &&
        typeof window.gameState.detectedNumber !== "undefined"
    ) {
        detectedNumberEl.textContent = window.gameState.detectedNumber;
    }
}

function resetBattle() {
    userScore = 0;
    opponentScore = 0;
    updateProgress();
    createTarget();
    resultText.textContent = "Battle dimulai setelah timer berjalan.";
    answerButton.disabled = false;
    setOpponentStatus("Menunggu aksi lawan...", "#94a3b8");
    logBattle("Battle dimulai. Jawab secepatnya!");
}

startButton?.addEventListener("click", () => {
    answerButton.disabled = false;
    startButton.disabled = true;
    resetBattle();
    startTimer();
});

answerButton?.addEventListener("click", () => {
    const detected = Number(window.gameState?.detectedNumber || 0);
    if (detected === 0) {
        logBattle(
            "Tidak ada angka terdeteksi. Pastikan jarimu jelas di kamera.",
        );
        setOpponentStatus("Tidak ada angka terdeteksi.", "#f87171");
        flashResult(false);
        return;
    }

    if (detected === currentTarget) {
        userScore += 1;
        updateProgress();
        setOpponentStatus("Kamu benar!", "#38bdf8");
        logBattle(`Jawaban benar (${detected}). +1 poin.`);
        sendScoreToServer(1);
        flashResult(true);
        createTarget();
    } else {
        setOpponentStatus(`Salah. Terdeteksi ${detected}.`, "#f87171");
        logBattle(`Jawaban salah (${detected}). Target ${currentTarget}.`);
        flashResult(false);
    }
});

function updateOpponentCameraUI(isActive) {
    if (!opponentVideo || !opponentPlaceholder) return;
    if (isActive) {
        opponentVideo.classList.remove("hidden");
        opponentPlaceholder.classList.add("hidden");
        statusText.textContent = "Kamera Lawan Aktif";
        statusText.style.color = "#10b981";
    } else {
        opponentVideo.classList.add("hidden");
        opponentPlaceholder.classList.remove("hidden");
        statusText.textContent = "Menunggu lawan...";
        statusText.style.color = "#94a3b8";
    }
}

let isCameraEnabled = false;

btnCameraSwitch?.addEventListener("click", () => {
    if (!canUseCamera()) {
        alert(
            "Kamera tidak tersedia: gunakan browser modern, akses melalui https://localhost, atau pakai ngrok / server HTTPS.",
        );
        return;
    }

    if (!isCameraEnabled) {
        if (window.startCameraSystem) window.startCameraSystem();
        if (videoElement && opponentVideo) {
            opponentVideo.srcObject = videoElement.srcObject;
            opponentVideo.play().catch(() => {});
        }
        isCameraEnabled = true;
        btnCameraSwitch.textContent = "Matikan Kamera";
        updateOpponentCameraUI(true);
    } else {
        if (window.stopCameraSystem) window.stopCameraSystem();
        if (opponentVideo) {
            opponentVideo.pause();
            opponentVideo.srcObject = null;
        }
        isCameraEnabled = false;
        btnCameraSwitch.textContent = "Nyalakan Kamera";
        updateOpponentCameraUI(false);
    }
});

function canUseCamera() {
    return (
        (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ||
        navigator.getUserMedia ||
        navigator.webkitGetUserMedia ||
        navigator.mozGetUserMedia
    );
}

setInterval(refreshDetectedNumber, 250);

bindPusher();
updateProgress();
createTarget();

if (!canUseCamera()) {
    const msg =
        "Kamera tidak dapat diaktifkan di mode ini. Pastikan browser mendukung getUserMedia dan gunakan https / localhost.";
    logBattle(msg);
    setOpponentStatus(msg, "#f87171");
}
