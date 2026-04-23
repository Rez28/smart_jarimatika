@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .room-code-display {
            background: linear-gradient(135deg, #38BDF8, #0EA5E9);
            border-radius: 24px;
            padding: 32px;
            text-align: center;
            color: white;
            border-bottom: 8px solid #0284C7;
        }

        .code-label {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.9;
            margin-bottom: 12px;
        }

        .code-text {
            font-size: 48px;
            font-weight: 900;
            letter-spacing: 6px;
            margin-bottom: 20px;
            font-family: 'Monaco', 'Courier New', monospace;
        }

        .btn-copy {
            background-color: white;
            color: #38BDF8;
            font-weight: bold;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-copy:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
        }

        .btn-copy:active {
            transform: scale(0.98);
        }

        .waiting-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(56, 189, 248, 0.2);
            border-top-color: #38BDF8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .radar-pulse {
            animation: ping-large 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes ping-large {

            75%,
            100% {
                transform: scale(1.8);
                opacity: 0;
            }
        }

        .btn-3d-red {
            background-color: #CF0F0F;
            border-bottom: 6px solid #900b0b;
            transition: all 0.15s ease;
        }

        .btn-3d-red:active {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .share-info {
            background: #FEF3C7;
            border-left: 4px solid #F79A19;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .share-info-title {
            font-weight: bold;
            color: #92400E;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .share-info-text {
            color: #B45309;
            font-size: 14px;
        }
    </style>

    <div class="jarimatika-container py-10 px-4 sm:px-6">
        <div class="max-w-2xl mx-auto">

            <div class="text-center mb-10">
                <div
                    class="inline-block bg-[#38BDF8] text-white px-6 py-2 rounded-full text-xl font-bold uppercase tracking-widest border-b-4 border-[#0284C7] mb-4 shadow-sm">
                    ⏳ Ruang Tunggu
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-800 drop-shadow-sm">Menunggu Lawan Bergabung...</h1>
            </div>

            <!-- Room Code Display -->
            <div class="room-code-display mb-8">
                <div class="code-label">Kode Room</div>
                <div class="code-text" id="room-code-display">{{ $roomCode }}</div>
                <button id="btn-copy-code" class="btn-copy">📋 Salin Kode</button>
            </div>

            <!-- Share Info -->
            <div class="share-info">
                <div class="share-info-title">
                    <span>💡</span>
                    <span>Cara Berbagi Kode</span>
                </div>
                <div class="share-info-text">
                    Bagikan kode room ini kepada teman Anda. Teman dapat memasukkan kode ini di halaman
                    <strong>"Join Room"</strong> untuk bergabung dengan ruangan ini.
                </div>
            </div>

            <!-- Waiting Section -->
            <div class="game-card p-8 md:p-12 border-b-[8px] border-[#38BDF8]">

                <div class="flex flex-col md:flex-row items-center justify-between gap-8">

                    <!-- Your Profile -->
                    <div class="flex-1 text-center">
                        <div
                            class="w-32 h-32 mx-auto bg-sky-100 rounded-full border-[8px] border-[#38BDF8] flex items-center justify-center text-6xl shadow-inner mb-4">
                            👤
                        </div>
                        <h2 class="text-2xl font-black text-slate-800">{{ $user->name }}</h2>
                        <p class="text-[#38BDF8] font-bold uppercase tracking-widest mt-2">
                            @if ($isHost)
                                🏠 Host
                            @else
                                👥 Tamu
                            @endif
                        </p>
                    </div>

                    <!-- VS -->
                    <div class="flex-shrink-0">
                        <div
                            class="bg-[#FFE52A] text-slate-800 text-5xl font-black w-24 h-24 rounded-full flex items-center justify-center border-[6px] border-white shadow-xl">
                            VS
                        </div>
                    </div>

                    <!-- Opponent Profile (Waiting) -->
                    <div class="flex-1 text-center">
                        <div
                            class="w-32 h-32 mx-auto bg-slate-100 rounded-full border-[8px] border-slate-300 flex items-center justify-center mb-4 relative">
                            <div class="waiting-spinner"></div>
                            <div class="absolute inset-0 rounded-full border-4 border-[#F79A19] opacity-75 radar-pulse"
                                style="z-index: -1;"></div>
                        </div>
                        <h2 class="text-2xl font-black text-slate-400">Menunggu...</h2>
                        <p id="waiting-status"
                            class="text-[#F79A19] font-bold uppercase tracking-widest mt-2 animate-pulse">
                            Pemain lain bergabung
                        </p>
                    </div>

                </div>

            </div>

            <!-- Status Info -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="game-card p-6 border-b-[8px] border-[#BBCB64]">
                    <h3 class="text-slate-400 font-bold uppercase tracking-widest mb-4 border-b-2 border-slate-100 pb-2">
                        📊 Informasi Room
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-semibold">Status</span>
                            <span class="bg-[#38BDF8] text-white px-3 py-1 rounded-xl text-sm font-bold">Menunggu</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-semibold">Pemain Terhubung</span>
                            <span class="font-mono font-bold text-slate-800">1 / 2</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-semibold">Mode</span>
                            <span class="font-bold text-[#38BDF8]">1 vs 1 Battle</span>
                        </div>
                    </div>
                </div>

                <div class="game-card p-6 border-b-[8px] border-slate-200 flex flex-col justify-center">
                    <div class="text-4xl mb-4 text-center">⏱️</div>
                    <p class="text-slate-500 font-semibold text-center mb-6">Sistem sedang menunggu pemain lain untuk
                        bergabung. Jangan tutup halaman ini.</p>
                    <a href="{{ route('jarimatika.match') }}"
                        class="btn-3d-red w-full py-4 text-xl font-bold text-white rounded-2xl flex justify-center items-center gap-2 text-decoration-none">
                        <span>❌</span> Batal & Kembali
                    </a>
                </div>

            </div>

        </div>
    </div>

    <script>
        // Copy code to clipboard
        document.getElementById('btn-copy-code').addEventListener('click', async function() {
            const code = '{{ $roomCode }}';
            try {
                await navigator.clipboard.writeText(code);
                const btn = this;
                btn.textContent = '✅ Disalin!';
                btn.style.backgroundColor = '#10B981';
                setTimeout(() => {
                    btn.textContent = '📋 Salin Kode';
                    btn.style.backgroundColor = 'white';
                }, 2000);
            } catch (err) {
                alert('Gagal menyalin kode. Silakan salin secara manual: ' + code);
            }
        });

        // Polling untuk mengecek status room
        let pollingInterval = null;
        const roomCode = '{{ $roomCode }}';
        const roomStatusUrl = "{{ route('jarimatika.room.status') }}";

        function startPolling() {
            pollingInterval = setInterval(async () => {
                try {
                    const response = await fetch(roomStatusUrl + '?room_code=' + encodeURIComponent(roomCode), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    if (data.status === 'matched') {
                        stopPolling();
                        // Redirect ke battle
                        location.href = "{{ route('jarimatika.battle') }}?gameId=" + encodeURIComponent(data
                            .gameId);
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 2000);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        // Mulai polling saat halaman load
        window.addEventListener('DOMContentLoaded', startPolling);

        // Bersihkan polling saat keluar halaman
        window.addEventListener('beforeunload', stopPolling);
    </script>
@endsection
