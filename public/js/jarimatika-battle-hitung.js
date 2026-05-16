// ==========================================
// MATH BATTLE MODE - JARIMATIKA BATTLE HITUNG
// ==========================================

// Safe element getter
function getElement(id) {
    return document.getElementById(id);
}

const battleArea = getElement("battle-screen");
const battleLog = getElement("battle-log");
const opponentVideo = getElement("opponent-video");
const opponentPlaceholder = getElement("opponent-placeholder");
const statusText = getElement("opponent-status");
const btnCameraSwitch = getElement("btn-camera-switch");
const btnStartBattle = getElement("btn-start-battle");
const btnAnswer = getElement("btn-answer");
const timerDisplay = getElement("battle-timer");
const readyStatusEl = getElement("ready-status");
const bigCountdownOverlay = getElement("big-countdown-overlay");
const bigCountdownText = getElement("big-countdown-text");
const localVideoElement = document.querySelector(".input_video");
const myName = (battleArea && battleArea.dataset.userName) || "Player";
const opponentNameDisplay = getElement("opponent-name-display");
const btnExitRoom = getElement("btn-exit-room");
const battleResultModal = getElement("battle-result-modal");
const modalFinalScore = getElement("modal-final-score");
const btnRematch = getElement("btn-rematch");

const BATTLE_DURATION = 20;
const BATTLE_WIN_SCORE = 2;
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
let currentProblem = null; // { a, b, answer, display: "2 + 3 = ?" }
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
const HOLD_DURATION = 1500;

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
                        remoteStreamActive = true;
                        showRemoteVideo();
                        startAutoStartTimer();
                    };
                }
            }
        };

        peerConnection.onconnectionstatechange = () => {
            log(`WebRTC State: ${peerConnection.connectionState}`, "webrtc");
            if (peerConnection.connectionState === "failed" || peerConnection.connectionState === "disconnected") {
                hideRemoteVideo();
            }
        };

        await getLocalStream();
        
        if (localStream && peerConnection) {
            localStream.getTracks().forEach((track) => {
                peerConnection.addTrack(track, localStream);
            });
        }
        
        log("WebRTC initialized", "success");
    } catch (err) {
        log(`WebRTC init error: ${err.message}`, "error");
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
    await initializeWebRTC();
    if (!peerConnection) return;

    try {
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        logSDP("Local Offer", offer.sdp);
        await sendSignal("offer", { peerId: localPeerId, sdp: offer.sdp, playerName: myName });
        log("Offer dikirim, menunggu lawan...", "info");
    } catch (err) {
        log(`Offer creation error: ${err.message}`, "error");
    }
}

async function handleOffer(sdp, peerId) {
    try {
        if (!peerConnection) await initializeWebRTC();
        if (!peerConnection) return;

        const normalizedSDP = normalizeSDP(sdp);
        await peerConnection.setRemoteDescription(new RTCSessionDescription({ type: "offer", sdp: normalizedSDP }));

        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        logSDP("Local Answer", answer.sdp);

        await sendSignal("answer", { peerId: localPeerId, sdp: answer.sdp, playerName: myName });
        log("Answer dikirim, WebRTC Connected", "success");
        processIceQueue();

        if (btnCameraSwitch) {
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
    try {
        const pusherKey = battleArea?.dataset.pusherKey || "";
        const pusherCluster = battleArea?.dataset.pusherCluster || "mt1";

        if (!pusherKey) {
            log("Pusher key not configured", "warning");
            return;
        }

        if (typeof Pusher === 'undefined') {
            log("Pusher library not loaded", "error");
            return;
        }

        pusher = new Pusher(pusherKey, { cluster: pusherCluster, forceTLS: true });
        channel = pusher.subscribe(`game.${gameId}`);

        channel.bind("pusher:subscription_succeeded", () => {
            pusherSocketId = pusher.connection.socket_id;
            if (statusText) statusText.textContent = "Siap untuk terhubung WebRTC";
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
            } catch (err) {
                log(`Error handling PeerSignal: ${err.message}`, "error");
            }
        });

        channel.bind("OpponentScored", (data) => {
            if (data.senderId === localPeerId) return;
            if (isGameOver) return;

            opponentScore += data.points || 1;
            log(`Lawan +${data.points || 1} poin! Skor lawan: ${opponentScore}/${BATTLE_WIN_SCORE}`, "warning");
            updateProgress();
            updateBattleDisplay();
            
            if (opponentScore >= BATTLE_WIN_SCORE) {
                isGameOver = true;
                log("😢 LAWAN MENANG!", "error");
                setTimeout(() => {
                    endBattle("loss");
                }, 500);
            }
        });
        
        log("Pusher connected", "success");
    } catch (err) {
        log(`Pusher setup error: ${err.message}`, "error");
    }
}

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
            readyStatusEl.parentElement.classList.remove("bg-purple-50", "border-purple-300");
            readyStatusEl.classList.add("text-green-600");
            readyStatusEl.classList.remove("text-purple-600");
        }
    }
}

