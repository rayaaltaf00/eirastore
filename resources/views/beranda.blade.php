<x-storefront-layout title="EiraStore - Layanan Joki Game Tepercaya">
    <div class="space-y-16 sm:space-y-24" x-data="{
        search: '{{ request('q') }}',
        activeTab: 'all',
        matchGame(name, cat) {
            const matchesSearch = name.toLowerCase().includes(this.search.toLowerCase());
            const matchesTab = this.activeTab === 'all' || cat.toLowerCase().includes(this.activeTab.toLowerCase());
            return matchesSearch && matchesTab;
        }
    }">

        <!-- ========================================================================= -->
        <!-- 1. BANNER PROMOSI (Carousel Slider)                                       -->
        <!-- ========================================================================= -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8">
            <div x-data="{
                currentSlide: 0,
                totalSlides: 3,
                autoplayInterval: null,
                init() {
                    this.autoplayInterval = setInterval(() => { this.next() }, 5000);
                },
                next() {
                    this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                },
                prev() {
                    this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                },
                goTo(index) {
                    this.currentSlide = index;
                    clearInterval(this.autoplayInterval);
                    this.autoplayInterval = setInterval(() => { this.next() }, 5000);
                }
            }" class="relative rounded-2xl overflow-hidden border border-brand-border shadow-2xl">
                <!-- Slide Container -->
                <div class="relative w-full aspect-[21/7] sm:aspect-[21/6] bg-brand-card">
                    {{-- 
                        BANNER PROMOSI: Ganti src dengan gambar banner custom dari Canva.
                        Simpan file banner di: public/images/banners/banner-1.jpg, banner-2.jpg, dst.
                        Ukuran rekomendasi: 1200x400px (rasio 3:1)
                    --}}

                    <!-- Slide 1: Promo Utama -->
                    <div x-show="currentSlide === 0" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0">
                        @if(file_exists(public_path('images/banners/banner-1.jpg')))
                            <img src="{{ asset('images/banners/banner-1.jpg') }}" alt="Promo EiraStore" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-r from-brand-blue/30 via-brand-card to-brand-cyan/20 flex items-center justify-center p-6 sm:p-10">
                                <div class="max-w-lg">
                                    <span class="inline-block px-3 py-1 rounded-full bg-brand-blue/20 text-brand-cyan text-xs font-bold mb-3">🔥 PROMO SPESIAL</span>
                                    <h2 class="text-xl sm:text-3xl font-black text-white leading-tight">Diskon 20% Joki Rank<br><span class="text-brand-cyan">Mobile Legends</span></h2>
                                    <p class="text-xs sm:text-sm text-slate-400 mt-2">Berlaku untuk semua paket joki rank. Proses cepat & bergaransi.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Slide 2: Keunggulan -->
                    <div x-show="currentSlide === 1" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0">
                        @if(file_exists(public_path('images/banners/banner-2.jpg')))
                            <img src="{{ asset('images/banners/banner-2.jpg') }}" alt="Keunggulan EiraStore" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-r from-emerald-900/40 via-brand-card to-brand-blue/20 flex items-center justify-center p-6 sm:p-10">
                                <div class="max-w-lg">
                                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold mb-3">✅ TERPERCAYA</span>
                                    <h2 class="text-xl sm:text-3xl font-black text-white leading-tight">Worker Pro & Berpengalaman<br><span class="text-emerald-400">Garansi 100%</span></h2>
                                    <p class="text-xs sm:text-sm text-slate-400 mt-2">Dikerjakan oleh pemain rank tinggi dengan win-rate terbaik.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Slide 3: Game Baru -->
                    <div x-show="currentSlide === 2" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0">
                        @if(file_exists(public_path('images/banners/banner-3.jpg')))
                            <img src="{{ asset('images/banners/banner-3.jpg') }}" alt="Game Baru EiraStore" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-r from-purple-900/40 via-brand-card to-amber-900/20 flex items-center justify-center p-6 sm:p-10">
                                <div class="max-w-lg">
                                    <span class="inline-block px-3 py-1 rounded-full bg-purple-500/20 text-purple-400 text-xs font-bold mb-3">🎮 GAME BARU</span>
                                    <h2 class="text-xl sm:text-3xl font-black text-white leading-tight">Valorant, Free Fire &<br><span class="text-amber-400">Game Populer Lainnya</span></h2>
                                    <p class="text-xs sm:text-sm text-slate-400 mt-2">Tersedia layanan joki untuk semua game favorit kamu.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Prev/Next Buttons -->
                    <button @click="prev(); goTo(currentSlide)" class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-colors z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="next(); goTo(currentSlide)" class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-colors z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Dot Indicators -->
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center space-x-2 z-10">
                        <template x-for="i in totalSlides" :key="i">
                            <button @click="goTo(i - 1)" :class="currentSlide === (i - 1) ? 'bg-brand-cyan w-6' : 'bg-white/40 w-2 hover:bg-white/60'" class="h-2 rounded-full transition-all duration-300"></button>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 2. HERO TEXT (Ringkas, di bawah banner)                                    -->
        <!-- ========================================================================= -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <!-- Glow background blob -->
            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-3/4 max-w-3xl h-64 bg-brand-blue/15 blur-[120px] rounded-full pointer-events-none -z-10"></div>

            <div class="relative rounded-3xl bg-gradient-to-b from-brand-card/90 to-brand-card/40 border border-brand-border/80 p-6 sm:p-12 overflow-hidden shadow-2xl">
                <!-- Grid pattern overlay -->
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b0a_1px,transparent_1px),linear-gradient(to_bottom,#1e293b0a_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl">
                    <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-brand-surface border border-brand-border text-brand-cyan text-xs font-semibold mb-6">
                        <span class="w-2 h-2 rounded-full bg-brand-cyan animate-pulse"></span>
                        <span>Layanan Joki Game #1 Indonesia</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15]">
                        Tingkatkan Rank Game <br>
                        <span class="bg-gradient-to-r from-blue-400 via-brand-cyan to-indigo-400 bg-clip-text text-transparent">Cepat, Aman & Bergaransi</span>
                    </h1>

                    <p class="mt-4 sm:mt-6 text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl">
                        EiraStore menyediakan layanan joki profesional untuk Mobile Legends, Valorant, Free Fire, dan game populer lainnya. Dikerjakan langsung oleh tim worker terverifikasi dengan sistem pelacakan progres transparan.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="#katalog-game" class="inline-flex items-center justify-center px-6 py-3.5 rounded-xl font-bold text-sm bg-brand-blue hover:bg-brand-blueHover text-white shadow-glow-blue transition-all duration-200 hover:-translate-y-0.5">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            Pilih Game Sekarang
                        </a>
                        <a href="{{ route('lacak.index') }}" class="inline-flex items-center justify-center px-6 py-3.5 rounded-xl font-bold text-sm bg-brand-surface hover:bg-slate-700 text-slate-200 border border-brand-border transition-all duration-200">
                            <svg class="w-4 h-4 mr-2 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Lacak Status Pesanan
                        </a>
                    </div>

                    <!-- Trust Points Bar -->
                    <div class="mt-10 pt-8 border-t border-brand-border/60 grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-500/10 text-brand-cyan flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">Proses Cepat</h4>
                                <p class="text-[11px] text-slate-400">Worker standby 24/7</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">100% Aman</h4>
                                <p class="text-[11px] text-slate-400">Privasi akun terjamin</p>
                            </div>
                        </div>

                        <div class="col-span-2 sm:col-span-1 flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">Garansi Rank</h4>
                                <p class="text-[11px] text-slate-400">Target selesai atau refund</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 3. KATALOG GAME                                                           -->
        <!-- ========================================================================= -->
        <section id="katalog-game" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="inline-flex items-center space-x-2 text-xs font-bold text-brand-cyan uppercase tracking-wider mb-2">
                        <span>🔥 POPULER SEKARANG</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Daftar Game Tersedia</h2>
                    <p class="text-sm text-slate-400 mt-1">Pilih game favoritmu dan tentukan paket joki yang kamu butuhkan.</p>
                </div>
            </div>

            <!-- Category Filter Tabs -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-none mb-6">
                <button 
                    @click="activeTab = 'all'" 
                    :class="activeTab === 'all' ? 'bg-brand-blue text-white shadow-glow-blue' : 'bg-brand-surface text-slate-400 hover:text-white border border-brand-border'"
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200">
                    Semua Game
                </button>
                <button 
                    @click="activeTab = 'moba'" 
                    :class="activeTab === 'moba' ? 'bg-brand-blue text-white shadow-glow-blue' : 'bg-brand-surface text-slate-400 hover:text-white border border-brand-border'"
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200">
                    MOBA
                </button>
                <button 
                    @click="activeTab = 'fps'" 
                    :class="activeTab === 'fps' ? 'bg-brand-blue text-white shadow-glow-blue' : 'bg-brand-surface text-slate-400 hover:text-white border border-brand-border'"
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200">
                    FPS & Shooter
                </button>
                <button 
                    @click="activeTab = 'rpg'" 
                    :class="activeTab === 'rpg' ? 'bg-brand-blue text-white shadow-glow-blue' : 'bg-brand-surface text-slate-400 hover:text-white border border-brand-border'"
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200">
                    RPG / Battle Royale
                </button>
            </div>

            <!-- Game Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @if(isset($games) && $games->count() > 0)
                    @foreach($games as $game)
                        <div x-show="matchGame('{{ $game->nama_game }}', '{{ $game->kategori ?? 'Game' }}')" x-transition>
                            <x-game-card :game="$game" />
                        </div>
                    @endforeach
                @else
                    <!-- Default Showcase Games (Ketika database belum memiliki seed game) -->
                    <div x-show="matchGame('Mobile Legends: Bang Bang', 'MOBA')" x-transition>
                        <x-game-card 
                            nama="Mobile Legends" 
                            kategori="MOBA" 
                            developer="Moonton"
                            thumbnail="https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500&auto=format&fit=crop&q=80"
                            href="{{ route('beranda') }}"
                        />
                    </div>
                    <div x-show="matchGame('Valorant', 'FPS')" x-transition>
                        <x-game-card 
                            nama="Valorant" 
                            kategori="FPS" 
                            developer="Riot Games"
                            thumbnail="https://images.unsplash.com/photo-1511512578047-dfb367046420?w=500&auto=format&fit=crop&q=80"
                            href="{{ route('beranda') }}"
                        />
                    </div>
                    <div x-show="matchGame('Free Fire', 'Battle Royale')" x-transition>
                        <x-game-card 
                            nama="Free Fire" 
                            kategori="FPS" 
                            developer="Garena"
                            thumbnail="https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=500&auto=format&fit=crop&q=80"
                            href="{{ route('beranda') }}"
                        />
                    </div>
                    <div x-show="matchGame('Honor of Kings', 'MOBA')" x-transition>
                        <x-game-card 
                            nama="Honor of Kings" 
                            kategori="MOBA" 
                            developer="Level Infinite"
                            thumbnail="https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=500&auto=format&fit=crop&q=80"
                            href="{{ route('beranda') }}"
                        />
                    </div>
                    <div x-show="matchGame('Genshin Impact', 'RPG')" x-transition>
                        <x-game-card 
                            nama="Genshin Impact" 
                            kategori="RPG" 
                            developer="HoYoverse"
                            thumbnail="https://images.unsplash.com/photo-1563089145-599997674d42?w=500&auto=format&fit=crop&q=80"
                            href="{{ route('beranda') }}"
                        />
                    </div>
                @endif
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 4. ALUR CARA PEMESANAN                                                    -->
        <!-- ========================================================================= -->
        <section id="cara-pesan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24 rounded-3xl bg-brand-card border border-brand-border p-6 sm:p-10">
            <div class="text-center max-w-xl mx-auto mb-10">
                <span class="text-xs font-bold text-brand-cyan uppercase tracking-wider">CARA PESAN</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">4 Langkah Mudah Joki di EiraStore</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-2">Sistem otomatis dan transparan dari awal pemesanan hingga rank tercapai.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Step 1 -->
                <div class="relative p-5 rounded-2xl bg-brand-surface border border-brand-border/80 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-brand-cyan flex items-center justify-center font-black text-sm mb-4">
                            01
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Pilih Game & Paket</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">Tentukan game dan varian joki (joki per bintang, per rank, atau paket hemat).</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative p-5 rounded-2xl bg-brand-surface border border-brand-border/80 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-brand-cyan flex items-center justify-center font-black text-sm mb-4">
                            02
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Input Data Akun</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">Masukkan User ID / info login akun dengan enkripsi keamanan terjamin.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative p-5 rounded-2xl bg-brand-surface border border-brand-border/80 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-brand-cyan flex items-center justify-center font-black text-sm mb-4">
                            03
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Bayar & Upload Bukti</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">Pilih metode QRIS atau transfer bank lalu unggah struk bukti bayar Anda.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="relative p-5 rounded-2xl bg-brand-surface border border-brand-border/80 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-brand-cyan flex items-center justify-center font-black text-sm mb-4">
                            04
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Pantau & Selesai</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">Worker langsung bertanding. Pantau status realtime via kode tracking pesanan.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 5. KEUNGGULAN / KENAPA EIRASTORE                                          -->
        <!-- ========================================================================= -->
        <section id="keunggulan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-2xl bg-brand-card border border-brand-border">
                <div class="w-10 h-10 rounded-xl bg-brand-blue/20 text-brand-cyan flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-base font-bold text-white mb-1.5">Kerahasiaan Akun 100%</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Data login Anda dienkripsi dan hanya dapat diakses oleh worker bertugas yang telah menandatangani persetujuan integritas.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-card border border-brand-border">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h3 class="text-base font-bold text-white mb-1.5">Worker Pro Teruji</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Semua worker merupakan mantan atlet esports atau pemain rank leaderboard (Mythical Immortal / Radiant) dengan win-rate tinggi.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-card border border-brand-border">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                </div>
                <h3 class="text-base font-bold text-white mb-1.5">Layanan Customer Responsif</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Kendala login, kode verifikasi 2FA, atau permintaan jadwal joki langsung ditanggapi secara cepat dan profesional.</p>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 6. CTA BOTTOM                                                             -->
        <!-- ========================================================================= -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative rounded-3xl bg-gradient-to-r from-brand-blue/20 via-brand-card to-brand-card border border-brand-blue/40 p-8 sm:p-12 overflow-hidden text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="text-xl sm:text-2xl font-black text-white">Siap Pamer Rank Tertinggi ke Temanmu?</h3>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-xl">Pesan sekarang dan nikmati proses pengerjaan tanpa ribet dengan harga paling kompetitif.</p>
            </div>
            <a href="#katalog-game" class="px-6 py-3.5 rounded-xl font-bold text-sm bg-brand-blue hover:bg-brand-blueHover text-white shadow-glow-blue whitespace-nowrap transition-all duration-200 hover:-translate-y-0.5">
                Mulai Pesan Sekarang →
            </a>
        </section>

    </div>
</x-storefront-layout>
