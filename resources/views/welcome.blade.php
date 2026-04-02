@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;900&display=swap');

        .jarimatika-landing {
            font-family: 'Fredoka', sans-serif;
            background-color: #FFFBEB;
            /* Krem terang */
            background-image: radial-gradient(#38BDF8 1.5px, transparent 1.5px);
            background-size: 30px 30px;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Kartu Timbul ala Game */
        .game-card {
            background: white;
            border-radius: 32px;
            border-bottom: 8px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        /* Tombol 3D */
        .btn-3d-green {
            background-color: #BBCB64;
            border-bottom: 8px solid #8fa040;
            transition: all 0.15s ease;
        }

        .btn-3d-green:hover {
            background-color: #aab95b;
        }

        .btn-3d-green:active {
            border-bottom-width: 0px;
            transform: translateY(8px);
        }

        .btn-3d-yellow {
            background-color: #FFE52A;
            border-bottom: 8px solid #ccb622;
            transition: all 0.15s ease;
        }

        .btn-3d-yellow:active {
            border-bottom-width: 0px;
            transform: translateY(8px);
        }

        /* Efek Melayang Lambat */
        .float-slow {
            animation: float 6s ease-in-out infinite;
        }

        .float-delayed {
            animation: float 6s ease-in-out 3s infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }
    </style>

    <div class="jarimatika-landing relative flex items-center pt-10 pb-20 px-4 sm:px-6">

        <div class="absolute top-20 left-10 w-64 h-64 bg-[#FFE52A] rounded-full blur-[80px] opacity-40 pointer-events-none">
        </div>
        <div
            class="absolute bottom-20 right-10 w-80 h-80 bg-[#38BDF8] rounded-full blur-[100px] opacity-40 pointer-events-none">
        </div>

        <div class="max-w-[1300px] mx-auto w-full relative z-10">

            <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-12 lg:gap-8 items-center">

                <div class="text-center lg:text-left">
                    <div
                        class="inline-block bg-white text-[#F79A19] px-6 py-2 rounded-full text-sm font-black uppercase tracking-widest border-2 border-slate-100 shadow-sm mb-6">
                        🚀 Smart Jarimatika
                    </div>

                    <h1 class="text-5xl lg:text-6xl xl:text-7xl font-black text-slate-800 leading-[1.1] drop-shadow-sm mb-6">
                        Belajar Hitung <br>
                        Jadi <span class="text-[#38BDF8]">Seru</span> dengan <br>
                        <span class="text-[#BBCB64]">Kamera AI!</span>
                    </h1>

                    <p
                        class="text-lg md:text-xl text-slate-600 font-medium mb-10 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        Mainkan mode belajar, latihan, hingga battle multiplayer 1vs1. Tunjukkan jarimu ke kamera, kumpulkan
                        XP, dan jadilah juara di papan peringkat!
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="{{ route('login') }}"
                            class="btn-3d-green w-full sm:w-auto px-10 py-5 rounded-full text-white text-2xl font-black tracking-wide flex items-center justify-center gap-3">
                            <span>🎮</span> MULAI MAIN
                        </a>
                        <a href="#fitur"
                            class="btn-3d-yellow w-full sm:w-auto px-8 py-5 rounded-full text-slate-800 text-xl font-bold flex items-center justify-center gap-2">
                            Pelajari Dulu ⬇️
                        </a>
                    </div>
                </div>

                <div class="relative h-[500px] hidden md:block">

                    <div
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 game-card border-b-[10px] border-[#38BDF8] p-4 rotate-3 float-slow z-20">
                        <div
                            class="bg-slate-800 aspect-video rounded-2xl flex items-center justify-center text-7xl border-4 border-slate-700 relative overflow-hidden">
                            ✌️
                            <div
                                class="absolute bottom-2 right-2 bg-[#BBCB64] text-white text-sm font-black px-3 py-1 rounded-full border-2 border-white">
                                Angka: 2
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute top-10 left-0 w-48 game-card border-b-[8px] border-[#FFE52A] p-4 -rotate-6 float-delayed z-10 flex items-center gap-4">
                        <div class="text-4xl">💰</div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Koin +50</p>
                            <p class="text-xl font-black text-slate-800">Beli Avatar!</p>
                        </div>
                    </div>

                    <div
                        class="absolute bottom-12 right-0 w-56 game-card border-b-[8px] border-[#F79A19] p-4 -rotate-3 float-slow z-30 flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-[#F79A19] text-white font-black flex items-center justify-center text-xl border-2 border-white shadow-md">
                            #1
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Rank Naik</p>
                            <p class="text-xl font-black text-slate-800">Kamu Juara!</p>
                        </div>
                    </div>

                </div>
            </div>

            <div id="fitur" class="mt-24 grid grid-cols-1 md:grid-cols-3 gap-6">

                <div
                    class="game-card p-8 border-b-[8px] border-[#38BDF8] text-center transform hover:-translate-y-2 transition-transform">
                    <div
                        class="w-20 h-20 mx-auto bg-[#E0F2FE] text-[#38BDF8] rounded-2xl flex items-center justify-center text-4xl mb-6 rotate-3">
                        🤖
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-3">AI Deteksi Pintar</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Cukup tunjukkan jarimu ke kamera laptop. Sistem
                        akan membaca jawabanmu secara otomatis tanpa perlu keyboard.</p>
                </div>

                <div
                    class="game-card p-8 border-b-[8px] border-[#BBCB64] text-center transform hover:-translate-y-2 transition-transform">
                    <div
                        class="w-20 h-20 mx-auto bg-[#F4FCE3] text-[#BBCB64] rounded-2xl flex items-center justify-center text-4xl mb-6 -rotate-3">
                        ⭐
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-3">Naik Level & Koin</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Setiap jawaban benar memberimu XP dan Koin.
                        Kumpulkan dan pamerkan pencapaianmu di papan peringkat.</p>
                </div>

                <div
                    class="game-card p-8 border-b-[8px] border-[#F79A19] text-center transform hover:-translate-y-2 transition-transform">
                    <div
                        class="w-20 h-20 mx-auto bg-[#FFEDD5] text-[#F79A19] rounded-2xl flex items-center justify-center text-4xl mb-6 rotate-3">
                        ⚔️
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-3">Battle Real-Time</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Tantang temanmu secara langsung. Siapa yang paling
                        cepat menyelesaikan soal, dialah pemenangnya!</p>
                </div>

            </div>

        </div>
    </div>
@endsection
