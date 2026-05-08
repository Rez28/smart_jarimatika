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
const localVideoElement = document.querySelector(".input_video");

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
const localPeerId = `peer-${Math.random().toString(36).slice(2, 12)}`;
let peerConnection = null;
let localStream = null;
let remotePeerId = null;
let isOfferer = false;
let offerSent = false; // Prevent duplicate offers
let iceCandidateBuffer = []; // Buffer ICE candidates until remote description is set
let remoteDescriptionSet = false; // Track if remote description is set
const userId = battleArea?.dataset.userId || null;
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

async function postSignal(type, payload) {
    if (!csrfToken) {
        logBattle("Peer signal gagal: CSRF token hilang.");
        return;
    }

    try {
        const response = await fetch("/jarimatika/battle/signal", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                gameId,
                type,
                payload,
                socket_id: pusherSocketId,
            }),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            logBattle(`Peer signal error (${response.status}): ${errorData.message || response.statusText}`);
            return false;
        }

        return true;
    } catch (err) {
        logBattle(`Peer signal error: ${err.message}`);
        return false;
    }
}

function closePeerConnection() {
    if (peerConnection) {
        peerConnection.close();
        peerConnection = null;
        remoteDescriptionSet = false;
        iceCandidateBuffer = [];
        logBattle("PeerConnection ditutup.");
    }
}

function initializePeerConnection() {
    if (peerConnection) return peerConnection;

    console.log("🔧 Creating new RTCPeerConnection...");
    peerConnection = new RTCPeerConnection({
        iceServers: [{ urls: "stun:stun.l.google.com:19302" }],
    });

    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            console.log("📤 ICE candidate generated:", event.candidate);
            postSignal("ice", {
                peerId: localPeerId,
                candidate: event.candidate,
            });
        }
    };

    peerConnection.ontrack = (event) => {
        console.log("🎥 ontrack event fired! Full event:", event);
        console.log("   Streams:", event.streams);
        console.log("   Track:", {
            kind: event.track.kind,
            id: event.track.id,
            enabled: event.track.enabled,
            readyState: event.track.readyState
        });
        
        logBattle(`🎥 Remote track diterima: ${event.track.kind}`);
        
        if (!event.streams || event.streams.length === 0) {
            console.error("❌ No streams in ontrack event!");
            logBattle("❌ ontrack event tetapi tidak ada stream!");
            return;
        }
        
        if (opponentVideo) {
            console.log("✅ Setting opponent video srcObject...");
            const stream = event.streams[0];
            console.log("   Stream ID:", stream.id, "Tracks:", stream.getTracks());
            opponentVideo.srcObject = stream;
            console.log("   srcObject set to:", opponentVideo.srcObject);
            
            opponentVideo.onloadedmetadata = () => {
                console.log("✅ Video metadata loaded, calling play()...");
                opponentVideo.play().catch((err) => {
                    console.error("❌ Play error:", err);
                    logBattle(`Video play error: ${err.message}`);
                });
            };
            
            opponentVideo.play().catch((err) => {
                console.error("❌ Play error (immediate):", err);
                logBattle(`Video play error (immediate): ${err.message}`);
            });
            
            updateOpponentCameraUI(true);
            //setOpponentStatus("🎥 Kamera lawan aktif", "#10b981");
            logBattle("✅ Kamera lawan berhasil ditampilkan!");
        } else {
            console.error("❌ opponentVideo element not found!");
            logBattle("❌ Element #opponent-video tidak ditemukan!");
        }
    };
    
    peerConnection.onconnectionstatechange = () => {
        console.log("🔌 Connection state:", peerConnection.connectionState);
        logBattle(`Connection state: ${peerConnection.connectionState}`);
    };

    peerConnection.oniceconnectionstatechange = () => {
        console.log("🧊 ICE connection state:", peerConnection.iceConnectionState);
        logBattle(`ICE state: ${peerConnection.iceConnectionState}`);
    };

    peerConnection.onsignalingstatechange = () => {
        console.log("📡 Signaling state:", peerConnection.signalingState);
    };

    return peerConnection;
}

