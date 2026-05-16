<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Smart Jarimatika') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* ==================== PROFESSIONAL LOADING SPINNER ==================== */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse-text {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        @keyframes dots-animation {

            0%,
            20% {
                content: '';
            }

            40% {
                content: '.';
            }

            60% {
                content: '..';
            }

            100% {
                content: '...';
            }
        }

        #loadingSpinner {
            width: 80px;
            height: 80px;
            border: 8px solid rgba(56, 189, 248, 0.1);
            border-top: 8px solid #38BDF8;
            border-right: 8px solid #F79A19;
            border-bottom: 8px solid #10B981;
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
            margin: 0 auto 2rem;
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.2);
        }

        #loadingText {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: 1px;
            animation: pulse-text 1.5s ease-in-out infinite;
        }

        .loading-dots::after {
            content: '';
            animation: dots-animation 1.5s steps(4, end) infinite;
        }
    </style>
</head>

<body class="font-sans antialiased" style="font-family: 'Fredoka', sans-serif; background: #f9f4e9;">
    <!-- Professional Loading Screen Overlay -->
    <div id="loadingOverlay"
        class="fixed inset-0 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-500"
        style="z-index: 99999; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px);">
        <div class="text-center">
            <!-- Spinner -->
            <div id="loadingSpinner"></div>

            <!-- Loading Text with Animated Dots -->
            <div id="loadingText">Menyiapkan Arena<span class="loading-dots"></span></div>
        </div>
    </div>

    <script>
        const loadingOverlay = document.getElementById('loadingOverlay');

        // Show loading overlay on link click
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.endsWith('#')) {
                loadingOverlay.style.opacity = '1';
                loadingOverlay.style.pointerEvents = 'auto';
            }
        });

        // Hide loading overlay when page finishes loading
        window.addEventListener('load', function() {
            setTimeout(function() {
                loadingOverlay.style.opacity = '0';
                loadingOverlay.style.pointerEvents = 'none';
            }, 300);
        });
    </script>

    <div class="min-h-screen bg-gray-100">
        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>
    </div>
</body>

</html>
