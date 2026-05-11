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

        .admin-table {
            background: white;
            border-radius: 16px;
            border-collapse: collapse;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .admin-table thead {
            background: linear-gradient(135deg, #38BDF8 0%, #0284C7 100%);
            color: white;
            font-weight: bold;
        }

        .admin-table th {
            padding: 1.25rem;
            text-align: left;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .admin-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.3s ease;
        }

        .admin-table tbody tr:hover {
            background-color: #f0f9ff;
        }

        .admin-table td {
            padding: 1rem 1.25rem;
        }

        .btn-small {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0 0.25rem;
        }

        .btn-edit {
            background: #38BDF8;
            color: white;
        }

        .btn-edit:hover {
            background: #0ea5e9;
        }

        .btn-delete {
            background: #EF4444;
            color: white;
        }

        .btn-delete:hover {
            background: #DC2626;
        }

        .admin-nav {
            background: white;
            border-bottom: 4px solid #38BDF8;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        .admin-nav a {
            display: inline-block;
            margin-right: 1.5rem;
            padding: 0.75rem 1.5rem;
            background: #E0F2FE;
            color: #0284C7;
            font-weight: bold;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .admin-nav a:hover {
            background: #0284C7;
            color: white;
        }

        .alert-success {
            background: #D1FAE5;
            border-left: 4px solid #10B981;
            padding: 1rem;
            border-radius: 8px;
            color: #065F46;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .sort-info {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 1rem;
            border-radius: 8px;
            color: #78350F;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
    </style>

    <div class="admin-container py-8 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-slate-800 mb-2">👥 User Management</h1>
                <p class="text-slate-600 font-semibold">Kelola statistik pemain</p>
            </div>

            <!-- Navigation -->
            <div class="admin-nav mb-8">
                <a href="{{ route('admin.dashboard') }}">📊 Dashboard</a>
                <a href="{{ route('admin.users.index') }}">👥 User Management</a>
                <a href="{{ route('admin.shop.index') }}">🛍️ Shop Items</a>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="alert-success">✅ {{ session('success') }}</div>
            @endif

            <!-- Sort Algorithm Info -->
            <div class="sort-info">
                📊 <strong>Algoritma Bubble Sort:</strong> User di bawah ini sudah diurutkan berdasarkan Koin (dari terbanyak ke tersedikit) menggunakan algoritma Bubble Sort manual di backend. Tidak ada penggunaan orderBy database.
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>💰 Koin</th>
                            <th>⭐ XP</th>
                            <th>🏆 Piala</th>
                            <th>Level</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr>
                                <td class="font-bold text-slate-600">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $user['name'] }}</td>
                                <td class="text-slate-600">{{ $user['email'] }}</td>
                                <td>
                                    <span class="bg-[#FFF9E6] text-[#F79A19] font-bold px-3 py-1 rounded-lg">
                                        {{ number_format($user['koin'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="bg-[#FEF3C7] text-[#D97706] font-bold px-3 py-1 rounded-lg">
                                        {{ number_format($user['total_xp'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="bg-[#E0E7FF] text-[#4338CA] font-bold px-3 py-1 rounded-lg">
                                        {{ $user['piala'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="bg-[#E0F2FE] text-[#0284C7] font-bold px-3 py-1 rounded-lg">
                                        {{ $user['level'] }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user['id']) }}" class="btn-small btn-edit">✏️ Edit</a>
                                    <form action="{{ route('admin.users.delete', $user['id']) }}" method="POST" style="display:inline;"
                                        onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-small btn-delete">🗑️ Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-500 font-semibold">
                                    Belum ada user terdaftar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
