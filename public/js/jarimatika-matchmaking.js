const quickJoinUrl =
    window?.jarimatikaMatchEndpoints?.quickJoinUrl || "/jarimatika/match/join";
const quickStatusUrl =
    window?.jarimatikaMatchEndpoints?.statusUrl || "/jarimatika/match/status";
const createRoomUrl =
    window?.jarimatikaMatchEndpoints?.createRoomUrl ||
    "/jarimatika/room/create";
const joinRoomUrl =
    window?.jarimatikaMatchEndpoints?.joinRoomUrl || "/jarimatika/room/join";
const roomStatusUrl =
    window?.jarimatikaMatchEndpoints?.roomStatusUrl ||
    "/jarimatika/room/status";
const battleUrl =
    window?.jarimatikaMatchEndpoints?.battleUrl || "/jarimatika/battle";
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
const btnCopyRoomCode = document.getElementById("btn-copy-room-code");
const roomWaitingNote = document.getElementById("room-waiting-note");

let isSearching = false;
let pollingInterval = null;
let currentMode = "quick";
let currentRoomCode = null;
let isRoomHost = false;

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
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify({}),
        });

        const data = await response.json();

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
    isRoomHost = true;

    try {
        const response = await fetch(createRoomUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify({}),
        });
        const data = await response.json();

        if (data.status === "created") {
            currentRoomCode = data.room_code;
            // Redirect ke halaman waiting room
            location.href = `/jarimatika/room/${encodeURIComponent(data.room_code)}/waiting`;
        } else {
            setStatus("Gagal membuat room.", "text-rose-400");
        }
    } catch (error) {
        setStatus("Gagal terhubung ke server.", "text-rose-400");
    }
}

async function joinRoom() {
    const code = roomCodeInput.value.trim().toUpperCase();

    if (isRoomHost && currentRoomCode) {
        try {
            await navigator.clipboard.writeText(currentRoomCode);
            setStatus("Kode room disalin ke clipboard.", "text-emerald-600");
            return;
        } catch (error) {
            setStatus(
                "Tidak bisa menyalin kode. Salin secara manual.",
                "text-rose-400",
            );
            return;
        }
    }

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
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify({ room_code: code }),
        });
        const data = await response.json();

        if (data.status === "matched") {
            handleMatchFound(data);
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
                headers: { Accept: "application/json" },
            });
            const data = await response.json();

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

    setTimeout(() => {
        location.href = `${battleUrl}?gameId=${encodeURIComponent(data.gameId)}`;
    }, 1200);
}

cancelButton?.addEventListener("click", () => {
    stopPolling();
    location.href = "/dashboard";
});

btnQuick?.addEventListener("click", () => {
    isRoomHost = false;
    currentRoomCode = null;
    setRoomVisibility(false);
    roomCodeInput.disabled = false;
    btnCopyRoomCode?.classList.add("hidden");
    roomWaitingNote?.classList.add("hidden");
    quickMatch();
});

btnCreateRoom?.addEventListener("click", () => {
    createRoom();
});

btnShowJoin?.addEventListener("click", () => {
    isRoomHost = false;
    setRoomVisibility(true);
    setStatus("Masukkan kode room dan tekan Gabung.", "text-slate-900");
    queueHint.textContent = "Room code terisi manual.";
    roomCodeInput.disabled = false;
    roomCodeInput.value = "";
    btnJoinRoom.textContent = "Gabung Room";
    btnJoinRoom.classList.remove("bg-slate-500");
    btnJoinRoom.classList.add("bg-indigo-600");
    btnCopyRoomCode?.classList.add("hidden");
    roomWaitingNote?.classList.add("hidden");
    roomCreated.textContent = "Masukkan kode room untuk bergabung.";
    setElementText(queueGameId, "-");
    setElementText(queueOpponent, "Belum ada");
});

btnJoinRoom?.addEventListener("click", () => {
    joinRoom();
});

btnCopyRoomCode?.addEventListener("click", async () => {
    if (!currentRoomCode) return;

    try {
        await navigator.clipboard.writeText(currentRoomCode);
        setStatus("Kode room disalin ke clipboard.", "text-emerald-600");
    } catch (error) {
        setStatus(
            "Tidak bisa menyalin kode. Salin secara manual.",
            "text-rose-400",
        );
    }
});

window.addEventListener("DOMContentLoaded", () => {
    queueConnection.textContent = "Terhubung";
    queueConnection.className = "font-semibold text-emerald-400";
    quickMatch();
});
