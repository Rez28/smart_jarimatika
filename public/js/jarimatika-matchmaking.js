const quickJoinUrl = "/jarimatika/match/join";
const quickStatusUrl = "/jarimatika/match/status";
const createRoomUrl = "/jarimatika/room/create";
const joinRoomUrl = "/jarimatika/room/join";
const roomStatusUrl = "/jarimatika/room/status";
const battleUrl = "/jarimatika/battle";
const queueStatus = document.getElementById("queue-status");
const queueHint = document.getElementById("queue-hint");
const queueGameId = document.getElementById("queue-game-id");
const queueOpponent = document.getElementById("queue-opponent");
const queueConnection = document.getElementById("queue-connection");
const cancelButton = document.getElementById("btn-cancel-search");
const btnQuick = document.getElementById("btn-quick-match");
const btnCreateRoom = document.getElementById("btn-create-room");
const btnShowJoin = document.getElementById("btn-show-join");
const roomControls = document.getElementById("room-controls");
const roomCodeInput = document.getElementById("room-code-input");
const btnJoinRoom = document.getElementById("btn-join-room");
const roomCreated = document.getElementById("room-created");

let isSearching = false;
let pollingInterval = null;
let currentMode = "quick";
let currentRoomCode = null;

function setStatus(text, color = "text-slate-900") {
    queueStatus.textContent = text;
    queueStatus.className = `mt-3 text-2xl font-bold ${color}`;
}

function setElementText(el, value) {
    if (!el) return;
    el.textContent = value;
}

async function quickMatch() {
    currentMode = "quick";
    setRoomVisibility(false);
    setStatus("Mencari lawan (Quick)...", "text-slate-900");
    queueHint.textContent = "Memulai pencarian random.";
    setElementText(queueGameId, "-");
    setElementText(queueOpponent, "Belum ada");

    try {
        const response = await fetch(quickJoinUrl, {
            method: "POST",
            credentials: 'same-origin',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify({}),
        });

        if (!response.ok) {
            console.error('Quick join responded with', response.status);
            const text = await response.text();
            console.error('Body:', text);
            setStatus('Gagal membuat permintaan quick match.', 'text-rose-400');
            queueHint.textContent = 'Periksa koneksi atau login.';
            return;
        }

        const data = await response.json();
        console.log('quickMatch response', data);

        if (data.status === "matched") {
            handleMatchFound(data);
        } else {
            startPolling();
        }
    } catch (error) {
        setStatus("Gagal terhubung ke server.", "text-rose-400");
        queueHint.textContent =
            "Periksa koneksi internet atau refresh halaman.";
    }
}

async function createRoom() {
    currentMode = "room";
    setRoomVisibility(true);
    setStatus("Room dibuat. Tunggu lawan bergabung.", "text-slate-900");
    queueHint.textContent = "Bagikan kode room ke teman.";

    try {
        const response = await fetch(createRoomUrl, {
            method: "POST",
            credentials: 'same-origin',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify({}),
        });

        if (!response.ok) {
            console.error('Create room failed', response.status);
            const text = await response.text();
            console.error('Body:', text);
            setStatus('Gagal membuat room.', 'text-rose-400');
            queueHint.textContent = 'Periksa koneksi atau login.';
            return;
        }

        const data = await response.json();
        console.log('createRoom response', data);

        if (data.status === "created") {
            currentRoomCode = data.room_code;
            // redirect host to waiting page
            location.href = `/jarimatika/room/wait?room_code=${encodeURIComponent(data.room_code)}`;
        } else {
            setStatus("Gagal membuat room.", "text-rose-400");
        }
    } catch (error) {
        setStatus("Gagal terhubung ke server.", "text-rose-400");
    }
}

