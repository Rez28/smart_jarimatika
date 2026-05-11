// ==========================================
// SIMPLE WEBRTC BATTLE - NEW CLEAN VERSION (FINAL SECURE)
// ==========================================

const battleArea = document.getElementById("battle-screen");
const battleLog = document.getElementById("battle-log");
const opponentVideo = document.getElementById("opponent-video");
const opponentPlaceholder = document.getElementById("opponent-placeholder");
const statusText = document.getElementById("opponent-status");
const btnCameraSwitch = document.getElementById("btn-camera-switch");
const btnStartBattle = document.getElementById("btn-start-battle");
const btnAnswer = document.getElementById("btn-answer");
const timerDisplay = document.getElementById("battle-timer");
const readyStatusEl = document.getElementById("ready-status");
const bigCountdownOverlay = document.getElementById("big-countdown-overlay");
const bigCountdownText = document.getElementById("big-countdown-text");
const localVideoElement = document.querySelector(".input_video");
const myName = battleArea?.dataset.userName || "Player";
const opponentNameDisplay = document.getElementById("opponent-name-display");
const btnExitRoom = document.getElementById("btn-exit-room");
const battleResultModal = document.getElementById("battle-result-modal");
const modalFinalScore = document.getElementById("modal-final-score");
const btnRematch = document.getElementById("btn-rematch");

const BATTLE_DURATION = 20;
const BATTLE_WIN_SCORE = 2; // Target kemenangan (2 poin untuk ujicoba)
const gameId = battleArea?.dataset.gameId || "demo";
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const localPeerId = `peer-${Math.random().toString(36).slice(2, 12)}`;
let peerConnection = null;
let localStream = null;
let pusher = null;
let channel = null;
let pusherSocketId = null;
let remoteStreamActive = false;
let iceCandidateQueue = [];

let userScore = 0;
let opponentScore = 0;
let currentTarget = 0;
let roundStartTime = null;
let lastRoundTimeTaken = 5;
let isLocalReady = false;
let isRemoteReady = false;
let autoStartTimeout = null;
let autoStartInterval = null;
let countdownInterval = null;
let battleInterval = null;
let battleLoopId = null;
let isHolding = false;
let holdStartTime = 0;
let isGameOver = false;
const HOLD_DURATION = 1500; // 1.5 detik

function log(message, type = "info") {
    const timestamp = new Date().toLocaleTimeString();
    const prefix = { info: "ℹ️", success: "✅", error: "❌", debug: "🔧", webrtc: "🎥", warning: "⚠️" }[type] || "•";
    const fullMsg = `[${timestamp}] ${prefix} ${message}`;
    console.log(fullMsg);

    const line = document.createElement("div");
    line.className = `text-xs font-mono ${type === 'error' ? 'text-red-500 font-bold' : (type === 'warning' ? 'text-yellow-600' : 'text-slate-500')}`;
    line.textContent = fullMsg;
    battleLog?.prepend(line);
}

function logSDP(label, sdp) {
    if (!sdp) return;
    const lines = sdp.split("\n").length;
    const firstLine = sdp.split("\n")[0];
    log(`${label}: SDP with ${lines} lines. First: "${firstLine.substring(0, 30)}..."`, "debug");
}

async function initializeWebRTC() {
    if (peerConnection) {
        peerConnection.close();
        peerConnection = null;
        iceCandidateQueue = [];
    }

    try {
        peerConnection = new RTCPeerConnection({
            iceServers: [{ urls: "stun:stun.l.google.com:19302" }],
        });

        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal("ice", { peerId: localPeerId, candidate: event.candidate });
            }
        };

        peerConnection.ontrack = (event) => {
            log("Remote track received!", "success");
            if (event.streams && event.streams.length > 0) {
                const remoteStream = event.streams[0];
                if (opponentVideo) {
                    opponentVideo.srcObject = remoteStream;
                    opponentVideo.onloadedmetadata = () => {
                        opponentVideo.play().catch((e) => log(`Play error: ${e.message}`, "error"));
                    };
                    remoteStreamActive = true;
                    showRemoteVideo();
                    startAutoStartTimer();
                }
            }
        };

        return peerConnection;
    } catch (err) {
        log(`WebRTC init error: ${err.message}`, "error");
        throw err;
    }
}