function showRemoteVideo() {
    if (opponentVideo && opponentPlaceholder) {
        opponentVideo.classList.remove("hidden");
        opponentPlaceholder.classList.add("hidden");
        statusText.classList.add("hidden"); 
    }
}

function hideRemoteVideo() {
    if (opponentVideo && opponentPlaceholder) {
        opponentVideo.classList.add("hidden");
        opponentPlaceholder.classList.remove("hidden");
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

btnStartBattle?.addEventListener("click", async () => {
    if (!remoteStreamActive || !localStream) {
        alert("Kamera belum saling terhubung! Silakan pastikan kamu dan lawan sudah menyalakan kamera.");
        return;
    }

    if (isLocalReady) return;
    
    isLocalReady = true;
    btnStartBattle.textContent = "⏳ Menunggu Lawan...";
    btnStartBattle.disabled = true;
    
    updateReadyStatus();
    log("Kamu sudah Ready! Menunggu lawan...", "info");
    
    await sendSignal("ready", { peerId: localPeerId });
    
    if (isRemoteReady) {
        startMatchCountdown();
    }
});

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
    if (autoStartTimeout) {
        clearTimeout(autoStartTimeout);
        autoStartTimeout = null;
    }
    if (autoStartInterval) {
        clearInterval(autoStartInterval);
        autoStartInterval = null;
    }
    
    if (btnStartBattle) {
        btnStartBattle.disabled = true;
        btnStartBattle.textContent = "⏳ Bermain...";
    }
    
    log("Hitung mundur dimulai...", "success");
    
    if (bigCountdownOverlay) {
        bigCountdownOverlay.classList.remove("hidden");
        bigCountdownOverlay.classList.add("flex");
        bigCountdownOverlay.offsetHeight;
    }
    
    let countdown = 3;
    
    if (countdownInterval) clearInterval(countdownInterval);
    
    countdownInterval = setInterval(() => {
        if (countdown > 0) {
            if (bigCountdownText) {
                bigCountdownText.textContent = countdown;
                bigCountdownText.classList.remove("text-emerald-400");
                bigCountdownText.classList.add("animate-pulse", "scale-100");
                bigCountdownText.style.animation = "pulse 0.6s cubic-bezier(0.4, 0, 0.6, 1)";
            }
            countdown--;
        } else if (countdown === 0) {
            if (bigCountdownText) {
                bigCountdownText.textContent = "MULAI!";
                bigCountdownText.classList.add("text-emerald-400");
                bigCountdownText.style.animation = "bounce 0.6s ease-out";
            }
            countdown--;
        } else {
            clearInterval(countdownInterval);
            countdownInterval = null;
            
            if (bigCountdownOverlay) {
                bigCountdownOverlay.style.opacity = "0";
                bigCountdownOverlay.style.transition = "opacity 0.3s ease-out";
                
                setTimeout(() => {
                    bigCountdownOverlay.classList.add("hidden");
                    bigCountdownOverlay.classList.remove("flex");
                    bigCountdownOverlay.style.opacity = "1";
                    bigCountdownOverlay.style.transition = "";
                }, 300);
            }
            
            startBattleTimer();
        }
    }, 1000);
}

function startBattleTimer() {
    isGameOver = false;
    isLocalReady = false;
    isRemoteReady = false;
    isBattleLoopRunning = true;
    updateReadyStatus();
    
    if (btnAnswer) {
        btnAnswer.classList.remove("hidden");
        btnAnswer.disabled = false;
        btnAnswer.textContent = "Bentuk Jari & Tahan!";
    }
    
    if (btnStartBattle) {
        btnStartBattle.classList.add("hidden");
    }
    
    log("🎮 Game dimulai! Jawab soal dan bentuk dengan jari!", "success");
    
    createProblem();
    updateBattleDisplay();
    
    battleLoopId = requestAnimationFrame(startBattleLoop);
}

// ==========================================
// MATH PROBLEM GENERATION
// ==========================================

function createProblem() {
    // Hitung waktu untuk round sebelumnya
    if (roundStartTime !== null) {
        const currentTime = Date.now();
        lastRoundTimeTaken = Math.round((currentTime - roundStartTime) / 1000);
        lastRoundTimeTaken = Math.max(1, Math.min(lastRoundTimeTaken, 20));
    }
    
    roundStartTime = Date.now();
    
    // Generate soal matematika adaptif
    const difficulty = getDifficulty(lastRoundTimeTaken, userScore, opponentScore);
    currentProblem = generateMathProblem(difficulty);
    
    const problemEl = document.getElementById("math-problem");
    if (problemEl) {
        problemEl.textContent = currentProblem.display;
    }
    
    log(`📝 Soal: ${currentProblem.display} (Jawaban: ${currentProblem.answer})`, "info");
}

function getDifficulty(timeTaken, myScore, opponentScore) {
    let difficulty = "MUDAH";
    
    if (timeTaken <= 3 && myScore > opponentScore) {
        difficulty = "SULIT";
    } else if (timeTaken <= 7 && myScore >= opponentScore) {
        difficulty = "SEDANG";
    } else if (timeTaken >= 10 || myScore < opponentScore) {
        difficulty = "MUDAH";
    }
    
    return difficulty;
}

function generateMathProblem(difficulty) {
    let a, b, answer, operation;
    
    if (difficulty === "MUDAH") {
        // 1-9 + 1-9 atau 1-9 - 0-9
        a = Math.floor(Math.random() * 9) + 1;
        b = Math.floor(Math.random() * 9);
        operation = Math.random() > 0.5 ? "+" : "-";
    } else if (difficulty === "SEDANG") {
        // 5-20 + 5-20 atau dengan puluhan
        a = Math.floor(Math.random() * 16) + 5;
        b = Math.floor(Math.random() * 16) + 5;
        operation = Math.random() > 0.5 ? "+" : "-";
    } else {
        // 10-50 + 10-50 atau soal lebih kompleks
        a = Math.floor(Math.random() * 41) + 10;
        b = Math.floor(Math.random() * 41) + 10;
        operation = Math.random() > 0.5 ? "+" : "-";
    }
    
    if (operation === "+") {
        answer = a + b;
    } else {
        answer = a - b;
        // Jamin answer positif
        if (answer < 0) {
            answer = Math.abs(answer);
            [a, b] = [b, a];
        }
    }
    
    // Batasi jawaban ke 1-99
    answer = Math.max(1, Math.min(answer, 99));
    
    return {
        a: a,
        b: b,
        operation: operation,
        answer: answer,
        display: `${a} ${operation} ${b} = ?`
    };
}

// ==========================================
// BATTLE LOOP - AUTO-CONFIRM SYSTEM
// ==========================================

let isBattleLoopRunning = false;

function startBattleLoop() {
    if (!isBattleLoopRunning || isGameOver) {
        return;
    }

    const detected = window.gameState?.detectedNumber || 0;
    
    if (detected === currentProblem.answer && currentProblem.answer !== 0) {
        if (!isHolding) {
            isHolding = true;
            holdStartTime = Date.now();
            btnAnswer.textContent = "Menahan... ⏳";
            btnAnswer.classList.add("btn-holding");
            btnAnswer.classList.remove("btn-3d-orange");
            log(`Menahan... Soal: ${currentProblem.display}, Jawaban: ${detected}`, "info");
        }
        
        if (Date.now() - holdStartTime >= HOLD_DURATION) {
            isHolding = false;
            holdStartTime = 0;
            handleCorrectAnswer(detected);
        }
    } else {
        if (isHolding) {
            isHolding = false;
            holdStartTime = 0;
            btnAnswer.textContent = "Bentuk Jari & Tahan!";
            btnAnswer.classList.remove("btn-holding");
            btnAnswer.classList.add("btn-3d-orange");
            log(`Hold dibatalkan. Soal: ${currentProblem.display}, Terdeteksi: ${detected}`, "info");
        }
    }
    
    battleLoopId = requestAnimationFrame(startBattleLoop);
}

function handleCorrectAnswer(detected) {
    if (isGameOver) return;

    userScore++;
    log(`✅ BENAR! ${currentProblem.display} = ${detected}. Skor: ${userScore}/${BATTLE_WIN_SCORE}`, "success");
    
    updateProgress();
    updateBattleDisplay();
    
    sendScoreToOpponent(1);
    
    btnAnswer.textContent = "BENAR! ✅";
    btnAnswer.classList.add("btn-correct");
    btnAnswer.classList.remove("btn-3d-orange", "btn-holding");
    
    if (userScore >= BATTLE_WIN_SCORE) {
        isGameOver = true;
        log("🎉 KAMU MENANG!", "success");
        setTimeout(() => {
            endBattle("win");
        }, 800);
        return;
    }
    
    setTimeout(() => {
        createProblem();
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

function endBattle(result = "timeout") {
    isBattleLoopRunning = false;
    if (battleInterval) clearInterval(battleInterval);
    if (battleLoopId) cancelAnimationFrame(battleLoopId);
    
    if (btnAnswer) {
        btnAnswer.disabled = true;
        btnAnswer.classList.add("hidden");
    }
    
    if (btnStartBattle) {
        btnStartBattle.classList.remove("hidden");
        btnStartBattle.textContent = "👋 Ready";
        btnStartBattle.disabled = false;
    }
    
    isHolding = false;
    holdStartTime = 0;
    
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
    
    const battleResultEl = getElement("battle-result");
    if (battleResultEl) {
        battleResultEl.textContent = battleResult;
    }
    
    submitBattleResult(result);
    
    if (battleResultModal && modalFinalScore) {
        battleResultModal.classList.remove("hidden");
        battleResultModal.classList.add("flex");
        modalFinalScore.textContent = `Kamu ${userScore} - ${opponentScore} Lawan`;
    }
}

if (btnExitRoom) {
    btnExitRoom.addEventListener("click", () => {
        location.href = "/dashboard";
    });
}

if (btnRematch) {
    btnRematch.addEventListener("click", () => {
        if (battleResultModal) {
            battleResultModal.classList.add("hidden");
            battleResultModal.classList.remove("flex");
        }
        
        userScore = 0;
        opponentScore = 0;
        isLocalReady = false;
        isRemoteReady = false;
        
        updateProgress();
        updateBattleDisplay();
        
        if (btnStartBattle) {
            btnStartBattle.disabled = false;
            btnStartBattle.textContent = "✋ Ready";
        }
        
        if (btnAnswer) {
            btnAnswer.textContent = "Bentuk Jari & Tahan!";
            btnAnswer.classList.remove("btn-holding", "btn-correct");
            btnAnswer.classList.add("btn-3d-orange");
        }
        
        createProblem();
    });
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
    const userScoreEl = document.getElementById("user-score");
    const opponentScoreEl = document.getElementById("opponent-score");
    
    if (userScoreEl) userScoreEl.textContent = userScore;
    if (opponentScoreEl) opponentScoreEl.textContent = opponentScore;
    
    if (timerDisplay && currentProblem) {
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
            }),
        });

        if (response.ok) {
            const data = await response.json();
            log(`Hasil tersimpan: ${data.message}`, "success");
        }
    } catch (err) {
        log(`Error submitting result: ${err.message}`, "error");
    }
}

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
    if (!battleArea) {
        console.warn("Battle Hitung: battleArea element not found!");
        return;
    }
    
    log("Battle Hitung initialized!", "success");
    
    // Optional: setupPusher only if Pusher is available
    if (typeof Pusher !== 'undefined') {
        setupPusher();
    } else {
        log("Pusher not available, running in demo mode", "warning");
    }
});

// Cleanup on page unload
window.addEventListener("beforeunload", () => {
    isBattleLoopRunning = false;
    if (countdownInterval) clearInterval(countdownInterval);
    if (autoStartInterval) clearInterval(autoStartInterval);
    if (battleLoopId) cancelAnimationFrame(battleLoopId);
    if (peerConnection) peerConnection.close();
});
