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

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Fredoka', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #38BDF8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
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

        .user-info {
            background: #E0F2FE;
            border-left: 4px solid #38BDF8;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .user-info p {
            margin: 0.5rem 0;
            color: #0369A1;
            font-weight: 600;
        }
    </style>

    <div class="admin-container py-8 px-4 sm:px-6">
        <div class="max-w-2xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-slate-800 mb-2">✏️ Edit User</h1>
                <p class="text-slate-600 font-semibold">Perbarui statistik pemain</p>
            </div>

            <!-- User Info -->
            <div class="user-info">
                <p>👤 <strong>Nama:</strong> {{ $user->name }}</p>
                <p>📧 <strong>Email:</strong> {{ $user->email }}</p>
            </div>

            <!-- Edit Form -->
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="form-card">
                @csrf
                @method('PUT')

                <!-- Koin -->
                <div class="form-group">
                    <label class="form-label" for="koin">💰 Koin</label>
                    <input type="number" id="koin" name="koin" class="form-input" value="{{ $user->koin }}"
                        min="0" required>
                    @error('koin')
                        <span class="text-red-500 text-sm font-bold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Total XP -->
                <div class="form-group">
                    <label class="form-label" for="total_xp">⭐ Total XP</label>
                    <input type="number" id="total_xp" name="total_xp" class="form-input" value="{{ $user->total_xp }}"
                        min="0" required>
                    @error('total_xp')
                        <span class="text-red-500 text-sm font-bold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Piala -->
                <div class="form-group">
                    <label class="form-label" for="piala">🏆 Piala</label>
                    <input type="number" id="piala" name="piala" class="form-input" value="{{ $user->piala }}"
                        min="0" required>
                    @error('piala')
                        <span class="text-red-500 text-sm font-bold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex gap-3 mt-8">
                    <button type="submit" class="btn-primary">💾 Simpan Perubahan</button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">⬅️ Kembali</a>
                </div>
            </form>

        </div>
    </div>
@endsection