async function getLocalStream() {
    if (localStream && localStream.getTracks().length > 0) {
        return localStream;
    }

    log("Requesting local camera...", "webrtc");

    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        log(`Local stream obtained`, "success");

        if (localVideoElement) {
            // PERBAIKAN WAJAH DOBEL: Kembalikan video asli menjadi transparan (opacity-0)
            localVideoElement.classList.add("opacity-0");
            localVideoElement.style.opacity = "";
            localVideoElement.style.transform = "";

            localVideoElement.srcObject = localStream;
            localVideoElement.muted = true;
            localVideoElement.autoplay = true;
            localVideoElement.playsInline = true;
            
            localVideoElement.onloadedmetadata = () => {
                localVideoElement.play().then(() => {
                    log("Video frame ready, starting MediaPipe...", "info");
                    if (typeof window.startCameraSystem === 'function') {
                        window.startCameraSystem();
                    }
                }).catch((e) => {
                    log(`Local play error: ${e.message}`, "error");
                });
            };
        }

        return localStream;
    } catch (err) {
        log(`Camera error: ${err.message}`, "error");
        throw err;
    }
}

async function createOffer() {
    try {
        if (peerConnection && peerConnection.signalingState !== "stable") {
            peerConnection.close();
            peerConnection = null;
        }

        const pc = await initializeWebRTC();
        const stream = await getLocalStream();

        stream.getTracks().forEach((track) => pc.addTrack(track, stream));

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        const success = await sendSignal("offer", { peerId: localPeerId, sdp: offer.sdp, playerName: myName });
        if (success) log("Offer sent successfully", "success");
    } catch (err) {
        log(`Offer creation error: ${err.message}`, "error");
    }
}

async function handleOffer(sdp, peerId) {
    try {
        const pc = await initializeWebRTC();
        const stream = await getLocalStream();

        const senders = pc.getSenders();
        stream.getTracks().forEach((track) => {
            if (!senders.find(s => s.track === track)) pc.addTrack(track, stream);
        });

        const normalizedSDP = normalizeSDP(sdp);
        await pc.setRemoteDescription(new RTCSessionDescription({ type: "offer", sdp: normalizedSDP }));

        processIceQueue();

        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);

        await sendSignal("answer", { peerId: localPeerId, sdp: answer.sdp, playerName: myName });
        log("Answer sent", "success");
        
        // Update UI untuk Pihak yang Menerima Panggilan
        if(btnCameraSwitch) {
            btnCameraSwitch.textContent = "Matikan Kamera";
        }
    } catch (err) {
        log(`Offer handling error: ${err.message}`, "error");
    }
}

async function handleAnswer(sdp, peerId) {
    try {
        if (!peerConnection) return;
        const normalizedSDP = normalizeSDP(sdp);
        await peerConnection.setRemoteDescription(new RTCSessionDescription({ type: "answer", sdp: normalizedSDP }));
        processIceQueue();
        log("WebRTC Connected (Answer applied)", "success");
    } catch (err) {
        log(`Answer handling error: ${err.message}`, "error");
    }
}

async function handleICECandidate(candidate, peerId) {
    if (!peerConnection) return;
    try {
        const iceCandidate = new RTCIceCandidate(candidate);
        if (peerConnection.remoteDescription) {
            await peerConnection.addIceCandidate(iceCandidate);
        } else {
            iceCandidateQueue.push(iceCandidate);
        }
    } catch (err) {
        log(`ICE error: ${err.message}`, "error");
    }
}

