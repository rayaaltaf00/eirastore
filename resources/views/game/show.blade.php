@extends('layouts.storefront')

@section('title', 'Layanan Joki ' . $game->nama_game . ' - EiraStore')

@section('content')
<div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-400">
        <a href="{{ route('beranda') }}" class="hover:text-white transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Beranda
        </a>
        <span class="text-slate-600">/</span>
        <a href="{{ route('beranda') }}#katalog-game" class="hover:text-white transition-colors">Katalog Game</a>
        <span class="text-slate-600">/</span>
        <span class="text-brand-cyan font-bold">{{ $game->nama_game }}</span>
    </nav>

    <!-- Game Profile Hero Header -->
    <div class="relative overflow-hidden rounded-3xl bg-brand-card/95 border border-brand-border/80 p-6 sm:p-10 shadow-2xl">
        <!-- Glow accents -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-brand-blue/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-brand-cyan/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-8">
            <!-- Cover Art Thumbnail Poster -->
            <div class="w-36 h-36 sm:w-44 sm:h-44 rounded-2xl overflow-hidden bg-brand-surface border-2 border-brand-cyan/30 shadow-glow-blue shrink-0 relative group">
                <img src="{{ $game->gambar_url }}" 
                     alt="{{ $game->nama_game }}" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
            </div>

            <!-- Game Details -->
            <div class="flex-1 text-center md:text-left space-y-3.5">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <h1 class="text-2xl sm:text-4xl font-display font-black text-white tracking-tight">
                        {{ $game->nama_game }}
                    </h1>
                    @if($game->kategori)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-blue/25 text-brand-cyan border border-brand-blue/40 shadow-sm">
                            {{ $game->kategori }}
                        </span>
                    @endif
                </div>

                <p class="text-xs sm:text-sm text-slate-300 max-w-3xl leading-relaxed">
                    {{ $game->deskripsi ?: 'Pilih paket joki terbaik untuk game ' . $game->nama_game . ' dengan pengerjaan cepat oleh pro-player terpercaya, amanah, dan bergaransi.' }}
                </p>

                <!-- Value Badges / Keunggulan Layanan -->
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 pt-2 text-xs text-slate-300">
                    <div class="flex items-center space-x-1.5 bg-brand-surface/80 px-3.5 py-1.5 rounded-xl border border-brand-border">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="font-semibold">{{ $layananList->count() }} Pilihan Paket Layanan</span>
                    </div>
                    <div class="flex items-center space-x-1.5 bg-brand-surface/80 px-3.5 py-1.5 rounded-xl border border-brand-border">
                        <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Worker Standby & Cepat</span>
                    </div>
                    <div class="flex items-center space-x-1.5 bg-brand-surface/80 px-3.5 py-1.5 rounded-xl border border-brand-border">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Garansi Akun 100% Aman</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Layanan Joki Tersedia (Visual-First Showcase) -->
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-brand-border/60 pb-4">
            <div>
                <div class="inline-flex items-center space-x-2 text-[11px] font-bold text-brand-cyan uppercase tracking-wider mb-1">
                    <span class="w-2 h-2 rounded-full bg-brand-cyan animate-pulse"></span>
                    <span>KATALOG LAYANAN JOKI</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-display font-extrabold text-white flex items-center gap-2">
                    Pilihan Paket Joki {{ $game->nama_game }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Pilih paket layanan joki yang Anda inginkan untuk melihat rincian tarif, cakupan level, dan estimasi waktu.
                </p>
            </div>
            <a href="{{ route('beranda') }}#katalog-game" class="text-xs font-bold text-brand-cyan hover:underline inline-flex items-center gap-1.5 self-start sm:self-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Lihat Game Lainnya
            </a>
        </div>

        <!-- Grid Layanan Poster Cards (Commercial Gaming Store Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($layananList as $layanan)
                @php
                    $minPrice = $layanan->items->where('harga', '>', 0)->min('harga');
                @endphp
                <div x-data="{ showTarif: false }" class="group relative flex flex-col rounded-2xl bg-brand-card/95 border border-brand-border hover:border-brand-cyan/60 hover:shadow-[0_0_30px_rgba(14,165,233,0.18)] transition-all duration-300 overflow-hidden">
                    
                    <!-- Visual Poster Art Banner (3:2 / 16:10 Ratio) -->
                    <div class="relative w-full aspect-[16/10] overflow-hidden bg-brand-surface">
                        <img src="{{ $layanan->gambar_url }}" 
                             alt="{{ $layanan->nama_layanan }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        
                        <!-- Gradient Scrim for Contrast -->
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-card via-brand-card/40 to-transparent"></div>

                        <!-- Top Floating Badges -->
                        <div class="absolute top-3 inset-x-3 flex items-center justify-between gap-2 pointer-events-none">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider backdrop-blur-md shadow-sm
                                {{ $layanan->tipe_harga->value === 'tangga' ? 'bg-indigo-600/80 text-white border border-indigo-400/40' : '' }}
                                {{ $layanan->tipe_harga->value === 'progres' ? 'bg-sky-600/80 text-white border border-sky-400/40' : '' }}
                                {{ $layanan->tipe_harga->value === 'pilihan' ? 'bg-emerald-600/80 text-white border border-emerald-400/40' : '' }}">
                                {{ $layanan->tipe_harga->label() }}
                            </span>

                            @if($layanan->estimasi_waktu)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-white bg-black/60 backdrop-blur-md px-2.5 py-1 rounded-lg border border-white/15">
                                    ⏱ {{ $layanan->estimasi_waktu }}
                                </span>
                            @endif
                        </div>

                        <!-- Service Title Sitting on Poster Bottom -->
                        <div class="absolute bottom-2.5 inset-x-4">
                            <h3 class="text-lg font-display font-black text-white group-hover:text-brand-cyan transition-colors leading-snug drop-shadow-md">
                                {{ $layanan->nama_layanan }}
                            </h3>
                        </div>
                    </div>

                    <!-- Card Body Content -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <!-- Deskripsi Singkat Layanan -->
                            <p class="text-xs text-slate-300 leading-relaxed min-h-[2.5rem]">
                                {{ $layanan->deskripsi ?: $layanan->tipe_harga->deskripsi() }}
                            </p>

                            <!-- Items / Tiers Preview Pills -->
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1.5">
                                    Tingkatan / Cakupan:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($layanan->items->take(4) as $item)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] bg-brand-surface text-slate-200 border border-brand-border">
                                            {{ $item->nama }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500 italic">Item sedang disiapkan</span>
                                    @endforelse
                                    @if($layanan->items->count() > 4)
                                        <button @click="showTarif = !showTarif" type="button" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold text-brand-cyan bg-brand-cyan/10 hover:bg-brand-cyan/20 border border-brand-cyan/30 transition-colors">
                                            +{{ $layanan->items->count() - 4 }} lainnya
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Interactive Expandable Tier Price List -->
                            @if($layanan->items->count() > 0)
                                <div class="pt-2">
                                    <button @click="showTarif = !showTarif" type="button" class="w-full py-1.5 px-3 rounded-lg bg-brand-surface/80 hover:bg-brand-surface border border-brand-border text-[11px] font-semibold text-brand-cyan flex items-center justify-between transition-colors">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span x-text="showTarif ? 'Tutup Rincian Tarif' : 'Lihat Rincian Tarif ({{ $layanan->items->count() }} Item)'"></span>
                                        </span>
                                        <svg :class="showTarif ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <div x-show="showTarif" x-collapse class="mt-2.5 max-h-44 overflow-y-auto space-y-1 p-2 rounded-xl bg-brand-surface/90 border border-brand-border text-xs divide-y divide-brand-border/40">
                                        @foreach($layanan->items as $subItem)
                                            <div class="flex items-center justify-between py-1.5 px-1.5">
                                                <span class="text-slate-200 font-medium text-[11px]">{{ $subItem->nama }}</span>
                                                <span class="font-mono font-bold text-brand-cyan text-[11px]">
                                                    @if($subItem->harga > 0)
                                                        Rp {{ number_format($subItem->harga, 0, ',', '.') }}
                                                    @else
                                                        <span class="text-slate-400 font-normal italic">Kategori</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer: Starting Price & CTA -->
                        <div class="pt-4 border-t border-brand-border/70 flex items-center justify-between gap-3">
                            <div>
                                <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold">Mulai Dari</span>
                                @if($minPrice)
                                    <span class="text-lg font-display font-black text-white group-hover:text-brand-cyan transition-colors font-mono">
                                        Rp {{ number_format($minPrice, 0, ',', '.') }}
                                        @if($layanan->tipe_harga->value === 'tangga')
                                            <span class="text-[10px] font-normal text-slate-400 font-sans">/ btg</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Sesuai kalkulator</span>
                                @endif
                            </div>

                            <!-- Tombol Pilih Layanan (diarahkan ke alur order) -->
                            <a href="{{ route('beranda') }}#cara-pesan" 
                               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-bold hover:opacity-95 shadow-glow-blue transition-all group-hover:shadow-[0_0_20px_rgba(14,165,233,0.35)] shrink-0">
                                <span>Pilih Layanan</span>
                                <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-brand-card/60 rounded-3xl border border-brand-border p-8">
                    <div class="max-w-md mx-auto space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-brand-surface border border-brand-border flex items-center justify-center mx-auto text-brand-cyan">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h3 class="text-lg font-display font-bold text-white">Layanan Sedang Dipersiapkan</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Paket layanan joki untuk game ini sedang dikonfigurasi oleh tim EiraStore. Silakan cek kembali dalam waktu dekat atau pilih judul game lainnya.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('beranda') }}#katalog-game" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-xs font-bold text-slate-200 hover:text-white hover:border-brand-cyan/50 transition-all">
                                Kembali ke Katalog Game
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
