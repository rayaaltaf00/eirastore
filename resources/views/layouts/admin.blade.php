<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Panel Admin - EiraStore' }}</title>

    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-darkest text-slate-100 min-h-screen flex font-sans selection:bg-brand-blue selection:text-white" x-data="{ sidebarOpen: false }">

    <!-- ── Mobile Sidebar Overlay ── -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- ── Sidebar Navigasi Admin ── -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-brand-card border-r border-brand-border flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0">
        
        <!-- Sidebar Header / Logo -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-brand-border">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-blue to-brand-cyan flex items-center justify-center shadow-glow-blue">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M7 6h10a6 6 0 0 1 5.92 5.09l.86 4.74A3.5 3.5 0 0 1 20.34 20H18a3 3 0 0 1-2.83-2l-.5-1.5a1 1 0 0 0-.95-.68h-3.44a1 1 0 0 0-.95.68l-.5 1.5A3 3 0 0 1 6 20H3.66a3.5 3.5 0 0 1-3.44-4.17l.86-4.74A6 6 0 0 1 7 6z"/></svg>
                </div>
                <div class="flex flex-col leading-none">
                    <span class="font-display font-black text-base tracking-wider text-white">EIRA<span class="text-brand-cyan">STORE</span></span>
                    <span class="text-[9px] font-bold uppercase tracking-[0.18em] text-amber-400 mt-1">Control Panel</span>
                </div>
            </a>
            <!-- Close button on mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Sidebar Menu Items -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Menu Utama</div>

            <!-- Dashboard Link -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-brand-blue text-white shadow-glow-blue' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Katalog & Layanan</div>

            <!-- Kelola Game -->
            <a href="{{ route('admin.games.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('admin.games.*') ? 'bg-brand-blue text-white shadow-glow-blue font-semibold' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.games.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Kelola Game
            </a>

            <!-- Kelola Layanan -->
            <a href="{{ route('admin.layanan.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ (request()->routeIs('admin.layanan.*') || request()->routeIs('admin.layanan.items.*')) ? 'bg-brand-blue text-white shadow-glow-blue font-semibold' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0 {{ (request()->routeIs('admin.layanan.*') || request()->routeIs('admin.layanan.items.*')) ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Kelola Layanan
            </a>

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Transaksi & Tim</div>

            <!-- Rekening Pembayaran -->
            <a href="{{ route('admin.metode-pembayaran.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('admin.metode-pembayaran.*') ? 'bg-brand-blue text-white shadow-glow-blue font-semibold' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.metode-pembayaran.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Rekening Pembayaran
            </a>

            <!-- Pesanan & Transaksi -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'semua']) }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('admin.pesanan.*') && request('status') === 'semua' ? 'bg-brand-blue text-white shadow-glow-blue font-semibold' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.pesanan.*') && request('status') === 'semua' ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Semua Pesanan
            </a>

            <!-- Antrean Verifikasi & Tindakan -->
            <a href="{{ route('admin.pesanan.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('admin.pesanan.*') && request('status') !== 'semua' ? 'bg-brand-blue text-white shadow-glow-blue font-semibold' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.pesanan.*') && request('status') !== 'semua' ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Antrean Tindakan Admin
            </a>

            <!-- Laporan Hasil Worker (A-26) -->
            <a href="{{ route('admin.laporan-worker.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('admin.laporan-worker.*') ? 'bg-brand-blue text-white shadow-glow-blue font-semibold' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.laporan-worker.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Laporan Hasil Worker
            </a>

            <!-- Manajemen Worker -->
            <a href="{{ route('admin.workers.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('admin.workers.*') ? 'bg-brand-blue text-white shadow-glow-blue font-semibold' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.workers.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Manajemen Worker
            </a>

            <!-- Laporan & Keuangan (A-30) -->
            <a href="{{ route('admin.laporan-transaksi.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('admin.laporan-transaksi.*') ? 'bg-brand-blue text-white shadow-glow-blue font-semibold' : 'text-slate-300 hover:bg-brand-surface hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.laporan-transaksi.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan & Pendapatan
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-brand-border space-y-2">
            <!-- Link ke Storefront Customer -->
            <a href="{{ route('beranda') }}" target="_blank" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium text-slate-400 hover:text-brand-cyan hover:bg-brand-surface transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Buka Website Customer
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-3 py-2 rounded-lg text-xs font-medium text-red-400 hover:text-red-300 hover:bg-brand-surface transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar Sesi
                </button>
            </form>
        </div>
    </aside>

    <!-- ── Main Content Area ── -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Topbar -->
        <header class="h-20 bg-brand-card/90 border-b border-brand-border flex items-center justify-between px-4 sm:px-8">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-brand-surface">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-white">{{ $header ?? 'Dashboard' }}</h1>
                    <p class="text-xs text-slate-400 hidden sm:block">Kelola seluruh aktivitas layanan dan tim EiraStore</p>
                </div>
            </div>

            <!-- Profile Info & Status -->
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    Administrator
                </span>
                <div class="flex items-center space-x-3 pl-2 border-l border-brand-border">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-blue to-brand-cyan flex items-center justify-center font-bold text-white text-sm">
                        {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-xs font-semibold text-white">{{ Auth::user()->nama }}</p>
                        <p class="text-[10px] text-slate-400">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Message Alerts -->
        @if(session('success') || session('error'))
            <div class="px-4 sm:px-8 pt-4">
                @if(session('success'))
                    <div class="p-4 rounded-xl bg-emerald-950/70 border border-emerald-500/30 text-emerald-300 text-sm flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-4 rounded-xl bg-red-950/70 border border-red-500/30 text-red-300 text-sm flex items-center space-x-2">
                        <svg class="w-5 h-5 text-red-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-8">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