function processIceQueue() {
    if (iceCandidateQueue.length > 0 && peerConnection && peerConnection.remoteDescription) {
        iceCandidateQueue.forEach(async (candidate) => {
            try { await peerConnection.addIceCandidate(candidate); } catch (e) {}
        });
        iceCandidateQueue = [];
    }
}

function normalizeSDP(sdp) {
    if (!sdp) return "";
    let text = sdp.replace(/\r\n/g, "\n").replace(/\r/g, "\n").replace(/\\r\\n/g, "\n").replace(/\\n/g, "\n").replace(/\\r/g, "\n");
    const lines = text.split("\n").map(line => line.trim()).filter(line => line.length > 0);
    return lines.join("\r\n") + "\r\n";
}

async function sendSignal(type, payload) {
    try {
        const response = await fetch("/jarimatika/battle/signal", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
            body: JSON.stringify({ gameId, type, payload, socket_id: pusherSocketId }),
        });
        return response.ok;
    } catch (err) {
        return false;
    }
}

function setupPusher() {
    const pusherKey = battleArea?.dataset.pusherKey || "";
    const pusherCluster = battleArea?.dataset.pusherCluster || "mt1";

    if (!pusherKey) return;

    pusher = new Pusher(pusherKey, { cluster: pusherCluster, forceTLS: true });
    channel = pusher.subscribe(`game.${gameId}`);

    channel.bind("pusher:subscription_succeeded", () => {
        pusherSocketId = pusher.connection.socket_id;
        statusText.textContent = "Siap untuk terhubung WebRTC";
    });

    channel.bind("PeerSignal", (data) => {
        try {
            let eventData = typeof data === "string" ? JSON.parse(data) : data;
            if (!eventData.type || !eventData.payload) return;
            if (eventData.payload.peerId === localPeerId) return;

            if (eventData.type === "offer") {
                setOpponentName(eventData.payload.playerName);
                handleOffer(eventData.payload.sdp, eventData.payload.peerId);
            }
            else if (eventData.type === "answer") {
                setOpponentName(eventData.payload.playerName);
                handleAnswer(eventData.payload.sdp, eventData.payload.peerId);
            }
            else if (eventData.type === "ice") handleICECandidate(eventData.payload.candidate, eventData.payload.peerId);
            else if (eventData.type === "ready") {
                isRemoteReady = true;
                updateReadyStatus();
                log("Lawan sudah Ready!", "success");
                if (isLocalReady) {
                    startMatchCountdown();
                }
            }
        } catch (err) {}
    });

    channel.bind("OpponentScored", (data) => {
        // Prevent echo: ignore skor dari diri sendiri
        if (data.senderId === localPeerId) return;
        // Prevent race condition
        if (isGameOver) return;

        opponentScore += data.points || 1;
        log(`Lawan +${data.points || 1} poin! Skor lawan: ${opponentScore}/${BATTLE_WIN_SCORE}`, "warning");
        updateProgress();
        updateBattleDisplay();
        
        // CEK APAKAH LAWAN SUDAH MENANG
        if (opponentScore >= BATTLE_WIN_SCORE) {
            isGameOver = true;
            log("😢 LAWAN MENANG!", "error");
            setTimeout(() => {
                endBattle("loss");
            }, 500);
        }
    });
}

// ==========================================
// UI FUNCTIONS
// ==========================================

function setOpponentName(name) {
    if (name && opponentNameDisplay) {
        opponentNameDisplay.textContent = "Player 2: " + name;
    }
}

function updateReadyStatus() {
    const readyCount = (isLocalReady ? 1 : 0) + (isRemoteReady ? 1 : 0);
    if (readyStatusEl) {
        readyStatusEl.textContent = `${readyCount}/2 Siap`;
        if (readyCount === 2) {
            readyStatusEl.parentElement.classList.add("bg-green-100", "border-green-300");
            readyStatusEl.parentElement.classList.remove("bg-blue-50", "border-blue-300");
            readyStatusEl.classList.add("text-green-600");
            readyStatusEl.classList.remove("text-blue-600");
        }
    }
}

