<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'EiraStore - Layanan Joki Game Cepat & Terpercaya' }}</title>

    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- 
        Smart Navigation & Scroll Helper:
        - Jika berada di Beranda: smooth scroll ke section target dengan offset dinamis.
        - Jika berada di halaman lain (misal /lacak): redirect otomatis ke beranda dengan hash.
    --}}
    <script>
        const HOME_URL = "{{ route('beranda') }}";

        function scrollToSection(id) {
            const el = document.getElementById(id);
            if (!el) return false;
            // Hitung tinggi header sticky secara dinamis
            const header = document.querySelector('header');
            const headerHeight = header ? header.offsetHeight : 0;
            // Padding ekstra agar section tidak mepet header (24px breathing room)
            const padding = 24;
            const targetY = el.getBoundingClientRect().top + window.scrollY - headerHeight - padding;
            window.scrollTo({ top: Math.max(0, targetY), behavior: 'smooth' });
            if (history.pushState) {
                history.pushState(null, '', '#' + id);
            }
            window.dispatchEvent(new CustomEvent('section-change', { detail: id }));
            return true;
        }

        function navigateToSection(id) {
            const success = scrollToSection(id);
            if (!success) {
                window.location.href = HOME_URL + '#' + id;
            }
        }

        function navigateToHome() {
            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
            const homePath = new URL(HOME_URL, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            if (currentPath === homePath) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                if (history.pushState) {
                    history.pushState(null, '', homePath);
                }
                window.dispatchEvent(new CustomEvent('section-change', { detail: 'beranda' }));
            } else {
                window.location.href = HOME_URL;
            }
        }

        // Handle hash on page load (jika user datang dari halaman lain dengan #anchor)
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                setTimeout(function() { scrollToSection(hash); }, 350);
            }
        });
    </script>
