# 🧮 Smart Jarimatika – Web-Based Interactive Learning Application

**Smart Jarimatika** adalah aplikasi web interaktif yang membantu pengguna belajar berhitung cepat menggunakan metode **jarimatika (perhitungan dengan jari tangan)**.  
Aplikasi ini menggabungkan metode tradisional berhitung dengan teknologi **Artificial Intelligence (AI)** berbasis **MediaPipe** dan framework **Laravel**.

---

## 🚀 Fitur Utama

| Fitur | Deskripsi |
|-------|------------|
| 🧠 **Mode Belajar** | Menampilkan langkah-langkah berhitung jarimatika secara visual interaktif. |
| ✋ **Mode Latihan (AI)** | Menggunakan kamera dan deteksi jari real-time (MediaPipe). |
| 🧾 **Progress Tracker** | Menyimpan dan menampilkan hasil latihan pengguna (skor & akurasi). |
| 🔐 **Login & Register** | Sistem autentikasi pengguna untuk menyimpan hasil belajar. |

---

## 🏗️ Teknologi yang Digunakan

| Komponen | Teknologi |
|-----------|------------|
| Backend | [Laravel 11](https://laravel.com) (PHP Framework) |
| Frontend | Blade Template, TailwindCSS |
| AI/Computer Vision | [MediaPipe Hands](https://developers.google.com/mediapipe/solutions/vision/hand_landmarker) |
| Database | MySQL |
| Hosting (Opsional) | Vercel / Firebase / Laragon Localhost |

---

## 🧩 Struktur Folder (Ringkasan)

smart-jarimatika/
├── app/
│ ├── Http/Controllers/
│ │ ├── LessonController.php
│ │ └── ScoreController.php
│ ├── Models/
│ │ ├── Lesson.php
│ │ └── Score.php
├── resources/
│ ├── views/
│ │ ├── learn.blade.php
│ │ ├── practice.blade.php
│ │ └── progress.blade.php
├── routes/
│ ├── web.php
│ └── api.php
└── database/
└── migrations/


---

## 🧑‍💻 Tim Pengembang

| Nama | Peran | Tanggung Jawab |
|-------|--------|----------------|
| **Muhammad Taufiq Reza** | Backend Developer + Mode Belajar | Membangun backend Laravel, sistem login, database, API, dan Mode Belajar (materi & animasi). |
| **Ghaza [Nama Lengkap]** | Frontend Developer + Mode Latihan (AI) | Mengembangkan Mode Latihan berbasis kamera (MediaPipe), desain UI interaktif, dan integrasi AI ke backend. |

---

## 🌿 Branch Workflow (Git)

| Branch | Deskripsi | Developer |
|---------|------------|------------|
| `main` | Branch utama (hasil final proyek). | Reza & Ghaza |
| `reza-mode-belajar` | Backend, login, database, dan Mode Belajar. | Reza |
| `ghaza-mode-latihan` | Frontend, AI kamera, dan Mode Latihan. | Ghaza |

### 💡 Alur kerja Git:
1. Clone project:
   ```bash
   git clone https://github.com/<username>/smart-jarimatika.git
   cd smart-jarimatika
Pindah ke branch masing-masing:

Reza → git checkout reza-mode-belajar

Ghaza → git checkout ghaza-mode-latihan

Commit dan push:

git add .
git commit -m "Pesan perubahan"
git push origin nama-branch


Merge ke main setelah pengujian selesai:

git checkout main
git merge reza-mode-belajar
git merge ghaza-mode-latihan
git push origin main

⚙️ Cara Menjalankan Proyek (Localhost Laragon)

Clone repository:

git clone https://github.com/<username>/smart-jarimatika.git
cd smart-jarimatika


Instal dependensi Laravel:

composer install
npm install && npm run dev


Buat file .env:

cp .env.example .env


Konfigurasi database di .env:

DB_DATABASE=jarimatika
DB_USERNAME=root
DB_PASSWORD=


Jalankan migrasi database:

php artisan migrate


Jalankan server Laravel:

php artisan serve


Akses di browser:
👉 http://127.0.0.1:8000

🧠 Mode Latihan (Integrasi AI)

Menggunakan MediaPipe Hands untuk mendeteksi jari kanan dan kiri secara real-time.

Aplikasi mengenali pola jari → menentukan angka 1–9.

Memberikan feedback otomatis “Benar” / “Salah”.

Skor disimpan ke database Laravel melalui API /api/score.

🧾 Lisensi

Proyek ini dikembangkan sebagai bagian dari Capstone Project – Teknik Informatika
Universitas Mercu Buana © 2025

✨ “Menggabungkan metode tradisional dengan teknologi modern untuk pembelajaran berhitung yang lebih interaktif.”