function showRemoteVideo() {
    if (opponentVideo && opponentPlaceholder) {
        opponentVideo.classList.remove("hidden");
        opponentPlaceholder.classList.add("hidden");
        
        // TAMBAHAN: Sembunyikan teks status sepenuhnya agar tidak menghalangi video
        statusText.classList.add("hidden"); 
    }
}

function hideRemoteVideo() {
    if (opponentVideo && opponentPlaceholder) {
        opponentVideo.classList.add("hidden");
        opponentPlaceholder.classList.remove("hidden");
        
        // TAMBAHAN: Munculkan kembali teks status saat video mati
        statusText.classList.remove("hidden"); 
        
        statusText.textContent = "🤖 Menunggu lawan...";
        statusText.style.color = "#94a3b8";
    }
    remoteStreamActive = false;
}

btnCameraSwitch?.addEventListener("click", async () => {
    const isCurrentlyEnabled = btnCameraSwitch.textContent.includes("Matikan");

    if (!isCurrentlyEnabled) {
        btnCameraSwitch.disabled = true;
        btnCameraSwitch.textContent = "Menghubungkan...";
        
        try {
            // PERBAIKAN TABRAKAN SINYAL: Cek apakah sudah ada offer dari lawan
            if (peerConnection && peerConnection.remoteDescription) {
                log("Koneksi dari lawan sudah ada, menyalakan kamera lokal...", "info");
                await getLocalStream();
            } else {
                log("Membuat room baru...", "info");
                statusText.textContent = "📤 Mencari lawan...";
                statusText.style.color = "#fbbf24";
                await createOffer();
            }
            btnCameraSwitch.textContent = "Matikan Kamera";
        } catch (err) {
            statusText.textContent = "❌ Gagal aktifkan kamera";
            statusText.style.color = "#f87171";
        } finally {
            btnCameraSwitch.disabled = false;
        }
    } else {
        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }
        if (localStream) {
            localStream.getTracks().forEach((track) => track.stop());
            localStream = null;
        }
        if (opponentVideo) {
            opponentVideo.pause();
            opponentVideo.srcObject = null;
        }
        
        if (typeof window.stopCameraSystem === 'function') {
            window.stopCameraSystem();
        }

        hideRemoteVideo();
        btnCameraSwitch.textContent = "Nyalakan Kamera";
        statusText.textContent = "Kamera dimatikan";
        statusText.style.color = "#94a3b8";
    }
});

// ==========================================
// DUAL-READY SYSTEM - EVENT LISTENER
// ==========================================

btnStartBattle?.addEventListener("click", async () => {
    if (isLocalReady) return;
    
    isLocalReady = true;
    btnStartBattle.textContent = "⏳ Menunggu Lawan...";
    btnStartBattle.disabled = true;
    
    updateReadyStatus();
    log("Kamu sudah Ready! Menunggu lawan...", "info");
    
    // Kirim sinyal ready ke lawan
    await sendSignal("ready", { peerId: localPeerId });
    
    // Cek apakah lawan juga sudah ready
    if (isRemoteReady) {
        startMatchCountdown();
    }
});

// ==========================================
// DUAL-READY SYSTEM - TIMER & COUNTDOWN FUNCTIONS
// ==========================================

function startAutoStartTimer() {
    if (autoStartTimeout) clearTimeout(autoStartTimeout);
    if (autoStartInterval) clearInterval(autoStartInterval);
    
    log("Auto-start 60 detik dimulai...", "info");
    
    let secondsLeft = 60;
    
    autoStartInterval = setInterval(() => {
        secondsLeft--;
        
        if (!isLocalReady && btnStartBattle) {
            btnStartBattle.textContent = "✋ Ready (Auto: " + secondsLeft + "s)";
        }
        
        if (secondsLeft <= 0) {
            clearInterval(autoStartInterval);
            autoStartInterval = null;
            log("Auto-start dipicu! Battle dimulai otomatis.", "warning");
            isLocalReady = true;
            isRemoteReady = true;
            startMatchCountdown();
        }
    }, 1000);
}