async function createAndSendOffer() {
    try {
        // Prevent duplicate offers
        if (offerSent) {
            logBattle("Offer sudah dikirim, menunggu response...");
            return;
        }

        // Close old PC if it's already negotiating
        if (peerConnection && (peerConnection.signalingState !== "stable" || peerConnection.connectionState !== "new")) {
            logBattle("Closing previous PeerConnection before creating new offer...");
            closePeerConnection();
        }

        if (!localStream) {
            localStream =
                localVideoElement && localVideoElement.srcObject
                    ? localVideoElement.srcObject
                    : await navigator.mediaDevices.getUserMedia({
                          video: true,
                          audio: false,
                      });
        }

        const pc = initializePeerConnection();

        console.log("🎬 Local stream check:", {
            exists: !!localStream,
            trackCount: localStream?.getTracks().length || 0,
            tracks: localStream?.getTracks().map(t => ({ kind: t.kind, enabled: t.enabled, id: t.id }))
        });

        if (localStream) {
            const tracksToAdd = [];
            localStream.getTracks().forEach((track) => {
                const hasSender = pc.getSenders().find((s) => s.track === track);
                console.log(`   Track ${track.kind}:`, { id: track.id, enabled: track.enabled, alreadyAdded: !!hasSender });
                
                if (!hasSender) {
                    console.log(`   📌 Adding track: ${track.kind}`);
                    const sender = pc.addTrack(track, localStream);
                    tracksToAdd.push({ track, sender });
                    console.log(`   ✅ Track added, sender:`, sender);
                }
            });
            console.log(`✅ Total tracks added: ${tracksToAdd.length}`);
        } else {
            console.error("❌ No localStream available!");
        }

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        const offerPayload = {
            peerId: localPeerId,
            sdp: offer.sdp,  // IMPORTANT: Only send SDP string, not the entire object
        };

        console.log("📤 Sending offer:", offerPayload);
        logBattle("📤 Mengirim offer WebRTC...");

        const success = await postSignal("offer", offerPayload);
        
        if (success) {
            offerSent = true;
            isOfferer = true;
            setOpponentStatus("Menunggu jawaban lawan...", "#fbbf24");
            logBattle("✅ Offer WebRTC terkirim.");
        } else {
            logBattle("❌ Gagal mengirim offer WebRTC.");
            setOpponentStatus("Gagal mengirim offer ke server", "#f87171");
        }
    } catch (error) {
        console.error("Offer creation error:", error);
        logBattle(`❌ Gagal buat offer WebRTC: ${error.message}`);
        setOpponentStatus("Gagal memulai koneksi lawan", "#f87171");
    }
}

