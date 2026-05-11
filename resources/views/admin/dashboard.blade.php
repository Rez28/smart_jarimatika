@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .admin-container {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
        }

        /* ===================== STAT CARDS ===================== */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            border-bottom: 6px solid;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card:hover::before {
            top: -25%;
            right: -25%;
        }

        .stat-content {
            position: relative;
            z-index: 1;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 2.8rem;
            font-weight: 900;
            margin: 0.5rem 0;
        }

        /* Card Color Variants */
        .card-blue {
            border-bottom-color: #38BDF8;
        }

        .card-blue .stat-label {
            color: #0284C7;
        }

        .card-blue .stat-value {
            color: #0284C7;
        }

        .card-yellow {
            border-bottom-color: #F59E0B;
        }

        .card-yellow .stat-label {
            color: #D97706;
        }

        .card-yellow .stat-value {
            color: #D97706;
        }

        .card-green {
            border-bottom-color: #10B981;
        }

        .card-green .stat-label {
            color: #059669;
        }

        .card-green .stat-value {
            color: #059669;
        }

        .card-orange {
            border-bottom-color: #F97316;
        }

        .card-orange .stat-label {
            color: #EA580C;
        }

        .card-orange .stat-value {
            color: #EA580C;
        }

        /* ===================== ADMIN NAV ===================== */
        .admin-nav {
            background: white;
            border-bottom: 4px solid #38BDF8;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .admin-nav a {
            display: inline-block;
            margin-right: 1rem;
            padding: 0.75rem 1.5rem;
            background: #E0F2FE;
            color: #0284C7;
            font-weight: 600;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .admin-nav a:hover {
            background: #0284C7;
            color: white;
        }

        /* ===================== CHART CARDS ===================== */
        .chart-card {
            background: white;
            border-radius: 20px;
            border-bottom: 6px solid #38BDF8;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ===================== DATA TABLES ===================== */
        .table-card {
            background: white;
            border-radius: 20px;
            border-bottom: 6px solid #38BDF8;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-card:nth-child(2) {
            border-bottom-color: #10B981;
        }

        .table-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: #F1F5F9;
            border-bottom: 3px solid #E2E8F0;
        }

        .data-table th {
            padding: 1rem 0.75rem;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0F172A;
        }

        .data-table td {
            padding: 0.9rem 0.75rem;
            border-bottom: 2px solid #F1F5F9;
            color: #475569;
            font-size: 0.95rem;
        }

        .data-table tbody tr {
            transition: all 0.3s ease;
        }

        .data-table tbody tr:hover {
            background: #F8FAFC;
        }

        .rank-badge {
            display: inline-block;
            width: 28px;
            height: 28px;
            background: #38BDF8;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 28px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .rank-1 {
            background: #FFD700;
            color: #1e293b;
        }

        .rank-2 {
            background: #C0C0C0;
            color: #1e293b;
        }

        .rank-3 {
            background: #CD7F32;
            color: white;
        }

        .stat-mini {
            font-weight: 700;
            color: #0284C7;
        }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 768px) {
            .stat-card {
                padding: 1.25rem;
            }

            .stat-value {
                font-size: 2rem;
            }

            .chart-card {
                padding: 1rem;
            }
        }
    </style>

    <div class="admin-container py-8 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-slate-800 mb-2">📊 Admin Dashboard</h1>
                <p class="text-slate-600 font-semibold">Ringkasan Sistem Game Jarimatika</p>
            </div>

            <!-- Navigation -->
            <div class="admin-nav mb-8">
                <a href="{{ route('admin.dashboard') }}">📊 Dashboard</a>
                <a href="{{ route('admin.users.index') }}">👥 User Management</a>
                <a href="{{ route('admin.shop.index') }}">🛍️ Shop Items</a>
            </div>

            <!-- ==================== BARIS 1: STAT CARDS ==================== -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Card 1: Total Users -->
                <div class="stat-card card-blue">
                    <div class="stat-content">
                        <div class="stat-icon">👥</div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value">{{ $totalUsers }}</div>
                    </div>
                </div>

                <!-- Card 2: Total Matches -->
                <div class="stat-card card-orange">
                    <div class="stat-content">
                        <div class="stat-icon">⚔️</div>
                        <div class="stat-label">Total Matches</div>
                        <div class="stat-value">{{ $totalMatches }}</div>
                    </div>
                </div>

                <!-- Card 3: Shop Items -->
                <div class="stat-card card-green">
                    <div class="stat-content">
                        <div class="stat-icon">🛍️</div>
                        <div class="stat-label">Shop Items</div>
                        <div class="stat-value">{{ $totalShopItems }}</div>
                    </div>
                </div>

                <!-- Card 4: Total Koin -->
                <div class="stat-card card-yellow">
                    <div class="stat-content">
                        <div class="stat-icon">💰</div>
                        <div class="stat-label">Total Koin</div>
                        <div class="stat-value">{{ number_format($totalRevenue, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- ==================== BARIS 2: CHARTS ==================== -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Chart 1: User Registration Stats -->
                <div class="chart-card">
                    <div class="chart-title">
                        <span>📈</span>
                        Statistik Pendaftaran User
                    </div>
                    <canvas id="userStatsChart" style="max-height: 300px;"></canvas>
                </div>

                <!-- Chart 2: Item Distribution -->
                <div class="chart-card">
                    <div class="chart-title">
                        <span>🎨</span>
                        Distribusi Tipe Item Toko
                    </div>
                    <canvas id="itemDistributionChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <!-- ==================== BARIS 3: DATA TABLES ==================== -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Table 1: Top 5 Sultan (Most Koin) -->
                <div class="table-card">
                    <div class="table-title">
                        <span>💰</span>
                        5 Pemain Sultan (Koin Terbanyak)
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Pemain</th>
                                <th>Koin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSultan as $rank => $user)
                                <tr>
                                    <td>
                                        <span class="rank-badge rank-{{ $rank + 1 }}">{{ $rank + 1 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="stat-mini">{{ number_format($user->koin, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Table 2: Top 5 Level (Highest Level) -->
                <div class="table-card">
                    <div class="table-title">
                        <span>⭐</span>
                        5 Pemain Level Tertinggi
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Pemain</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topLevel as $rank => $user)
                                <tr>
                                    <td>
                                        <span class="rank-badge rank-{{ $rank + 1 }}">{{ $rank + 1 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="stat-mini">{{ $user->level }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ==================== RIWAYAT PERTANDINGAN TERAKHIR ==================== -->
            <div class="chart-card">
                <h3 class="text-xl font-bold text-slate-800 mb-6">⚔️ Riwayat Pertandingan Terakhir</h3>
                <div style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Mode</th>
                                <th>Pemain 1 vs Pemain 2</th>
                                <th>Skor Akhir</th>
                                <th>Pemenang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMatches as $match)
                                <tr>
                                    <!-- Waktu -->
                                    <td>
                                        <span style="font-size: 0.85rem; color: #64748b;">
                                            {{ $match->created_at->format('d M Y H:i') }}
                                        </span>
                                    </td>

                                    <!-- Mode Badge -->
                                    <td>
                                        @php
                                            $modeColor = match ($match->mode) {
                                                'classic' => '#3B82F6',
                                                'tebak' => '#8B5CF6',
                                                'hitung' => '#10B981',
                                                default => '#6B7280',
                                            };
                                            $modeBadge = match ($match->mode) {
                                                'classic' => '🎮 Classic',
                                                'tebak' => '❓ Tebak',
                                                'hitung' => '🔢 Hitung',
                                                default => 'Unknown',
                                            };
                                        @endphp
                                        <span
                                            style="
                                            display: inline-block;
                                            background: {{ $modeColor }};
                                            color: white;
                                            padding: 0.4rem 0.8rem;
                                            border-radius: 20px;
                                            font-size: 0.8rem;
                                            font-weight: 600;
                                        ">
                                            {{ $modeBadge }}
                                        </span>
                                    </td>

                                    <!-- Pemain 1 vs Pemain 2 -->
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <strong style="color: #0284C7;">{{ $match->user1->name ?? 'Unknown' }}</strong>
                                            <span style="color: #94a3b8; font-weight: 700;">vs</span>
                                            @if ($match->user_id_2 === null)
                                                <span style="color: #64748b; font-style: italic;">🤖 Bot</span>
                                            @else
                                                <strong
                                                    style="color: #8B5CF6;">{{ $match->user2->name ?? 'Unknown' }}</strong>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Skor Akhir -->
                                    <td>
                                        <span
                                            style="
                                            font-weight: 700;
                                            font-size: 1rem;
                                            color: #0F172A;
                                        ">
                                            {{ $match->score_1 }} - {{ $match->score_2 }}
                                        </span>
                                    </td>

                                    <!-- Pemenang -->
                                    <td>
                                        @if ($match->winner_id === null)
                                            <span
                                                style="
                                                display: inline-block;
                                                background: #F3F4F6;
                                                color: #6B7280;
                                                padding: 0.4rem 0.8rem;
                                                border-radius: 6px;
                                                font-weight: 600;
                                                font-size: 0.85rem;
                                            ">
                                                Draw 🤝
                                            </span>
                                        @else
                                            <span
                                                style="
                                                display: inline-block;
                                                background: #FCD34D;
                                                color: #92400E;
                                                padding: 0.4rem 0.8rem;
                                                border-radius: 6px;
                                                font-weight: 600;
                                                font-size: 0.85rem;
                                            ">
                                                🏆 {{ $match->winner->name ?? 'Unknown' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 2rem;">
                                        Belum ada data pertandingan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ==================== LEADERBOARD PROGRESS BELAJAR ==================== -->
            <div class="chart-card" style="margin-top: 2rem;">
                <h3 class="text-xl font-bold text-slate-800 mb-6">📚 Leaderboard Progress Belajar</h3>
                <div style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Peringkat</th>
                                <th>Nama Pemain</th>
                                <th>Progress Angka Terbuka</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($learningProgress as $rank => $progress)
                                <tr>
                                    <!-- Peringkat -->
                                    <td>
                                        <span class="rank-badge rank-{{ $rank + 1 }}">{{ $rank + 1 }}</span>
                                    </td>

                                    <!-- Nama Pemain -->
                                    <td>
                                        <strong>{{ $progress->user->name ?? 'Unknown' }}</strong>
                                    </td>

                                    <!-- Progress Angka Terbuka -->
                                    <td>
                                        @php
                                            $isMaxed = $progress->highest_number_unlocked >= 99;
                                        @endphp
                                        @if ($isMaxed)
                                            <span
                                                style="
                                                display: inline-block;
                                                background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
                                                color: #92400E;
                                                padding: 0.5rem 1rem;
                                                border-radius: 12px;
                                                font-weight: 700;
                                                font-size: 0.9rem;
                                                border: 2px solid #FCD34D;
                                                box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
                                            ">
                                                🎓 LULUS/MASTER ({{ $progress->highest_number_unlocked }})
                                            </span>
                                        @else
                                            <span
                                                style="
                                                display: inline-block;
                                                background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%);
                                                color: white;
                                                padding: 0.5rem 1rem;
                                                border-radius: 12px;
                                                font-weight: 700;
                                                font-size: 0.9rem;
                                                border: 2px solid #BBCB64;
                                                box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
                                            ">
                                                📈 {{ $progress->highest_number_unlocked }}/99
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #94a3b8; padding: 2rem;">
                                        Belum ada data progress belajar
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ==================== CHART.JS SCRIPTS ==================== -->
    <script>
        // ===== Chart 1: User Registration Stats (Bar Chart) =====
        const userStatsCtx = document.getElementById('userStatsChart').getContext('2d');
        const userStatsChart = new Chart(userStatsCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'Pendaftaran User',
                    data: [12, 19, 15, 25, 20, 30],
                    backgroundColor: [
                        '#38BDF8',
                        '#3B82F6',
                        '#06B6D4',
                        '#0284C7',
                        '#0369A1',
                        '#075985'
                    ],
                    borderColor: '#0284C7',
                    borderWidth: 2,
                    borderRadius: 8,
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: {
                                family: "'Fredoka', sans-serif",
                                weight: 'bold',
                                size: 13
                            },
                            color: '#1e293b',
                            padding: 15
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                family: "'Fredoka', sans-serif",
                                weight: 600
                            },
                            color: '#64748b'
                        },
                        grid: {
                            color: '#e2e8f0',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: "'Fredoka', sans-serif",
                                weight: 600
                            },
                            color: '#64748b'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // ===== Chart 2: Item Distribution (Doughnut Chart) =====
        const itemDistCtx = document.getElementById('itemDistributionChart').getContext('2d');
        const itemDistChart = new Chart(itemDistCtx, {
            type: 'doughnut',
            data: {
                labels: ['Avatar', 'Border', 'Badge', 'Effect'],
                datasets: [{
                    label: 'Jumlah Item',
                    data: [15, 8, 12, 10],
                    backgroundColor: [
                        '#FEE2E2', // Red for Avatar
                        '#E0E7FF', // Purple for Border
                        '#FEF3C7', // Yellow for Badge
                        '#F0FDF4' // Green for Effect
                    ],
                    borderColor: [
                        '#DC2626',
                        '#4F46E5',
                        '#D97706',
                        '#16A34A'
                    ],
                    borderWidth: 3,
                    hoverOffset: 8,
                    spacing: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: "'Fredoka', sans-serif",
                                weight: 'bold',
                                size: 13
                            },
                            color: '#1e293b',
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