function startMatchCountdown() {
    // Clear auto-start timer dan interval agar tidak bentrok
    if (autoStartTimeout) {
        clearTimeout(autoStartTimeout);
        autoStartTimeout = null;
    }
    if (autoStartInterval) {
        clearInterval(autoStartInterval);
        autoStartInterval = null;
    }
    
    // Disable tombol ready
    if (btnStartBattle) {
        btnStartBattle.disabled = true;
        btnStartBattle.textContent = "⏳ Bermain...";
    }
    
    log("Hitung mundur dimulai...", "success");
    
    // Tampilkan overlay dengan backdrop
    if (bigCountdownOverlay) {
        bigCountdownOverlay.classList.remove("hidden");
        bigCountdownOverlay.classList.add("flex");
        // Force repaint to ensure z-index is applied
        bigCountdownOverlay.offsetHeight;
    }
    
    // Hitung mundur 3, 2, 1, MULAI
    let countdown = 3;
    
    if (countdownInterval) clearInterval(countdownInterval);
    
    countdownInterval = setInterval(() => {
        if (countdown > 0) {
            if (bigCountdownText) {
                bigCountdownText.textContent = countdown;
                bigCountdownText.classList.remove("text-emerald-400");
                // Add animation
                bigCountdownText.classList.add("animate-pulse", "scale-100");
                bigCountdownText.style.animation = "pulse 0.6s cubic-bezier(0.4, 0, 0.6, 1)";
            }
            countdown--;
        } else if (countdown === 0) {
            // Tampilkan "MULAI!"
            if (bigCountdownText) {
                bigCountdownText.textContent = "MULAI!";
                bigCountdownText.classList.add("text-emerald-400");
                bigCountdownText.style.animation = "bounce 0.6s ease-out";
            }
            countdown--;
        } else {
            // Setelah 1 detik menampilkan "MULAI!", sembunyikan overlay
            clearInterval(countdownInterval);
            countdownInterval = null;
            
            if (bigCountdownOverlay) {
                // Fade out overlay
                bigCountdownOverlay.style.opacity = "0";
                bigCountdownOverlay.style.transition = "opacity 0.3s ease-out";
                
                setTimeout(() => {
                    bigCountdownOverlay.classList.add("hidden");
                    bigCountdownOverlay.classList.remove("flex");
                    bigCountdownOverlay.style.opacity = "1";
                    bigCountdownOverlay.style.transition = "";
                }, 300);
            }
            
            // Panggil startBattleTimer()
            startBattleTimer();
        }
    }, 1000);
}

function startBattleTimer() {
    // Reset game state
    isGameOver = false;
    isLocalReady = false;
    isRemoteReady = false;
    updateReadyStatus();
    
    // Tampilkan tombol konfirmasi jari saat battle dimulai
    if (btnAnswer) {
        btnAnswer.classList.remove("hidden");
        btnAnswer.disabled = false;
        btnAnswer.textContent = "Bentuk Jari & Tahan!";
    }
    
    // Sembunyikan tombol ready saat battle berjalan
    if (btnStartBattle) {
        btnStartBattle.classList.add("hidden");
    }
    
    log("🎮 Game dimulai! Siapa yang capai 2 poin duluan menang!", "success");
    
    // Generate target pertama kali
    createTarget();
    
    // Update display untuk menampilkan skor
    updateBattleDisplay();
    
    // Mulai battle loop dengan requestAnimationFrame (tidak ada timer, balapan skor)
    battleLoopId = requestAnimationFrame(startBattleLoop);
}

