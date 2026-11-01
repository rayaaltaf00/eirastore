<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EiraStore') }} - Masuk / Daftar</title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo-sm.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-100 bg-brand-darkest antialiased min-h-screen selection:bg-brand-blue selection:text-white relative overflow-x-hidden flex flex-col justify-between">
        
        <!-- Global Toast Notification (Tahap 2) -->
        <x-toast-notification />

        <!-- Subtle Ambient Lighting -->
        <div class="pointer-events-none fixed inset-0 flex justify-center -z-10 overflow-hidden">
            <div class="w-[600px] h-[600px] rounded-full bg-brand-blue/10 blur-[130px] -top-32"></div>
        </div>

        <!-- Top Header Back Button -->
        <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
            <a href="{{ route('beranda') }}" class="flex items-center space-x-2.5 group">
                <img src="{{ asset('images/logo.png') }}" alt="EiraStore Logo" class="w-9 h-9 object-contain drop-shadow transition-transform group-hover:scale-105">
                <div class="flex flex-col">
                    <span class="text-lg font-black tracking-wider text-white">EIRA<span class="text-brand-cyan">STORE</span></span>
                    <span class="text-[9px] uppercase tracking-widest text-slate-400 font-semibold -mt-1">Joki Game Service</span>
                </div>
            </a>

            <a href="{{ route('beranda') }}" class="inline-flex items-center text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Beranda
            </a>
        </header>

        <!-- Center Form Card -->
        <main class="flex-1 flex flex-col justify-center items-center px-4 py-8">
            <div class="w-full sm:max-w-md bg-brand-card border border-brand-border rounded-2xl p-6 sm:p-8 shadow-2xl relative">
                <!-- Border Top Highlight -->
                <div class="absolute top-0 left-6 right-6 h-[2px] bg-gradient-to-r from-transparent via-brand-cyan to-transparent"></div>
                {{ $slot }}
            </div>
        </main>

        <!-- Minimal Footer -->
        <footer class="py-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} EiraStore. Layanan Joki Game Tepercaya.
        </footer>

    </body>
</html>
