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

        .form-card {
            background: white;
            border-radius: 16px;
            border-bottom: 6px solid #38BDF8;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Fredoka', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #38BDF8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-file {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .form-file input[type="file"] {
            position: absolute;
            left: -9999px;
        }

        .form-file-label {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #E0F2FE;
            color: #0284C7;
            border-radius: 8px;
            border: 2px dashed #38BDF8;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-file-label:hover {
            background: #0284C7;
            color: white;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-group label {
            cursor: pointer;
            font-weight: 600;
            color: #334155;
        }

        .btn-primary {
            background: #38BDF8;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
        }

        .btn-primary:hover {
            background: #0ea5e9;
        }

        .btn-secondary {
            background: #94a3b8;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: #64748b;
        }

        .error-message {
            color: #DC2626;
            font-size: 0.85rem;
            font-weight: bold;
            margin-top: 0.25rem;
        }
    </style>

    <div class="admin-container py-8 px-4 sm:px-6">
        <div class="max-w-2xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-slate-800 mb-2">➕ Tambah Item Baru</h1>
                <p class="text-slate-600 font-semibold">Tambahkan item ke toko Anda</p>
            </div>

            <!-- Error Alert -->
            @if ($errors->any())
                <div
                    style="background-color: #FEE2E2; border: 2px solid #DC2626; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-size: 1.5rem; margin-right: 0.5rem;">⚠️</span>
                        <strong style="color: #991B1B; font-size: 1rem;">Validasi Gagal!</strong>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; color: #7F1D1D;">
                        @foreach ($errors->all() as $error)
                            <li style="margin-bottom: 0.25rem;">• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Create Form -->
            <form action="{{ route('admin.shop.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
                @csrf

                <!-- Nama Item -->
                <div class="form-group">
                    <label class="form-label" for="name">📝 Nama Item</label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}"
                        required>
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Tipe Item -->
                <div class="form-group">
                    <label class="form-label" for="type">📂 Tipe Item</label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="">-- Pilih Tipe --</option>
                        <option value="avatar" {{ old('type') === 'avatar' ? 'selected' : '' }}>👤 Avatar</option>
                        <option value="border" {{ old('type') === 'border' ? 'selected' : '' }}>🖼️ Border</option>
                        <option value="badge" {{ old('type') === 'badge' ? 'selected' : '' }}>🎖️ Badge</option>
                        <option value="effect" {{ old('type') === 'effect' ? 'selected' : '' }}>✨ Effect</option>
                    </select>
                    @error('type')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Harga -->
                <div class="form-group">
                    <label class="form-label" for="price">💰 Harga (Koin)</label>
                    <input type="number" id="price" name="price" class="form-input" value="{{ old('price') }}"
                        min="1" required>
                    @error('price')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Gambar -->
                <div class="form-group">
                    <label class="form-label">🖼️ Gambar Item</label>
                    <label class="form-file-label">
                        Pilih Gambar (JPG, PNG, GIF, WebP - Max 2MB)
                        <input type="file" name="image_path" accept="image/jpeg,image/png,image/gif,image/webp">
                    </label>
                    @error('image_path')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label class="form-label" for="description">📄 Deskripsi</label>
                    <textarea id="description" name="description" class="form-textarea" placeholder="Jelaskan item ini...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" {{ old('is_active') ? 'checked' : '' }}>
                        <label for="is_active">✅ Aktifkan Item</label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 mt-8">
                    <button type="submit" class="btn-primary">💾 Tambah Item</button>
                    <a href="{{ route('admin.shop.index') }}" class="btn-secondary">⬅️ Kembali</a>
                </div>
            </form>

        </div>
    </div>
@endsection