async function joinRoom() {
    const code = roomCodeInput.value.trim().toUpperCase();
    if (!code) {
        setStatus("Masukkan kode room terlebih dahulu.", "text-rose-400");
        return;
    }

    currentMode = "room";
    roomCreated.textContent = `Bergabung ke room ${code}`;
    queueGameId.textContent = code;

    try {
        const response = await fetch(joinRoomUrl, {
            method: "POST",
            credentials: 'same-origin',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify({ room_code: code }),
        });

        if (!response.ok) {
            console.error('Join room failed', response.status);
            const text = await response.text();
            console.error('Body:', text);
            try {
                const err = JSON.parse(text);
                setStatus(err.message || 'Gagal bergabung room.', 'text-rose-400');
            } catch (e) {
                setStatus('Gagal bergabung room.', 'text-rose-400');
            }
            queueHint.textContent = "Cek kode room atau buat room baru.";
            return;
        }

        const data = await response.json();
        console.log('joinRoom response', data);

        if (data.status === "matched") {
            handleMatchFound(data);
        } else if (data.status === "waiting" || data.status === 'joined') {
            // guest joined but room not yet started - redirect to waiting page
            location.href = `/jarimatika/room/wait?room_code=${encodeURIComponent(code)}`;
        } else if (data.status === "error") {
            setStatus(data.message, "text-rose-400");
            queueHint.textContent = "Cek kode room atau buat room baru.";
        }
    } catch (error) {
        setStatus("Gagal terhubung ke server.", "text-rose-400");
    }
}

function startPolling() {
    if (pollingInterval) return;

    pollingInterval = setInterval(async () => {
        try {
            let url = quickStatusUrl;
            if (currentMode === "room" && currentRoomCode) {
                url = `${roomStatusUrl}?room_code=${encodeURIComponent(currentRoomCode)}`;
            }

            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: "application/json" },
            });

            if (!response.ok) {
                console.error('Status poll failed', response.status);
                queueConnection.textContent = "Terputus";
                queueConnection.className = "font-semibold text-rose-400";
                return;
            }

            const data = await response.json();
            console.log('poll status', data);

            if (data.status === "matched") {
                handleMatchFound(data);
            }
        } catch (error) {
            queueConnection.textContent = "Terputus";
            queueConnection.className = "font-semibold text-rose-400";
        }
    }, 2000);
}

function startRoomPolling(code) {
    currentRoomCode = code;
    stopPolling();
    startPolling();
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

function setRoomVisibility(show) {
    if (!roomControls) return;
    if (show) {
        roomControls.classList.remove("hidden");
    } else {
        roomControls.classList.add("hidden");
    }
}

function handleMatchFound(data) {
    stopPolling();
    setStatus("Lawan ditemukan!", "text-emerald-600");
    queueHint.textContent = "Bersiap memasuki arena battle.";
    setElementText(queueGameId, data.gameId ?? currentRoomCode ?? "-");
    setElementText(queueOpponent, data.opponent || "Opponent");
    queueConnection.textContent = "Terhubung";
    queueConnection.className = "font-semibold text-emerald-400";

    // fallback: beberapa endpoint mungkin mengembalikan gameId atau hanya room_code
    const gid = data.gameId || data.game_id || data.room_code || currentRoomCode;
    console.log('Redirecting to battle with gid=', gid, 'raw response=', data);

    setTimeout(() => {
        if (gid) {
            location.href = `${battleUrl}?gameId=${encodeURIComponent(gid)}`;
        } else {
            setStatus('Gagal mendapatkan gameId.', 'text-rose-400');
            queueHint.textContent = 'Silahkan refresh atau coba lagi.';
        }
    }, 1200);
}

cancelButton?.addEventListener("click", () => {
    stopPolling();
    location.href = "/dashboard";
});

btnQuick?.addEventListener("click", () => {
    setRoomVisibility(false);
    quickMatch();
});

btnCreateRoom?.addEventListener("click", () => {
    createRoom();
});

btnShowJoin?.addEventListener("click", () => {
    setRoomVisibility(true);
    setStatus("Masukkan kode room dan tekan Gabung.", "text-slate-900");
    queueHint.textContent = "Room code terisi manual.";
});

btnJoinRoom?.addEventListener("click", () => {
    joinRoom();
});

window.addEventListener("DOMContentLoaded", () => {
    queueConnection.textContent = "Terhubung";
    queueConnection.className = "font-semibold text-emerald-400";
    quickMatch();
});