// ==========================================
// AUTO-CONFIRM SYSTEM (Hold to Shoot)
// ==========================================

function startBattleLoop() {
    // Ambil angka yang terdeteksi dari window.gameState
    const detected = window.gameState?.detectedNumber || 0;
    
    // Cek jika terdeteksi sama dengan target
    if (detected === currentTarget && currentTarget !== 0) {
        // Jika belum holding, mulai holding
        if (!isHolding) {
            isHolding = true;
            holdStartTime = Date.now();
            btnAnswer.textContent = "Menahan... ⏳";
            btnAnswer.classList.add("btn-holding");
            btnAnswer.classList.remove("btn-3d-orange");
            log(`Menahan... Target: ${currentTarget}, Terdeteksi: ${detected}`, "info");
        }
        
        // Cek apakah sudah mencapai durasi hold
        if (Date.now() - holdStartTime >= HOLD_DURATION) {
            isHolding = false;
            holdStartTime = 0;
            handleCorrectAnswer(detected);
        }
    } else {
        // Jika tidak cocok atau target 0, reset holding
        if (isHolding) {
            isHolding = false;
            holdStartTime = 0;
            btnAnswer.textContent = "Bentuk Jari & Tahan!";
            btnAnswer.classList.remove("btn-holding");
            btnAnswer.classList.add("btn-3d-orange");
            log(`Hold dibatalkan. Target: ${currentTarget}, Terdeteksi: ${detected}`, "info");
        }
    }
    
    // Lanjutkan battle loop
    battleLoopId = requestAnimationFrame(startBattleLoop);
}

function handleCorrectAnswer(detected) {
    // Prevent race condition
    if (isGameOver) return;

    // Tambah skor
    userScore++;
    log(`✅ Jawaban Benar! Skor: ${userScore}/${BATTLE_WIN_SCORE}`, "success");
    
    // Update UI progress
    updateProgress();
    updateBattleDisplay();
    
    // Kirim skor ke opponent
    sendScoreToOpponent(1);
    
    // Update button visual
    btnAnswer.textContent = "BERHASIL! ✅";
    btnAnswer.classList.add("btn-correct");
    btnAnswer.classList.remove("btn-3d-orange", "btn-holding");
    
    // CEK APAKAH SUDAH MENANG
    if (userScore >= BATTLE_WIN_SCORE) {
        isGameOver = true;
        log("🎉 KAMU MENANG!", "success");
        setTimeout(() => {
            endBattle("win");
        }, 800);
        return;
    }
    
    // Jika belum menang, generate soal baru
    setTimeout(() => {
        createTarget();
        btnAnswer.textContent = "Bentuk Jari & Tahan!";
        btnAnswer.classList.remove("btn-correct");
        btnAnswer.classList.add("btn-3d-orange");
    }, 800);
}

async function sendScoreToOpponent(points) {
    try {
        const response = await fetch("/jarimatika/battle/score", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                gameId: gameId,
                points: points,
                senderId: localPeerId,
                socket_id: pusherSocketId,
            }),
        });
        
        if (!response.ok) {
            log(`Gagal mengirim skor ke opponent: ${response.status}`, "error");
        }
    } catch (err) {
        log(`Error sendScoreToOpponent: ${err.message}`, "error");
    }
}

// ==========================================

