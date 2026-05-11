@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .profile-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .profile-header {
            background: linear-gradient(135deg, #FFE52A 0%, #F79A19 100%);
            border: 4px solid #FCD34D;
            box-shadow: 0 8px 32px rgba(255, 229, 42, 0.3);
        }

        .player-card {
            background: white;
            border-radius: 24px;
            border: 4px solid #FFE52A;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .player-avatar-section {
            flex-shrink: 0;
            text-align: center;
        }

        .player-avatar {
            font-size: 6rem;
            line-height: 1;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .player-info {
            flex: 1;
        }

        .player-name {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1f2937;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .player-level {
            display: inline-block;
            background: #38BDF8;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            border: 3px solid #0284C7;
        }

        .xp-section {
            margin-top: 1rem;
        }

        .xp-label {
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 0.5rem;
            letter-spacing: 0.05em;
        }

        .xp-bar-container {
            background: #e2e8f0;
            border: 3px solid #cbd5e1;
            border-radius: 16px;
            height: 32px;
            overflow: hidden;
            position: relative;
        }

        .xp-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #BBCB64 0%, #8fa040 100%);
            border-radius: 13px;
            transition: width 0.6s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 0.75rem;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.3);
        }

        .inventory-section {
            margin-top: 2rem;
        }

        .section-title {
            font-size: 1.875rem;
            font-weight: 900;
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .inventory-item {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            border: 4px solid #e2e8f0;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
        }

        .inventory-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            border-color: #FFE52A;
        }

        .item-equipped-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #22C55E;
            color: white;
            font-weight: 900;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            border: 2px solid white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .item-image {
            font-size: 3.5rem;
            margin: 1rem 0;
            line-height: 1;
        }

        .item-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
            word-break: break-word;
        }

        .item-type {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            background: #f3f4f6;
            color: #6b7280;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
        }

        .item-button {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 4px solid transparent;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .item-button-equip {
            background: #BBCB64;
            color: white;
            border-bottom-color: #8fa040;
        }

        .item-button-equip:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(187, 203, 100, 0.3);
        }

        .item-button-equip:active:not(:disabled) {
            border-bottom-width: 0;
            transform: translateY(4px);
        }

        .item-button-equipped {
            background: #cbd5e1;
            color: #64748b;
            border-bottom-color: #94a3b8;
            cursor: not-allowed;
        }

        .empty-inventory {
            text-align: center;
            padding: 3rem 1rem;
            background: #f9fafb;
            border: 3px dashed #e5e7eb;
            border-radius: 20px;
        }

        .empty-inventory-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-inventory-text {
            font-size: 1.125rem;
            color: #6b7280;
            font-weight: 600;
        }

        .settings-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 3px dashed #e5e7eb;
        }

        .settings-title {
            font-size: 1.5rem;
            font-weight: 900;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }

        /* Loading state */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .player-card {
                flex-direction: column;
                text-align: center;
            }

            .player-avatar {
                font-size: 4rem;
            }

            .player-name {
                font-size: 1.875rem;
            }

            .inventory-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 1rem;
            }
        }
    </style>

    <div class="profile-container">
        <div class="max-w-6xl mx-auto">
            <!-- HEADER -->
            <div class="game-card profile-header p-6 mb-8">
                <h1 class="text-4xl md:text-5xl font-black text-white text-center drop-shadow-lg">
                    📊 Statistik & Ransel Pemain
                </h1>
            </div>

            <!-- PLAYER CARD -->
            <div class="player-card">
                <div class="player-avatar-section">
                    <div class="player-avatar">
                        {{ auth()->user()->equippedAvatar() }}
                    </div>
                    @if (auth()->user()->equippedBorder())
                        <div style="margin-top: 0.5rem; font-size: 2rem;">
                            {{ auth()->user()->equippedBorder() }}
                        </div>
                    @endif
                </div>

                <div class="player-info">
                    <h2 class="player-name">{{ auth()->user()->name }}</h2>
                    <span class="player-level">⭐ Level {{ auth()->user()->level }}</span>

                    <div class="xp-section">
                        <div class="xp-label">Progress XP</div>
                        <div class="xp-bar-container">
                            @php
                                $xpPerLevel = 500;
                                $currentLevelXp = (auth()->user()->level - 1) * $xpPerLevel;
                                $nextLevelXp = auth()->user()->level * $xpPerLevel;
                                $xpInCurrentLevel = auth()->user()->total_xp - $currentLevelXp;
                                $xpNeededForLevel = $xpPerLevel;
                                $xpProgress = min(100, ($xpInCurrentLevel / $xpNeededForLevel) * 100);
                            @endphp
                            <div class="xp-bar-fill" style="width: {{ $xpProgress }}%">
                                {{ intval($xpProgress) }}%
                            </div>
                        </div>
                        <div class="xp-label" style="margin-top: 0.5rem; text-align: center;">
                            {{ $xpInCurrentLevel }} / {{ $xpNeededForLevel }} XP
                        </div>
                    </div>

                    <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 120px;">
                            <div class="xp-label">💰 Koin</div>
                            <div style="font-size: 1.5rem; font-weight: 900; color: #F79A19;">
                                {{ auth()->user()->koin }}
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 120px;">
                            <div class="xp-label">🏆 Piala</div>
                            <div style="font-size: 1.5rem; font-weight: 900; color: #FCD34D;">
                                {{ auth()->user()->piala }}
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 120px;">
                            <div class="xp-label">📚 Total XP</div>
                            <div style="font-size: 1.5rem; font-weight: 900; color: #BBCB64;">
                                {{ auth()->user()->total_xp }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INVENTORY SECTION -->
            <div class="inventory-section">
                <h2 class="section-title">
                    🎒 Ransel Saya (Inventaris)
                </h2>

                @if (auth()->user()->items->count() > 0)
                    <div class="inventory-grid">
                        @foreach (auth()->user()->items as $userItem)
                            @php
                                $item = $userItem->item;
                                $isEquipped = $userItem->is_equipped;
                            @endphp
                            <div class="inventory-item" data-item-id="{{ $item->id }}">
                                @if ($isEquipped)
                                    <div class="item-equipped-badge">✅ Dipakai</div>
                                @endif

                                <div class="item-image">
                                    {{ $item->image_path ?? '🎁' }}
                                </div>

                                <div class="item-name">
                                    {{ $item->name }}
                                </div>

                                <div class="item-type">
                                    @switch($item->type)
                                        @case('avatar')
                                            👤 Avatar
                                        @break

                                        @case('border')
                                            🖼️ Border
                                        @break

                                        @case('badge')
                                            🏅 Badge
                                        @break

                                        @default
                                            ✨ Item
                                    @endswitch
                                </div>

                                @if ($isEquipped)
                                    <button type="button" class="item-button item-button-equipped" disabled>
                                        ✓ Sedang Dipakai
                                    </button>
                                @else
                                    <button type="button" class="item-button item-button-equip equip-btn"
                                        data-item-id="{{ $item->id }}">
                                        Pakai (Equip)
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-inventory">
                        <div class="empty-inventory-icon">🎒</div>
                        <div class="empty-inventory-text">
                            Ransel masih kosong. Kunjungi toko untuk membeli item!
                        </div>
                    </div>
                @endif
            </div>

            <!-- SETTINGS SECTION -->
            <div class="settings-section">
                <h2 class="settings-title">⚙️ Pengaturan Akun</h2>

                <div class="game-card p-6 md:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="game-card p-6 md:p-8 mt-6">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="game-card p-6 md:p-8 mt-6">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX SCRIPT untuk Equip Item -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        document.querySelectorAll('.equip-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const itemId = btn.dataset.itemId;

                // Disable button selama proses
                btn.disabled = true;
                btn.classList.add('loading');
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
                        // Reload halaman untuk update UI
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    } else {
                        alert('❌ ' + (data.error || 'Gagal equip item'));
                        btn.disabled = false;
                        btn.classList.remove('loading');
                        btn.textContent = originalText;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan. Silakan coba lagi.');
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    btn.textContent = originalText;
                }
            });
        });
    </script>
@endsection
