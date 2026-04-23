@extends('layouts.app')

@section('content')
    <style>
        .wait-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            padding: 3rem 1rem;
        }

        .card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .room-code {
            font-family: monospace;
            background: #f1f5f9;
            padding: .5rem 1rem;
            border-radius: 12px;
            font-weight: 700
        }

        .status-badge {
            padding: .4rem .8rem;
            border-radius: 999px;
            font-weight: 700
        }
    </style>

    <div class="wait-container">
        <div class="card">
            <h2 class="text-3xl font-black text-slate-800">Room Waiting</h2>
            <p class="text-slate-600 mt-2">Bagikan kode ini ke teman untuk bergabung. Tunggu sampai lawan bergabung.</p>

            <div class="mt-6 flex items-center gap-4">
                <div>
                    <div class="text-sm text-slate-500">Kode Room</div>
                    <div id="room-code" class="room-code text-2xl mt-2">{{ $room_code }}</div>
                </div>
                <div class="ml-auto text-right">
                    <div class="text-sm text-slate-500">Status</div>
                    <div id="room-status" class="status-badge bg-yellow-200 text-slate-800 mt-2">
                        {{ optional($room)->status ?? 'not_found' }}</div>
                </div>
            </div>

            <div class="mt-6" id="players">
                <div class="mb-3"><strong>Host:</strong> {{ optional($room->host)->name ?? auth()->user()->name }}</div>
                <div id="guest-row" class="mb-3"><strong>Guest:</strong> {{ optional($room->guest)->name ?? 'Belum ada' }}
                </div>
            </div>

            <div class="mt-6">
                <button id="btn-leave" class="btn-3d-red">Keluar</button>
            </div>
        </div>
    </div>

    <script>
        const roomCode = document.getElementById('room-code').textContent.trim();
        const roomStatusEl = document.getElementById('room-status');
        const guestRow = document.getElementById('guest-row');
        const pollInterval = 2000;
        let pollId = null;

        async function pollRoomStatus() {
            try {
                const res = await fetch(`/jarimatika/room/status?room_code=${encodeURIComponent(roomCode)}`, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json'
                    }
                });
                if (!res.ok) {
                    console.error('room status poll failed', res.status);
                    return;
                }
                const data = await res.json();
                console.log('roomStatus poll', data);

                if (data.status === 'waiting') {
                    roomStatusEl.textContent = 'waiting';
                    roomStatusEl.className = 'status-badge bg-yellow-200 text-slate-800 mt-2';
                    guestRow.innerHTML = '<strong>Guest:</strong> Belum ada';
                } else if (data.status === 'matched') {
                    roomStatusEl.textContent = 'matched';
                    roomStatusEl.className = 'status-badge bg-emerald-200 text-slate-800 mt-2';
                    guestRow.innerHTML = `<strong>Guest:</strong> ${data.opponent || 'Opponent'}`;
                    // Redirect to battle when matched
                    const gid = data.gameId || data.game_id;
                    if (gid) {
                        clearInterval(pollId);
                        location.href = `/jarimatika/battle?gameId=${encodeURIComponent(gid)}`;
                    }
                } else if (data.status === 'error') {
                    roomStatusEl.textContent = 'error';
                    roomStatusEl.className = 'status-badge bg-rose-200 text-slate-800 mt-2';
                }
            } catch (e) {
                console.error('poll error', e);
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            pollId = setInterval(pollRoomStatus, pollInterval);
            pollRoomStatus();
        });

        document.getElementById('btn-leave').addEventListener('click', () => {
            // simple: go back to match lobby
            location.href = '/jarimatika/match';
        });
    </script>
@endsection
