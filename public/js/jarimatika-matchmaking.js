const joinUrl = '/jarimatika/match/join';
const statusUrl = '/jarimatika/match/status';
const battleUrl = '/jarimatika/battle';
const queueStatus = document.getElementById('queue-status');
const queueHint = document.getElementById('queue-hint');
const queueGameId = document.getElementById('queue-game-id');
const queueOpponent = document.getElementById('queue-opponent');
const queueConnection = document.getElementById('queue-connection');
const cancelButton = document.getElementById('btn-cancel-search');

let isSearching = false;
let pollingInterval = null;

function setStatus(text, color = 'text-slate-900') {
    queueStatus.textContent = text;
    queueStatus.className = `mt-3 text-2xl font-bold ${color}`;
}

function setElementText(el, value) {
    if (!el) return;
    el.textContent = value;
}

async function joinQueue() {
    try {
        const response = await fetch(joinUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({}),
        });

        const data = await response.json();

        if (data.status === 'matched') {
            handleMatchFound(data);
        } else {
            setStatus('Menunggu lawan...', 'text-slate-900');
            queueHint.textContent = 'Antrean aktif. Kami sedang mencari lawan.';
            setElementText(queueGameId, '-');
            setElementText(queueOpponent, 'Belum ada');
            startPolling();
        }
    } catch (error) {
        setStatus('Gagal terhubung ke server.', 'text-rose-400');
        queueHint.textContent = 'Periksa koneksi internet atau refresh halaman.';
    }
}

function startPolling() {
    if (pollingInterval) {
        return;
    }
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (data.status === 'matched') {
                handleMatchFound(data);
            }
        } catch (error) {
            queueConnection.textContent = 'Terputus';
            queueConnection.className = 'font-semibold text-rose-400';
        }
    }, 2000);
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

function handleMatchFound(data) {
    stopPolling();
    setStatus('Lawan ditemukan!', 'text-emerald-600');
    queueHint.textContent = 'Bersiap memasuki arena battle.';
    setElementText(queueGameId, data.gameId);
    setElementText(queueOpponent, data.opponent || 'Opponent');
    queueConnection.textContent = 'Terhubung';
    queueConnection.className = 'font-semibold text-emerald-400';

    setTimeout(() => {
        location.href = `${battleUrl}?gameId=${encodeURIComponent(data.gameId)}`;
    }, 1200);
}

cancelButton?.addEventListener('click', () => {
    stopPolling();
    location.href = '/dashboard';
});

window.addEventListener('DOMContentLoaded', () => {
    queueConnection.textContent = 'Terhubung';
    queueConnection.className = 'font-semibold text-emerald-400';
    joinQueue();
});