async function handleSignal(type, data) {
    if (!data || !data.type || !data.payload) return;

    console.log(`📡 handleSignal called: type=${type}, data:`, data);

    // Payload should be an object at this point (parsed from JSON string)
    let payload = data.payload;
    if (typeof payload === 'string') {
        try {
            console.log("Parsing payload string:", payload.substring(0, 100));
            payload = JSON.parse(payload);
        } catch (e) {
            console.error("Payload parse error:", e);
            logBattle(`❌ Payload parse error: ${e.message}`);
            return;
        }
    }

    console.log("✅ Parsed payload:", JSON.stringify(payload, null, 2));

    if (payload.peerId === localPeerId) {
        console.log("🔄 Ignoring self signal");
        return; // Ignore self
    }

    remotePeerId = payload.peerId || remotePeerId;

    try {
        if (type === "offer") {
            console.log("📥 Received OFFER from:", payload.peerId);
            console.log("   SDP type:", typeof payload.sdp);
            console.log("   SDP first 100 chars:", payload.sdp?.substring(0, 100));
            
            // Close old PC if exists and not stable
            if (peerConnection && peerConnection.signalingState !== "stable") {
                logBattle("Closing previous PeerConnection before accepting offer...");
                closePeerConnection();
                remoteDescriptionSet = false;
                iceCandidateBuffer = [];
            }

            const pc = initializePeerConnection();

            if (localStream == null) {
                localStream =
                    (localVideoElement && localVideoElement.srcObject) ||
                    (await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false,
                    }));
                console.log("📸 Got local stream:", { 
                    id: localStream?.id, 
                    trackCount: localStream?.getTracks().length 
                });
            }

            if (localStream) {
                console.log("📌 Adding tracks from localStream (answerer side)...");
                const tracksToAdd = [];
                localStream.getTracks().forEach((track) => {
                    const hasSender = pc.getSenders().find((s) => s.track === track);
                    console.log(`   Track ${track.kind}:`, { id: track.id, alreadyAdded: !!hasSender });
                    
                    if (!hasSender) {
                        console.log(`   ✅ Adding ${track.kind} track`);
                        const sender = pc.addTrack(track, localStream);
                        tracksToAdd.push(sender);
                    }
                });
                console.log(`✅ Total tracks added by answerer: ${tracksToAdd.length}`);
            } else {
                console.error("❌ No localStream in offer handler!");
            }

            // Create RTCSessionDescription from SDP string
            // IMPORTANT: Fix escaped newlines that may come from JSON encoding
            let sdpText = payload.sdp;
            if (typeof sdpText === 'string') {
                // Replace escaped newlines with actual newlines
                sdpText = sdpText.replace(/\\n/g, '\n');
            }
            
            const remoteOffer = new RTCSessionDescription({
                type: 'offer',
                sdp: sdpText,
            });

            console.log("Setting remote description with:", remoteOffer);
            await pc.setRemoteDescription(remoteOffer);
            remoteDescriptionSet = true;
            console.log("✅ Remote description set! Processing buffered ICE candidates...");
            
            // Process buffered ICE candidates
            for (const candidate of iceCandidateBuffer) {
                try {
                    await pc.addIceCandidate(candidate);
                    console.log("✅ Buffered ICE candidate added");
                } catch (err) {
                    console.error("Buffered ICE error:", err);
                }
            }
            iceCandidateBuffer = [];
            
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);

            await postSignal("answer", {
                peerId: localPeerId,
                sdp: answer.sdp,  // Send only SDP string
            });

            setOpponentStatus("Terkoneksi ke lawan", "#10b981");
            logBattle("✅ Answer WebRTC dikirim.");
        }

        if (type === "answer" && payload.sdp) {
            console.log("📥 Received ANSWER from:", payload.peerId);
            console.log("   SDP type:", typeof payload.sdp);
            
            const pc = initializePeerConnection();
            
            // Create RTCSessionDescription from SDP string
            // IMPORTANT: Fix escaped newlines that may come from JSON encoding
            let sdpText = payload.sdp;
            if (typeof sdpText === 'string') {
                // Replace escaped newlines with actual newlines
                sdpText = sdpText.replace(/\\n/g, '\n');
            }
            
            const remoteAnswer = new RTCSessionDescription({
                type: 'answer',
                sdp: sdpText,
            });
            
            console.log("Setting remote description with:", remoteAnswer);
            await pc.setRemoteDescription(remoteAnswer);
            remoteDescriptionSet = true;
            console.log("✅ Remote description set! Processing buffered ICE candidates...");
            
            // Process buffered ICE candidates
            for (const candidate of iceCandidateBuffer) {
                try {
                    await pc.addIceCandidate(candidate);
                    console.log("✅ Buffered ICE candidate added");
                } catch (err) {
                    console.error("Buffered ICE error:", err);
                }
            }
            iceCandidateBuffer = [];
            
            setOpponentStatus("Koneksi lawan terinstal", "#10b981");
            logBattle("✅ Answer diterima dan remote description di-set.");
        }

        if (type === "ice" && payload.candidate) {
            const pc = initializePeerConnection();
            
            if (!remoteDescriptionSet) {
                // Buffer the candidate until remote description is set
                console.log("📦 Buffering ICE candidate (remote description not set yet)");
                iceCandidateBuffer.push(new RTCIceCandidate(payload.candidate));
            } else {
                // Remote description is already set, add immediately
                try {
                    console.log("📥 Adding ICE candidate from:", payload.peerId);
                    await pc.addIceCandidate(new RTCIceCandidate(payload.candidate));
                    logBattle("✅ ICE candidate ditambahkan.");
                } catch (err) {
                    console.error("ICE error:", err);
                    logBattle(`ICE candidate error: ${err.message}`);
                }
            }
        }
    } catch (err) {
        console.error("Signal handling error:", err);
        logBattle(`❌ Signal handling error: ${err.message}`);
    }
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
    offerSent = false;
    closePeerConnection();
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

    channel.bind("PeerSignal", (data) => {
        console.log("🔔 Raw PeerSignal received:", JSON.stringify(data, null, 2));
        
        // Parse the data if it's a string (Pusher sends 'data' as JSON string)
        let eventData = data;
        if (typeof data === 'string') {
            try {
                console.log("Parsing string data:", data.substring(0, 100));
                eventData = JSON.parse(data);
            } catch (e) {
                logBattle(`❌ PeerSignal parse error: ${e.message}`);
                console.error("Parse error for:", data);
                return;
            }
        }
        
        console.log("✅ Parsed eventData:", JSON.stringify(eventData, null, 2));
        
        if (!eventData || !eventData.type || !eventData.payload) {
            logBattle(`❌ Invalid PeerSignal: missing type or payload`);
            console.log("eventData structure:", eventData);
            return;
        }
        
        console.log("📨 Calling handleSignal with type:", eventData.type);
        handleSignal(eventData.type, eventData);
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
    offerSent = false; // Reset flag for new battle
    remoteDescriptionSet = false;
    iceCandidateBuffer = [];
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

btnCameraSwitch?.addEventListener("click", async () => {
    if (!canUseCamera()) {
        alert(
            "Kamera tidak tersedia: gunakan browser modern, akses melalui https://localhost, atau pakai ngrok / server HTTPS.",
        );
        return;
    }

    if (!isCameraEnabled) {
        if (window.startCameraSystem) window.startCameraSystem();

        // Pastikan stream sudah tersedia dari video input MediaPipe
        if (!localStream) {
            localStream =
                window.videoElement && window.videoElement.srcObject
                    ? window.videoElement.srcObject
                    : await navigator.mediaDevices.getUserMedia({
                          video: true,
                          audio: false,
                      });
        }

        await createAndSendOffer();

        isCameraEnabled = true;
        btnCameraSwitch.textContent = "Matikan Kamera";
        setOpponentStatus("Menunggu lawan terhubung...", "#38bdf8");
    } else {
        if (window.stopCameraSystem) window.stopCameraSystem();
        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }
        if (opponentVideo) {
            opponentVideo.pause();
            opponentVideo.srcObject = null;
        }
        localStream = null;
        isCameraEnabled = false;
        btnCameraSwitch.textContent = "Nyalakan Kamera";
        updateOpponentCameraUI(false);
        setOpponentStatus("Kamera dimatikan", "#94a3b8");
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