</head>
<body class="bg-brand-darkest text-slate-100 min-h-screen flex flex-col font-sans selection:bg-brand-blue selection:text-white">

    <!-- ── Navbar Storefront (2-Row ala Ourastore) ── -->
    <header 
        x-data="{ 
            mobileMenu: false, 
            userDropdown: false,
            activeSection: '{{ request()->routeIs('lacak.index') ? 'lacak' : 'beranda' }}',
            isHomePage: {{ request()->routeIs('beranda') ? 'true' : 'false' }},
            updateActiveSection() {
                if (!this.isHomePage) return;
                const header = document.querySelector('header');
                const threshold = (header ? header.offsetHeight : 100) + 100;

                // Jika scroll sudah di dekat bagian paling bawah
                if ((window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 150)) {
                    this.activeSection = 'keunggulan';
                    return;
                }

                const keunggulan = document.getElementById('keunggulan');
                const caraPesan = document.getElementById('cara-pesan');
                const katalog = document.getElementById('katalog-game');

                if (keunggulan && keunggulan.getBoundingClientRect().top <= threshold) {
                    this.activeSection = 'keunggulan';
                } else if (caraPesan && caraPesan.getBoundingClientRect().top <= threshold) {
                    this.activeSection = 'cara-pesan';
                } else if (katalog && katalog.getBoundingClientRect().top <= threshold) {
                    this.activeSection = 'katalog-game';
                } else {
                    this.activeSection = 'beranda';
                }
            }
        }" 
        x-init="
            if (isHomePage) {
                window.addEventListener('scroll', () => updateActiveSection(), { passive: true });
                window.addEventListener('section-change', (e) => { activeSection = e.detail; });
                updateActiveSection();
            }
        "
        class="sticky top-0 z-50 bg-brand-darkest/95 backdrop-blur-md border-b border-brand-border">
        
        <!-- ═══ Baris 1: Logo + Search Bar + Auth ═══ -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand -->
                <a href="{{ route('beranda') }}" @click.prevent="navigateToHome()" class="flex items-center space-x-3 group flex-shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-blue to-brand-cyan flex items-center justify-center shadow-glow-blue group-hover:scale-105 transition-transform duration-200">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 6h10a6 6 0 0 1 5.92 5.09l.86 4.74A3.5 3.5 0 0 1 20.34 20H18a3 3 0 0 1-2.83-2l-.5-1.5a1 1 0 0 0-.95-.68h-3.44a1 1 0 0 0-.95.68l-.5 1.5A3 3 0 0 1 6 20H3.66a3.5 3.5 0 0 1-3.44-4.17l.86-4.74A6 6 0 0 1 7 6zm0 2a4 4 0 0 0-3.95 3.39l-.86 4.74A1.5 1.5 0 0 0 3.66 18H6a1 1 0 0 0 .95-.68l.5-1.5A3 3 0 0 1 10.28 14h3.44a3 3 0 0 1 2.83 1.82l.5 1.5a1 1 0 0 0 .95.68h2.34a1.5 1.5 0 0 0 1.47-1.87l-.86-4.74A4 4 0 0 0 17 8H7zm2 2h2v2h2v2h-2v2H9v-2H7v-2h2v-2zm7.5 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5zm2.5 2.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col leading-none">
                        <div class="flex items-center gap-1.5">
                            <span class="font-display font-black text-xl tracking-wider text-white">
                                EIRA<span class="text-brand-cyan">STORE</span>
                            </span>
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand-cyan shadow-[0_0_8px_#0ea5e9]"></span>
                        </div>
                        <span class="text-[9px] font-bold tracking-[0.22em] uppercase text-slate-400 mt-1">
                            Joki & Game Services
                        </span>
                    </div>
                </a>

                <!-- Search Bar (Tengah, Desktop) -->
                <div class="hidden md:flex flex-1 max-w-md mx-6">
                    <form action="{{ route('beranda') }}#katalog-game" method="GET" class="w-full relative">
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ request('q') }}"
                            placeholder="Cari game atau layanan joki..." 
                            class="w-full bg-brand-surface border-brand-border text-slate-100 placeholder-slate-500 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:ring-1 focus:ring-brand-blue focus:border-brand-blue transition-colors">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </form>
                </div>

                <!-- Auth Buttons / User Dropdown -->
                <div class="flex items-center space-x-3 flex-shrink-0">
                    
                    @guest
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-brand-surface transition-colors border border-transparent hover:border-brand-border">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white bg-brand-blue hover:bg-brand-blueHover shadow-glow-blue transition-all duration-150">
                            Daftar
                        </a>
                    @else
                        <div class="relative" @click.outside="userDropdown = false">
                            <button @click="userDropdown = !userDropdown" class="flex items-center space-x-3 px-3 py-1.5 rounded-xl bg-brand-card hover:bg-brand-surface border border-brand-border transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-blue to-brand-cyan flex items-center justify-center font-bold text-white text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                                </div>
                                <div class="hidden sm:flex flex-col text-left">
                                    <span class="text-xs font-semibold text-white line-clamp-1 max-w-[120px]">{{ Auth::user()->nama }}</span>
                                    <span class="text-[10px] text-brand-cyan font-medium capitalize">{{ Auth::user()->role->value }}</span>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="userDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userDropdown" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-xl bg-brand-card border border-brand-border shadow-card-md py-1.5 z-50">
                                
                                <div class="px-4 py-2 border-b border-brand-border">
                                    <p class="text-xs font-medium text-slate-400">Login sebagai</p>
                                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->email }}</p>
                                </div>

                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-brand-cyan hover:bg-brand-surface hover:text-white transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        Panel Admin
                                    </a>
                                @elseif(Auth::user()->isWorker())
                                    <a href="{{ route('worker.pesanan') }}" class="flex items-center px-4 py-2 text-sm text-brand-cyan hover:bg-brand-surface hover:text-white transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        Pesanan Saya (Worker)
                                    </a>
                                @else
                                    <a href="{{ route('pesanan.saya') }}" class="flex items-center px-4 py-2 text-sm text-brand-cyan hover:bg-brand-surface hover:text-white transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        Pesanan Saya
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('logout') }}" class="border-t border-brand-border mt-1 pt-1">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-400 hover:bg-brand-surface hover:text-red-300 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest

                    <!-- Hamburger Button (Mobile) -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-brand-surface transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ═══ Baris 2: Navigasi Utama (Desktop) ═══ -->
        <div class="hidden md:block border-t border-brand-border/50 bg-brand-darkest/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center space-x-1 h-11">
                    <a href="{{ route('beranda') }}" 
                       @click.prevent="navigateToHome()" 
                       :class="activeSection === 'beranda' ? 'text-brand-cyan bg-brand-surface/70' : 'text-slate-300 hover:text-white hover:bg-brand-surface/40'"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Beranda
                    </a>
                    <a href="{{ route('beranda') }}#katalog-game" 
                       @click.prevent="navigateToSection('katalog-game')" 
                       :class="activeSection === 'katalog-game' ? 'text-brand-cyan bg-brand-surface/70' : 'text-slate-300 hover:text-white hover:bg-brand-surface/40'"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Katalog Game
                    </a>
                    <a href="{{ route('beranda') }}#cara-pesan" 
                       @click.prevent="navigateToSection('cara-pesan')" 
                       :class="activeSection === 'cara-pesan' ? 'text-brand-cyan bg-brand-surface/70' : 'text-slate-300 hover:text-white hover:bg-brand-surface/40'"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Cara Pesan
                    </a>
                    <a href="{{ route('beranda') }}#keunggulan" 
                       @click.prevent="navigateToSection('keunggulan')" 
                       :class="activeSection === 'keunggulan' ? 'text-brand-cyan bg-brand-surface/70' : 'text-slate-300 hover:text-white hover:bg-brand-surface/40'"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Keunggulan
                    </a>
                    <a href="{{ route('lacak.index') }}" 
                       :class="activeSection === 'lacak' ? 'text-brand-cyan bg-brand-surface/70' : 'text-slate-300 hover:text-white hover:bg-brand-surface/40'"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Lacak Pesanan
                    </a>
                </nav>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenu" 
             x-transition 
             class="md:hidden border-t border-brand-border bg-brand-darkest px-4 pt-3 pb-5 space-y-2">
            <!-- Mobile Search -->
            <form action="{{ route('beranda') }}#katalog-game" method="GET" class="relative mb-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari game atau layanan..." class="w-full bg-brand-surface border-brand-border text-slate-100 placeholder-slate-500 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:ring-1 focus:ring-brand-blue focus:border-brand-blue">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>

            <a href="{{ route('beranda') }}" 
               @click.prevent="mobileMenu = false; navigateToHome()" 
               :class="activeSection === 'beranda' ? 'text-brand-cyan bg-brand-surface font-semibold border-l-2 border-brand-cyan' : 'text-slate-200 hover:bg-brand-surface hover:text-white'"
               class="block px-3 py-2 rounded-lg text-base transition-colors">
                Beranda
            </a>
            <a href="{{ route('beranda') }}#katalog-game" 
               @click.prevent="mobileMenu = false; setTimeout(() => navigateToSection('katalog-game'), 150)" 
               :class="activeSection === 'katalog-game' ? 'text-brand-cyan bg-brand-surface font-semibold border-l-2 border-brand-cyan' : 'text-slate-200 hover:bg-brand-surface hover:text-white'"
               class="block px-3 py-2 rounded-lg text-base transition-colors">
                Katalog Game
            </a>
            <a href="{{ route('beranda') }}#cara-pesan" 
               @click.prevent="mobileMenu = false; setTimeout(() => navigateToSection('cara-pesan'), 150)" 
               :class="activeSection === 'cara-pesan' ? 'text-brand-cyan bg-brand-surface font-semibold border-l-2 border-brand-cyan' : 'text-slate-200 hover:bg-brand-surface hover:text-white'"
               class="block px-3 py-2 rounded-lg text-base transition-colors">
                Cara Pesan
            </a>
            <a href="{{ route('beranda') }}#keunggulan" 
               @click.prevent="mobileMenu = false; setTimeout(() => navigateToSection('keunggulan'), 150)" 
               :class="activeSection === 'keunggulan' ? 'text-brand-cyan bg-brand-surface font-semibold border-l-2 border-brand-cyan' : 'text-slate-200 hover:bg-brand-surface hover:text-white'"
               class="block px-3 py-2 rounded-lg text-base transition-colors">
                Keunggulan
            </a>
            <a href="{{ route('lacak.index') }}" 
               :class="activeSection === 'lacak' ? 'text-brand-cyan bg-brand-surface font-semibold border-l-2 border-brand-cyan' : 'text-slate-200 hover:bg-brand-surface hover:text-white'"
               class="block px-3 py-2 rounded-lg text-base transition-colors">
                Lacak Pesanan
            </a>

            @guest
                <div class="pt-3 border-t border-brand-border flex gap-2">
                    <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2.5 rounded-lg text-sm font-medium text-slate-200 bg-brand-surface border border-brand-border">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-brand-blue">
                        Daftar
                    </a>
                </div>
            @else
                <div class="pt-3 border-t border-brand-border space-y-1">
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-brand-cyan hover:bg-brand-surface">
                            Panel Admin
                        </a>
                    @elseif(Auth::user()->isWorker())
                        <a href="{{ route('worker.pesanan') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-brand-cyan hover:bg-brand-surface">
                            Pesanan Saya (Worker)
                        </a>
                    @else
                        <a href="{{ route('pesanan.saya') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-brand-cyan hover:bg-brand-surface">
                            Pesanan Saya
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-red-400 hover:bg-brand-surface">
                            Keluar ({{ Auth::user()->nama }})
                        </button>
                    </form>
                </div>
            @endguest
        </div>
    </header>

    <!-- ── Flash Message Notifikasi ── -->
    @if(session('success') || session('error') || session('status_error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-950/70 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error') || session('status_error'))
                <div class="p-4 rounded-xl bg-red-950/70 border border-red-500/30 text-red-300 text-sm flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-red-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <span>{{ session('error') ?? session('status_error') }}</span>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- ── Main Content Slot ── -->
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- ── Footer Modern (Inspirasi Ourastore) ── -->
    <footer class="mt-20 border-t border-brand-border bg-brand-card/60 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                
                <!-- Kolom 1: Profil Brand -->
                <div class="space-x-0 space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-blue to-brand-cyan flex items-center justify-center shadow-glow-blue">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M7 6h10a6 6 0 0 1 5.92 5.09l.86 4.74A3.5 3.5 0 0 1 20.34 20H18a3 3 0 0 1-2.83-2l-.5-1.5a1 1 0 0 0-.95-.68h-3.44a1 1 0 0 0-.95.68l-.5 1.5A3 3 0 0 1 6 20H3.66a3.5 3.5 0 0 1-3.44-4.17l.86-4.74A6 6 0 0 1 7 6z"/></svg>
                        </div>
                        <span class="font-display font-black text-lg tracking-wider text-white">EIRA<span class="text-brand-cyan">STORE</span></span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Platform penyedia layanan joki game terpercaya, cepat, dan profesional. Dikerjakan oleh worker terverifikasi dengan keamanan akun terjamin 100%.
                    </p>
                    <div class="flex items-center space-x-3 pt-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            ● Layanan Aktif 24/7
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-brand-blue/10 text-brand-cyan border border-brand-blue/20">
                            🛡️ Garansi 100%
                        </span>
                    </div>
                </div>

                <!-- Kolom 2: Navigasi Cepat -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Navigasi</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('beranda') }}" @click.prevent="navigateToHome()" class="text-slate-400 hover:text-brand-cyan transition-colors">Beranda</a></li>
                        <li><a href="{{ route('beranda') }}#katalog-game" @click.prevent="navigateToSection('katalog-game')" class="text-slate-400 hover:text-brand-cyan transition-colors">Katalog Game</a></li>
                        <li><a href="{{ route('beranda') }}#cara-pesan" @click.prevent="navigateToSection('cara-pesan')" class="text-slate-400 hover:text-brand-cyan transition-colors">Panduan Pemesanan</a></li>
                        <li><a href="{{ route('beranda') }}#keunggulan" @click.prevent="navigateToSection('keunggulan')" class="text-slate-400 hover:text-brand-cyan transition-colors">Keunggulan Layanan</a></li>
                        <li><a href="{{ route('lacak.index') }}" class="text-slate-400 hover:text-brand-cyan transition-colors">Lacak Pesanan</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Metode Pembayaran & Keamanan -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Pembayaran</h3>
                    <p class="text-xs text-slate-400 mb-3">Mendukung berbagai metode pembayaran instan dan aman:</p>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="p-2 rounded-lg bg-brand-surface border border-brand-border text-center text-xs font-bold text-slate-300">QRIS</div>
                        <div class="p-2 rounded-lg bg-brand-surface border border-brand-border text-center text-xs font-bold text-slate-300">BCA</div>
                        <div class="p-2 rounded-lg bg-brand-surface border border-brand-border text-center text-xs font-bold text-slate-300">MANDIRI</div>
                        <div class="p-2 rounded-lg bg-brand-surface border border-brand-border text-center text-xs font-bold text-slate-300">DANA</div>
                        <div class="p-2 rounded-lg bg-brand-surface border border-brand-border text-center text-xs font-bold text-slate-300">OVO</div>
                        <div class="p-2 rounded-lg bg-brand-surface border border-brand-border text-center text-xs font-bold text-slate-300">GOPAY</div>
                    </div>
                </div>

                <!-- Kolom 4: Bantuan & Kontak CS -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Bantuan & Kontak</h3>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li class="flex items-start space-x-3">
                            <span class="text-brand-cyan font-bold">WA:</span>
                            <span>+62 812-0000-0000 (Customer Care)</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-brand-cyan font-bold">Jam:</span>
                            <span>Setiap Hari (09.00 - 23.00 WIB)</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-brand-cyan font-bold">Email:</span>
                            <span>support@eirastore.com</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="mt-12 pt-8 border-t border-brand-border flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© 2026 EiraStore. Seluruh hak cipta dilindungi.</p>
                <div class="flex items-center space-x-4">
                    <span class="text-slate-600">|</span>
                    <span>Aman & Terpercaya</span>
                    <span class="text-slate-600">|</span>
                    <span>Versi 1.0</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
