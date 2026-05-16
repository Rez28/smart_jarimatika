@extends('layouts.app')

@section('content')
    @include('components.navbar')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-shop {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            /* Krem terang */
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        /* Game Cards Dasar */
        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        /* 3D Buttons */
        .btn-3d-green {
            background-color: #BBCB64;
            border-bottom: 6px solid #8fa040;
            transition: all 0.15s ease;
        }

        .btn-3d-green:active:not(:disabled) {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .btn-3d-back {
            background: linear-gradient(135deg, #F79A19 0%, #EA580C 100%);
            border-bottom: 6px solid #c8790f;
            transition: all 0.15s ease;
        }

        .btn-3d-back:active:not(:disabled) {
            border-bottom-width: 0px;
            transform: translateY(6px);
        }

        .btn-3d-disabled {
            background-color: #cbd5e1;
            border-bottom: 6px solid #94a3b8;
            color: #64748b;
            cursor: not-allowed;
            transition: all 0.15s ease;
        }
    </style>

    <div class="jarimatika-shop py-8 px-4 sm:px-6">
        <div class="max-w-[1200px] mx-auto grid gap-8 xl:grid-cols-[350px_1fr] items-start">

            <aside
                class="game-card p-6 md:p-8 border-b-[8px] border-[#38BDF8] flex flex-col items-center text-center sticky top-8">

                @php
                    $avatar = $user->active_avatar;
                    $isEmoji = $avatar && strlen($avatar) < 10 && !str_contains($avatar, '/');
                @endphp
                @if ($avatar && !$isEmoji)
                    <img src="{{ asset($avatar) }}"
                        class="rounded-full w-32 h-32 border-[6px] border-white shadow-lg mb-4 transform hover:scale-105 transition-transform object-cover">
                @elseif ($avatar && $isEmoji)
                    <div
                        class="w-32 h-32 rounded-full bg-white border-[6px] border-[#38BDF8] shadow-lg flex items-center justify-center text-6xl mb-4 transform hover:scale-105 transition-transform">
                        {{ $avatar }}
                    </div>
                @else
                    <div
                        class="w-32 h-32 rounded-full bg-[#FFE52A] border-[6px] border-white shadow-lg flex items-center justify-center text-6xl font-black text-slate-800 mb-4 transform hover:scale-105 transition-transform">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif

                <p class="text-sm font-bold uppercase tracking-widest text-[#38BDF8] mb-1">Penjelajah Angka</p>
                <h2 class="text-3xl font-black text-slate-800 mb-6">{{ $user->name }}</h2>

                <div class="w-full space-y-4 text-left">
                    <div class="bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 flex justify-between items-center">
                        <span class="text-slate-400 font-bold text-xs uppercase tracking-widest">Level Saat Ini</span>
                        <span class="text-2xl font-black text-slate-800">{{ $user->level }}</span>
                    </div>

                    <div class="bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 flex justify-between items-center">
                        <span class="text-slate-400 font-bold text-xs uppercase tracking-widest">Total XP</span>
                        <span class="text-2xl font-black text-[#BBCB64]">⭐ {{ $user->total_xp }}</span>
                    </div>

                    <div
                        class="bg-[#FFF9E6] border-2 border-[#FFE52A] rounded-2xl p-5 flex justify-between items-center transform scale-105 shadow-sm mt-6">
                        <span class="text-[#c8790f] font-bold text-sm uppercase tracking-widest">Dompet Koin</span>
                        <span class="text-3xl font-black text-[#F79A19]">💰 {{ $user->koin }}</span>
                    </div>
                </div>
            </aside>

            <main class="space-y-6">

                <!-- Tombol Kembali -->
                <div class="flex gap-3">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 px-6 py-4 text-lg font-black rounded-2xl tracking-wide btn-3d-back text-white hover:shadow-lg transition-shadow">
                        <span>←</span> Kembali
                    </a>
                </div>

                <section
                    class="game-card p-6 md:p-8 border-b-[8px] border-[#FFE52A] flex flex-col sm:flex-row items-center justify-between gap-6 bg-white overflow-hidden relative">
                    <div class="absolute -right-10 -bottom-10 text-[10rem] opacity-10 pointer-events-none">🛍️</div>
                    <div class="relative z-10 text-center sm:text-left">
                        <p class="text-sm font-bold uppercase tracking-widest text-[#F79A19] mb-1">Toko Virtual</p>
                        <h1 class="text-3xl md:text-4xl font-black text-slate-800">Etalase Item</h1>
                        <p class="mt-2 text-slate-500 font-medium">Beli avatar, bingkai, atau warna tema khusus menggunakan
                            koin hasil latihanmu!</p>
                    </div>
                </section>

                <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2">
                    @foreach ($items as $item)
                        @php
                            $canBuy = $user->koin >= $item['price'] && !$item['owned'];
                            $isOwned = $item['owned'];
                            $isEquipped = $item['equipped'];
                        @endphp

                        <div
                            class="game-card p-6 border-b-[8px] {{ $isOwned ? 'border-[#22C55E]' : ($canBuy ? 'border-[#BBCB64]' : 'border-slate-300 bg-slate-50') }} flex flex-col text-center relative group">

                            @if ($isEquipped)
                                <div
                                    class="absolute top-3 right-3 bg-[#22C55E] text-white font-black px-3 py-1 rounded-full text-xs z-10 flex items-center gap-1 animate-pulse">
                                    ✅ Sedang Dipakai
                                </div>
                            @endif

                            @if ($isOwned && !$isEquipped)
                                <div
                                    class="absolute top-3 right-3 bg-[#38BDF8] text-white font-black px-3 py-1 rounded-full text-xs z-10">
                                    ✓ Dimiliki
                                </div>
                            @endif

                            <div
                                class="absolute top-4 left-4 bg-[#FFE52A] text-slate-800 font-black px-4 py-1 rounded-full border-2 border-white shadow-sm z-10 flex items-center gap-1">
                                <span>💰</span> {{ $item['price'] }}
                            </div>

                            <div
                                class="h-32 flex items-center justify-center text-[5rem] mb-2 transform group-hover:scale-110 transition-transform duration-300 drop-shadow-md">
                                {{ $item['image_path'] ?? '🎁' }}
                            </div>

                            <h2 class="text-2xl font-black text-slate-800 mb-2">{{ $item['name'] }}</h2>
                            <p class="text-sm text-slate-500 font-medium mb-6 flex-grow leading-relaxed">
                                {{ $item['description'] }}</p>

                            @if (!$isOwned)
                                <!-- Tombol Beli -->
                                <button type="button"
                                    class="shop-buy-btn w-full py-4 text-lg font-black rounded-2xl tracking-wide {{ $canBuy ? 'btn-3d-green text-white' : 'btn-3d-disabled' }}"
                                    data-item-id="{{ $item['id'] }}" {{ $canBuy ? '' : 'disabled' }}>
                                    {{ $user->koin >= $item['price'] ? 'Beli Item' : 'Koin Tidak Cukup' }}
                                </button>
                            @else
                                @if ($isEquipped)
                                    <!-- Tombol Sedang Dipakai (Disabled) -->
                                    <button type="button"
                                        class="w-full py-4 text-lg font-black rounded-2xl tracking-wide btn-3d-disabled"
                                        disabled>
                                        ✓ Sedang Dipakai
                                    </button>
                                @else
                                    <!-- Tombol Pakai -->
                                    <button type="button"
                                        class="shop-equip-btn w-full py-4 text-lg font-black rounded-2xl tracking-wide btn-3d-green text-white"
                                        data-item-id="{{ $item['id'] }}">
                                        Pakai Item
                                    </button>
                                @endif
                            @endif

                        </div>
                    @endforeach
                </section>

            </main>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const userKoinDisplay = document.querySelector('[data-user-koin]');

        // Event listener untuk tombol Beli
        document.querySelectorAll('.shop-buy-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const itemId = btn.dataset.itemId;

                btn.disabled = true;
                const originalText = btn.textContent;
                btn.textContent = '⏳ Memproses...';

                try {
                    const response = await fetch(`/shop/buy/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const data = await response.json();

                    if (response.ok) {
                        alert('✅ ' + data.message);
                        // Reload halaman untuk update UI
                        location.reload();
                    } else {
                        alert('❌ ' + data.error);
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan. Silakan coba lagi.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            });
        });

        // Event listener untuk tombol Pakai
        document.querySelectorAll('.shop-equip-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const itemId = btn.dataset.itemId;

                btn.disabled = true;
                const originalText = btn.textContent;
                btn.textContent = '⏳ Memproses...';

                try {
                    const response = await fetch(`/shop/equip/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const data = await response.json();

                    if (response.ok) {
                        alert('✅ ' + data.message);
                        // Reload halaman untuk update UI
                        location.reload();
                    } else {
                        alert('❌ ' + data.error);
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan. Silakan coba lagi.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            });
        });
    </script>
@endsection
