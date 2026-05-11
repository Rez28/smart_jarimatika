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

        /* Grid Card Layout */
        .shop-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .shop-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .shop-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Item Card */
        .shop-item-card {
            background: white;
            border-radius: 20px;
            border-bottom: 5px solid #38BDF8;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .shop-item-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        /* Image Container */
        .shop-item-image-container {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #E0F2FE 0%, #DBEAFE 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .shop-item-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .shop-item-default-icon {
            font-size: 3rem;
            opacity: 0.5;
        }

        /* Content Container */
        .shop-item-content {
            padding: 1rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .shop-item-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .shop-item-type-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
            width: fit-content;
        }

        .badge-avatar {
            background: #FEE2E2;
            color: #991B1B;
        }

        .badge-border {
            background: #E0E7FF;
            color: #3730A3;
        }

        .badge-badge {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-effect {
            background: #F0FDF4;
            color: #166534;
        }

        /* Price & Description */
        .shop-item-price {
            font-size: 1.35rem;
            font-weight: 800;
            color: #38BDF8;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .shop-item-description {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.4;
            margin-bottom: 0.75rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .shop-item-status {
            display: inline-block;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            width: fit-content;
        }

        .status-active {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-inactive {
            background: #FEE2E2;
            color: #7F1D1D;
        }

        /* Footer Actions */
        .shop-item-footer {
            padding: 0 1rem 1rem 1rem;
            display: flex;
            gap: 0.5rem;
            margin-top: auto;
        }

        .btn-card {
            flex: 1;
            padding: 0.6rem 0.75rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .btn-edit {
            background: #38BDF8;
            color: white;
        }

        .btn-edit:hover {
            background: #0ea5e9;
            transform: scale(1.02);
        }

        .btn-delete {
            background: #EF4444;
            color: white;
        }

        .btn-delete:hover {
            background: #DC2626;
            transform: scale(1.02);
        }

        /* Header & Navigation */
        .btn-create {
            background: #22C55E;
            color: white;
            padding: 0.85rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-create:hover {
            background: #16A34A;
            transform: scale(1.02);
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

        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 20px;
            border-bottom: 5px solid #38BDF8;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-state-text {
            color: #64748b;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination a {
            background: #38BDF8;
            color: white;
            text-decoration: none;
        }

        .pagination a:hover {
            background: #0ea5e9;
        }

        .pagination span.current {
            background: #38BDF8;
            color: white;
        }
    </style>

    <div class="admin-container py-8 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-slate-800 mb-2">🛍️ Shop Management</h1>
                <p class="text-slate-600 font-semibold">Kelola item toko dan harga</p>
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

            <!-- Create Button -->
            <a href="{{ route('admin.shop.create') }}" class="btn-create">➕ Tambah Item Baru</a>

            <!-- Shop Items Grid -->
            @forelse ($items as $item)
                @if ($loop->first)
                    <div class="shop-grid">
                @endif

                <div class="shop-item-card">
                    <!-- Image Container -->
                    <div class="shop-item-image-container">
                        @if ($item->image_path && file_exists(public_path($item->image_path)))
                            <img src="{{ asset($item->image_path) }}" alt="{{ $item->name }}" class="shop-item-image">
                        @else
                            <div class="shop-item-default-icon">
                                @if ($item->type === 'avatar')
                                    👤
                                @elseif ($item->type === 'border')
                                    🖼️
                                @elseif ($item->type === 'badge')
                                    🎖️
                                @elseif ($item->type === 'effect')
                                    ✨
                                @else
                                    📦
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Content Container -->
                    <div class="shop-item-content">
                        <!-- Name -->
                        <h3 class="shop-item-name">{{ $item->name }}</h3>

                        <!-- Type Badge -->
                        <span class="shop-item-type-badge badge-{{ $item->type }}">
                            @if ($item->type === 'avatar')
                                👤 Avatar
                            @elseif ($item->type === 'border')
                                🖼️ Border
                            @elseif ($item->type === 'badge')
                                🎖️ Badge
                            @elseif ($item->type === 'effect')
                                ✨ Effect
                            @endif
                        </span>

                        <!-- Price -->
                        <div class="shop-item-price">
                            💰 {{ number_format($item->price, 0, ',', '.') }}
                        </div>

                        <!-- Description -->
                        @if ($item->description)
                            <p class="shop-item-description">{{ $item->description }}</p>
                        @else
                            <p class="shop-item-description text-slate-400 italic">No description</p>
                        @endif

                        <!-- Status Badge -->
                        <span class="shop-item-status {{ $item->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $item->is_active ? '✅ Aktif' : '❌ Nonaktif' }}
                        </span>
                    </div>

                    <!-- Footer Actions -->
                    <div class="shop-item-footer">
                        <a href="{{ route('admin.shop.edit', $item->id) }}" class="btn-card btn-edit">✏️ Edit</a>
                        <form action="{{ route('admin.shop.delete', $item->id) }}" method="POST"
                            style="display:inline; flex: 1;" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-card btn-delete" style="width: 100%; margin: 0;">🗑️
                                Hapus</button>
                        </form>
                    </div>
                </div>

                @if ($loop->last)
        </div>
        @endif

    @empty
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p class="empty-state-text">Belum ada item di toko Anda</p>
            <a href="{{ route('admin.shop.create') }}" class="btn-create">➕ Buat Item Pertama</a>
        </div>
        @endforelse

        <!-- Pagination -->
        @if ($items->hasPages())
            <div class="mt-8">
                {{ $items->links() }}
            </div>
        @endif

    </div>
    </div>
@endsection
