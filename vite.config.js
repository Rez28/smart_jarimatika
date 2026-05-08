import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js' // Sesuaikan jika ada file js utama lain
            ],
            refresh: true,
        }),
    ],
    // TAMBAHKAN BLOK SERVER INI
    server: {
        host: '0.0.0.0',
        hmr: {
            host: '192.168.0.9' // Masukkan IP laptopmu di sini
        }
    }
});