function endBattle(result = "timeout") {
    // Clear timers
    if (battleInterval) clearInterval(battleInterval);
    if (battleLoopId) cancelAnimationFrame(battleLoopId);
    
    if (btnAnswer) {
        btnAnswer.disabled = true;
        btnAnswer.classList.add("hidden");
    }
    
    // Tampilkan kembali tombol ready
    if (btnStartBattle) {
        btnStartBattle.classList.remove("hidden");
        btnStartBattle.textContent = "👋 Ready";
        btnStartBattle.disabled = false;
    }
    
    // Reset holding state
    isHolding = false;
    holdStartTime = 0;
    
    // Determine result
    let battleResult = "";
    if (result === "win") {
        battleResult = `🏆 KAMU MENANG! (${userScore} vs ${opponentScore})`;
        timerDisplay.textContent = "MENANG!";
    } else if (result === "loss") {
        battleResult = `😢 KAMU KALAH! (${userScore} vs ${opponentScore})`;
        timerDisplay.textContent = "KALAH!";
    } else {
        battleResult = `⏱️ WAKTU HABIS! (${userScore} vs ${opponentScore})`;
        timerDisplay.textContent = "SELESAI!";
    }
    
    log(battleResult, "info");
    
    // Display result
    const battleResultEl = document.getElementById("battle-result");
    if (battleResultEl) {
        battleResultEl.textContent = battleResult;
    }
    
    // SUBMIT HASIL KE BACKEND
    submitBattleResult(result);
    
    // Tampilkan modal hasil pertandingan
    if (battleResultModal && modalFinalScore) {
        battleResultModal.classList.remove("hidden");
        battleResultModal.classList.add("flex");
        modalFinalScore.textContent = `Kamu ${userScore} - ${opponentScore} Lawan`;
    }
}

// Event Listener untuk Tombol Keluar Room
if (btnExitRoom) {
    btnExitRoom.addEventListener("click", () => {
        location.href = "/dashboard";
    });
}

// Event Listener untuk Tombol Rematch
if (btnRematch) {
    btnRematch.addEventListener("click", () => {
        // Sembunyikan modal
        if (battleResultModal) {
            battleResultModal.classList.add("hidden");
            battleResultModal.classList.remove("flex");
        }
        
        // Reset state
        userScore = 0;
        opponentScore = 0;
        isLocalReady = false;
        isRemoteReady = false;
        
        // Update UI
        updateProgress();
        updateBattleDisplay();
        
        // Aktifkan kembali tombol start battle
        if (btnStartBattle) {
            btnStartBattle.disabled = false;
            btnStartBattle.textContent = "✋ Ready";
        }
        
        // Reset button answer
        if (btnAnswer) {
            btnAnswer.textContent = "Bentuk Jari & Tahan!";
            btnAnswer.classList.remove("btn-holding", "btn-correct");
            btnAnswer.classList.add("btn-3d-orange");
        }
        
        // Buat target baru
        createTarget();
    });
}

function generateAdaptiveTarget(timeTakenSeconds, myScore, opponentScore) {
  // FUZZIFIKASI - Tentukan Status Waktu
  let timeStatus;
  if (timeTakenSeconds <= 3) {
    timeStatus = "Cepat";
  } else if (timeTakenSeconds <= 7) {
    timeStatus = "Normal";
  } else {
    timeStatus = "Lambat";
  }

  // FUZZIFIKASI - Tentukan Status Skor
  let scoreStatus;
  if (myScore > opponentScore) {
    scoreStatus = "Menang";
  } else if (myScore === opponentScore) {
    scoreStatus = "Seri";
  } else {
    scoreStatus = "Kalah";
  }

  // ATURAN INFERENSI - Tentukan Tingkat Kesulitan
  let difficultyLevel = "MUDAH"; // Default

  if (timeStatus === "Cepat" && scoreStatus === "Menang") {
    difficultyLevel = "SULIT";
  } else if (timeStatus === "Cepat" && scoreStatus === "Seri") {
    difficultyLevel = "SEDANG";
  } else if (timeStatus === "Normal" && scoreStatus === "Menang") {
    difficultyLevel = "SULIT";
  } else if (timeStatus === "Normal" && scoreStatus === "Seri") {
    difficultyLevel = "SEDANG";
  } else if (timeStatus === "Lambat" || scoreStatus === "Kalah") {
    difficultyLevel = "MUDAH";
  }

  // DEFUZZIFIKASI - Generate Output berdasarkan Tingkat Kesulitan
  let adaptiveTarget;
  if (difficultyLevel === "MUDAH") {
    adaptiveTarget = Math.floor(Math.random() * 9) + 1; // 1-9
  } else if (difficultyLevel === "SEDANG") {
    adaptiveTarget = Math.floor(Math.random() * 40) + 10; // 10-49
  } else if (difficultyLevel === "SULIT") {
    adaptiveTarget = Math.floor(Math.random() * 50) + 50; // 50-99
  }

  // Console logging untuk debugging
  console.log(`Status Waktu: ${timeStatus} (${timeTakenSeconds}s)`);
  console.log(`Status Skor: ${scoreStatus} (Saya: ${myScore}, Lawan: ${opponentScore})`);
  console.log(`Tingkat Kesulitan: ${difficultyLevel}`);
  console.log(`Target Adaptif: ${adaptiveTarget}`);

  return adaptiveTarget;
}

// ==========================================
// UI FUNCTIONS
// ==========================================

function createTarget() {
    // Hitung waktu yang diambil untuk round sebelumnya
    if (roundStartTime !== null) {
        const currentTime = Date.now();
        lastRoundTimeTaken = Math.round((currentTime - roundStartTime) / 1000); // Konversi ke detik
        lastRoundTimeTaken = Math.max(1, Math.min(lastRoundTimeTaken, 20)); // Batasi 1-20 detik
    }
    
    // Mulai tracking waktu untuk round berikutnya
    roundStartTime = Date.now();
    
    // Generate target adaptif berdasarkan DDA Fuzzy Logic
    currentTarget = generateAdaptiveTarget(lastRoundTimeTaken, userScore, opponentScore);
    
    const targetEl = document.getElementById("target-number");
    if (targetEl) targetEl.textContent = currentTarget;
}

function updateProgress() {
    const total = Math.max(1, userScore + opponentScore, 10);
    const userPercent = Math.min(100, Math.round((userScore / total) * 100));
    const opponentPercent = 100 - userPercent;

    const userProgressEl = document.getElementById("user-progress");
    const opponentProgressEl = document.getElementById("opponent-progress");
    const userProgressTextEl = document.getElementById("user-progress-text");
    const opponentProgressTextEl = document.getElementById("opponent-progress-text");

    if (userProgressEl) userProgressEl.style.width = `${userPercent}%`;
    if (opponentProgressEl) opponentProgressEl.style.width = `${opponentPercent}%`;
    if (userProgressTextEl) userProgressTextEl.textContent = userScore;
    if (opponentProgressTextEl) opponentProgressTextEl.textContent = opponentScore;
}

function updateBattleDisplay() {
    // Update skor display
    const userScoreEl = document.getElementById("user-score");
    const opponentScoreEl = document.getElementById("opponent-score");
    
    if (userScoreEl) userScoreEl.textContent = userScore;
    if (opponentScoreEl) opponentScoreEl.textContent = opponentScore;
    
    // Update timer display untuk menampilkan status
    if (timerDisplay) {
        timerDisplay.textContent = `${userScore} vs ${opponentScore}`;
    }
}

async function submitBattleResult(result) {
    try {
        const response = await fetch("/jarimatika/battle/result", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                gameId: gameId,
                isVictory: result === "win",
                userScore: userScore,
                opponentScore: opponentScore,
                socket_id: pusherSocketId,
            }),
        });
        
        if (response.ok) {
            const resData = await response.json();
            log(resData.message || "Hasil battle berhasil dikirim ke server", "success");
        } else {
            log(`Gagal mengirim hasil: ${response.status}`, "error");
        }
    } catch (err) {
        log(`Error submitBattleResult: ${err.message}`, "error");
    }
}

document.addEventListener("DOMContentLoaded", () => {
    setupPusher();
    createTarget();
    updateProgress();
});